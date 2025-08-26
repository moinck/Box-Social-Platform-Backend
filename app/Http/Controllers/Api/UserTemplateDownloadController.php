<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserTemplates;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use App\Models\PostTemplate;
use App\Helpers\Helpers;
use App\Models\BrandKit;
use App\Models\User;
use App\Models\UserSubscription;

class UserTemplateDownloadController extends Controller
{
    public function downloadDocument(Request $request)
    {
        $request->validate([
            'document_ids' => 'required',
            'is_images' => 'required',
        ]);

        $isImages = $request->is_images;
        $decryptIds = [];
        foreach ($request->document_ids as $id) {
            $decryptIds[] = Helpers::decrypt($id);
        }
        $userTemplates = UserTemplates::select('id','template_id','category_id','template_name','template_image','user_id')
            ->with('template.postContent','category:id,name')
            ->whereIn('id', $decryptIds)
            ->get();
    
        if (!$userTemplates) {
            return response()->json([
                'status' => 'error',
                'message' => 'User Template not found',
            ]);
        }

        // get users Detail
        $userDetail = User::select('id','first_name','last_name','email')
            ->where('id', $userTemplates->first()->user_id)
            ->first();
        
        if (!empty($userDetail)) {
            $fullName = $userDetail->first_name . ' ' . $userDetail->last_name;
        } else {
            $fullName = 'Template Name';
        }
        
        // Create a new Word document (moved outside the loop)
        $phpWord = new PhpWord();
        
        // Define custom styles (moved outside the loop)
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
        
        // Add a section to the document with margins (moved outside the loop)
        $section = $phpWord->addSection([
            'marginLeft' => 720,
            'marginRight' => 720,
            'marginTop' => 720,
            'marginBottom' => 720
        ]);
        
        $imagePathsToCleanup = []; // Array to store image paths for cleanup
        
        foreach ($userTemplates as $index => $userTemplate) {            
            // get user brandkit data
            $brnadKitData = BrandKit::select(['id','company_name','email','phone','website'])
                ->where('user_id', $userTemplate->user_id)
                ->first()
                ->toArray();
            
    
            $mainTemplateData = $userTemplate->template ?? null;
            $postContentData = $mainTemplateData->postContent ?? null;
    
            $updatedDescription = null;
            if (!empty($postContentData->description)) {
                $updatedDescription = str_replace(['|name|', '|email|', '|phone|', '|website|'],
                    [$fullName ?? '', $brnadKitData['email'] ?? '', $brnadKitData['phone'] ?? '', $brnadKitData['website'] ?? ''],
                    $postContentData->description);
            }
    
            if (!empty($postContentData)) {
                $writtenData = [
                    "title" => $index + 1 . '. ' . $postContentData->title ?? $userTemplate->template_name,
                    "category_name" => $userTemplate->category->name ?? 'Uncategorized',
                    "description" => $updatedDescription ?? "No description provided",
                    "warning_message" => $postContentData->warning_message ?? "No warning message provided",
                ];
            } else {
                $writtenData = [
                    "title" => $index + 1 . '. ' . ($userTemplate->template_name ?? 'No Template Name'),
                    "category_name" => $userTemplate->category->name ?? 'Uncategorized',
                    "description" => "No description provided",
                    "warning_message" => "No warning message provided",
                ];
            }
        
            // Add main title
            $section->addTitle($writtenData['title'], 1);
            $section->addTextBreak(1);
            
            // Add a separator line
            $section->addText(str_repeat('_', 80), ['color' => 'CCCCCC'], 'centeredStyle');
            $section->addTextBreak(1);
    
            
            // Add Category
            $section->addText('Category: ', $labelFont, 'contentStyle');
            $section->addText($writtenData['category_name'], $contentFont);
            $section->addTextBreak(2);
            
            // Add Description with HTML parsing
            $section->addText('Description:', $labelFont, 'contentStyle');
            
            // Parse HTML content from Quill editor
            $this->htmlContentConversation($section, $writtenData['description'], $contentFont);
            
            $section->addTextBreak(2);
            
            // Add Warning Message
            $section->addText('⚠️ Warning:', $labelFont, 'contentStyle');
            $section->addText($writtenData['warning_message'], $warningFont, 'contentStyle');
    
            if ($isImages == 'true') {
                // Add separator before image
                $section->addText(str_repeat('_', 80), ['color' => 'CCCCCC'], 'centeredStyle');
                // Add Template Image
                if (!empty($userTemplate->template_image)) {
                    $imageContent = file_get_contents($userTemplate->template_image);
                    $imagePath = tempnam(sys_get_temp_dir(), 'img');
                    file_put_contents($imagePath, $imageContent);
                    $imagePathsToCleanup[] = $imagePath; // Store for cleanup
                    
                    $section->addImage($imagePath, [
                        'width' => 200,
                        'height' => 250,
                        'alignment' => 'center'
                    ]);
                }
            }
    
            // Add page break only if it's not the last template
            if ($index < count($userTemplates) - 1) {
                $section->addPageBreak();
            }
        }
    
        
        // Save the document to a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'word') . '.docx';
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);
        
        // Generate filename with title
        $filename = 'New Document-' . time() . '.docx';
    
        // Clean up all image files
        foreach ($imagePathsToCleanup as $imagePath) {
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Return the file as a download response
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
    
    /**
     * Parse HTML content from Quill editor and add to Word document
     */
    private function htmlContentConversation($section, $htmlContent, $defaultFont)
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
}
