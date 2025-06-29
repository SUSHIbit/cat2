<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Simplification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Simplification Factory
 * 
 * Factory for generating test simplification data with realistic cat stories,
 * AI processing metrics, and user feedback for comprehensive testing.
 * 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Simplification>
 */
class SimplificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Simplification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'user_id' => User::factory(),
            'ai_model' => $this->faker->randomElement([
                Simplification::MODEL_GPT_35_TURBO,
                Simplification::MODEL_GPT_4,
            ]),
            'complexity_level' => $this->faker->randomElement([
                Simplification::COMPLEXITY_BASIC,
                Simplification::COMPLEXITY_INTERMEDIATE,
                Simplification::COMPLEXITY_ADVANCED,
            ]),
            'processing_parameters' => $this->generateProcessingParameters(),
            'simplified_title' => 'Cat Story: ' . $this->faker->sentence(3),
            'cat_story' => $this->generateCatStory(),
            'summary' => $this->faker->optional(0.8)->paragraph(3),
            'key_concepts' => $this->generateKeyConcepts(),
            'status' => Simplification::STATUS_PENDING,
            'processing_error' => null,
            'processed_at' => null,
            'tokens_used' => null,
            'processing_cost' => null,
            'processing_time_seconds' => null,
            'readability_score' => null,
            'quality_metrics' => null,
            'is_favorite' => false,
            'user_rating' => null,
            'user_notes' => null,
            'is_public' => false,
            'share_token' => null,
            'download_count' => 0,
            'last_downloaded_at' => null,
        ];
    }

    /**
     * Create a completed simplification.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Simplification::STATUS_COMPLETED,
            'processed_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'tokens_used' => $this->faker->numberBetween(500, 3000),
            'processing_cost' => $this->faker->randomFloat(4, 0.001, 0.05),
            'processing_time_seconds' => $this->faker->numberBetween(5, 60),
            'readability_score' => $this->faker->numberBetween(6, 10),
            'quality_metrics' => $this->generateQualityMetrics(),
        ]);
    }

    /**
     * Create a simplification that is currently being processed.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Simplification::STATUS_PROCESSING,
        ]);
    }

    /**
     * Create a simplification that failed processing.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Simplification::STATUS_FAILED,
            'processing_error' => $this->faker->randomElement([
                'OpenAI API rate limit exceeded',
                'Insufficient content to simplify',
                'AI model temporarily unavailable',
                'Content moderation flag triggered',
                'Token limit exceeded for request',
            ]),
        ]);
    }

    /**
     * Create a favorite simplification.
     */
    public function favorite(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_favorite' => true,
            'user_rating' => $this->faker->numberBetween(4, 5),
            'user_notes' => $this->faker->optional(0.7)->sentence(),
        ]);
    }

    /**
     * Create a public simplification with share token.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
            'share_token' => Str::random(32),
            'download_count' => $this->faker->numberBetween(0, 50),
            'last_downloaded_at' => $this->faker->optional(0.8)->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Create a highly rated simplification.
     */
    public function highlyRated(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_rating' => $this->faker->numberBetween(4, 5),
            'readability_score' => $this->faker->numberBetween(8, 10),
            'user_notes' => 'Excellent simplification, very clear and engaging!',
        ]);
    }

    /**
     * Create a basic complexity simplification.
     */
    public function basic(): static
    {
        return $this->state(fn (array $attributes) => [
            'complexity_level' => Simplification::COMPLEXITY_BASIC,
            'cat_story' => $this->generateBasicCatStory(),
            'readability_score' => $this->faker->numberBetween(8, 10),
        ]);
    }

    /**
     * Create an intermediate complexity simplification.
     */
    public function intermediate(): static
    {
        return $this->state(fn (array $attributes) => [
            'complexity_level' => Simplification::COMPLEXITY_INTERMEDIATE,
            'cat_story' => $this->generateIntermediateCatStory(),
            'readability_score' => $this->faker->numberBetween(6, 8),
        ]);
    }

    /**
     * Create an advanced complexity simplification.
     */
    public function advanced(): static
    {
        return $this->state(fn (array $attributes) => [
            'complexity_level' => Simplification::COMPLEXITY_ADVANCED,
            'cat_story' => $this->generateAdvancedCatStory(),
            'readability_score' => $this->faker->numberBetween(4, 7),
        ]);
    }

    /**
     * Create a simplification using GPT-4.
     */
    public function gpt4(): static
    {
        return $this->state(fn (array $attributes) => [
            'ai_model' => Simplification::MODEL_GPT_4,
            'tokens_used' => $this->faker->numberBetween(800, 2000),
            'processing_cost' => $this->faker->randomFloat(4, 0.015, 0.08),
        ]);
    }

    /**
     * Create a simplification using GPT-3.5 Turbo.
     */
    public function gpt35(): static
    {
        return $this->state(fn (array $attributes) => [
            'ai_model' => Simplification::MODEL_GPT_35_TURBO,
            'tokens_used' => $this->faker->numberBetween(500, 1500),
            'processing_cost' => $this->faker->randomFloat(4, 0.001, 0.03),
        ]);
    }

    /**
     * Generate realistic processing parameters.
     */
    protected function generateProcessingParameters(): array
    {
        return [
            'temperature' => $this->faker->randomFloat(2, 0.1, 1.0),
            'max_tokens' => $this->faker->numberBetween(1000, 4000),
            'top_p' => $this->faker->randomFloat(2, 0.8, 1.0),
            'frequency_penalty' => $this->faker->randomFloat(2, 0.0, 0.5),
            'presence_penalty' => $this->faker->randomFloat(2, 0.0, 0.5),
        ];
    }

    /**
     * Generate a generic cat story.
     */
    protected function generateCatStory(): string
    {
        $stories = [
            "Once upon a time, there was a smart kitty who loved to learn new things. This kitty discovered that understanding complex ideas is like solving puzzles - you need to break them into smaller pieces first. The kitty learned that when humans share complicated information, it's like giving someone a big ball of yarn. But when you unravel it slowly and explain each piece, it becomes much easier to understand. The kitty realized that simplifying complex topics helps everyone learn better, just like how a mother cat teaches her kittens one skill at a time.",
            
            "There once lived a curious cat who worked as a translator in the land of Big Words. Every day, humans would bring the cat long, confusing documents full of fancy language. The cat's job was to turn these scary papers into simple, friendly stories that anyone could understand. The cat discovered that most complex ideas are actually quite simple when you remove all the extra fluff and explain them using everyday words that everyone knows.",
            
            "In a cozy library, there lived a wise old cat who specialized in making difficult books easier to read. This cat had a magical ability to take any complicated text and transform it into a story that even kittens could understand. The secret was to imagine explaining the topic to a friend over a cup of milk - using simple words, clear examples, and focusing on the most important points first.",
        ];

        return $this->faker->randomElement($stories);
    }

    /**
     * Generate a basic complexity cat story.
     */
    protected function generateBasicCatStory(): string
    {
        return "Kitty wants to tell you about something important. Imagine kitty has a big box of toys. Some toys are easy to understand, like a ball. You throw it, you catch it. Simple! But some toys are like puzzles with many pieces. Kitty learned that when things seem hard, you can make them easier by breaking them into small parts. Just like when kitty eats dinner, kitty takes small bites instead of trying to eat everything at once. This makes learning fun and easy!";
    }

    /**
     * Generate an intermediate complexity cat story.
     */
    protected function generateIntermediateCatStory(): string
    {
        return "Professor Whiskers here with an important lesson! You know how kitty sometimes encounters complicated situations, like figuring out how to open a new type of treat container? Well, complex topics work the same way. They might seem impossible at first, but with patience and the right approach, any puzzle can be solved. Kitty has learned that breaking down information into manageable chunks - like separating different types of kibble - makes even the most challenging concepts digestible. The key is to start with what you already know and build from there, just like how kitty learned to hunt by first practicing with toy mice.";
    }

    /**
     * Generate an advanced complexity cat story.
     */
    protected function generateAdvancedCatStory(): string
    {
        return "Greetings from Dr. Mittens, your feline academic advisor! Today we're exploring the sophisticated art of knowledge transformation - much like how kitty evolved from wild hunter to domestic scholar. Consider how complex ecosystems function: each component has its role, yet the whole system operates with elegant simplicity. Similarly, when we encounter dense academic or technical material, we must identify the core mechanisms at play. Think of it as kitty's approach to understanding territory - we map the boundaries, identify key landmarks, and establish clear pathways between important concepts. This methodical deconstruction allows us to rebuild understanding in an accessible format while preserving essential complexity where it truly matters.";
    }

    /**
     * Generate key concepts array.
     */
    protected function generateKeyConcepts(): array
    {
        $concepts = [
            'simplification',
            'accessibility',
            'education',
            'communication',
            'understanding',
            'learning',
            'explanation',
            'clarity',
            'comprehension',
            'knowledge transfer',
            'cat metaphors',
            'storytelling',
            'engagement',
            'retention',
            'visualization',
        ];

        return $this->faker->randomElements($concepts, $this->faker->numberBetween(3, 8));
    }

    /**
     * Generate quality metrics.
     */
    protected function generateQualityMetrics(): array
    {
        return [
            'coherence_score' => $this->faker->randomFloat(2, 0.7, 1.0),
            'engagement_score' => $this->faker->randomFloat(2, 0.6, 1.0),
            'accuracy_score' => $this->faker->randomFloat(2, 0.8, 1.0),
            'creativity_score' => $this->faker->randomFloat(2, 0.5, 1.0),
            'cat_theme_consistency' => $this->faker->randomFloat(2, 0.8, 1.0),
            'educational_value' => $this->faker->randomFloat(2, 0.7, 1.0),
            'language_simplicity' => $this->faker->randomFloat(2, 0.6, 1.0),
        ];
    }
}