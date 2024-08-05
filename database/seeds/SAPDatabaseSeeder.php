<?php

use Illuminate\Database\Seeder;
use App\SapDatabase;

class SAPDatabaseSeeder extends Seeder
{

    public function run()
    {
        $databases = [
            'AddessaJul2024',
            'AppliantechJul2024',
            'EasyToOwnJul2024',
            'ElectroloopJul2024',
            'OutexcelJul2024',
            'PanApplianceJul2024',
            'SteadfordJul2024',
            'ThreathonsJul2024',
        ];

        foreach ($databases as $key => $database) {
            SapDatabase::firstOrCreate([
                'server' => '192.168.1.25',
                'database' => $database,
                'username' => 'sapprog105',
                'password' => Crypt::encrypt('105*Prog'),
                'active' => 1,
            ]);
        }
    }
}
