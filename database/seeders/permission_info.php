<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class permission_info extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permissions')->delete();
        //  Permission::truncate();

        $permissions = [
            
            'View Dashboard',
            'View Permission',
            'System Audit',
            'Report Management',


            'User Modules',
                'Create User',
                'Update User',
                'Delete User',
                'View User',

                'Create Role',
                'Update Role',
                'Delete Role',
                'View Role',

            'Setup Modules',
                'Create Location',
                'Update Location',
                'Delete Location',
                'View Location',

                'Create Department',
                'Update Department',
                'Delete Department',
                'View Department',

                'Create Asset',
                'Update Asset',
                'Delete Asset',
                'View Asset',

                'Create Condition',
                'Update Condition',
                'Delete Condition',
                'View Condition',

                'Create Asset Department',
                'Update Asset Department',
                'Delete Asset Department',
                'View Asset Department',

                'Create Facility Level',
                'Update Facility Level',
                'Delete Facility Level',
                'View Facility Level',

                'Create Admin Hierarchies',
                'Update Admin Hierarchies',
                'Delete Admin Hierarchies',
                'View Admin Hierarchies',

                'Create Work Station',
                'Update Work Station',
                'Delete Work Station',
                'View Work Station',

                'Create Upload Types',
                'Update Upload Types',
                'Delete Upload Types',
                'View Upload Types',

                'Create Parent Upload Type',
                'Update Parent Upload Type',
                'Delete Parent Upload Type',
                'View Parent Upload Type',
                
                'Zone B Permission',
                'Division permission',
                'Unit permission',
                'DMO permission',

               
          


               


         ];

         foreach ($permissions as $permission) {
 
            Permission::create(['name' => $permission,'guard_name'=>'web']);
 
         }
    }
}
