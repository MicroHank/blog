<?php

use Illuminate\Database\Seeder;
use App\Models\MemberInformation;

class MemberTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $records = config('henwen.seeder.member.list', 10) ;
    	$faker = \Faker\Factory::create();
        // $faker->addProvider(new \Faker\Provider\zh_TW\Person($faker));
        $faker->addProvider(new \Faker\Provider\en_US\Company($faker));
        $faker->addProvider(new Faker\Provider\en_US\Address($faker));

        // 利用工廠建立 member Table 資料
        // factory(App\Models\Member::class, 10)->create() ;

        // 利用工廠建立 member Table 資料: 每建立一筆 member 時, 再建立 member_information Table 資料
    	factory(App\Models\Member::class, $records)->create()->each(function($member) use ($faker) {
    		$member_info = new MemberInformation ;

            // Data from member
            $member_info->user_id = $member->user_id ;
            $member_info->created_at = $member->created_at ;
            $member_info->updated_at = $member->updated_at ;

            // Generate data from $faker
            $member_info->email = $faker->unique()->safeEmail ;
            $member_info->address = $faker->address ;
            $member_info->jobtitle = $faker->jobTitle ;
            $member_info->birthday = $faker->dateTimeBetween('-40 years', '-10 years') ;
            $member_info->save() ;
    	});
    }
}
