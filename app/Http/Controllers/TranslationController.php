<?php

namespace App\Http\Controllers;

use App\Models\Translation;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|unique:translations,key',
            'tags' => 'nullable|string',
            'languages' => 'required|array',
            'languages.*.locale' => 'required|string',
            'languages.*.content' => 'required|string',
        ]);

        $translation = Translation::create([
            'key' => $validated['key'],
            'tags' => $validated['tags'],
        ]);

        foreach ($validated['languages'] as $language) {
            $translation->languages()->create($language);
        }

        return response()->json($translation->load('languages'), 201);
    }

    public function update(Request $request, $id)
    {
        $translation = Translation::findOrFail($id);

        $validated = $request->validate([
            'tags' => 'nullable|string',
            'languages' => 'nullable|array',
            'languages.*.locale' => 'required|string',
            'languages.*.content' => 'required|string',
        ]);

        $translation->update([
            'tags' => $validated['tags'] ?? $translation->tags,
        ]);

        if (!empty($validated['languages'])) {
            foreach ($validated['languages'] as $language) {
                $translation->languages()->updateOrCreate(
                    ['locale' => $language['locale']],
                    ['content' => $language['content']]
                );
            }
        }

        return response()->json($translation->load('languages'), 200);
    }

    public function show($id)
    {
        $translation = Translation::with('languages')->findOrFail($id);
        return response()->json($translation, 200);
    }

    public function search(Request $request)
    {
        $query = Translation::with('languages');

        if ($request->has('key')) {
            $query->where('key', 'like', '%' . $request->key . '%');
        }

        if ($request->has('tags')) {
            $query->where('tags', 'like', '%' . $request->tags . '%');
        }

        if ($request->has('content')) {
            $query->whereHas('languages', function ($q) use ($request) {
                $q->where('content', 'like', '%' . $request->content . '%');
            });
        }

        $translations = $query->get();

        return response()->json($translations, 200);
    }

//    public function export(Request $request)
//    {
//        $locale = $request->input('locale', 'en'); // Default to English
//
//        // Use cursor for memory-efficient handling of large datasets
//        $translations = Translation::with(['languages' => function ($query) use ($locale) {
//            $query->where('locale', $locale);
//        }])->cursor();
//
//        $exportData = [];
//
//        foreach ($translations as $translation) {
//            $content = $translation->languages->first()?->content ?? null;
//            if ($content) {
//                $exportData[$translation->key] = $content;
//            }
//        }
//
//        return response()->json($exportData, 200);
//    }

    public function export(Request $request)
    {
        $locale = $request->input('locale'); // Get the requested locale

        // Fetch translations with all associated languages
        $translations = Translation::with('languages')->cursor();

        $exportData = [];

        foreach ($translations as $translation) {
            foreach ($translation->languages as $language) {
                // If locale is specified, filter by locale
                if ($locale && $language->locale !== $locale) {
                    continue;
                }

                // Group translations by locale
                $exportData[$language->locale][$translation->key] = $language->content;
            }
        }

        return response()->json($exportData, 200);
    }



}
