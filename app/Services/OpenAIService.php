<?php

namespace App\Services;

use App\Models\Simplification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenAI Service
 * 
 * Handles communication with the OpenAI API to generate cat story simplifications
 * from document content. Provides rate limiting, error handling, and cost tracking.
 */
class OpenAIService
{
    /**
     * OpenAI API configuration
     */
    protected string $apiKey;
    protected string $baseUrl;
    protected int $timeout;
    protected int $maxTokens;

    /**
     * Rate limiting configuration
     */
    protected int $requestsPerMinute = 20;
    protected int $tokensPerMinute = 40000;
    
    /**
     * Model pricing (per 1K tokens)
     */
    protected array $pricing = [
        'gpt-3.5-turbo' => [
            'input' => 0.0015,   // $0.0015 per 1K input tokens
            'output' => 0.002,   // $0.002 per 1K output tokens
        ],
        'gpt-4' => [
            'input' => 0.03,     // $0.03 per 1K input tokens
            'output' => 0.06,    // $0.06 per 1K output tokens
        ],
    ];

    /**
     * OpenAIService constructor.
     */
    public function __construct()
    {
        $this->apiKey = config('cat-simplifier.openai.api_key');
        $this->baseUrl = config('cat-simplifier.openai.base_url', 'https://api.openai.com/v1');
        $this->timeout = config('cat-simplifier.openai.timeout', 120);
        $this->maxTokens = config('cat-simplifier.openai.max_tokens', 4000);
        
        if (empty($this->apiKey)) {
            throw new \Exception('OpenAI API key is not configured');
        }
    }

    /**
     * Generate a cat story simplification from document content.
     */
    public function generateSimplification(Simplification $simplification): array
    {
        $startTime = microtime(true);
        
        try {
            // Prepare the request
            $requestData = $this->buildRequestData($simplification);
            
            // Make API request
            $response = $this->makeApiRequest($requestData);
            
            // Process response
            $result = $this->processResponse($response, $simplification, $startTime);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('OpenAI API request failed', [
                'simplification_id' => $simplification->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Build the request data for OpenAI API.
     */
    protected function buildRequestData(Simplification $simplification): array
    {
        $document = $simplification->document;
        $complexityLevel = $simplification->complexity_level;
        $model = $simplification->ai_model;
        
        // Get processing parameters
        $parameters = $simplification->processing_parameters ?? [];
        
        // Build system prompt based on complexity level
        $systemPrompt = $this->buildSystemPrompt($complexityLevel);
        
        // Build user prompt with document content
        $userPrompt = $this->buildUserPrompt($document, $complexityLevel);
        
        return [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user', 
                    'content' => $userPrompt,
                ],
            ],
            'temperature' => $parameters['temperature'] ?? 0.7,
            'max_tokens' => min($parameters['max_tokens'] ?? 2000, $this->maxTokens),
            'top_p' => $parameters['top_p'] ?? 0.9,
            'frequency_penalty' => $parameters['frequency_penalty'] ?? 0.1,
            'presence_penalty' => $parameters['presence_penalty'] ?? 0.1,
        ];
    }

    /**
     * Build system prompt based on complexity level.
     */
    protected function buildSystemPrompt(string $complexityLevel): string
    {
        $basePrompt = "You are a helpful AI assistant that specializes in simplifying complex documents by turning them into engaging cat-themed stories. Your goal is to make difficult concepts accessible and entertaining while preserving the essential information.";
        
        switch ($complexityLevel) {
            case Simplification::COMPLEXITY_BASIC:
                return $basePrompt . " Write for young children (ages 5-8) using very simple words, short sentences, and basic cat metaphors. Focus on the most important points only.";
                
            case Simplification::COMPLEXITY_INTERMEDIATE:
                return $basePrompt . " Write for middle school students (ages 9-14) using clear language, moderate vocabulary, and creative cat analogies. Include key details while keeping it engaging.";
                
            case Simplification::COMPLEXITY_ADVANCED:
                return $basePrompt . " Write for high school students and adults using more sophisticated language while maintaining the cat theme. Include nuanced concepts and deeper explanations.";
                
            default:
                return $basePrompt . " Write for a general audience using clear, accessible language with cat-themed explanations.";
        }
    }

    /**
     * Build user prompt with document content.
     */
    protected function buildUserPrompt($document, string $complexityLevel): string
    {
        $content = $document->extracted_content;
        $title = $document->title;
        
        // Truncate content if too long (leave room for prompt and response)
        $maxContentLength = $this->calculateMaxContentLength($simplification->ai_model ?? 'gpt-3.5-turbo');
        if (strlen($content) > $maxContentLength) {
            $content = substr($content, 0, $maxContentLength) . '...';
        }
        
        $prompt = "Please transform the following document into an engaging cat story that explains the concepts in simple terms:\n\n";
        $prompt .= "Document Title: {$title}\n\n";
        $prompt .= "Document Content:\n{$content}\n\n";
        $prompt .= "Instructions:\n";
        $prompt .= "1. Create a cat story that explains the main concepts\n";
        $prompt .= "2. Use cat characters, situations, and metaphors\n";
        $prompt .= "3. Make it educational but entertaining\n";
        $prompt .= "4. Keep the essential information intact\n";
        $prompt .= "5. Structure it as a cohesive narrative\n\n";
        
        // Add complexity-specific instructions
        switch ($complexityLevel) {
            case Simplification::COMPLEXITY_BASIC:
                $prompt .= "6. Use very simple words and short sentences\n";
                $prompt .= "7. Focus only on the most important 2-3 concepts\n";
                $prompt .= "8. Include lots of 'meow' and cat sounds for fun\n";
                break;
                
            case Simplification::COMPLEXITY_INTERMEDIATE:
                $prompt .= "6. Use clear but more detailed explanations\n";
                $prompt .= "7. Include multiple related concepts and their connections\n";
                $prompt .= "8. Add some educational cat facts that relate to the topic\n";
                break;
                
            case Simplification::COMPLEXITY_ADVANCED:
                $prompt .= "6. Include sophisticated vocabulary while keeping it accessible\n";
                $prompt .= "7. Explain complex relationships and nuanced concepts\n";
                $prompt .= "8. Use extended cat metaphors for deeper understanding\n";
                break;
        }
        
        $prompt .= "\nPlease respond with a JSON object containing:\n";
        $prompt .= "{\n";
        $prompt .= '  "simplified_title": "A catchy title for your cat story",';
        $prompt .= "\n";
        $prompt .= '  "cat_story": "The full cat story explanation",';
        $prompt .= "\n";
        $prompt .= '  "summary": "A brief 2-3 sentence summary of the main points",';
        $prompt .= "\n";
        $prompt .= '  "key_concepts": ["concept1", "concept2", "concept3"]';
        $prompt .= "\n";
        $prompt .= "}\n";
        
        return $prompt;
    }

    /**
     * Calculate maximum content length based on model and available tokens.
     */
    protected function calculateMaxContentLength(string $model): int
    {
        // Rough estimate: 1 token ≈ 4 characters
        $modelLimits = [
            'gpt-3.5-turbo' => 4096,
            'gpt-4' => 8192,
        ];
        
        $maxTokens = $modelLimits[$model] ?? 4096;
        $promptTokens = 800; // Estimated tokens for system prompt + instructions
        $responseTokens = $this->maxTokens; // Tokens reserved for response
        
        $availableTokens = $maxTokens - $promptTokens - $responseTokens;
        
        return max(1000, $availableTokens * 3); // Conservative estimate: 3 chars per token
    }

    /**
     * Make the actual API request to OpenAI.
     */
    protected function makeApiRequest(array $requestData): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])
        ->timeout($this->timeout)
        ->post($this->baseUrl . '/chat/completions', $requestData);

