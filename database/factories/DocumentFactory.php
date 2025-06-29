<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Document Factory
 * 
 * Factory for generating test document data with realistic file information
 * and various processing states for comprehensive testing.
 * 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Document::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileTypes = ['pdf', 'docx', 'pptx'];
        $fileType = $this->faker->randomElement($fileTypes);
        $originalFilename = $this->faker->words(3, true) . '.' . $fileType;
        $storedFilename = Document::generateStoredFilename($originalFilename);
        
        return [
            'user_id' => User::factory(),
            'original_filename' => $originalFilename,
            'stored_filename' => $storedFilename,
            'file_path' => 'documents/' . $storedFilename,
            'mime_type' => Document::SUPPORTED_TYPES[$fileType],
            'file_size' => $this->faker->numberBetween(50000, 5000000), // 50KB to 5MB
            'file_hash' => hash('sha256', $originalFilename . time()),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->optional(0.7)->paragraph(),
            'metadata' => $this->generateMetadata($fileType),
            'status' => Document::STATUS_UPLOADED,
            'processing_error' => null,
            'processed_at' => null,
            'extracted_content' => null,
            'content_statistics' => null,
        ];
    }

    /**
     * Create a document that has been processed successfully.
     */
    public function processed(): static
    {
        $content = $this->faker->paragraphs(10, true);
        
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_COMPLETED,
            'processed_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'extracted_content' => $content,
            'content_statistics' => $this->generateContentStatistics($content),
        ]);
    }

    /**
     * Create a document that is currently being processed.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_PROCESSING,
        ]);
    }

    /**
     * Create a document that failed processing.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_FAILED,
            'processing_error' => $this->faker->randomElement([
                'File format not supported',
                'Document is password protected',
                'File appears to be corrupted',
                'OpenAI API rate limit exceeded',
                'Insufficient content to process',
            ]),
        ]);
    }

    /**
     * Create a document that has been archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_ARCHIVED,
            'processed_at' => $this->faker->dateTimeBetween('-60 days', '-30 days'),
        ]);
    }

    /**
     * Create a PDF document.
     */
    public function pdf(): static
    {
        $originalFilename = $this->faker->words(3, true) . '.pdf';
        $storedFilename = Document::generateStoredFilename($originalFilename);
        
        return $this->state(fn (array $attributes) => [
            'original_filename' => $originalFilename,
            'stored_filename' => $storedFilename,
            'file_path' => 'documents/' . $storedFilename,
            'mime_type' => Document::SUPPORTED_TYPES['pdf'],
            'metadata' => $this->generateMetadata('pdf'),
        ]);
    }

    /**
     * Create a Word document.
     */
    public function docx(): static
    {
        $originalFilename = $this->faker->words(3, true) . '.docx';
        $storedFilename = Document::generateStoredFilename($originalFilename);
        
        return $this->state(fn (array $attributes) => [
            'original_filename' => $originalFilename,
            'stored_filename' => $storedFilename,
            'file_path' => 'documents/' . $storedFilename,
            'mime_type' => Document::SUPPORTED_TYPES['docx'],
            'metadata' => $this->generateMetadata('docx'),
        ]);
    }

    /**
     * Create a PowerPoint document.
     */
    public function pptx(): static
    {
        $originalFilename = $this->faker->words(3, true) . '.pptx';
        $storedFilename = Document::generateStoredFilename($originalFilename);
        
        return $this->state(fn (array $attributes) => [
            'original_filename' => $originalFilename,
            'stored_filename' => $storedFilename,
            'file_path' => 'documents/' . $storedFilename,
            'mime_type' => Document::SUPPORTED_TYPES['pptx'],
            'metadata' => $this->generateMetadata('pptx'),
        ]);
    }

    /**
     * Create a large document.
     */
    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_size' => $this->faker->numberBetween(5000000, 10000000), // 5MB to 10MB
        ]);
    }

    /**
     * Create a small document.
     */
    public function small(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_size' => $this->faker->numberBetween(10000, 100000), // 10KB to 100KB
        ]);
    }

    /**
     * Create a document with academic content.
     */
    public function academic(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $this->faker->randomElement([
                'Research Paper on Machine Learning',
                'Thesis: Climate Change Impact',
                'Analysis of Economic Trends',
                'Study on Renewable Energy',
                'Medical Research Publication',
            ]),
            'description' => 'Academic document requiring simplification for educational purposes.',
        ]);
    }

    /**
     * Create a document with business content.
     */
    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $this->faker->randomElement([
                'Annual Financial Report',
                'Marketing Strategy Document',
                'Business Plan 2024',
                'Project Management Guidelines',
                'Company Policy Manual',
            ]),
            'description' => 'Business document for professional review and analysis.',
        ]);
    }

    /**
     * Generate realistic metadata based on file type.
     */
    protected function generateMetadata(string $fileType): array
    {
        $baseMetadata = [
            'created_by' => $this->faker->name(),
            'created_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'language' => $this->faker->randomElement(['en', 'en-US', 'en-GB']),
        ];

        switch ($fileType) {
            case 'pdf':
                return array_merge($baseMetadata, [
                    'pdf_version' => $this->faker->randomElement(['1.4', '1.5', '1.6', '1.7']),
                    'pages' => $this->faker->numberBetween(1, 50),
                    'producer' => $this->faker->randomElement(['Adobe PDF', 'Microsoft Word', 'LibreOffice']),
                    'encrypted' => false,
                ]);

            case 'docx':
                return array_merge($baseMetadata, [
                    'word_count' => $this->faker->numberBetween(100, 10000),
                    'pages' => $this->faker->numberBetween(1, 20),
                    'application' => 'Microsoft Word',
                    'version' => $this->faker->randomElement(['16.0', '15.0', '14.0']),
                ]);

            case 'pptx':
                return array_merge($baseMetadata, [
                    'slides' => $this->faker->numberBetween(5, 30),
                    'application' => 'Microsoft PowerPoint',
                    'version' => $this->faker->randomElement(['16.0', '15.0', '14.0']),
                    'template' => $this->faker->randomElement(['Default', 'Professional', 'Academic']),
                ]);

            default:
                return $baseMetadata;
        }
    }

    /**
     * Generate content statistics for processed documents.
     */
    protected function generateContentStatistics(string $content): array
    {
        $wordCount = str_word_count($content);
        $characterCount = strlen($content);
        $characterCountNoSpaces = strlen(str_replace(' ', '', $content));
        $lineCount = substr_count($content, "\n") + 1;
        $paragraphCount = count(array_filter(explode("\n\n", $content)));

        return [
            'word_count' => $wordCount,
            'character_count' => $characterCount,
            'character_count_no_spaces' => $characterCountNoSpaces,
            'line_count' => $lineCount,
            'paragraph_count' => $paragraphCount,
            'estimated_reading_time_minutes' => max(1, round($wordCount / 200)),
        ];
    }
}