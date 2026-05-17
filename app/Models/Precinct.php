<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Precinct extends Model
{
    use HasFactory;

    protected $fillable = [
        'precinct_code',
        'name',
        'region',
        'province',
        'municipality',
        'barangay',
        'registered_voters',
        'api_key_hash',
        'aes_key_encrypted',
        'status',
    ];

    protected $casts = [
        'registered_voters' => 'integer',
        'status' => 'string',
    ];

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function uploadSessions(): HasMany
    {
        return $this->hasMany(UploadSession::class);
    }
}
