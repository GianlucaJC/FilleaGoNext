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
        $lat = 41.9027835; // Latitudine del centro di Roma
        $lon = 12.4963655; // Longitudine del centro di Roma
        $radius = 3; // Raggio in km

        // Formula di Haversine per calcolare la distanza in km
        $haversine = "( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) )";

        $cantieri = Segnalazione::with('aziende')
            ->selectRaw("*, {$haversine} AS distance", [$lat, $lon, $lat])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('fine_lavori', '>=', now()->subDays(60)) // Filtro per cantieri attivi
            ->whereRaw("{$haversine} < ?", [$lat, $lon, $lat, $radius]) // Filtro per raggio
            ->orderBy('distance', 'asc') // Ordina per vicinanza a Roma
            ->limit(200)
            ->get();

        return view('elenco', ['cantieri' => $cantieri]);
    }

    /**
     * Fornisce i dati dei cantieri attivi nel raggio di 3km da Roma in formato JSON.
     */
    public function getCantieri()
    {
        $lat = 41.9027835; // Latitudine del centro di Roma
        $lon = 12.4963655; // Longitudine del centro di Roma
        $radius = 3; // Raggio in km

        // Formula di Haversine per calcolare la distanza in km
        $haversine = "( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) )";

        $cantieri = Segnalazione::with('aziende')
            ->selectRaw("*, {$haversine} AS distance", [$lat, $lon, $lat])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('fine_lavori', '>=', now()->subDays(60)) // Filtro per cantieri attivi
            ->whereRaw("{$haversine} < ?", [$lat, $lon, $lat, $radius]) // Filtro per raggio
            ->orderBy('distance', 'asc') // Ordina per vicinanza a Roma
            ->limit(200)
            ->get();
        return response()->json($cantieri);
    }
}