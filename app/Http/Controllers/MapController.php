<?php

namespace App\Http\Controllers;

use App\Models\Segnalazione;
use Illuminate\Http\Request;

class MapController extends Controller
{
    /**
     * Mostra la pagina principale con la mappa.
     */
    public function index()
    {
        return view('map');
    }

    /**
     * Mostra la pagina con l'elenco dei cantieri.
     */
    public function elenco()
    {
        $cantieri = Segnalazione::with('aziende')->select(
            'id', 'cantiere', 'indirizzo_c', 'localita_c', 'provincia_c', 'inizio_lavori'
        )
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->orderBy('id', 'desc')
        ->limit(200)
        ->get();

        return view('elenco', ['cantieri' => $cantieri]);
    }

    /**
     * Fornisce i dati dei cantieri in formato JSON.
     */
    public function getCantieri()
    {
        // Seleziona solo le colonne necessarie e i cantieri con coordinate valide
        $cantieri = Segnalazione::with('aziende')->select(
            'id', 'cantiere', 'indirizzo_c', 'localita_c', 'latitude', 'longitude'
        )
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->orderBy('id', 'desc')
        ->limit(200)
        ->get();
        return response()->json($cantieri);
    }
}