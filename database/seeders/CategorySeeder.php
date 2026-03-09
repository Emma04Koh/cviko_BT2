<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        DB::table('categories')->insert([
            ['name' => 'Práca', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Škola', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Osobné', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Nápady', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'TODO', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Dôležité', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Nákupy', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Cvičenie', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Zábava', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ostatné', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
