<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranslationLanguage extends Model
{
    use HasFactory;

    protected $fillable = ['locale', 'content', 'translation_id'];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function translation()
    {
        return $this->belongsTo(Translation::class);
    }
}
