<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Azienda extends Model
{
    use HasFactory;

    protected $table = 'aziende';

    protected $primaryKey = 'p_iva';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;
}
