@extends('layouts.app')

@push('styles')
<style>
    /* Stili per l'autocomplete, per coerenza con la pagina elenco */
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

    /* Layout per contenere la ricerca e la mappa */
    #map-wrapper {
        display: flex;
        flex-direction: column;
        /* Altezza viewport meno l'altezza della navbar (approx 56px) */
        height: calc(100vh - 56px);
    }
    #map-container {
        flex-grow: 1; /* La mappa occupa tutto lo spazio verticale disponibile */
    }
</style>
@endpush

@section('content')
<div id="map-wrapper">
    {{-- Form di ricerca per la mappa --}}
    <div class="container-fluid p-3 bg-light shadow-sm">
        <form action="{{ route('mappa') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-auto d-none d-md-block">
                <label for="location" class="col-form-label"><strong>Cerca Località:</strong></label>
            </div>
            <div class="col position-relative">
                <input type="text" class="form-control" id="location" name="location" placeholder="Cerca una città..." value="{{ e($searchLocation ?? 'Roma') }}" autocomplete="off">
                <div id="autocomplete-container" class="autocomplete-suggestions"></div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i>
                    <span class="d-none d-md-inline ms-1">Cerca</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Il componente Vue per la mappa viene montato qui --}}
    <div id="map-container"
         class="container-fluid p-0"
         data-mode="{{ $mode ?? 'coords' }}"
         data-lat="{{ $lat ?? '41.9027835' }}"
         data-lon="{{ $lon ?? '12.4963655' }}"
         data-search-location="{{ e($searchLocation ?? 'Roma') }}">
    </div>
</div>
@endsection

@push('scripts')
{{-- Includo lo stesso script di autocompletamento della pagina elenco --}}
<script>
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
        }, 300);
    });

    locationInput.addEventListener('keydown', function(e) {
        const items = suggestionsContainer.querySelectorAll('.autocomplete-suggestion');
        if (items.length === 0) return;
        if (e.keyCode === 40) { e.preventDefault(); activeSuggestion = (activeSuggestion + 1) % items.length; }
        else if (e.keyCode === 38) { e.preventDefault(); activeSuggestion = (activeSuggestion - 1 + items.length) % items.length; }
        else if (e.keyCode === 13) { e.preventDefault(); if (activeSuggestion > -1) { items[activeSuggestion].click(); } else { locationInput.form.submit(); } return; }
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