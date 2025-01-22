<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class TranslationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $translationData;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->withHeader('X-API-KEY', env('API_KEY'));
        Sanctum::actingAs($this->user);

        $this->translationData = [
            'key' => 'test.greeting',
            'tags' => 'common,test',
            'languages' => [
                ['locale' => 'en', 'content' => 'Hello'],
                ['locale' => 'fr', 'content' => 'Bonjour']
            ]
        ];
    }

    /** @test */
    public function can_create_translation()
    {

        $response = $this->postJson('/api/translations', $this->translationData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'key',
                'tags',
                'languages' => [
                    '*' => ['locale', 'content']
                ]
            ]);
    }

    /** @test */
    public function cannot_create_translation_with_duplicate_key()
    {
        Translation::create([
            'key' => $this->translationData['key'],
            'tags' => 'existing'
        ]);

        $response = $this->postJson('/api/translations', $this->translationData);

        $response->assertStatus(200)
            ->assertJsonStructure(['error' => ['key']]);
    }

    /** @test */
    public function can_update_translation()
    {
        $translation = Translation::create([
            'key' => 'test.key',
            'tags' => 'old-tag'
        ]);

        $updateData = [
            'tags' => 'new-tag',
            'languages' => [
                ['locale' => 'en', 'content' => 'Updated content']
            ]
        ];

        $response = $this->putJson("/api/translations/{$translation->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['tags' => 'new-tag']);
    }

    /** @test */
    public function can_search_translations()
    {
        // Create test translations
        Translation::create([
            'key' => 'test.search',
            'tags' => 'searchable'
        ]);

        $searchParams = [
            'key' => 'test',
            'tags' => 'searchable'
        ];

        $response = $this->getJson("/api/translations/search?" . http_build_query($searchParams));

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'key', 'tags']
            ]);
    }

    /** @test */
    public function can_export_translations()
    {
        // Create test translations with languages
        $translation = Translation::create([
            'key' => 'test.export',
            'tags' => 'exportable'
        ]);

        $translation->languages()->createMany([
            ['locale' => 'en', 'content' => 'Hello'],
            ['locale' => 'fr', 'content' => 'Bonjour']
        ]);

        $response = $this->getJson('/api/translations/export?locale=en');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'en' => ['test.export']
            ]);
    }

    /** @test */
    public function can_delete_translation()
    {
        $translation = Translation::create([
            'key' => 'test.delete',
            'tags' => 'deletable'
        ]);

        $response = $this->deleteJson("/api/translations/{$translation->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Translation deleted successfully']);

        $this->assertDatabaseMissing('translations', ['id' => $translation->id]);
    }

    /** @test */
    public function handles_not_found_translation()
    {
        $nonExistentId = 99999;

        $response = $this->getJson("/api/translations/{$nonExistentId}");

        $response->assertStatus(404)
            ->assertJson(['error' => 'Translation not found']);
    }

    /** @test */
    public function validates_language_data_structure()
    {
        $invalidData = [
            'key' => 'test.invalid',
            'tags' => 'test',
            'languages' => [
                ['locale' => 'en'] // missing content
            ]
        ];

        $response = $this->postJson('/api/translations', $invalidData);

        $response->assertStatus(200)
            ->assertJsonStructure(['error' => ['languages.0.content']]);
    }
}

class TranslationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $translationService;
    protected $translationRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->translationRepository = $this->app->make(\App\Repositories\TranslationRepository::class);
        $this->translationService = new \App\Services\TranslationService($this->translationRepository);
        $this->withHeader('X-API-KEY', env('API_KEY'));
    }

    /** @test */
    public function it_creates_translation_with_languages()
    {
        $data = [
            'key' => 'test.service',
            'tags' => 'service-test',
            'languages' => [
                ['locale' => 'en', 'content' => 'Test content'],
                ['locale' => 'fr', 'content' => 'Contenu test']
            ]
        ];

        $response = $this->translationService->storeTranslation($data);

        $this->assertEquals(201, $response->status());
        $this->assertDatabaseHas('translations', [
            'key' => 'test.service',
            'tags' => 'service-test'
        ]);
    }

    /** @test */
    public function it_handles_export_with_empty_translations()
    {
        $response = $this->translationService->exportTranslations(['locale' => 'en']);

        $this->assertEquals(200, $response->status());
        $this->assertEmpty(json_decode($response->getContent(), true));
    }

    /** @test */
    public function it_handles_search_with_no_results()
    {
        $response = $this->translationService->searchTranslations([
            'key' => 'nonexistent',
            'tags' => 'notfound'
        ]);

        $this->assertEquals(404, $response->status());
        $this->assertArrayHasKey('message', json_decode($response->getContent(), true));
    }
}

