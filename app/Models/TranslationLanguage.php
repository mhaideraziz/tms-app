<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranslationLanguage extends Model
{
    use HasFactory;

    protected $fillable = ['locale', 'content', 'translation_id'];

    public function translation()
    {
        return $this->belongsTo(Translation::class);
    }
}
