<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'precinct_id',
        'ballot_count',
        'transmission_mode',
        'checksum',
        'received_at',
        'status',
    ];

    protected $casts = [
        'ballot_count' => 'integer',
        'transmission_mode' => 'string',
        'received_at' => 'datetime',
        'status' => 'string',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Batch $batch) {
            if (empty($batch->id)) {
                $batch->id = (string) str()->uuid();
            }
        });
    }

    public function precinct(): BelongsTo
    {
        return $this->belongsTo(Precinct::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
