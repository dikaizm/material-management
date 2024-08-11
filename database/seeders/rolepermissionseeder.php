<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class rolepermissionseeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permission = [
            'kelola akun',
            'kelola data',
            
        ];

        foreach ( $permission as $key ) {
            Permission::firstOrCreate([
                'name'=> $key,
            
            ]);

        }
        $adminrole = Role::firstOrCreate([
            'name'=>'admin'
        ]);

        $adminpermission=[
            'kelola akun',
            'kelola data',
        ];
        $adminrole->syncPermissions($adminpermission);


        $staffgudangrole = Role::firstOrCreate([
            'name'=>'staffgudang'
        ]);

        $staffgudangpermission=[
        
            'kelola data'
        ];
        $staffgudangrole->syncPermissions($staffgudangpermission);

    }
}
