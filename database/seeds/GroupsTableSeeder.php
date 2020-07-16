<?php

use Illuminate\Database\Seeder;

class GroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $records = config('henwen.seeder.member.group', 3) ;

        factory(App\Models\Group::class, $records)->create() ;
        // factory(App\Models\Group::class, 3)->create()->each(function($group) {
    	//     do something;
    	// });
    }
}
