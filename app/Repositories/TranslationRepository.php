<?php

namespace App\Repositories;


use App\Models\Translation;

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

    public function searchTranslations(array $criteria)
    {
        $query = Translation::with('languages');

        if (!empty($criteria['key'])) {
            $query->where('key', 'like', '%' . $criteria['key'] . '%');
        }

        if (!empty($criteria['tags'])) {
            $query->where('tags', 'like', '%' . $criteria['tags'] . '%');
        }

        if (!empty($criteria['content'])) {
            $query->whereHas('languages', function ($q) use ($criteria) {
                $q->where('content', 'like', '%' . $criteria['content'] . '%');
            });
        }

        return $query->get();
    }

    public function getAllTranslationsWithLanguages()
    {
        return Translation::with('languages')->cursor();
    }

    public function deleteTranslation(Translation $translation)
    {
        // Delete related languages first (if cascading is not set up in the database)
        $translation->languages()->delete();

        // Delete the translation itself
        $translation->delete();
    }

}
