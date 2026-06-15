<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cantiere extends Model
{
    use HasFactory;

    protected $fillable = ['cantiere', 'indirizzo_c', 'localita_c', 'latitude', 'longitude'];
}