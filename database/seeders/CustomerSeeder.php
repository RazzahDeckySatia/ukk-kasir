<?php

namespace Database\Seeders;

use App\Models\customers;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        {
            $users = [
                [
                    'name' => 'Farid firmansyah',
                    'no_hp' => '083892992100',
                    'point' => '100'
                ],
                [
                    'name' => 'muhamad Fahri',
                    'no_hp' => '083894709185',
                    'point' => '100',
                ],
            ];
    
            foreach ($users as $user) {
                customers::create($user);
            }
        }
    }
}