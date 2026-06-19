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
</style>
@endpush

@section('content')
<div class="container mt-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Elenco Cantieri</h1>
        @if($cantieri->isNotEmpty())
            <span class="badge bg-primary rounded-pill">{{ count($cantieri) }} trovati</span>
        @endif
    </div>

    {{-- Form di ricerca e ordinamento --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">Cerca e Ordina i Cantieri</h5>
            <form action="{{ route('elenco') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-6 position-relative">
                    <label for="location" class="form-label fw-semibold">Località</label>
                    <input type="text" class="form-control" id="location" name="location" placeholder="Es. Milano, Napoli, Torino..." value="{{ $searchLocation ?? 'Roma' }}" autocomplete="off">
                    <div id="autocomplete-container" class="autocomplete-suggestions"></div>
                </div>
                <div class="col-md-4">
                    <label for="order" class="form-label fw-semibold">Ordina per</label>
                    <select class="form-select" id="order" name="order" onchange="this.form.submit()">
                        <option value="distance" {{ ($order ?? 'distance') === 'distance' ? 'selected' : '' }}>Distanza (Default)</option>
                        <option value="azienda" {{ ($order ?? '') === 'azienda' ? 'selected' : '' }}>Nome Azienda</option>
                        <option value="data_notifica" {{ ($order ?? '') === 'data_notifica' ? 'selected' : '' }}>Data Notifica e Nome Azienda</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Cerca</button>
                </div>
            </form>
        </div>
    </div>

    <p class="text-muted">
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
                        <th scope="col">Aziende Coinvolte</th>
                        <th scope="col" class="text-end">Dettagli</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cantieri as $cantiere)
                        <tr>
                            <td class="fw-bold">{{ $cantiere->cantiere }}</td>
                            <td>{{ $cantiere->indirizzo_c }}, {{ $cantiere->localita_c }}</td>
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
                                <button class="btn btn-sm btn-outline-primary" onclick='showDetails(@json($cantiere))'>
                                    <i class="bi bi-info-circle"></i> Dettagli
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">Nessun cantiere trovato.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Vista a Card per Mobile (schermi medi e inferiori) --}}
    <div class="d-lg-none">
        @forelse ($cantieri as $cantiere)
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">{{ $cantiere->cantiere }}</h5>
                    <p class="card-text text-muted mb-2"><i class="bi bi-geo-alt-fill"></i> {{ $cantiere->indirizzo_c }}, {{ $cantiere->localita_c }}</p>
                    <button class="btn btn-primary w-100" onclick='showDetails(@json($cantiere))'>
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
    const suggestionsContainer = document.getElementById('autocomplete-container');
    let activeSuggestion = -1;

    let debounceTimer;

    locationInput.addEventListener('input', function() {
        const query = this.value;
        clearTimeout(debounceTimer);

        if (query.length < 3) {
            suggestionsContainer.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
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
                            locationInput.form.submit();
                        });
                        suggestionsContainer.appendChild(suggestionDiv);
                    });
                }).catch(error => console.error('Error fetching suggestions:', error));
        }, 300); // Debounce di 300ms
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
                locationInput.form.submit();
            }
            return;
        }

        items.forEach((item, index) => item.classList.toggle('autocomplete-active', index === activeSuggestion));
    });

    document.addEventListener('click', function (e) {
        if (!suggestionsContainer.contains(e.target) && e.target !== locationInput) {
            suggestionsContainer.innerHTML = '';
        }
    });
});
</script>
@endpush