<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;

// Reindirizza la root alla pagina della mappa
Route::get('/', function () {
    return redirect()->route('mappa');
});

Route::get('/mappa', [MapController::class, 'index'])->name('mappa');
Route::get('/elenco', [MapController::class, 'elenco'])->name('elenco');

// Rotta API per i cantieri, spostata qui per debug
// Questa rotta è stata spostata in routes/api.php per seguire le convenzioni di Laravel