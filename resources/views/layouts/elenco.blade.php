@extends('layouts.app')

@push('styles')
<style>
    /* Stile per il popup di SweetAlert su mobile */
    @media (max-width: 576px) {
        .swal2-popup {
            width: 95% !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container mt-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Elenco Cantieri</h1>
        @if(count($cantieri) > 0)
            <span class="badge bg-primary rounded-pill">{{ count($cantieri) }} trovati</span>
        @endif
    </div>
    <p class="text-muted">
        Elenco dei cantieri attivi negli ultimi 60 giorni nel raggio di 2km dal centro di Roma.
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
</script>
@endpush