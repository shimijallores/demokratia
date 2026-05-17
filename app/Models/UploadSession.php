<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadSession extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'precinct_id',
        'batch_id',
        'total_chunks',
        'received_chunks',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'total_chunks' => 'integer',
        'received_chunks' => 'array',
        'expires_at' => 'datetime',
        'status' => 'string',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (UploadSession $session) {
            if (empty($session->id)) {
                $session->id = (string) str()->uuid();
            }

            if (empty($session->received_chunks)) {
                $session->received_chunks = [];
            }
        });
    }

    public function precinct(): BelongsTo
    {
        return $this->belongsTo(Precinct::class);
    }
}
