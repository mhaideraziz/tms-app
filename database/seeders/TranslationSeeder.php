<?php

namespace Database\Seeders;

use App\Models\Translation;
use App\Models\TranslationLanguage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TranslationSeeder extends Seeder
{
    private array $locales = ['en', 'fr', 'es', 'de', 'it'];
    private array $tags = ['mobile', 'desktop', 'web'];
    private int $batchSize = 1000;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting translation seeding...');

        // Generate 100k+ translations
        $totalRecords = 50000;
        $processedRecords = 0;

        while ($processedRecords < $totalRecords) {
            $translations = [];
            $translationLanguages = [];

            $currentBatch = min($this->batchSize, $totalRecords - $processedRecords);

            // Generate translations batch
            for ($i = 0; $i < $currentBatch; $i++) {
                $translations[] = [
                    'key' => $this->generateUniqueKey($processedRecords + $i),
                    'tags' => $this->generateTags(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insert translations batch
            DB::table('translations')->insert($translations);

            // Get the IDs of inserted translations
            $lastInsertId = DB::getPdo()->lastInsertId();

            // Generate translation languages for each translation
            foreach ($translations as $index => $translation) {
                $translationId = $lastInsertId + $index;
                foreach ($this->locales as $locale) {
                    $translationLanguages[] = [
                        'translation_id' => $translationId,
                        'locale' => $locale,
                        'content' => $this->generateContent($locale),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Insert translation languages batch
            DB::table('translation_languages')->insert($translationLanguages);

            $processedRecords += $currentBatch;
            $this->command->info("Processed $processedRecords records out of $totalRecords");
        }

        $this->command->info('Seeding completed successfully!');
    }

    /**
     * Generate a unique translation key
     */
    private function generateUniqueKey(int $index): string
    {
        $prefixes = ['header', 'footer', 'sidebar', 'menu', 'button', 'label', 'error', 'success'];
        $prefix = $prefixes[array_rand($prefixes)];
        $uniqueHash = substr(md5((string)microtime(true) . $index), 0, 8);
        return strtolower("{$prefix}.{$uniqueHash}.item_{$index}");
    }

    /**
     * Generate random tags
     */
    private function generateTags(): string
    {
        $numTags = rand(1, 3);
        $selectedTags = array_rand(array_flip($this->tags), $numTags);
        return is_array($selectedTags) ? implode(',', $selectedTags) : $selectedTags;
    }

    /**
     * Generate content for different locales
     */
    private function generateContent(string $locale): string
    {
        $words = [
            'en' => ['Hello', 'Welcome', 'Thank you', 'Submit', 'Cancel', 'Error', 'Success'],
            'fr' => ['Bonjour', 'Bienvenue', 'Merci', 'Soumettre', 'Annuler', 'Erreur', 'Succès'],
            'es' => ['Hola', 'Bienvenido', 'Gracias', 'Enviar', 'Cancelar', 'Error', 'Éxito'],
            'de' => ['Hallo', 'Willkommen', 'Danke', 'Absenden', 'Abbrechen', 'Fehler', 'Erfolg'],
            'it' => ['Ciao', 'Benvenuto', 'Grazie', 'Inviare', 'Annulla', 'Errore', 'Successo']
        ];

        $wordCount = rand(1, 4);
        $content = [];

        for ($i = 0; $i < $wordCount; $i++) {
            $content[] = $words[$locale][array_rand($words[$locale])];
        }

        return implode(' ', $content);
    }
}
