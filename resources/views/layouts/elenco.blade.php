@extends('layouts.app')

@push('styles')
<style>
    /* Stile per il popup di SweetAlert su mobile */
    @media (max-width: 576px) {
        .swal2-popup {
            width: 95% !important;
        }
    }
    .autocomplete-suggestions {
        border: 1px solid #ddd;
        border-top: none;
        max-height: 150px;
        overflow-y: auto;
        position: absolute;
        background-color: white;
        z-index: 1050; /* Sopra altri elementi */
        width: 100%;
    }
    .autocomplete-suggestion {
        padding: 8px 12px;
        cursor: pointer;
    }
    .autocomplete-suggestion:hover {
        background-color: #f2f2f2;
    }
    .autocomplete-active {
        background-color: #e9e9e9;
    }
    .loading-fade {
        opacity: 0.5;
        pointer-events: none;
        transition: opacity 0.2s ease-in-out;
    }
</style>
@endpush

@section('content')
<div class="container mt-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Elenco Cantieri</h1>
        <span id="cantieri-count-badge" class="badge bg-primary rounded-pill" style="{{ $cantieri->isNotEmpty() ? '' : 'display: none;' }}">
            {{ count($cantieri) }} trovati
        </span>
    </div>

    {{-- Form di ricerca e ordinamento --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">Cerca e Ordina i Cantieri</h5>
            <form action="{{ route('elenco') }}" method="GET" id="search-form" class="row g-3 align-items-end">
                <div class="col-md-4 position-relative">
                    <label for="location" class="form-label fw-semibold">Località</label>
                    <input type="text" class="form-control" id="location" name="location" placeholder="Es. Milano, Napoli, Torino..." value="{{ $searchLocation ?? 'Roma' }}" autocomplete="off">
                    <div id="autocomplete-container" class="autocomplete-suggestions"></div>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label fw-semibold">Cerca Azienda, Cantiere o Indirizzo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted" id="search-icon-container">
                            <i class="bi bi-search" id="search-icon"></i>
                            <div class="spinner-border spinner-border-sm text-primary d-none" id="search-spinner" role="status"></div>
                        </span>
                        <input type="text" class="form-control" id="search" name="search" placeholder="Es. Impresa Rossi, Via Roma..." value="{{ request('search') }}" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="order" class="form-label fw-semibold">Ordina per</label>
                    <select class="form-select" id="order" name="order">
                        <option value="distance" {{ ($order ?? 'distance') === 'distance' ? 'selected' : '' }}>Distanza (Default)</option>
                        <option value="azienda" {{ ($order ?? '') === 'azienda' ? 'selected' : '' }}>Nome Azienda</option>
                        <option value="data_notifica" {{ ($order ?? '') === 'data_notifica' ? 'selected' : '' }}>Data Notifica e Nome Azienda</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100 px-0">Cerca</button>
                </div>
            </form>
        </div>
    </div>

    <p class="text-muted" id="search-description-text">
        @if(isset($searchLocation) && strcasecmp($searchLocation, 'Roma') !== 0)
            Elenco dei cantieri attivi negli ultimi 60 giorni nel raggio di 2km dal centro di <strong>{{ e($searchLocation) }}</strong>.
        @else
            Elenco dei cantieri attivi negli ultimi 60 giorni nel raggio di 2km dal centro di Roma.
        @endif
    </p>

    {{-- Vista Tabella per Desktop (schermi grandi e superiori) --}}
    <div class="d-none d-lg-block">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Cantiere</th>
                        <th scope="col">Indirizzo</th>
                        <th scope="col">Distanza</th>
                        <th scope="col">Aziende Coinvolte</th>
                        <th scope="col" class="text-end">Dettagli</th>
                    </tr>
                </thead>
                <tbody id="cantieri-table-body">
                    @forelse ($cantieri as $index => $cantiere)
                        <tr class="cantiere-row" data-lat="{{ $cantiere->latitude }}" data-lon="{{ $cantiere->longitude }}">
                            <td class="fw-bold">{{ $cantiere->cantiere }}</td>
                            <td>{{ $cantiere->indirizzo_c }}, {{ $cantiere->localita_c }}</td>
                            <td>
                                <span class="badge bg-secondary font-monospace distance-badge">{{ number_format($cantiere->distance, 2, ',', '.') }} km</span>
                            </td>
                            <td>
                                @if($cantiere->aziende->isNotEmpty())
                                    {{ $cantiere->aziende->pluck('denominazione')->take(2)->implode(', ') }}
                                    @if($cantiere->aziende->count() > 2)
                                        ...
                                    @endif
                                @else
                                    <span class="text-muted">Nessuna</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" onclick="showDetailsByIndex({{ $index }})">
                                    <i class="bi bi-info-circle"></i> Dettagli
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">Nessun cantiere trovato.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Vista a Card per Mobile (schermi medi e inferiori) --}}
    <div class="d-lg-none" id="cantieri-cards-container">
        @forelse ($cantieri as $index => $cantiere)
            <div class="card mb-3 shadow-sm cantiere-card" data-lat="{{ $cantiere->latitude }}" data-lon="{{ $cantiere->longitude }}">
                <div class="card-body">
                    <h5 class="card-title">{{ $cantiere->cantiere }}</h5>
                    <p class="card-text text-muted mb-2">
                        <i class="bi bi-geo-alt-fill"></i> {{ $cantiere->indirizzo_c }}, {{ $cantiere->localita_c }}
                    </p>
                    <p class="card-text mb-3">
                        <span class="badge bg-secondary distance-badge"><i class="bi bi-compass"></i> {{ number_format($cantiere->distance, 2, ',', '.') }} km di distanza</span>
                    </p>
                    <button class="btn btn-primary w-100" onclick="showDetailsByIndex({{ $index }})">
                        Mostra Dettagli
                    </button>
                </div>
            </div>
        @empty
            <div class="alert alert-info text-center">
                Nessun cantiere trovato.
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
// Array globale per contenere i dettagli dei cantieri correnti
window.loadedCantieri = @json($cantieri);

function showDetailsByIndex(index) {
    if (window.loadedCantieri && window.loadedCantieri[index]) {
        showDetails(window.loadedCantieri[index]);
    }
}

function showDetails(cantiere) {
    let aziendeHtml = '<p class="text-muted">Nessuna azienda specificata.</p>';
    if (cantiere.aziende && cantiere.aziende.length > 0) {
        aziendeHtml = '<ul class="list-group list-group-flush text-start">';
        cantiere.aziende.forEach(azienda => {
            aziendeHtml += `<li class="list-group-item">${azienda.denominazione}</li>`;
        });
        aziendeHtml += '</ul>';
    }

    const contentHtml = `
        <div class="text-start px-1">
            <p><strong><i class="bi bi-geo-alt-fill"></i> Indirizzo:</strong><br>${cantiere.indirizzo_c}, ${cantiere.localita_c}</p>
            <hr>
            <h5>Aziende Coinvolte</h5>
            ${aziendeHtml}
        </div>
    `;

    Swal.fire({
        title: `<strong>${cantiere.cantiere}</strong>`,
        html: contentHtml,
        showCloseButton: true,
        showConfirmButton: false,
        focusConfirm: false,
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const locationInput = document.getElementById('location');
    const searchInput = document.getElementById('search');
    const orderSelect = document.getElementById('order');
    const searchForm = document.getElementById('search-form');
    const tableBody = document.getElementById('cantieri-table-body');
    const cardsContainer = document.getElementById('cantieri-cards-container');
    const countBadge = document.getElementById('cantieri-count-badge');
    const descText = document.getElementById('search-description-text');
    const searchIcon = document.getElementById('search-icon');
    const searchSpinner = document.getElementById('search-spinner');
    const suggestionsContainer = document.getElementById('autocomplete-container');

    let activeSuggestion = -1;
    let autocompleteDebounceTimer;
    let searchDebounceTimer;
    let userPosition = null;

    // Funzione helper per l'escaping dei caratteri HTML speciali
    function escapeHtml(str) {
        if (!str) return '';
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function getHaversineDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Raggio della Terra in km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = 
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
            Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    function updateDistancesOnPage(userLat, userLon) {
        // Aggiorna le distanze per la tabella desktop
        document.querySelectorAll('.cantiere-row').forEach(row => {
            const lat = parseFloat(row.getAttribute('data-lat'));
            const lon = parseFloat(row.getAttribute('data-lon'));
            if (!isNaN(lat) && !isNaN(lon)) {
                const dist = getHaversineDistance(userLat, userLon, lat, lon);
                const badge = row.querySelector('.distance-badge');
                if (badge) {
                    badge.textContent = dist.toLocaleString('it-IT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' km';
                }
            }
        });

        // Aggiorna le distanze per le card mobile
        document.querySelectorAll('.cantiere-card').forEach(card => {
            const lat = parseFloat(card.getAttribute('data-lat'));
            const lon = parseFloat(card.getAttribute('data-lon'));
            if (!isNaN(lat) && !isNaN(lon)) {
                const dist = getHaversineDistance(userLat, userLon, lat, lon);
                const badge = card.querySelector('.distance-badge');
                if (badge) {
                    badge.innerHTML = '<i class="bi bi-compass"></i> ' + dist.toLocaleString('it-IT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' km di distanza';
                }
            }
        });
    }

    function fetchCantieri() {
        const location = locationInput.value;
        const order = orderSelect.value;
        const search = searchInput.value;

        // Mostra lo spinner di caricamento e attenua l'opacità delle liste
        if (searchIcon) searchIcon.classList.add('d-none');
        if (searchSpinner) searchSpinner.classList.remove('d-none');
        if (tableBody) tableBody.classList.add('loading-fade');
        if (cardsContainer) cardsContainer.classList.add('loading-fade');

        // Costruisce l'URL della richiesta
        const url = new URL('{{ route("elenco") }}', window.location.origin);
        url.searchParams.append('location', location);
        url.searchParams.append('order', order);
        if (search) {
            url.searchParams.append('search', search);
        }

        // Aggiorna la cronologia della barra degli indirizzi
        window.history.pushState({}, '', url.pathname + url.search);

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Aggiorna il testo descrittivo della località
            if (descText) {
                if (data.searchLocation && data.searchLocation.toLowerCase() !== 'roma') {
                    descText.innerHTML = `Elenco dei cantieri attivi negli ultimi 60 giorni nel raggio di 2km dal centro di <strong>${escapeHtml(data.searchLocation)}</strong>.`;
                } else {
                    descText.innerHTML = `Elenco dei cantieri attivi negli ultimi 60 giorni nel raggio di 2km dal centro di Roma.`;
                }
            }
            renderCantieri(data.cantieri);
        })
        .catch(error => {
            console.error('Error fetching cantieri:', error);
        })
        .finally(() => {
            // Nasconde lo spinner e ripristina l'opacità
            if (searchIcon) searchIcon.classList.remove('d-none');
            if (searchSpinner) searchSpinner.classList.add('d-none');
            if (tableBody) tableBody.classList.remove('loading-fade');
            if (cardsContainer) cardsContainer.classList.remove('loading-fade');
        });
    }

    function renderCantieri(cantieri) {
        // Aggiorna l'array globale in memoria
        window.loadedCantieri = cantieri;

        // 1. Aggiorna il badge con il conteggio dei trovati
        if (countBadge) {
            if (cantieri && cantieri.length > 0) {
                countBadge.textContent = `${cantieri.length} trovati`;
                countBadge.style.display = '';
            } else {
                countBadge.style.display = 'none';
            }
        }

        // 2. Renderizza la tabella desktop
        if (tableBody) {
            let tableHtml = '';
            if (cantieri && cantieri.length > 0) {
                cantieri.forEach((cantiere, index) => {
                    let aziendeText = '<span class="text-muted">Nessuna</span>';
                    if (cantiere.aziende && cantiere.aziende.length > 0) {
                        const names = cantiere.aziende.slice(0, 2).map(a => a.denominazione).join(', ');
                        aziendeText = escapeHtml(names);
                        if (cantiere.aziende.length > 2) {
                            aziendeText += '...';
                        }
                    }

                    tableHtml += `
                        <tr class="cantiere-row" data-lat="${cantiere.latitude}" data-lon="${cantiere.longitude}">
                            <td class="fw-bold">${escapeHtml(cantiere.cantiere)}</td>
                            <td>${escapeHtml(cantiere.indirizzo_c)}, ${escapeHtml(cantiere.localita_c)}</td>
                            <td>
                                <span class="badge bg-secondary font-monospace distance-badge">
                                    ${cantiere.distance ? parseFloat(cantiere.distance).toLocaleString('it-IT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0,00'} km
                                </span>
                            </td>
                            <td>${aziendeText}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" onclick="showDetailsByIndex(${index})">
                                    <i class="bi bi-info-circle"></i> Dettagli
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                tableHtml = `
                    <tr>
                        <td colspan="5" class="text-center py-4">Nessun cantiere trovato.</td>
                    </tr>
                `;
            }
            tableBody.innerHTML = tableHtml;
        }

        // 3. Renderizza le card mobile
        if (cardsContainer) {
            let cardsHtml = '';
            if (cantieri && cantieri.length > 0) {
                cantieri.forEach((cantiere, index) => {
                    const distanceFormatted = cantiere.distance ? parseFloat(cantiere.distance).toLocaleString('it-IT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0,00';

                    cardsHtml += `
                        <div class="card mb-3 shadow-sm cantiere-card" data-lat="${cantiere.latitude}" data-lon="${cantiere.longitude}">
                            <div class="card-body">
                                <h5 class="card-title">${escapeHtml(cantiere.cantiere)}</h5>
                                <p class="card-text text-muted mb-2">
                                    <i class="bi bi-geo-alt-fill"></i> ${escapeHtml(cantiere.indirizzo_c)}, ${escapeHtml(cantiere.localita_c)}
                                </p>
                                <p class="card-text mb-3">
                                    <span class="badge bg-secondary distance-badge">
                                        <i class="bi bi-compass"></i> ${distanceFormatted} km di distanza
                                    </span>
                                </p>
                                <button class="btn btn-primary w-100" onclick="showDetailsByIndex(${index})">
                                    Mostra Dettagli
                                </button>
                            </div>
                        </div>
                    `;
                });
            } else {
                cardsHtml = `
                    <div class="alert alert-info text-center">
                        Nessun cantiere trovato.
                    </div>
                `;
            }
            cardsContainer.innerHTML = cardsHtml;
        }

        // 4. Ricalcola le distanze basate su coordinate utente (se già disponibili)
        if (userPosition) {
            updateDistancesOnPage(userPosition.lat, userPosition.lon);
        }
    }

    // Event Listeners per la form
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(fetchCantieri, 300);
        });
    }

    if (orderSelect) {
        orderSelect.addEventListener('change', function() {
            fetchCantieri();
        });
    }

    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearTimeout(searchDebounceTimer);
            fetchCantieri();
        });
    }

    // Gestione autocompletamento località
    if (locationInput) {
        locationInput.addEventListener('input', function() {
            const query = this.value;
            clearTimeout(autocompleteDebounceTimer);

            if (query.length < 3) {
                suggestionsContainer.innerHTML = '';
                return;
            }

            autocompleteDebounceTimer = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&addressdetails=1&limit=5`)
                    .then(response => response.json())
                    .then(data => {
                        activeSuggestion = -1;
                        suggestionsContainer.innerHTML = '';
                        data.forEach((item) => {
                            const suggestionDiv = document.createElement('div');
                            suggestionDiv.innerHTML = item.display_name;
                            suggestionDiv.classList.add('autocomplete-suggestion');
                            suggestionDiv.addEventListener('click', function() {
                                locationInput.value = item.display_name;
                                suggestionsContainer.innerHTML = '';
                                fetchCantieri();
                            });
                            suggestionsContainer.appendChild(suggestionDiv);
                        });
                    }).catch(error => console.error('Error fetching suggestions:', error));
            }, 300);
        });

        locationInput.addEventListener('keydown', function(e) {
            const items = suggestionsContainer.querySelectorAll('.autocomplete-suggestion');
            if (items.length === 0) return;

            if (e.keyCode === 40) { // Down
                e.preventDefault();
                activeSuggestion = (activeSuggestion + 1) % items.length;
            } else if (e.keyCode === 38) { // Up
                e.preventDefault();
                activeSuggestion = (activeSuggestion - 1 + items.length) % items.length;
            } else if (e.keyCode === 13) { // Enter
                e.preventDefault();
                if (activeSuggestion > -1) {
                    items[activeSuggestion].click();
                } else {
                    clearTimeout(autocompleteDebounceTimer);
                    fetchCantieri();
                }
                return;
            }

            items.forEach((item, index) => item.classList.toggle('autocomplete-active', index === activeSuggestion));
        });
    }

    document.addEventListener('click', function (e) {
        if (suggestionsContainer && !suggestionsContainer.contains(e.target) && e.target !== locationInput) {
            suggestionsContainer.innerHTML = '';
        }
    });

    // Calcolo dinamico della distanza tramite GPS all'avvio
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            const userLat = position.coords.latitude;
            const userLon = position.coords.longitude;
            userPosition = { lat: userLat, lon: userLon };

            updateDistancesOnPage(userLat, userLon);
        }, function (error) {
            console.warn('Rilevamento posizione GPS non riuscito o negato. Utilizzo della distanza di fallback dal server.', error);
        });
    }
});
</script>
@endpush