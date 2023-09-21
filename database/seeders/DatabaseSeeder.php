<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        User::factory()->create([
            'name' => 'Quoc Tuan',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12341234')
        ]);

        Category::factory()->create([
            'name' => 'category 1',
            'slug' => 'category-1',
        ]);

        Tag::factory()->create([
            'name' => 'tag 1',
            'slug' => 'tag-1',
        ]);
    }
}
