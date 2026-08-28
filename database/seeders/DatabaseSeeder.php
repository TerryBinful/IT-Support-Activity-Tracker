<?php
namespace Database\Seeders;
use App\Models\Category;use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder{public function run():void{foreach(['Network','Systems','Cybersecurity','User Support','Infrastructure','Applications','Projects','Monitoring','Procurement','Administration','Meetings','Other'] as $i=>$name)Category::firstOrCreate(['name'=>$name],['sort_order'=>$i]);}}
