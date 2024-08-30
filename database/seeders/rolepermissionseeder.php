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
        $direkturrole = Role::firstOrCreate([
            'name'=>'direktur'
        ]);

        $direkturpermission=[
            'kelola akun',
            'kelola data',
        ];
        $direkturrole->syncPermissions($direkturpermission);

        $adminrole = Role::firstOrCreate([
            'name'=>'admin'
        ]);

        $adminpermission=[
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
