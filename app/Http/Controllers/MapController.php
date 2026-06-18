<?php

namespace App\Http\Controllers;

use App\Models\Segnalazione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MapController extends Controller
{
    /**
     * Mostra la pagina principale con la mappa e gestisce la ricerca per località.
     */
    public function map(Request $request)
    {
        $searchLocation = $request->input('location', 'Roma');
        $lat = 41.9027835; // Default: Roma
        $lon = 12.4963655; // Default: Roma
        Log::debug('MAP: Inizializzazione con coordinate di default (Roma).', ['lat' => $lat, 'lon' => $lon]);

        // Se è stata fornita una location diversa da Roma, la geocodifichiamo
        if ($request->filled('location') && strcasecmp($searchLocation, 'Roma') !== 0) {
            Log::debug('MAP: Tentativo di geocodifica per una nuova località.', ['location' => $searchLocation]);
            $geoData = $this->geocodeLocation($searchLocation);
            if ($geoData) {
                $lat = $geoData['lat'];
                $lon = $geoData['lon'];
                Log::info('MAP: Geocodifica RIUSCITA.', ['location' => $searchLocation, 'lat' => $lat, 'lon' => $lon]);
            } else {
                // Se la geocodifica fallisce, si torna a Roma.
                // Il termine di ricerca viene mantenuto nell'input per feedback all'utente.
                $lat = 41.9027835;
                $lon = 12.4963655;
                Log::warning('MAP: Geocodifica FALLITA. Fallback su coordinate di Roma.', ['location' => $searchLocation]);
            }
        } else {
            Log::debug('MAP: Nessuna ricerca o ricerca per "Roma". Si usano le coordinate di default.');
        }

        Log::debug('MAP: Coordinate finali passate alla vista.', ['location' => $searchLocation, 'lat' => $lat, 'lon' => $lon]);

        return view('layouts.map', [
            'mode' => 'coords', // Indica al componente Vue di usare le coordinate fornite
            'searchLocation' => $searchLocation,
            'lat' => $lat,
            'lon' => $lon,
        ]);
    }

    /**
     * Mostra la mappa predisposta per la geolocalizzazione dell'utente.
     */
    public function mapVicinanze()
    {
        // Passiamo le coordinate di fallback (Roma) e la modalità 'geolocation'.
        // Il componente Vue si occuperà di chiedere la posizione all'utente.
        return view('layouts.map', [
            'mode' => 'geolocation',
            'searchLocation' => 'La tua posizione',
            'lat' => 41.9027835, // Fallback lat
            'lon' => 12.4963655, // Fallback lon
        ]);
    }

    /**
     * Fornisce i dati dei cantieri attivi in un dato raggio (default 2km) in formato JSON.
     */
    public function getCantieri(Request $request)
    {
        // Valida e ottiene i parametri dalla richiesta, con valori di default per Roma
        $validated = $request->validate([
            'lat' => 'nullable|numeric|between:-90,90',
            'lon' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0',
        ]);

        $lat = $validated['lat'] ?? 41.9027835;
        $lon = $validated['lon'] ?? 12.4963655;
        $radius = $validated['radius'] ?? 2;

        $cantieri = $this->getActiveCantieri($lat, $lon, $radius);
        return response()->json($cantieri);
    }

    /**
     * Metodo privato per recuperare i cantieri attivi in un dato raggio.
     */
    private function getActiveCantieri($lat, $lon, $radius)
    {
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
            ->orderBy('distance', 'asc') // Ordina per vicinanza al punto di ricerca
            ->limit(120)
            ->get();
    }

    /**
     * Mostra la pagina con l'elenco dei cantieri.
     * Permette di cercare per località.
     */
    public function elenco(Request $request)
    {
        $searchLocation = $request->input('location', 'Roma');
        $lat = 41.9027835; // Default: Roma
        $lon = 12.4963655; // Default: Roma
        $radius = 2; // Raggio di ricerca in km
        Log::debug('ELENCO: Inizializzazione con coordinate di default (Roma).', ['lat' => $lat, 'lon' => $lon]);

        if ($request->filled('location') && strcasecmp($request->input('location'), 'Roma') !== 0) {
            Log::debug('ELENCO: Tentativo di geocodifica per una nuova località.', ['location' => $searchLocation]);
            // Geocodifica la località fornita
            $geoData = $this->geocodeLocation($searchLocation);
            if ($geoData) {
                $lat = $geoData['lat'];
                $lon = $geoData['lon'];
                Log::info('ELENCO: Geocodifica RIUSCITA.', ['location' => $searchLocation, 'lat' => $lat, 'lon' => $lon]);
            } else {
                Log::warning('ELENCO: Geocodifica FALLITA. La ricerca userà le coordinate di default (Roma).', ['location' => $searchLocation]);
            }
        } else {
            Log::debug('ELENCO: Nessuna ricerca o ricerca per "Roma". Si usano le coordinate di default.');
        }

        Log::debug('ELENCO: Esecuzione query con coordinate finali.', ['location' => $searchLocation, 'lat' => $lat, 'lon' => $lon]);
        $cantieri = $this->getActiveCantieri($lat, $lon, $radius);
        return view('layouts.elenco', [
            'cantieri' => $cantieri,
            'searchLocation' => $searchLocation,
        ]);
    }

    /**
     * Geocodifica una stringa di località usando Nominatim.
     * @return array|null
     */
    private function geocodeLocation(string $location)
    {
        try {
            // Nominatim richiede uno User-Agent specifico e un Referer per rispettare le policy di utilizzo.
            // Questo previene l'errore 403 (Access Denied).
            // Vedi: https://operations.osmfoundation.org/policies/nominatim/
            $response = Http::timeout(10)
                ->withUserAgent('FilleaGoNext/1.0 (https://filleagonext.example.com)')
                ->withHeader('Referer', config('app.url'))
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $location,
                    'format' => 'json',
                    'limit' => 1,
                    'addressdetails' => 1 // Richiedo i dettagli per un log più ricco
                ]);

            if ($response->failed()) {
                // Logga errori client (4xx) o server (5xx)
                Log::error('Geocoding HTTP request failed.', [
                    'location' => $location,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $jsonData = $response->json();

            if (empty($jsonData)) {
                Log::warning('Geocoding successful but no results found for location.', ['location' => $location]);
                return null;
            }

            $data = $jsonData[0];

            if (!isset($data['lat']) || !isset($data['lon'])) {
                Log::error('Geocoding result is missing lat/lon.', ['location' => $location, 'data' => $data]);
                return null;
            }

            return ['lat' => (float) $data['lat'], 'lon' => (float) $data['lon']];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Errore specifico di connessione (es. DNS, SSL, timeout)
            Log::error('Geocoding connection error for location: ' . $location, ['message' => $e->getMessage()]);
        } catch (\Exception $e) {
            // Altre eccezioni generiche
            Log::error('Generic geocoding exception for location: ' . $location, ['message' => $e->getMessage()]);
        }

        return null;
    }
}