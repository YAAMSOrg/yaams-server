<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirlineMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'airline_id',
        'user_id'
    ];

    public function airline()
    {
        return $this->belongsTo(Airline::class, 'airline_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
