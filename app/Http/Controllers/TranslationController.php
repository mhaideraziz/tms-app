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

    /**
     * @OA\Post(
     *     path="/api/translations",
     *     summary="Create Translation",
     *     description="Creates a new translation with a unique key, optional tags, and multiple language entries.",
     *     tags={"Translations"},
     *     security={{"sanctum": {}}, {"apiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"key", "languages"},
     *             @OA\Property(property="key", type="string", example="greeting"),
     *             @OA\Property(property="tags", type="string", nullable=true, example="common"),
     *             @OA\Property(
     *                 property="languages",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="locale", type="string", example="en"),
     *                     @OA\Property(property="content", type="string", example="Hello")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Translation created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Translation created successfully"),
     *             @OA\Property(property="translation", type="object",
     *                 @OA\Property(property="key", type="string", example="greeting"),
     *                 @OA\Property(property="tags", type="string", nullable=true, example="common"),
     *                 @OA\Property(
     *                     property="languages",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="locale", type="string", example="en"),
     *                         @OA\Property(property="content", type="string", example="Hello")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="key", type="array", @OA\Items(type="string", example="The key field is required.")),
     *                 @OA\Property(property="languages", type="array", @OA\Items(type="string", example="The languages field is required."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
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

        return $this->service->storeTranslation($validator->validated());
    }


    /**
     * @OA\Put(
     *     path="/api/translations/{id}",
     *     summary="Update Translation",
     *     description="Updates an existing translation with new tags and language content.",
     *     tags={"Translations"},
     *     security={{"sanctum": {}}, {"apiKey": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the translation to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="tags", type="string", nullable=true, example="updated-tag"),
     *             @OA\Property(
     *                 property="languages",
     *                 type="array",
     *                 nullable=true,
     *                 @OA\Items(
     *                     @OA\Property(property="locale", type="string", example="en"),
     *                     @OA\Property(property="content", type="string", example="Updated Hello")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translation updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Translation updated successfully"),
     *             @OA\Property(property="translation", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="tags", type="string", nullable=true, example="updated-tag"),
     *                 @OA\Property(
     *                     property="languages",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="locale", type="string", example="en"),
     *                         @OA\Property(property="content", type="string", example="Updated Hello")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="languages", type="array", @OA\Items(type="string", example="The languages field is required.")),
     *                 @OA\Property(property="languages.*.locale", type="array", @OA\Items(type="string", example="The languages.*.locale field is required.")),
     *                 @OA\Property(property="languages.*.content", type="array", @OA\Items(type="string", example="The languages.*.content field is required."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Translation not found.")
     *         )
     *     )
     * )
     */

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

    /**
     * @OA\Get(
     *     path="/api/translations/{id}",
     *     summary="Get Translation by ID",
     *     description="Retrieves a translation and its associated languages by the given ID.",
     *     tags={"Translations"},
     *     security={{"sanctum": {}}, {"apiKey": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the translation to retrieve",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translation retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="key", type="string", example="greeting"),
     *             @OA\Property(property="tags", type="string", nullable=true, example="common"),
     *             @OA\Property(
     *                 property="languages",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="locale", type="string", example="en"),
     *                     @OA\Property(property="content", type="string", example="Hello")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Translation not found.")
     *         )
     *     )
     * )
     */

    public function show($id)
    {
        return $this->service->getTranslationById($id);
    }


    /**
     * @OA\Get(
     *     path="/api/translations/search",
     *     summary="Search Translations",
     *     description="Search translations by key, tags, or content. Filters are optional.",
     *     tags={"Translations"},
     *     security={{"sanctum": {}}, {"apiKey": {}}},
     *     @OA\Parameter(
     *         name="key",
     *         in="query",
     *         required=false,
     *         description="Translation key to search for",
     *         @OA\Schema(type="string", example="greeting")
     *     ),
     *     @OA\Parameter(
     *         name="tags",
     *         in="query",
     *         required=false,
     *         description="Tags associated with the translation",
     *         @OA\Schema(type="string", example="common")
     *     ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         required=false,
     *         description="Content to search within the translations' languages",
     *         @OA\Schema(type="string", example="Hello")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="key", type="string", example="greeting"),
     *                 @OA\Property(property="tags", type="string", nullable=true, example="common"),
     *                 @OA\Property(
     *                     property="languages",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="locale", type="string", example="en"),
     *                         @OA\Property(property="content", type="string", example="Hello")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="key", type="array", @OA\Items(type="string", example="The key field must be a string.")),
     *                 @OA\Property(property="tags", type="array", @OA\Items(type="string", example="The tags field must be a string.")),
     *                 @OA\Property(property="content", type="array", @OA\Items(type="string", example="The content field must be a string."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:255',
            'locale' => 'nullable|string|max:10',
            'content' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        return $this->service->searchTranslations($validator->validated());

    }


    /**
     * @OA\Get(
     *     path="/api/translations/export",
     *     summary="Export Translations",
     *     description="Exports translations in JSON format, optionally filtered by locale or other criteria.",
     *     tags={"Translations"},
     *     security={{"sanctum": {}}, {"apiKey": {}}},
     *     @OA\Parameter(
     *         name="locale",
     *         in="query",
     *         required=false,
     *         description="Filter translations by locale (e.g., 'en', 'fr')",
     *         @OA\Schema(type="string", example="en")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translations exported successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "en": {
     *                     "greeting": "Hello",
     *                     "farewell": "Goodbye"
     *                 },
     *                 "fr": {
     *                     "greeting": "Bonjour",
     *                     "farewell": "Au revoir"
     *                 }
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="An error occurred while exporting translations.")
     *         )
     *     )
     * )
     */

    public function export(Request $request)
    {
        return $this->service->exportTranslations($request->all());
    }

    /**
     * @OA\Delete(
     *     path="/api/translations/{id}",
     *     summary="Delete Translation",
     *     description="Deletes a translation and its associated languages by the given ID.",
     *     tags={"Translations"},
     *     security={{"sanctum": {}}, {"apiKey": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the translation to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translation deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Translation deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Translation not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="An error occurred while deleting the translation.")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        return $this->service->deleteTranslation($id);
    }



}

