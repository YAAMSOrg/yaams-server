<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\LogsModelActivity;

class InviteCode extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = ['code', 'airline_id', 'created_by', 'role', 'used_by', 'used_at'];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    public function isUsed(): bool
    {
        return $this->used_by !== null;
    }
}
