<?php

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\Group;

class UserGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $records = config('henwen.seeder.member.user_group', 10) ;

    	$faker = \Faker\Factory::create();
    	$user_ids = Member::all()->pluck('user_id')->map(function ($id) {
            return (int) $id ;
        })->toArray() ;  // [1, 2, 3, ...]
        
    	$group_ids = Group::all()->pluck('group_id')->map(function ($id) {
            return (int) $id ;
        })->toArray() ;  // [1, 2, 3, ...]

    	for ($i = 0 ; $i < $records ; $i++) {
    		DB::table('user_group')->insert([
	        	'user_id' => $faker->randomElement($user_ids),
	        	'group_id' => $faker->randomElement($group_ids),
	        ]) ;
    	}
    }
}
