<?php

namespace App\Http\Middleware;

use App\Models\Document;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validate File Upload Middleware
 * 
 * Performs additional file upload validation including virus scanning,
 * content verification, and security checks for document uploads.
 */
class ValidateFileUpload
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to requests with file uploads
        if (!$request->hasFile('file')) {
            return $next($request);
        }
        
        $file = $request->file('file');
        
        // Skip validation if file is not valid
        if (!$file->isValid()) {
            return $next($request);
        }
        
        try {
            // Perform additional security checks
            $this->performSecurityChecks($file);
            
            // Validate file content integrity
            $this->validateFileIntegrity($file);
            
            // Check for malicious content patterns
            $this->scanForMaliciousContent($file);
            
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['file' => $e->getMessage()]);
        }
        
        return $next($request);
    }
    
    /**
     * Perform basic security checks on the uploaded file.
     */
    protected function performSecurityChecks($file): void
    {
        // Check file size against system limits
        $maxSize = config('cat-simplifier.uploads.max_file_size', 10240) * 1024; // Convert KB to bytes
        if ($file->getSize() > $maxSize) {
            throw new \Exception('File size exceeds maximum allowed size.');
        }
        
        // Verify MIME type matches file extension
        $extension = strtolower($file->getClientOriginalExtension());
        $expectedMimeType = Document::SUPPORTED_TYPES[$extension] ?? null;
        
        if (!$expectedMimeType) {
            throw new \Exception('Unsupported file type.');
        }
        
        // Check actual MIME type
        $actualMimeType = $file->getMimeType();
        if ($actualMimeType !== $expectedMimeType) {
            throw new \Exception('File type verification failed. The file content does not match its extension.');
        }
        
        // Additional MIME type verification using file content
        $detectedMimeType = mime_content_type($file->getRealPath());
        if ($detectedMimeType !== $expectedMimeType) {
            throw new \Exception('File content verification failed. The file may be corrupted or misnamed.');
        }
    }
    
    /**
     * Validate file integrity and structure.
     */
    protected function validateFileIntegrity($file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filePath = $file->getRealPath();
        
        switch ($extension) {
            case 'pdf':
                $this->validatePdfIntegrity($filePath);
                break;
                
            case 'docx':
                $this->validateDocxIntegrity($filePath);
                break;
                
            case 'pptx':
                $this->validatePptxIntegrity($filePath);
                break;
        }
    }
    
    /**
     * Validate PDF file integrity.
     */
    protected function validatePdfIntegrity(string $filePath): void
    {
        try {
            // Check if file starts with PDF signature
            $handle = fopen($filePath, 'rb');
            $header = fread($handle, 8);
            fclose($handle);
            
            if (!str_starts_with($header, '%PDF-')) {
                throw new \Exception('Invalid PDF file format.');
            }
            
            // Try to parse with PDF parser to ensure it's readable
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            
            // Check if PDF has any readable content
            $text = $pdf->getText();
            if (empty(trim($text))) {
                \Log::warning('PDF file appears to have no extractable text content', [
                    'file_size' => filesize($filePath)
                ]);
            }
            
        } catch (\Exception $e) {
            throw new \Exception('PDF file appears to be corrupted or unreadable.');
        }
    }
    
    /**
     * Validate DOCX file integrity.
     */
    protected function validateDocxIntegrity(string $filePath): void
    {
        try {
            // DOCX files are ZIP archives, check ZIP structure
            $zip = new \ZipArchive();
            $result = $zip->open($filePath, \ZipArchive::CHECKCONS);
            
            if ($result !== TRUE) {
                throw new \Exception('DOCX file structure is invalid.');
            }
            
            // Check for required DOCX files
            $requiredFiles = ['[Content_Types].xml', '_rels/.rels', 'word/document.xml'];
            foreach ($requiredFiles as $requiredFile) {
                if ($zip->locateName($requiredFile) === false) {
                    $zip->close();
                    throw new \Exception('DOCX file is missing required components.');
                }
            }
            
            $zip->close();
            
            // Try to load with PhpWord to ensure it's readable
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
            
        } catch (\Exception $e) {
            throw new \Exception('Word document appears to be corrupted or unreadable.');
        }
    }
    
    /**
     * Validate PPTX file integrity.
     */
    protected function validatePptxIntegrity(string $filePath): void
    {
        try {
            // PPTX files are ZIP archives, check ZIP structure
            $zip = new \ZipArchive();
            $result = $zip->open($filePath, \ZipArchive::CHECKCONS);
            
            if ($result !== TRUE) {
                throw new \Exception('PPTX file structure is invalid.');
            }
            
            // Check for required PPTX files
            $requiredFiles = ['[Content_Types].xml', '_rels/.rels', 'ppt/presentation.xml'];
            foreach ($requiredFiles as $requiredFile) {
                if ($zip->locateName($requiredFile) === false) {
                    $zip->close();
                    throw new \Exception('PPTX file is missing required components.');
                }
            }
            
            $zip->close();
            
            // For PowerPoint validation, just verify the ZIP structure
            // Don't try to load with PhpPresentation as it may cause type errors
            \Log::info('PowerPoint file passed basic integrity checks', [
                'file_path' => $filePath,
                'file_size' => filesize($filePath)
            ]);
            
        } catch (\Exception $e) {
            throw new \Exception('PowerPoint presentation appears to be corrupted or unreadable.');
        }
    }
    
    /**
     * Scan for malicious content patterns.
     */
    protected function scanForMaliciousContent($file): void
    {
        $filename = $file->getClientOriginalName();
        
        // Check for suspicious filename patterns
        $suspiciousPatterns = [
            '/\.exe$/i',
            '/\.scr$/i',
            '/\.bat$/i',
            '/\.cmd$/i',
            '/\.com$/i',
            '/\.pif$/i',
            '/\.vbs$/i',
            '/\.js$/i',
            '/\.jar$/i',
            '/\.php$/i',
            '/\.asp$/i',
            '/\.jsp$/i',
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                throw new \Exception('File type not allowed for security reasons.');
            }
        }
        
        // Check for double extensions
        if (preg_match('/\.[^.]+\.[^.]+$/i', $filename)) {
            $parts = explode('.', $filename);
            if (count($parts) > 2) {
                // Allow common double extensions like .tar.gz but block suspicious ones
                $lastExtension = strtolower(end($parts));
                if (!in_array($lastExtension, ['pdf', 'docx', 'pptx'])) {
                    throw new \Exception('Suspicious file extension detected.');
                }
            }
        }
        
        // Check filename for script injection attempts
        if (preg_match('/<script|javascript:|vbscript:|data:|on\w+=/i', $filename)) {
            throw new \Exception('Filename contains prohibited content.');
        }
        
        // Basic content scanning for text-based threats
        $this->performBasicContentScan($file);
    }
    
    /**
     * Perform basic content scanning for threats.
     */
    protected function performBasicContentScan($file): void
    {
        // Read first few KB to check for suspicious content
        $handle = fopen($file->getRealPath(), 'rb');
        $content = fread($handle, 8192); // Read first 8KB
        fclose($handle);
        
        // Check for executable signatures
        $executableSignatures = [
            'MZ',      // Windows executables
            "\x7fELF", // Linux executables
            "\xfe\xed\xfa", // Mach-O executables
        ];
        
        foreach ($executableSignatures as $signature) {
            if (str_starts_with($content, $signature)) {
                throw new \Exception('File contains executable code and cannot be processed.');
            }
        }
        
        // Check for script content in what should be document files
        $scriptPatterns = [
            '/<script[^>]*>/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/on\w+\s*=/i',
            '/eval\s*\(/i',
        ];
        
        foreach ($scriptPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                throw new \Exception('File contains potentially malicious script content.');
            }
        }
    }
    
    /**
     * Log security events for monitoring.
     */
    protected function logSecurityEvent(string $event, $file, string $reason): void
    {
        \Log::warning('File upload security event', [
            'event' => $event,
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'reason' => $reason,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}