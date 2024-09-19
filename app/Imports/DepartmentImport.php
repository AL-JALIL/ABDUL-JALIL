<?php

namespace App\Imports;

use App\Models\Departments;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class DepartmentImport implements ToModel, WithValidation, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Departments([
        
            'department_name' => $row['department_name'],
            'uuid' => Str::uuid(),
            'parent_id' => $row['parent_id'],
            'created_by' => $row['created_by']
        ]);
    }

    public function rules():array{
        return [
            '*.department_name' =>['unique:departments'],
            '*.created_by' =>['required'],
        ];
    }
}
