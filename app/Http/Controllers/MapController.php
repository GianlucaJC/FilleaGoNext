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
        $cantieri = $this->getActiveCantieriInRome();
        return view('elenco', ['cantieri' => $cantieri]);
    }

    /**
     * Fornisce i dati dei cantieri attivi nel raggio di 3km da Roma in formato JSON.
     */
    public function getCantieri()
    {
        $cantieri = $this->getActiveCantieriInRome();
        return response()->json($cantieri);
    }

    /**
     * Metodo privato per recuperare i cantieri attivi nel raggio di 3km da Roma.
     */
    private function getActiveCantieriInRome()
    {
        $lat = 41.9027835; // Latitudine del centro di Roma
        $lon = 12.4963655; // Longitudine del centro di Roma
        $radius = 2; // Raggio in km, come richiesto

        // Formula di Haversine per calcolare la distanza in km
        $haversine = "( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) )";

        // Calcola un "bounding box" per un pre-filtro veloce che possa usare gli indici
        $lat_min = $lat - ($radius / 111.045);
        $lat_max = $lat + ($radius / 111.045);
        $lon_min = $lon - ($radius / (111.045 * cos(deg2rad($lat))));
        $lon_max = $lon + ($radius / (111.045 * cos(deg2rad($lat))));

        return Segnalazione::with('aziende')
            ->selectRaw("*, {$haversine} AS distance", [$lat, $lon, $lat])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            // 1. Filtro veloce sul bounding box (usa gli indici del DB)
            ->whereBetween('latitude', [$lat_min, $lat_max])
            ->whereBetween('longitude', [$lon_min, $lon_max])
            ->where('fine_lavori', '>=', now()->subDays(60)) // Filtro per cantieri attivi (ultimi 60 giorni)
            ->whereRaw("{$haversine} < ?", [$lat, $lon, $lat, $radius]) // 2. Filtro preciso sul cerchio
            ->orderBy('distance', 'asc') // Ordina per vicinanza a Roma
            ->limit(120)
            ->get();
    }
}