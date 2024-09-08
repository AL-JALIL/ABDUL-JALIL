<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\countries;

class condition_info extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        DB::table('conditions')->delete();

        $conditions = array(
            array('condition_name' => 'WORKING','created_by' => '1', 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()),
            array('condition_name' => 'NOT WORKING', 'created_by' => '1', 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()),
            array('condition_name' => 'UNSTABLE', 'created_by' => '1', 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now())
          );

          DB::table('conditions')->insert($conditions);
    }
    
}
