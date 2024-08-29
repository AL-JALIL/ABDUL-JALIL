<?php

namespace App\Imports;

use App\Models\WorkingStations;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WorkingStationsImport implements ToModel, WithValidation, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new WorkingStations([
            //
            'working_station_name' => $row['working_station_name'],
            'location_id' => $row['location_id'],
            'admin_hierarchy_id' => $row['admin_hierarchy_id'],
            'created_by' => $row['created_by']
        ]);
    }

    public function rules():array{
        return [
            '*.working_station_name' =>['required'],
            '*.location_id' =>['required'],
            '*.admin_hierarchy_id' =>['required'],
            '*.created_by' =>['required'],
        ];
    }
}
