<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AircraftImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'aircraft_id',
        'path',
        'is_primary',
        'uploaded_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function aircraft()
    {
        return $this->belongsTo(Aircraft::class, 'aircraft_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Remove the backing file from the private disk. Call before deleting the row.
     */
    public function deleteFile(): void
    {
        Storage::disk('local')->delete($this->path);
    }
}
