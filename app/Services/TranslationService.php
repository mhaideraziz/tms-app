<?php

namespace App\Services;

use App\Repositories\TranslationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TranslationService
{
    protected $repository;
    public function __construct(TranslationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function storeTranslation($data)
    {
        $translation = $this->repository->createTranslation([
            'key' => $data['key'],
            'tags' => $data['tags'] ?? null,
        ]);

        foreach ($data['languages'] as $language) {
            $this->repository->addLanguageToTranslation($translation, $language);
        }

        return response()->json($translation->load('languages'), 201);
    }

    public function updateTranslation($id, $data)
    {
        $translation = $this->repository->findTranslationById($id);

        $updatedTranslation = $this->repository->updateTranslation($translation, [
            'tags' => $data['tags'] ?? $translation->tags,
        ]);

        if (!empty($data['languages'])) {
            foreach ($data['languages'] as $language) {
                $this->repository->updateOrCreateLanguage($updatedTranslation, $language);
            }
        }

        return response()->json($updatedTranslation->load('languages'), 200);
    }

    public function getTranslationById($id)
    {
        try {
            $translation = $this->repository->findTranslationWithLanguages($id);
            return response()->json($translation, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Translation not found',
            ], 404);
        }
    }

    public function searchTranslations($data)
    {

        // Fetch translations
        $translations = $this->repository->searchTranslations($data);

        // Check if result is empty
        if ($translations->isEmpty()) {
            return response()->json([
                'message' => 'No translations found',
            ], 404);
        }

        return response()->json($translations, 200);
    }

    public function exportTranslations($data)
    {
        $locale = $data['locale'] ?? 'en';

        // Fetch translations
        $translations = $this->repository->getAllTranslationsWithLanguages();

        $exportData = [];

        foreach ($translations as $translation) {
            foreach ($translation->languages as $language) {
                // Filter by locale if specified
                if ($locale && $language->locale !== $locale) {
                    continue;
                }

                // Group translations by locale
                $exportData[$language->locale][$translation->key] = $language->content;
            }
        }

        return response()->json($exportData, 200);
    }

    public function deleteTranslation($id)
    {
        // Find the translation
        $translation = $this->repository->findTranslationById($id);

        if (!$translation) {
            return response()->json([
                'error' => 'Translation not found',
            ], 404);
        }

        // Delete the translation and its related languages
        $this->repository->deleteTranslation($translation);

        return response()->json([
            'message' => 'Translation deleted successfully',
        ], 200);
    }

}
