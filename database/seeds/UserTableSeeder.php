<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$records = config('henwen.seeder.user.list', 1) ;
        factory(App\Models\User::class, $records)->create() ;
    }
}
