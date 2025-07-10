<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostTemplate;
use App\Helpers\Helpers;
use Exception;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class ProjectTestController extends Controller
{
    public function downloadDocument($id)
    {
        $id = Helpers::decrypt($id);
        $postTemplate = PostTemplate::with('postContent','category:id,name')->find($id);
        if (!$postTemplate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post Template not found',
            ]);
        }
    
        $postContentData = $postTemplate->postContent;
    
        // Create a new Word document
        $phpWord = new PhpWord();
        
        // Define custom styles
        $phpWord->addTitleStyle(1, [
            'name' => 'Arial',
            'size' => 20,
            'bold' => true,
            'color' => '2E74B5'
        ]);
        
        $phpWord->addTitleStyle(2, [
            'name' => 'Arial',
            'size' => 16,
            'bold' => true,
            'color' => '4A90C2'
        ]);
        
        // Define paragraph styles
        $phpWord->addParagraphStyle('headerStyle', [
            'alignment' => 'center',
            'spaceAfter' => 240
        ]);
        
        $phpWord->addParagraphStyle('contentStyle', [
            'alignment' => 'left',
            'spaceAfter' => 120,
            'spaceBefore' => 80
        ]);
        
        $phpWord->addParagraphStyle('centeredStyle', [
            'alignment' => 'center',
            'spaceAfter' => 200
        ]);
        
        // Define font styles
        $titleFont = [
            'name' => 'Arial',
            'size' => 18,
            'bold' => true,
            'color' => '1F4E79'
        ];
        
        $labelFont = [
            'name' => 'Arial',
            'size' => 12,
            'bold' => true,
            'color' => '2E74B5'
        ];
        
        $contentFont = [
            'name' => 'Arial',
            'size' => 11,
            'color' => '333333'
        ];
        
        $warningFont = [
            'name' => 'Arial',
            'size' => 11,
            'color' => 'D32F2F',
            'italic' => true
        ];
        
        // Add a section to the document with margins
        $section = $phpWord->addSection([
            'marginLeft' => 720,
            'marginRight' => 720,
            'marginTop' => 720,
            'marginBottom' => 720
        ]);
        
        // Add main title
        $section->addTitle($postContentData->title, 1);
        $section->addTextBreak(1);
        
        // Add a separator line
        $section->addText(str_repeat('_', 80), ['color' => 'CCCCCC'], 'centeredStyle');
        $section->addTextBreak(1);
        
        // Add Category
        $section->addText('Category: ', $labelFont, 'contentStyle');
        $section->addText($postTemplate->category->name ?? 'Uncategorized', $contentFont);
        $section->addTextBreak(2);
        
        // Add Description with HTML parsing
        $section->addText('Description:', $labelFont, 'contentStyle');
        // $section->addTextBreak(1);
        
        // Parse HTML content from Quill editor
        $this->addHtmlContentToSection($section, $postContentData->description, $contentFont);
        
        $section->addTextBreak(2);
        
        // Add Warning Message
        if (!empty($postContentData->warning_message)) {
            $section->addText('⚠️ Warning:', $labelFont, 'contentStyle');
            // $section->addTextBreak(1);
            $section->addText($postContentData->warning_message, $warningFont, 'contentStyle');
        }
        
        // Add separator before image
        $section->addText(str_repeat('_', 80), ['color' => 'CCCCCC'], 'centeredStyle');
        // $section->addTextBreak(1);
        
        // Add Template Image
        if (!empty($postTemplate->template_image)) {
            $imageContent = file_get_contents($postTemplate->template_image);
            $imagePath = tempnam(sys_get_temp_dir(), 'img');
            file_put_contents($imagePath, $imageContent);
            
            $section->addImage($imagePath, [
                'width' => 200,
                'height' => 250,
                'alignment' => 'center'
            ]);
        }
        // Add footer
        // $section->addTextBreak(2);
        // $section->addText(str_repeat('_', 80), ['color' => 'CCCCCC'], 'centeredStyle');
        // $section->addText('Generated on: ' . date('Y-m-d H:i:s'), [
        //     'name' => 'Arial',
        //     'size' => 10,
        //     'color' => '666666',
        //     'italic' => true
        // ], 'centeredStyle');
        
        // Save the document to a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'word') . '.docx';
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);
        
        // Generate filename with title
        $filename = 'post-template-' . time() . '.docx';

        unlink($imagePath);
        
        // Return the file as a download response
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
    
    /**
     * Parse HTML content from Quill editor and add to Word document
     */
    private function addHtmlContentToSection($section, $htmlContent, $defaultFont)
    {
        if (empty($htmlContent)) {
            $section->addText('No description provided', [
                'name' => 'Arial',
                'size' => 11,
                'color' => '999999',
                'italic' => true
            ]);
            return;
        }
        
        // Basic HTML parsing - you might want to use a more robust HTML parser
        $content = $htmlContent;
        
        // Remove HTML tags but preserve line breaks
        $content = str_replace(['<br>', '<br/>', '<br />'], "\n", $content);
        $content = str_replace(['<p>', '</p>'], ["\n", "\n"], $content);
        $content = str_replace(['<div>', '</div>'], ["\n", "\n"], $content);
        
        // Handle basic formatting
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Check for bold text
            if (preg_match_all('/<strong>(.*?)<\/strong>/', $line, $matches)) {
                $line = preg_replace('/<strong>(.*?)<\/strong>/', '$1', $line);
                $font = array_merge($defaultFont, ['bold' => true]);
            } 
            // Check for italic text
            elseif (preg_match_all('/<em>(.*?)<\/em>/', $line, $matches)) {
                $line = preg_replace('/<em>(.*?)<\/em>/', '$1', $line);
                $font = array_merge($defaultFont, ['italic' => true]);
            } 
            // Check for underlined text
            elseif (preg_match_all('/<u>(.*?)<\/u>/', $line, $matches)) {
                $line = preg_replace('/<u>(.*?)<\/u>/', '$1', $line);
                $font = array_merge($defaultFont, ['underline' => 'single']);
            } else {
                $font = $defaultFont;
            }
            
            // Remove any remaining HTML tags
            $line = strip_tags($line);
            $line = html_entity_decode($line, ENT_QUOTES, 'UTF-8');
            
            if (!empty($line)) {
                $section->addText($line, $font, 'contentStyle');
            }
        }
    }

    public function downloadDocumentNew($id)
    {
        $id = Helpers::decrypt($id);
        $postTemplate = PostTemplate::with('postContent','category')->find($id);
        if (!$postTemplate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post Template not found',
            ]);
        }

        $postContentData = $postTemplate->postContent;
        // dd($postTemplate,$postContentData);

        // Create a new Word document
        $phpWord = new PhpWord();
        
        // Add a section to the document
        $section = $phpWord->addSection();
        
        // Add title
        $section->addText($postContentData->title, ['name' => 'Arial', 'size' => 16, 'bold' => true]);
        
        // Add image (download it first if it's a URL)
        $imageContent = file_get_contents($postTemplate->template_image);
        $imagePath = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($imagePath, $imageContent);
        
        $section->addImage($imagePath, [
            'width' => 200,
            'height' => 200,
            'alignment' => 'center'
        ]);
        
        // Add description
        $section->addText($postContentData->description, ['name' => 'Arial', 'size' => 12]);
        
        // Add metadata
        $section->addText("Category: " . $postContentData->category->name);
        
        // Save the document to a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'word') . '.docx';
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);
        
        // Clean up the temporary image file
        unlink($imagePath);
        
        // Return the file as a download response
        return response()->download($tempFile, 'post-template.docx')->deleteFileAfterSend(true);
    }
}
