<?php

namespace App\Http\Controllers;

use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TranslationController extends Controller
{
    protected $service;

    public function __construct(TranslationService $service)
    {
        $this->service = $service;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|unique:translations,key',
            'tags' => 'nullable|string',
            'languages' => 'required|array',
            'languages.*.locale' => 'required|string',
            'languages.*.content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        return $this->service->storeTranslation($validator);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tags' => 'nullable|string',
            'languages' => 'nullable|array',
            'languages.*.locale' => 'required|string',
            'languages.*.content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        return $this->service->updateTranslation($id, $validator->validated());
    }


    public function show($id)
    {
        return $this->service->getTranslationById($id);
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'nullable|string',
            'tags' => 'nullable|string',
            'content' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        return $this->service->searchTranslations($validator->validated());

    }

    public function export(Request $request)
    {
        return $this->service->exportTranslations($request->all());
    }

    public function destroy($id)
    {
        return $this->service->deleteTranslation($id);
    }



}

