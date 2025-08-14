<?php

namespace App\Imports;

use App\Helpers\Helpers;
use App\Models\Categories;
use App\Models\PostContent;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PostContentImport implements ToCollection
{
    public function collection(Collection $collection)
    {
        try {
            $skipRecords = [];
            DB::beginTransaction();
            foreach ($collection as $index => $row) {
                if ($index == 0 || $index == 1) {
                    continue;
                }
                if ($row[0] == null || $row[1] == null || $row[3] == null) {
                    $skipRecords[] = $row;
                    continue;
                }
                $categoryName = $row[1];
                $subCategoryName = $row[2] ?? null;
                $category = Categories::where('name','like', "%$categoryName%")->first();
                $subCategory = null;
                if ($subCategoryName) {
                    $subCategory = Categories::where('name','like', "%$subCategoryName%")->where('parent_id', $category->id)->first();
                }
                if ($category) {
                    PostContent::create([
                        'category_id' => $category->id,
                        'sub_category_id' => $subCategory->id ?? null,
                        'title' => $row[3] ?? null,
                        'description' => $row[4] ?? null,
                        'warning_message' => $row[5] ?? null,
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Helpers::sendErrorMailToDeveloper($e,'import post content data');
            throw $e;
        }
    }
}
