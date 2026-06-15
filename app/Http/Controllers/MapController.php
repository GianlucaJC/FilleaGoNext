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
        $cantieri = Segnalazione::with('aziende')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('fine_lavori', '>=', now()->subDays(60))
            ->orderBy('id', 'desc')
            ->limit(200)
            ->get();

        return view('elenco', ['cantieri' => $cantieri]);
    }

    /**
     * Fornisce i dati dei cantieri attivi in formato JSON.
     */
    public function getCantieri()
    {
        $cantieri = Segnalazione::with('aziende')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('fine_lavori', '>=', now()->subDays(60))
            ->orderBy('id', 'desc')
            ->limit(200)
            ->get();
        return response()->json($cantieri);
    }
}