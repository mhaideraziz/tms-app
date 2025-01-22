<?php

namespace App\Repositories;


use App\Models\Translation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TranslationRepository
{
    public function createTranslation(array $data)
    {
        return Translation::create($data);
    }

    public function addLanguageToTranslation(Translation $translation, array $languageData)
    {
        $translation->languages()->create($languageData);
    }

    public function findTranslationById($id)
    {
        return Translation::findOrFail($id);
    }

    public function updateTranslation(Translation $translation, array $data)
    {
        $translation->update($data);
        return $translation;
    }

    public function updateOrCreateLanguage(Translation $translation, array $languageData)
    {
        $translation->languages()->updateOrCreate(
            ['locale' => $languageData['locale']],
            ['content' => $languageData['content']]
        );
    }

    public function findTranslationWithLanguages($id)
    {
        return Translation::with('languages')->findOrFail($id);
    }

    public function searchTranslations($criteria)
    {
        $query = Translation::query();

        if(!empty($criteria['key'])) {
            $query->where('key', 'LIKE', $criteria['key'] . '%'); // Optimize for index use
        }

        if(!empty($criteria['tags'])) {
            $query->where('tags', $criteria['tags']);
        }

        $query->with(['languages' => function ($query) use ($criteria) {
            if(!empty($criteria['locale'])) {
                $query->where('locale', $criteria['locale']);
            }

            if(!empty($criteria['content'])) {
                $query->where('content', 'LIKE', '%' . $criteria['content'] . '%');
            }
        }]);

        // Cache results
        $cacheKey = 'search_' . md5(json_encode($criteria));
        return Cache::remember($cacheKey, 600, function () use ($query) {
            return $query->paginate(50);
        });
    }
    public function getTranslationsWithLanguages($locale)
    {
        $query = DB::table('translations')
            ->join('translation_languages', 'translations.id', '=', 'translation_languages.translation_id')
            ->select('translations.key', 'translation_languages.locale', 'translation_languages.content')
            ->orderBy('translations.id'); // Ensure the query is ordered

        if ($locale) {
            $query->where('translation_languages.locale', $locale);
        }

        return $query;
    }

    public function getRecentTranslations($locale, $since)
    {
        return DB::table('translations')
            ->join('translation_languages', 'translations.id', '=', 'translation_languages.translation_id')
            ->select('translations.key', 'translation_languages.locale', 'translation_languages.content')
            ->where('translation_languages.updated_at', '>=', $since)
            ->when($locale, function ($query) use ($locale) {
                $query->where('translation_languages.locale', $locale);
            })
            ->orderBy('translations.id');
    }


    public function deleteTranslation(Translation $translation)
    {
        // Delete related languages first (if cascading is not set up in the database)
        $translation->languages()->delete();

        // Delete the translation itself
        $translation->delete();
    }

}
