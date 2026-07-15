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
        'status',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'approved_at' => 'datetime',
    ];

    // Moderation states stored in the `status` column.
    public const STATUS_PENDING = 'pending';    // awaiting manager review, hidden from the gallery
    public const STATUS_APPROVED = 'approved';  // visible to all airline members

    public function aircraft()
    {
        return $this->belongsTo(Aircraft::class, 'aircraft_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Remove the backing file from the private disk. Call before deleting the row.
     */
    public function deleteFile(): void
    {
        Storage::disk('local')->delete($this->path);
    }
}
