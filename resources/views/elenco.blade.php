@extends('layouts.app')

@section('content')
<div class="container mt-4"> 
    <h1 class="mb-3">Cantieri Attivi a Roma (raggio 2km)</h1>
    <p class="text-muted">Questa è una lista dei 120 cantieri più vicini al centro di Roma (raggio 2km), attivi (con data fine lavori non più vecchia di 60 giorni).</p>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Oggetto Cantiere</th>
                    <th>Indirizzo</th>
                    <th>Località</th>
                    <th>Prov.</th>
                    <th>Aziende Presenti</th>
                    <th>Inizio Lavori</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cantieri as $cantiere)
                    <tr>
                        <td class="text-muted">{{ $cantiere->id }}</td>
                        <td>{{ $cantiere->cantiere }}</td>
                        <td>{{ $cantiere->indirizzo_c }}</td>
                        <td>{{ $cantiere->localita_c }}</td>
                        <td>{{ $cantiere->provincia_c }}</td>
                        <td>
                            @forelse($cantiere->aziende as $azienda)
                                {{ $azienda->denominazione }}<br>
                            @empty
                                <span class="text-muted fst-italic">Nessuna azienda associata</span>
                            @endforelse
                        </td>
                        <td>{{ $cantiere->inizio_lavori ? \Carbon\Carbon::parse($cantiere->inizio_lavori)->format('d/m/Y') : 'N/D' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Nessun cantiere da mostrare.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection