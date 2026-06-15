<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AziendaSegnalazione extends Model
{
    use HasFactory;

    protected $table = 'aziende_segnalazioni';

    public $timestamps = false;
}