<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segnalazione extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'segnalazioni';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Definisce la relazione "uno a molti" con le aziende associate a questa segnalazione.
     */
    public function aziende()
    {
        return $this->hasMany(AziendaSegnalazione::class, 'id_segnalazione')
            ->join('aziende', 'aziende_segnalazioni.id_azienda', '=', 'aziende.p_iva')
            ->selectRaw('MAX(aziende_segnalazioni.id) as id, aziende_segnalazioni.id_segnalazione, aziende_segnalazioni.id_azienda, aziende_segnalazioni.denominazione')
            ->groupBy('aziende_segnalazioni.id_segnalazione', 'aziende_segnalazioni.id_azienda', 'aziende_segnalazioni.denominazione');
    }
}