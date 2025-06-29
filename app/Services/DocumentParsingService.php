<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpPresentation\IOFactory as PresentationIOFactory;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Document Parsing Service
 * 
 * Handles extraction of text content from various document formats
 * including PDF, Word documents, and PowerPoint presentations.
 */
class DocumentParsingService
{
    /**
     * Extract text content from a document.
     */
    public function extractContent(Document $document): string
    {
        $filePath = $document->getAbsoluteFilePath();
        
        if (!file_exists($filePath)) {
            throw new \Exception('Document file not found: ' . $filePath);
        }
        
        try {
            $content = match($document->getFileExtension()) {
                'pdf' => $this->extractFromPdf($filePath),
                'docx' => $this->extractFromWord($filePath),
                'pptx' => $this->extractFromPowerPoint($filePath),
                default => throw new \Exception('Unsupported file type: ' . $document->getFileExtension()),
            };
            
            // Clean and validate extracted content
            $cleanContent = $this->cleanExtractedContent($content);
            
            if (empty($cleanContent)) {
                throw new \Exception('No readable content found in document');
            }
            
            return $cleanContent;
            
        } catch (\Exception $e) {
            Log::error('Document content extraction failed', [
                'document_id' => $document->id,
                'file_path' => $filePath,
                'file_type' => $document->getFileExtension(),
                'error' => $e->getMessage(),
            ]);
            
            throw new \Exception('Failed to extract content: ' . $e->getMessage());
        }
    }

    /**
     * Extract text content from PDF files.
     */
    protected function extractFromPdf(string $filePath): string
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($filePath);
            
            $text = $pdf->getText();
            
            if (empty(trim($text))) {
                throw new \Exception('PDF appears to be empty or contains only images');
            }
            
