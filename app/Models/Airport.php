<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    use HasFactory;

    protected $primaryKey = 'icao_code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'icao_code',
        'name',
        'latitude_deg',
        'longitude_deg',
        'elevation_ft',
        'iso_country',
        'iata_code'
    ];

}
