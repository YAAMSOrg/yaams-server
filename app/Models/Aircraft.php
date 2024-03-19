<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Aircraft extends Model
{
    use HasFactory;

    protected $primaryKey = 'registration';
    public $incrementing = false;
    protected $keyType = 'string';

   protected $fillable = [
        'registration',
        'manufacturer',
        'model',
        'remarks',
        'usedByAirline'
    ];

    protected $appends = [
        'full_type'
    ];

    public function getFullTypeAttribute()
    {
        return $this->manufacturer . ' ' . $this->model;
    }

    public function airline()
    {
        return $this->belongsTo(Airline::class, 'used_by');
    }

}