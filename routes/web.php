<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;

// Reindirizza la root alla pagina della mappa
Route::get('/', function () {
    return redirect()->route('mappa');
});

Route::get('/mappa', function () {
    return view('map', ['mode' => 'rome']);
})->name('mappa');

Route::get('/vicinanze', function () {
    return view('map', ['mode' => 'geolocation']);
})->name('vicinanze');
Route::get('/elenco', [MapController::class, 'elenco'])->name('elenco');

// Rotta API per i cantieri, spostata qui per debug
// Questa rotta è stata spostata in routes/api.php per seguire le convenzioni di Laravel