        if (!$response->successful()) {
            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? 'Unknown OpenAI API error';
            
            throw new \Exception("OpenAI API error: {$errorMessage} (Status: {$response->status()})");
        }

        return $response->json();
    }

    /**
     * Process the API response and extract relevant data.
     */
    protected function processResponse(array $response, Simplification $simplification, float $startTime): array
    {
        $endTime = microtime(true);
        $processingTime = round($endTime - $startTime, 2);
        
        // Extract usage information
        $usage = $response['usage'] ?? [];
        $promptTokens = $usage['prompt_tokens'] ?? 0;
        $completionTokens = $usage['completion_tokens'] ?? 0;
        $totalTokens = $usage['total_tokens'] ?? 0;
        
        // Calculate cost
        $cost = $this->calculateCost($simplification->ai_model, $promptTokens, $completionTokens);
        
        // Extract content
        $content = $response['choices'][0]['message']['content'] ?? '';
        
        // Parse JSON response
        $parsedContent = $this->parseContentResponse($content);
        
        // Calculate readability score
        $readabilityScore = $this->calculateReadabilityScore($parsedContent['cat_story'], $simplification->complexity_level);
        
        // Build quality metrics
        $qualityMetrics = $this->buildQualityMetrics($parsedContent, $simplification);
        
        return [
            'simplified_title' => $parsedContent['simplified_title'],
            'cat_story' => $parsedContent['cat_story'],
            'summary' => $parsedContent['summary'],
            'key_concepts' => $parsedContent['key_concepts'],
            'tokens_used' => $totalTokens,
            'processing_cost' => $cost,
            'processing_time_seconds' => $processingTime,
            'readability_score' => $readabilityScore,
            'quality_metrics' => $qualityMetrics,
        ];
    }

    /**
     * Parse JSON content from OpenAI response.
     */
    protected function parseContentResponse(string $content): array
    {
        // Try to extract JSON from the response
        $jsonStart = strpos($content, '{');
        $jsonEnd = strrpos($content, '}');
        
        if ($jsonStart === false || $jsonEnd === false) {
            throw new \Exception('Invalid response format: No JSON found');
        }
        
        $jsonContent = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
        $parsed = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON in response: ' . json_last_error_msg());
        }
        
        // Validate required fields
        $required = ['simplified_title', 'cat_story', 'summary', 'key_concepts'];
        foreach ($required as $field) {
            if (!isset($parsed[$field])) {
                throw new \Exception("Missing required field in response: {$field}");
            }
        }
        
        return $parsed;
    }

    /**
     * Calculate the cost of the API request.
     */
    protected function calculateCost(string $model, int $promptTokens, int $completionTokens): float
    {
        if (!isset($this->pricing[$model])) {
            return 0.0;
        }
        
        $pricing = $this->pricing[$model];
        
        $promptCost = ($promptTokens / 1000) * $pricing['input'];
        $completionCost = ($completionTokens / 1000) * $pricing['output'];
        
        return round($promptCost + $completionCost, 6);
    }

    /**
     * Calculate readability score based on complexity and content.
     */
    protected function calculateReadabilityScore(string $content, string $complexityLevel): int
    {
        // Simple readability calculation based on various factors
        $wordCount = str_word_count($content);
        $sentenceCount = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $sentenceCount = count($sentenceCount);
        
        if ($sentenceCount === 0) {
            return 5;
        }
        
        $avgWordsPerSentence = $wordCount / $sentenceCount;
        
        // Base score based on complexity level
        $baseScore = match($complexityLevel) {
            Simplification::COMPLEXITY_BASIC => 9,
            Simplification::COMPLEXITY_INTERMEDIATE => 7,
            Simplification::COMPLEXITY_ADVANCED => 5,
            default => 6,
        };
        
        // Adjust based on sentence complexity
        if ($avgWordsPerSentence > 20) {
            $baseScore -= 2;
        } elseif ($avgWordsPerSentence > 15) {
            $baseScore -= 1;
        } elseif ($avgWordsPerSentence < 8) {
            $baseScore += 1;
        }
        
        // Check for cat-themed content
        $catWords = ['cat', 'kitten', 'kitty', 'meow', 'purr', 'whiskers', 'paw', 'tail', 'feline'];
        $catWordCount = 0;
        foreach ($catWords as $catWord) {
            $catWordCount += substr_count(strtolower($content), $catWord);
        }
        
        if ($catWordCount > 5) {
            $baseScore += 1;
        }
        
        return max(1, min(10, $baseScore));
    }

    /**
     * Build quality metrics for the generated content.
     */
    protected function buildQualityMetrics(array $content, Simplification $simplification): array
    {
        $catStory = $content['cat_story'];
        $wordCount = str_word_count($catStory);
        
        return [
            'coherence_score' => $this->calculateCoherenceScore($catStory),
            'engagement_score' => $this->calculateEngagementScore($catStory),
            'accuracy_score' => $this->calculateAccuracyScore($catStory, $simplification->document),
            'creativity_score' => $this->calculateCreativityScore($catStory),
            'cat_theme_consistency' => $this->calculateCatThemeConsistency($catStory),
            'educational_value' => $this->calculateEducationalValue($catStory, $content['key_concepts']),
            'language_simplicity' => $this->calculateLanguageSimplicity($catStory, $simplification->complexity_level),
            'word_count' => $wordCount,
            'estimated_reading_time' => max(1, round($wordCount / 200)),
        ];
    }

    /**
     * Calculate coherence score (0.0 to 1.0).
     */
    protected function calculateCoherenceScore(string $content): float
    {
        // Simple coherence check based on narrative flow indicators
        $flowIndicators = ['first', 'then', 'next', 'finally', 'meanwhile', 'however', 'therefore', 'because'];
        $indicatorCount = 0;
        
        foreach ($flowIndicators as $indicator) {
            if (stripos($content, $indicator) !== false) {
                $indicatorCount++;
            }
        }
        
        $sentences = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $sentenceCount = count($sentences);
        
        if ($sentenceCount === 0) {
            return 0.5;
        }
        
        $coherenceRatio = min(1.0, $indicatorCount / max(1, $sentenceCount / 3));
        return max(0.3, min(1.0, 0.7 + ($coherenceRatio * 0.3)));
    }

    /**
     * Calculate engagement score (0.0 to 1.0).
     */
    protected function calculateEngagementScore(string $content): float
    {
        $engagementWords = ['exciting', 'amazing', 'wonderful', 'curious', 'adventure', 'discovered', 'surprised', 'delighted', 'fun'];
        $engagementCount = 0;
        
        foreach ($engagementWords as $word) {
            $engagementCount += substr_count(strtolower($content), $word);
        }
        
        $wordCount = str_word_count($content);
        if ($wordCount === 0) {
            return 0.5;
        }
        
        $engagementRatio = $engagementCount / $wordCount;
        return max(0.4, min(1.0, $engagementRatio * 20));
    }

    /**
     * Calculate accuracy score based on original document (simplified for demo).
     */
    protected function calculateAccuracyScore(string $catStory, $document): float
    {
        // This is a simplified approach - in reality, you'd use more sophisticated NLP
        $originalWords = str_word_count(strtolower($document->extracted_content), 1);
        $storyWords = str_word_count(strtolower($catStory), 1);
        
        $commonWords = array_intersect($originalWords, $storyWords);
        $accuracyRatio = count($commonWords) / max(1, count($originalWords));
        
        return max(0.6, min(1.0, $accuracyRatio * 2));
    }

    /**
     * Calculate creativity score (0.0 to 1.0).
     */
    protected function calculateCreativityScore(string $content): float
    {
        $creativeWords = ['magical', 'enchanted', 'mystical', 'clever', 'brilliant', 'ingenious', 'remarkable', 'extraordinary'];
        $creativeCount = 0;
        
        foreach ($creativeWords as $word) {
            $creativeCount += substr_count(strtolower($content), $word);
        }
        
        // Check for creative cat scenarios
        $catScenarios = ['cat cafe', 'cat kingdom', 'cat school', 'cat laboratory', 'cat library', 'cat detective'];
        $scenarioCount = 0;
        
        foreach ($catScenarios as $scenario) {
            if (stripos($content, $scenario) !== false) {
                $scenarioCount++;
            }
        }
        
        return max(0.3, min(1.0, ($creativeCount + $scenarioCount * 2) / 10));
    }

    /**
     * Calculate cat theme consistency (0.0 to 1.0).
     */
    protected function calculateCatThemeConsistency(string $content): float
    {
        $catWords = ['cat', 'kitten', 'kitty', 'meow', 'purr', 'whiskers', 'paw', 'tail', 'feline', 'scratch'];
        $catWordCount = 0;
        
        foreach ($catWords as $catWord) {
            $catWordCount += substr_count(strtolower($content), $catWord);
        }
        
        $wordCount = str_word_count($content);
        if ($wordCount === 0) {
            return 0.5;
        }
        
        $catRatio = $catWordCount / $wordCount;
        return max(0.4, min(1.0, $catRatio * 15));
    }

    /**
     * Calculate educational value (0.0 to 1.0).
     */
    protected function calculateEducationalValue(string $content, array $keyConcepts): float
    {
        $educationalWords = ['learn', 'understand', 'explain', 'discover', 'knowledge', 'education', 'concept', 'important'];
        $educationalCount = 0;
        
        foreach ($educationalWords as $word) {
            $educationalCount += substr_count(strtolower($content), $word);
        }
        
        $conceptMentions = 0;
        foreach ($keyConcepts as $concept) {
            if (stripos($content, $concept) !== false) {
                $conceptMentions++;
            }
        }
        
        return max(0.5, min(1.0, ($educationalCount + $conceptMentions * 2) / 15));
    }

    /**
     * Calculate language simplicity (0.0 to 1.0).
     */
    protected function calculateLanguageSimplicity(string $content, string $complexityLevel): float
    {
        $sentences = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $totalWords = 0;
        $totalSentences = count($sentences);
        
        foreach ($sentences as $sentence) {
            $totalWords += str_word_count($sentence);
        }
        
        if ($totalSentences === 0) {
            return 0.5;
        }
        
        $avgWordsPerSentence = $totalWords / $totalSentences;
        
        // Expected complexity based on level
        $expectedComplexity = match($complexityLevel) {
            Simplification::COMPLEXITY_BASIC => 8,
            Simplification::COMPLEXITY_INTERMEDIATE => 12,
            Simplification::COMPLEXITY_ADVANCED => 16,
            default => 12,
        };
        
        $simplicityScore = 1.0 - abs($avgWordsPerSentence - $expectedComplexity) / $expectedComplexity;
        return max(0.3, min(1.0, $simplicityScore));
    }

    /**
     * Test the OpenAI API connection.
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout(10)
            ->get($this->baseUrl . '/models');

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('OpenAI connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get available models from OpenAI.
     */
    public function getAvailableModels(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout(10)
            ->get($this->baseUrl . '/models');

            if ($response->successful()) {
                $data = $response->json();
                return collect($data['data'])
                    ->where('id', 'like', 'gpt*')
                    ->pluck('id')
                    ->sort()
                    ->values()
                    ->toArray();
            }
            
            return [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch OpenAI models', ['error' => $e->getMessage()]);
            return [];
        }
    }
}