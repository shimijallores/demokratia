<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'position',
        'party',
        'ballot_number',
        'photo_url',
    ];

    protected $casts = [
        'position' => 'string',
    ];

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
