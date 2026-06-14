<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'ShopeePay', 'type' => 'ewallet'],
            ['name' => 'Dana', 'type' => 'ewallet'],
            ['name' => 'OrderKuota', 'type' => 'ppob'],
            ['name' => 'GoPay', 'type' => 'ewallet'],
            ['name' => 'Rita', 'type' => 'ppob'],
            ['name' => 'Sidiva', 'type' => 'ppob'],
            ['name' => 'Simpel', 'type' => 'ppob'],
            ['name' => 'Digipos', 'type' => 'ppob'],
            ['name' => 'BCA', 'type' => 'bank'],
            ['name' => 'Cash', 'type' => 'cash'],
        ];

        foreach ($accounts as $acc) {
            Account::create($acc);
        }
    }
}