            return $text;
            
        } catch (\Exception $e) {
            throw new \Exception('PDF parsing error: ' . $e->getMessage());
        }
    }

    /**
     * Extract text content from Word documents.
     */
    protected function extractFromWord(string $filePath): string
    {
        try {
            $phpWord = WordIOFactory::load($filePath);
            $content = [];
            
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $elementContent = $this->extractWordElement($element);
                    if (!empty($elementContent)) {
                        $content[] = $elementContent;
                    }
                }
            }
            
            $text = implode("\n\n", $content);
            
            if (empty(trim($text))) {
                throw new \Exception('Word document appears to be empty');
            }
            
            return $text;
            
        } catch (\Exception $e) {
            throw new \Exception('Word document parsing error: ' . $e->getMessage());
        }
    }

    /**
     * Extract text content from PowerPoint presentations.
     */
    protected function extractFromPowerPoint(string $filePath): string
    {
        try {
            $presentation = PresentationIOFactory::load($filePath);
            $content = [];
            
            $slideNumber = 1;
            foreach ($presentation->getAllSlides() as $slide) {
                $slideContent = ["Slide {$slideNumber}:"];
                
                foreach ($slide->getShapeCollection() as $shape) {
                    $shapeContent = $this->extractPowerPointShape($shape);
                    if (!empty($shapeContent)) {
                        $slideContent[] = $shapeContent;
                    }
                }
                
                if (count($slideContent) > 1) { // Only add if there's content beyond the slide header
                    $content[] = implode("\n", $slideContent);
                }
                
                $slideNumber++;
            }
            
            $text = implode("\n\n", $content);
            
            if (empty(trim($text))) {
                throw new \Exception('PowerPoint presentation appears to be empty');
            }
            
            return $text;
            
        } catch (\Exception $e) {
            throw new \Exception('PowerPoint parsing error: ' . $e->getMessage());
        }
    }

    /**
     * Extract text from Word document elements recursively.
     */
    protected function extractWordElement($element): string
    {
        $content = [];
        
        // Handle different element types
        switch (get_class($element)) {
            case 'PhpOffice\PhpWord\Element\TextRun':
                foreach ($element->getElements() as $textElement) {
                    if (method_exists($textElement, 'getText')) {
                        $content[] = $textElement->getText();
                    }
                }
                break;
                
            case 'PhpOffice\PhpWord\Element\Text':
                $content[] = $element->getText();
                break;
                
            case 'PhpOffice\PhpWord\Element\Table':
                foreach ($element->getRows() as $row) {
                    $rowContent = [];
                    foreach ($row->getCells() as $cell) {
                        $cellContent = [];
                        foreach ($cell->getElements() as $cellElement) {
                            $cellText = $this->extractWordElement($cellElement);
                            if (!empty($cellText)) {
                                $cellContent[] = $cellText;
                            }
                        }
                        if (!empty($cellContent)) {
                            $rowContent[] = implode(' ', $cellContent);
                        }
                    }
                    if (!empty($rowContent)) {
                        $content[] = implode(' | ', $rowContent);
                    }
                }
                break;
                
            case 'PhpOffice\PhpWord\Element\ListItem':
                $listContent = [];
                foreach ($element->getElements() as $listElement) {
                    $listText = $this->extractWordElement($listElement);
                    if (!empty($listText)) {
                        $listContent[] = $listText;
                    }
                }
                if (!empty($listContent)) {
                    $content[] = '• ' . implode(' ', $listContent);
                }
                break;
        }
        
        return implode(' ', $content);
    }

    /**
     * Extract text from PowerPoint shapes.
     */
    protected function extractPowerPointShape($shape): string
    {
        $content = [];
        
        try {
            // Handle text shapes
            if (method_exists($shape, 'getActiveParagraphs')) {
                foreach ($shape->getActiveParagraphs() as $paragraph) {
                    foreach ($paragraph->getRichTextElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $text = trim($element->getText());
                            if (!empty($text)) {
                                $content[] = $text;
                            }
                        }
                    }
                }
            }
            
            // Handle table shapes
            if (method_exists($shape, 'getRows')) {
                foreach ($shape->getRows() as $row) {
                    $rowContent = [];
                    foreach ($row->getCells() as $cell) {
                        foreach ($cell->getActiveParagraphs() as $paragraph) {
                            foreach ($paragraph->getRichTextElements() as $element) {
                                if (method_exists($element, 'getText')) {
                                    $text = trim($element->getText());
                                    if (!empty($text)) {
                                        $rowContent[] = $text;
                                    }
                                }
                            }
                        }
                    }
                    if (!empty($rowContent)) {
                        $content[] = implode(' | ', $rowContent);
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::warning('Error extracting PowerPoint shape content', [
                'error' => $e->getMessage(),
                'shape_type' => get_class($shape),
            ]);
        }
        
        return implode("\n", $content);
    }

    /**
     * Clean and normalize extracted content.
     */
    protected function cleanExtractedContent(string $content): string
    {
        // Remove excessive whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Remove multiple consecutive newlines
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        // Remove control characters except newlines and tabs
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);
        
        // Remove common PDF artifacts
        $content = preg_replace('/\f+/', "\n", $content);
        
        // Fix common encoding issues using hex codes
        $content = str_replace([
            "\xe2\x80\x9c", // Left double quotation mark
            "\xe2\x80\x9d", // Right double quotation mark
            "\xe2\x80\x98", // Left single quotation mark
            "\xe2\x80\x99", // Right single quotation mark
        ], ['"', '"', "'", "'"], $content);
        
        // Normalize line endings
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        
        // Trim and ensure proper encoding
        $content = trim($content);
        
        // Ensure content is valid UTF-8
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'auto');
        }
        
        return $content;
    }

    /**
     * Validate extracted content quality.
     */
    public function validateContentQuality(string $content): array
    {
        $wordCount = str_word_count($content);
        $characterCount = mb_strlen($content);
        $sentences = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $sentenceCount = count($sentences);
        
        $issues = [];
        
        // Check minimum content requirements
        if ($wordCount < 10) {
            $issues[] = 'Content has too few words (minimum 10 required)';
        }
        
        if ($characterCount < 50) {
            $issues[] = 'Content is too short (minimum 50 characters required)';
        }
        
        // Check for gibberish or encoding issues
        $alphanumericRatio = preg_match_all('/[a-zA-Z0-9]/', $content) / max(1, $characterCount);
        if ($alphanumericRatio < 0.7) {
            $issues[] = 'Content may contain encoding issues or excessive special characters';
        }
        
        // Check for reasonable sentence structure
        if ($sentenceCount > 0) {
            $averageWordsPerSentence = $wordCount / $sentenceCount;
            if ($averageWordsPerSentence < 3) {
                $issues[] = 'Content may be fragmented or poorly structured';
            }
        }
        
        // Check for excessive repetition
        $words = str_word_count(strtolower($content), 1);
        $uniqueWords = array_unique($words);
        $uniqueRatio = count($uniqueWords) / max(1, count($words));
        
        if ($uniqueRatio < 0.3 && $wordCount > 50) {
            $issues[] = 'Content contains excessive repetition';
        }
        
        return [
            'is_valid' => empty($issues),
            'issues' => $issues,
            'statistics' => [
                'word_count' => $wordCount,
                'character_count' => $characterCount,
                'sentence_count' => $sentenceCount,
                'unique_word_ratio' => $uniqueRatio,
                'alphanumeric_ratio' => $alphanumericRatio,
            ],
        ];
    }

    /**
     * Get document metadata during parsing.
     */
    public function extractMetadata(Document $document): array
    {
        $filePath = $document->getAbsoluteFilePath();
        $metadata = [];
        
        try {
            switch ($document->getFileExtension()) {
                case 'pdf':
                    $metadata = $this->extractPdfMetadata($filePath);
                    break;
                case 'docx':
                    $metadata = $this->extractWordMetadata($filePath);
                    break;
                case 'pptx':
                    $metadata = $this->extractPowerPointMetadata($filePath);
                    break;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to extract document metadata', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
        }
        
        return $metadata;
    }

    /**
     * Extract metadata from PDF files.
     */
    protected function extractPdfMetadata(string $filePath): array
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($filePath);
            $details = $pdf->getDetails();
            
            return [
                'pages' => $details['Pages'] ?? 0,
                'title' => $details['Title'] ?? null,
                'author' => $details['Author'] ?? null,
                'creator' => $details['Creator'] ?? null,
                'producer' => $details['Producer'] ?? null,
                'creation_date' => $details['CreationDate'] ?? null,
                'modification_date' => $details['ModDate'] ?? null,
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Extract metadata from Word documents.
     */
    protected function extractWordMetadata(string $filePath): array
    {
        try {
            $phpWord = WordIOFactory::load($filePath);
            $properties = $phpWord->getDocInfo();
            
            return [
                'title' => $properties->getTitle(),
                'author' => $properties->getCreator(),
                'company' => $properties->getCompany(),
                'description' => $properties->getDescription(),
                'keywords' => $properties->getKeywords(),
                'creation_date' => $properties->getCreated(),
                'modification_date' => $properties->getModified(),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Extract metadata from PowerPoint presentations.
     */
    protected function extractPowerPointMetadata(string $filePath): array
    {
        try {
            $presentation = PresentationIOFactory::load($filePath);
            $properties = $presentation->getDocumentProperties();
            
            return [
                'title' => $properties->getTitle(),
                'author' => $properties->getCreator(),
                'company' => $properties->getCompany(),
                'description' => $properties->getDescription(),
                'keywords' => $properties->getKeywords(),
                'creation_date' => $properties->getCreated(),
                'modification_date' => $properties->getModified(),
                'slides' => $presentation->getSlideCount(),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }
}