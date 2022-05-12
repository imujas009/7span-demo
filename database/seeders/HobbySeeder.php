<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hobby;

class HobbySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Hobby::truncate();

        $data = array(
	        		array(
	        			'name' => 'Hacking' 
	        		),
	       			array(
	        			'name' => 'Blogging' 
	        		),
	        		array(
	        			'name' => 'Acting' 
	        		),
	        		array(
	        			'name' => 'Bowling' 
	        		),
	        		array(
	        			'name' => 'Drama' 
	        		),
	        		array(
	        			'name' => 'Modeling' 
	        		),
	        		array(
	        			'name' => 'Stretching' 
	        		)
	        	);
        Hobby::insert($data);
    }
}
