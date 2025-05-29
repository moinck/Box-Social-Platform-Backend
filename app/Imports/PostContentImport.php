<?php

namespace App\Imports;

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
                if ($row[0] == null || $row[1] == null || $row[2] == null || $row[3] == null) {
                    $skipRecords[] = $row;
                    continue;
                }
                $categoryName = $row[1];
                $category = Categories::where('name','like', "%$categoryName%")->first();
                if ($category) {
                    PostContent::create([
                        'category_id' => $category->id,
                        'title' => $row[2],
                        'description' => $row[3],
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $th) {
            DB::rollBack();
            dd($th->getMessage());
            throw $th;
        }
    }
}
