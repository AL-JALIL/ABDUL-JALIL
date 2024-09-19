<?php

namespace App\Imports;

use App\Models\Conditions;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class ConditionImport implements ToModel, WithValidation, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Conditions([
        
            'condition_name' => $row['condition_name'],
            'uuid' => Str::uuid(),
            'created_by' => $row['created_by']
        ]);
    }

    public function rules():array{
        return [
            '*.condition_name' =>['unique:conditions'],
            '*.created_by' =>['required']
        ];
    }
}
