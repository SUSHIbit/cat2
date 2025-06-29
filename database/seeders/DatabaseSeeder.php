<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Simplification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Database Seeder
 * 
 * Seeds the database with comprehensive test data including users, documents,
 * and simplifications in various states for development and testing.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test users
        $this->createTestUsers();
        
        // Create sample documents and simplifications
        $this->createSampleDocuments();
        
        // Create additional test data for comprehensive testing
        $this->createAdditionalTestData();
    }

    /**
     * Create test users with predefined accounts.
     */
    protected function createTestUsers(): void
    {
        // Create admin test user
        $admin = User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@catdocs.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create regular test user
        $user = User::factory()->create([
            'name' => 'Test Student',
            'email' => 'student@catdocs.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create demo user with sample data
        $demoUser = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@catdocs.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create additional random users
        User::factory(7)->create();

        $this->command->info('Created test users (admin@catdocs.com, student@catdocs.com, demo@catdocs.com)');
    }

    /**
     * Create sample documents with various states and types.
     */
    protected function createSampleDocuments(): void
    {
        $users = User::all();
        
        foreach ($users as $user) {
            // Create documents in different states
            $this->createDocumentsForUser($user);
        }

        $this->command->info('Created sample documents in various processing states');
    }

    /**
     * Create documents for a specific user.
     */
    protected function createDocumentsForUser(User $user): void
    {
        // Create processed academic documents
        $academicDocs = Document::factory(3)
            ->for($user)
            ->academic()
            ->processed()
            ->create();

        // Create processed business documents
        $businessDocs = Document::factory(2)
            ->for($user)
            ->business()
            ->processed()
            ->create();

        // Create documents currently being processed
        Document::factory(1)
            ->for($user)
            ->processing()
            ->create();

        // Create failed documents
        Document::factory(1)
            ->for($user)
            ->failed()
            ->create();

        // Create uploaded documents ready for processing
        Document::factory(2)
            ->for($user)
            ->create();

        // Create simplifications for processed documents
        $processedDocs = $academicDocs->merge($businessDocs);
        
        foreach ($processedDocs as $document) {
            $this->createSimplificationsForDocument($document, $user);
        }
    }

    /**
     * Create simplifications for a document.
     */
    protected function createSimplificationsForDocument(Document $document, User $user): void
    {
        // Create 1-3 simplifications per document
        $count = rand(1, 3);
        
        for ($i = 0; $i < $count; $i++) {
            $simplification = Simplification::factory()
                ->for($document)
                ->for($user)
                ->completed()
                ->create();

            // Randomly make some simplifications favorites
            if (rand(1, 100) <= 30) { // 30% chance
                $simplification->update([
                    'is_favorite' => true,
                    'user_rating' => rand(4, 5),
                    'user_notes' => 'Great simplification! Very helpful for understanding the concepts.',
                ]);
            }

            // Randomly make some simplifications public
            if (rand(1, 100) <= 20) { // 20% chance
                $simplification->makePublic();
                $simplification->update([
                    'download_count' => rand(0, 25),
                    'last_downloaded_at' => now()->subDays(rand(1, 30)),
                ]);
            }

            // Randomly add user ratings
            if (rand(1, 100) <= 60) { // 60% chance
                $simplification->update([
                    'user_rating' => rand(3, 5),
                ]);
            }
        }
    }

    /**
     * Create additional test data for edge cases and comprehensive testing.
     */
    protected function createAdditionalTestData(): void
    {
        // Get demo user for specific test scenarios
        $demoUser = User::where('email', 'demo@catdocs.com')->first();
        
        if ($demoUser) {
            $this->createSpecialTestCases($demoUser);
        }

        // Create some public simplifications from different users
        $this->createPublicSimplifications();
        
        // Create simplifications in various processing states
        $this->createProcessingStateExamples();

        $this->command->info('Created additional test data and edge cases');
    }

    /**
     * Create special test cases for the demo user.
     */
    protected function createSpecialTestCases(User $demoUser): void
    {
        // Large document
        $largeDoc = Document::factory()
            ->for($demoUser)
            ->large()
            ->pdf()
            ->processed()
            ->create([
                'title' => 'Large Research Paper - Climate Change Analysis',
                'description' => 'Comprehensive 50-page research paper on climate change impacts.',
            ]);

        // Small document
        $smallDoc = Document::factory()
            ->for($demoUser)
            ->small()
            ->docx()
            ->processed()
            ->create([
                'title' => 'Quick Reference Guide',
                'description' => 'Simple 2-page reference document.',
            ]);

        // PowerPoint presentation
        $pptDoc = Document::factory()
            ->for($demoUser)
            ->pptx()
            ->processed()
            ->create([
                'title' => 'Marketing Strategy Presentation',
                'description' => 'Business presentation with charts and graphs.',
            ]);

        // Create simplifications for these special documents
        foreach ([$largeDoc, $smallDoc, $pptDoc] as $doc) {
            // Create one simplification for each complexity level
            foreach (['basic', 'intermediate', 'advanced'] as $complexity) {
                Simplification::factory()
                    ->for($doc)
                    ->for($demoUser)
                    ->completed()
                    ->$complexity()
                    ->create();
            }
        }

        // Create an archived document
        Document::factory()
            ->for($demoUser)
            ->archived()
            ->create([
                'title' => 'Old Project Documentation',
                'description' => 'Legacy document that has been archived.',
            ]);
    }

    /**
     * Create public simplifications for sharing examples.
     */
    protected function createPublicSimplifications(): void
    {
        $users = User::limit(3)->get();
        
        foreach ($users as $user) {
            $documents = $user->processedDocuments()->limit(2)->get();
            
            foreach ($documents as $document) {
                $simplification = Simplification::factory()
                    ->for($document)
                    ->for($user)
                    ->completed()
                    ->public()
                    ->highlyRated()
                    ->create();
            }
        }
    }

    /**
     * Create examples of simplifications in various processing states.
     */
    protected function createProcessingStateExamples(): void
    {
        $users = User::limit(2)->get();
        
        foreach ($users as $user) {
            $document = Document::factory()
                ->for($user)
                ->processed()
                ->create();

            // Pending simplification
            Simplification::factory()
                ->for($document)
                ->for($user)
                ->create([
                    'status' => Simplification::STATUS_PENDING,
                ]);

            // Processing simplification
            Simplification::factory()
                ->for($document)
                ->for($user)
                ->processing()
                ->create();

            // Failed simplification
            Simplification::factory()
                ->for($document)
                ->for($user)
                ->failed()
                ->create();
        }
    }
}