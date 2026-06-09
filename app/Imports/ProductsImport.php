<?php

namespace App\Imports;

use App\Http\Controllers\Back\ProductController;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Price;
use App\Models\Product;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Morilog\Jalali\Jalalian;
use GuzzleHttp\Middleware;

class ProductsImport extends Controller implements ToModel,WithUpserts,WithBatchInserts,WithChunkReading,WithStartRow
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request    = $request;
    }

   public function model(array $file)
    {
        $row=0;
        $Alldata=[];
        $allArrays = [];
        $fileCount=count($file)-1;
        $filtersCount=count($this->request->filters)-1;
        if ($filtersCount > $fileCount){
           return null;
        }

            session()->forget('ImportError');
            foreach ($this->request->filters as $key => $filter){
                $Alldata[]=[$key=>$file[$row]];
                $row++;
            }
            $count=count($Alldata)-1;
            for ($i=0;$i<=$count;$i++){
                $allArrays[] = $Alldata[$i];
            }
            $combinedArray = call_user_func_array('array_merge', $allArrays);


            if (isset($combinedArray['slug'])){
                $slug=$combinedArray['slug'];
                $slug=sluggable_helper_function($slug);
            }else{
                $slug=sluggable_helper_function($combinedArray['title']);
            }

            $product=Product::where('slug',$slug)->first();

            if ($product){
                $img=$product->image;

                if (isset($combinedArray['image'])) {

                    if (!file_exists(public_path("/uploads/products"))) {
                        Storage::disk('public')->makeDirectory("/uploads/products");
                    }
                    if ($img){
                        if(file_exists(public_path() . '/' . $img)){
                            unlink(public_path() . '/' . $img);
                        }
                    }


                    $image_url=$combinedArray['image'];
                    $headers_url = @get_headers($image_url);
                    if ($headers_url && strpos($headers_url[0], '200')) {
                        $imageName = 'img' . '-' . time() . '-' . $product->id . '.jpg';
                        $http = explode('/', pathinfo($image_url)['dirname']);
                        if ($http[0]) {

                            $localFileName = public_path("/uploads/products/" . $imageName);
                            file_put_contents($localFileName, fopen($image_url, 'r'));
                            $img = Image::make(public_path("/uploads/products/" . $imageName));
                            $img->resize(1024, null, function ($constraint) {
                                $constraint->aspectRatio();
                            })->save();
                            $product->image = '/uploads/products/' . $imageName;

                        } else {
                            $product->image = null;
                        }
                    }
                    $product->save();
                }


                $price=[
                    'prices'=>[
                        'price'=> $combinedArray['price'],
                        'stock'=> $combinedArray['stock'],
                        'seller_id'=> null,
                        'discount'=> null,
                        'cart_max'=> null,
                        'cart_min'=> null,
                        'discount_expire_at'=> null,
                        'published'=> 1,
                        'attributes'=> [
                            '0'=>null,
                            '1'=>null,
                            '2'=>null,
                            '3'=>null,
                        ],
                    ]
                ];
                $this->updateProductPrices($product, $price);

                if (isset($combinedArray['brand'])){
                    $brand=[
                        'brand'=> $combinedArray['brand']
                    ];
                    $this->updateProductBrand($product, $brand);
                }

                if (isset($combinedArray['tags'])) {
                    $allTags=explode(',',$combinedArray['tags']);
                    foreach ($allTags as $tag){
                        $tag_slug=sluggable_helper_function($tag);
                        $get_tag=Tag::where('slug',$tag_slug)->first();
                        if (!$get_tag){
                            $new_tag=new Tag();
                            $new_tag->name=$tag;
                            $new_tag->slug=$tag_slug;
                            $new_tag->lang='fa';
                            $new_tag->save();
                            $tag_id=$new_tag->id;
                        }else{
                            $tag_id=$get_tag->id;
                        }
                        $Taggable=DB::table('taggables')->where(['tag_id'=>$tag_id,'taggable_id'=>$product->id])->first();
                        if (!$Taggable){
                            DB::table('taggables')->insert(['tag_id'=>$tag_id,'taggable_id'=>$product->id,'taggable_type'=>'App\Models\Product']);
                        }
                    }
                }
            }
            else{
                $product = new Product;
                foreach ($combinedArray as $key=> $data) {
                    $key2=$key;
                    if ($key=='brand' or $key=='price' or $key=='stock' or $key=='tags'){
                        $key="more";
                        $key2="title";
                    }
                    if ($combinedArray[$key2]=="" and $key2!="image"){
                        return null;
                    }
                    $product[$key] = $combinedArray[$key2];
                }
                $product->save();

                if (isset($combinedArray['image'])) {
                    if (!file_exists(public_path("/uploads/products"))) {
                        Storage::disk('public')->makeDirectory("/uploads/products");
                    }
                    $image_url=$combinedArray['image'];
                    $headers_url = @get_headers($image_url);
                    if ($headers_url && strpos($headers_url[0], '200')) {
                        $imageName = 'img' . '-' . time() . '-' . $product->id . '.jpg';
                        $http = explode('/', pathinfo($image_url)['dirname']);
                        if ($http[0]) {

                            $localFileName = public_path("/uploads/products/" . $imageName);
                            file_put_contents($localFileName, fopen($image_url, 'r'));
                            $img = Image::make(public_path("/uploads/products/" . $imageName));
                            $img->resize(1024, null, function ($constraint) {
                                $constraint->aspectRatio();
                            })->save();
                            $product->image = '/uploads/products/' . $imageName;

                        } else {
                            $product->image = null;
                        }
                    }
                    $product->save();
                }



                $price=[
                    'prices'=>[
                        'price'=> $combinedArray['price'],
                        'stock'=> $combinedArray['stock'],
                        'seller_id'=> null,
                        'discount'=> null,
                        'cart_max'=> null,
                        'cart_min'=> null,
                        'discount_expire_at'=> null,
                        'published'=> 1,
                        'attributes'=> [
                            '0'=>null,
                            '1'=>null,
                            '2'=>null,
                            '3'=>null,
                        ],
                    ]
                ];
                $this->updateProductPrices($product, $price);

                if (isset($combinedArray['brand'])){
                    $brand=[
                        'brand'=> $combinedArray['brand']
                    ];
                    $this->updateProductBrand($product, $brand);
                }

                if (isset($combinedArray['tags'])) {
                    $allTags=explode(',',$combinedArray['tags']);
                    foreach ($allTags as $tag){
                        $tag_slug=sluggable_helper_function($tag);
                        $get_tag=Tag::where('slug',$tag_slug)->first();
                        if (!$get_tag){
                            $new_tag=new Tag();
                            $new_tag->name=$tag;
                            $new_tag->slug=$tag_slug;
                            $new_tag->lang='fa';
                            $new_tag->save();
                            $tag_id=$new_tag->id;
                        }else{
                            $tag_id=$get_tag->id;
                        }
                        $Taggable=DB::table('taggables')->where(['tag_id'=>$tag_id,'taggable_id'=>$product->id])->first();
                        if (!$Taggable){
                            DB::table('taggables')->insert(['tag_id'=>$tag_id,'taggable_id'=>$product->id,'taggable_type'=>'App\Models\Product']);
                        }
                    }
                }
            }


            session()->put('ImportSuccess','محصولات با موفقیت آپلود شدند.');
           //return $product;

    }

    public function uniqueBy()
    {
        return 'slug';
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function startRow(): int
    {
        return 2;
    }

    private function updateProductBrand(Product $product, $request)
    {

        if ($request['brand']) {
            $brand = Brand::firstOrCreate(
                [
                    'name'    => $request['brand'],
                    'lang'    => app()->getLocale(),
                ],
                [
                    'slug' => $request['brand'],
                ]
            );

            $product->update([
                'brand_id' => $brand->id
            ]);
        }
    }


    private function updateProductPrices(Product $product,$request)
    {
        if ($product->isDownload()) {
            return;
        }

        $prices_id = [];

        foreach ($request as $price) {
            $time = null;
            if (isset($price['discount_expire']) && $price['discount_expire']) {
                $time = Carbon::instance(Jalalian::fromFormat('Y-m-d H:i:s', $price['discount_expire'])->toCarbon())->toDateTimeString() ?? null;
            }

            $attributes = array_filter($price['attributes'] ?? []);

            $seller_id=[$price["seller_id"]] ?? [];


            $update_price = false;


            if (count($product->prices()->withTrashed()->get())){
                foreach ($product->prices()->withTrashed()->get() as $product_price) {
                    $product_price_attributes = $product_price->get_attributes()->get()->pluck('id')->toArray();

                    $product_price_attributes2=[];
                    if($product_price->seller_id){
                        $product_price_attributes2 = $product_price->get_sellers()->get()->pluck('id')->toArray();
                    }
                    sort($product_price_attributes);

                    $product_price_attributes=array_merge($product_price_attributes,$product_price_attributes2);

                    if ($attributes == $product_price_attributes) {
                        $update_price = $product_price;
                        break 1;
                    }

                }
            }



            if ($price['attributes'][3]){
                $Newattributes = array_slice($attributes, 0, count($attributes) - 1);
            }else{
                sort($attributes);
                $Newattributes=$attributes;
            }




            if ($update_price) {

                $update_price->createChange(
                    $price["price"],
                    $price["discount"]
                );

                $update_price->update([
                    "seller_id"          => $price["seller_id"],
                    "price"              => $price["price"],
                    "discount"           => $price["discount"],
                    "discount_price"     => get_discount_price($price["price"], $price["discount"], $product),
                    "regular_price"      => get_discount_price($price["price"], 0, $product),
                    "stock"              => $price["stock"],
                    "cart_max"           => $price["cart_max"],
                    "cart_min"           => $price["cart_min"],
                    "discount_expire_at" => $price["discount_expire_at"] ? Jalalian::fromFormat('Y-m-d H:i:s', $price["discount_expire_at"])->toCarbon() : null,
                    "deleted_at"         => null,
                    "published"          => $price["published"],
                ]);

                $update_price->get_attributes()->sync($Newattributes);
                if ($price['attributes'][3]) {

                    foreach ($Newattributes as $attribute) {
                        $attribute_price = DB::table('attribute_price')->where(['attribute_id' => $attribute, 'price_id' => $update_price->id])->first();

                        if ($attribute_price) {
                            DB::table('attribute_price')->where(['attribute_id' => $attribute, 'price_id' => $update_price->id])->update(['seller_id' => $price['attributes'][3],'product_id'=>$product->id]);

                        }
                    }
                }

                $prices_id[] = $update_price->id;

            } else {

                $insert_price = $product->prices()->create(
                    [
                        "seller_id"           => $price["seller_id"],
                        "price"               => $price["price"],
                        "discount"            => $price["discount"],
                        "discount_price"      => get_discount_price($price["price"], $price["discount"], $product),
                        "regular_price"       => get_discount_price($price["price"], 0, $product),
                        "stock"               => $price["stock"],
                        "cart_max"            => $price["cart_max"],
                        "cart_min"            => $price["cart_min"],
                        "discount_expire_at" => $price["discount_expire_at"] ? Jalalian::fromFormat('Y-m-d H:i:s', $price["discount_expire_at"])->toCarbon() : null,
                        "published"          => $price["published"],
                    ]
                );

                foreach ($Newattributes as $attribute) {
                    $attribute_price= DB::table('attribute_price')->where(['attribute_id'=>$attribute,'price_id'=>$insert_price->id])->first();
                    if (!$attribute_price){
                        $insert_price->get_attributes()->attach([$attribute]);
                        if ($price['attributes'][3]) {
                            DB::table('attribute_price')->where(['attribute_id' => $attribute, 'price_id' => $insert_price->id])->update(['seller_id' => $price['attributes'][3],'product_id'=>$product->id]);

                        }
                        //DB::table('attribute_price')->where(['attribute_id'=>1,'price_id'=>$insert_price->id,'seller_id'=>seller_info()->seller_id])->delete();
                    }
                }


                $insert_price->createChange($price["price"], $price["discount"]);

                $insert_price->createChange(
                    $price["price"],
                    $price["discount"],
                    $price["stock"]
                );

                $prices_id[] = $insert_price->id;

            }
        }

        $get_prices=$product->prices()->whereNotIn('id', $prices_id)->get();



        foreach ($get_prices as $get_price){
            DB::table('attribute_price')->where(['price_id'=> $get_price->id])->delete();
        }

        $product->prices()->whereNotIn('id', $prices_id)->forceDelete();


        DB::table('cart_product')
            ->where('product_id', $product->id)
            ->whereNotNull('price_id')
            ->whereNotIn('price_id', $prices_id)
            ->delete();


        // start update seller_variants
        DB::table('seller_variants')->where(['product_id'=>$product->id])->delete();
        $get_sellers_seller_ids=Price::whereIn('id',$prices_id)->get();
        foreach ($get_sellers_seller_ids as $get_sellers_seller_id){
            $seller_id[]=$get_sellers_seller_id->seller_id;
        }
        $seller_ids=array_unique($seller_id);
        foreach ($seller_ids as $seller_id){

            $get_sellers_ids=Price::where(['seller_id'=>$seller_id,'product_id'=>$product->id])->get();
            $ids=[];
            for ($i=0;$i<=count($get_sellers_ids)-1;$i++){
                $ids[]=$get_sellers_ids[$i]['id'];
            }

            if (count(DB::table('seller_variants')->where(['product_id'=>$product->id,'seller_id' =>$seller_id])->get())){
                DB::table('seller_variants')->where(['product_id'=>$product->id,'seller_id' =>$seller_id])->update(['prices_id'=>$ids]);
            }else{
                if ($seller_id){
                    DB::insert('insert into seller_variants (`product_id`, `seller_id`) values (?, ?)', [$product->id, $seller_id]);
                    DB::table('seller_variants')->where(['product_id'=>$product->id,'seller_id' =>$seller_id])->update(['prices_id'=>$ids]);
                }
            }
        }
        //end update seller_variants
    }
}
