<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\DesignStyles;
use App\Models\PostContent;
use App\Models\PostTemplate;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;

class AdminApiController extends Controller
{
    public function index()
    {
        $designStyles = DesignStyles::select('id', 'name')->get()->map(function ($designStyle) {
            return [
                'id' => Helpers::encrypt($designStyle->id),
                'name' => $designStyle->name,
            ];
        });

        // get only those categories which post-content data are created
        $categoryIds = PostContent::select('category_id')->distinct()->get()->pluck('category_id');

        $categories = Categories::select('id', 'name')
            ->where(function ($query) use ($categoryIds) {
                $query->where('status', true)
                    ->where('parent_id', null)
                    // ->where('is_comming_soon', false)
                    ->whereIn('id', $categoryIds);
            })
            ->get()
            ->map(function ($category) {
                return [
                    'id' => Helpers::encrypt($category->id),
                    'name' => $category->name,
                ];
            });
        
        $subCategories = Categories::select('id', 'name','parent_id','month_id')
            ->where(function ($query) {
                $query->whereNotNull('parent_id')
                    ->where('status', true);
                    // ->where('is_comming_soon', false);
            })
            ->get()
            ->map(function ($subCategory) {
                return [
                    'id' => Helpers::encrypt($subCategory->id),
                    'name' => $subCategory->name,
                    'parent_id' => $subCategory->parent_id ? Helpers::encrypt($subCategory->parent_id) : null,
                    'month_id'=> $subCategory->month_id
                ];
            });

        $postContents = PostContent::select('id', 'category_id', 'title')->get()->map(function ($postContent) {
            return [
                'id' => Helpers::encrypt($postContent->id),
                'category_id' => Helpers::encrypt($postContent->category_id),
                'title' => $postContent->title,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Admin API',
            'data' => [
                'designStyles' => $designStyles,
                'categories' => $categories,
                'subCategories' => $subCategories,
                'postContents' => $postContents,
            ],
        ]);
    }

    public function downloadDocument($id)
    {
        $postTemplate = PostTemplate::with('postContent','category:id,name')->find($id);
        if (!$postTemplate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Design Template not found',
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
