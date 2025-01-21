<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'tags'];

    public function languages()
    {
        return $this->hasMany(TranslationLanguage::class);
    }
}
