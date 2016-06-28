<?php

use Illuminate\Database\Seeder;

class TemplatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seeds = [
            ['Main', 'index'],
            ['Blog', 'blog'],
            ['Projects', 'projects'],
            ['Page', 'page'],
            ['Tag', 'tag'],
        ];
        foreach($seeds as $seed){
            DB::table('templates')->insert([
                'name' => $seed[0],
                'path' => $seed[1]
            ]);
        }
    }
}
