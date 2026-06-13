<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'SHOPEEPAY', 'type' => 'ewallet'],
            ['name' => 'DANA', 'type' => 'ewallet'],
            ['name' => 'ORDERKUOTA', 'type' => 'ppob'],
            ['name' => 'GOPAY', 'type' => 'ewallet'],
            ['name' => 'RITA', 'type' => 'ppob'],
            ['name' => 'SIDIVA', 'type' => 'ppob'],
            ['name' => 'SIMPEL', 'type' => 'ppob'],
            ['name' => 'DIGIPOS', 'type' => 'ppob'],
            ['name' => 'BCA', 'type' => 'bank'],
            ['name' => 'CASH', 'type' => 'cash'],
        ];

        foreach ($accounts as $acc) {
            Account::create($acc);
        }
    }
}
