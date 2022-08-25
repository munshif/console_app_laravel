<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flashcard extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @author mohamedmunshif
     */
    public function answers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }
}
