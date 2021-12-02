<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $i = 1;
        User::factory(200)->create()->each(function($user) use(&$i){
            $user->email = "user_".$i."@gmail.com";
            $user->update();
            $i++;
        });
    }
}
