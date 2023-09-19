<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->importData();
    }
 
    /**
     * Parses a single csv file to obtain an array of data.
     * 
     */
    private function parsefile($file_name)
    {
		$file = __DIR__ . '/' . $file_name;
		if (is_file($file)) {
			return array_map('str_getcsv', file($file));
		}
		return false;
	}
 
    /**
     * Imports Data from the default csv fiels (categories, products, tags, associations).
     * The files should be located inside the seeders directory.
     * Data import is executed, as soon as the application is deployed.
     */
    public function importData()
    {
        $categories_csv = $this->parsefile('categories.csv');
        $products_csv = $this->parsefile('products.csv');
        $tags_csv = $this->parsefile('tags.csv');
        $associations_csv = $this->parsefile('associations.csv');	
		
		if(!empty($categories_csv)) {
			if(empty(DB::table('product_categories')->count())) {
				foreach ($categories_csv as $category) {
					DB::table('product_categories')->insert([
						'id'   => $category[0],
						'name' => $category[1]
					]);
				}
			}
		} else {
			throw new \Exception('Categories CSV is empty');
		}
		
		if(!empty($tags_csv)) {
			if(empty(DB::table('product_tags')->count())) {
				foreach ($tags_csv as $tag) {
					DB::table('product_tags')->insert([
						'id'   => $tag[0],
						'tag' => $tag[1]
					]);
				}
			}
		} else {
			throw new \Exception('Tags CSV is empty');
		}
		
		if(!empty($products_csv)) {
			if(empty(DB::table('products')->count())) {
				foreach ($products_csv as $product) {
					DB::table('products')->insert([
						'name' => $product[0],
						'code' => $product[1],
						'category_id' => $product[2],
						'price' => $product[3],
						'release_date' => $product[4]
					]);
				}
			}
		} else {
			throw new \Exception('Products CSV is empty');
		}
		
		if(!empty($associations_csv)) {
			if(empty(DB::table('product_tag_associations')->count())) {
				foreach ($associations_csv as $association) {
					DB::table('product_tag_associations')->insert([
						'product_id'   => $association[0],
						'tag_id' => $association[1]
					]);
				}
			}
		} else {
			throw new \Exception('Product tags associations CSV is empty');
		}
    }
}
