<?php

namespace Database\Seeders;

use App\Models\products;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        {
            $users = [
                [
                    'name' => 'JBL Live 770NC',
                    'price' => '500000',
                    'stock' => '30',
                    'image' => 'image'
                ],
                [
                    'name' => 'JBL 5',
                    'price' => '650000',
                    'stock' => '25',
                    'image' => 'image'
                ],
            ];
    
            foreach ($users as $user) {
                products::create($user);
            }
        }
    }
}