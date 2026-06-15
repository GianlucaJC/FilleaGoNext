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
        $lat = 41.9027835; // Roma
        $lon = 12.4963655; // Roma
        $radius = 3; // km

        $cantieri = Segnalazione::with('aziende')
            ->selectRaw(
                'id, cantiere, indirizzo_c, localita_c, provincia_c, inizio_lavori, ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance',
                [$lat, $lon, $lat]
            )
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->having('distance', '<', $radius)
            ->orderBy('distance', 'asc')
            ->get();

        return view('elenco', ['cantieri' => $cantieri]);
    }

    /**
     * Fornisce i dati dei cantieri in formato JSON, filtrati per prossimità.
     */
    public function getCantieri()
    {
        $lat = 41.9027835; // Roma
        $lon = 12.4963655; // Roma
        $radius = 3; // km

        $cantieri = Segnalazione::with('aziende')
            ->selectRaw(
                'id, cantiere, indirizzo_c, localita_c, latitude, longitude, ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance',
                [$lat, $lon, $lat]
            )
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->having('distance', '<', $radius)
            ->orderBy('distance', 'asc')
            ->get();
        return response()->json($cantieri);
    }
}