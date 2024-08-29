<?php

namespace App\Imports;

use App\Models\AdminHierarchies;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AdminHierarchiesImport implements ToModel, WithValidation, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        $existingData = AdminHierarchies::where('admin_hierarchy_id', $row['admin_hierarchy_id'])->first();

        if (!$existingData) {
            return new AdminHierarchies([
                'admin_hierarchy_id' => $row['admin_hierarchy_id'],
                'admin_hierarchy_name' => $row['admin_hierarchy_name'],
                'parent_id' => $row['parent_id'],
                'created_by' => $row['created_by']
            ]);
            return null; 
        }

    }

    public function rules():array{
        return [
            '*.admin_hierarchy_id' =>['required'],
            '*.admin_hierarchy_name' =>['required'],
            '*.parent_id' =>['required'],
            '*.created_by' =>['required']
        ];
    }
}
