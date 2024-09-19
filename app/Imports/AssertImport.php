<?php

namespace App\Imports;

use App\Models\Assets;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class AssertImport implements ToModel, WithValidation, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Assets([
        
            'asset_name' => $row['asset_name'],
            'uuid' => Str::uuid(),
            'serial_number' => $row['serial_number'],
            'code' => $row['code'],
            'created_by' => $row['created_by']
        ]);
    }

    public function rules():array{
        return [
            '*.asset_name' =>['required'],
            '*.serial_number' =>['unique:assets'],
            '*.code' =>['required'],
            '*.created_by' =>['required']
        ];
    }
}
