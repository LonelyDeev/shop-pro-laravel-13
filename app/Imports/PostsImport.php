<?php

namespace App\Imports;

use App\Models\Post;
use App\Models\Tag;
use App\Traits\Taggable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class PostsImport implements ToModel,WithUpserts,WithBatchInserts,WithChunkReading,WithStartRow
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
        }else{
            $slug=sluggable_helper_function($combinedArray['title']);
        }
        $post=Post::where('slug',$slug)->first();
        if ($post){
            $img=$post->image;
            foreach ($combinedArray as $key=> $data) {
                $key2=$key;
                if ($key=='tags'){
                    $key="more";
                    $key2="title";
                }
                $post[$key] = $combinedArray[$key2];
            }
            $post->save();

            if (isset($combinedArray['image'])) {

                if (!file_exists(public_path("/uploads/posts"))) {
                    Storage::disk('public')->makeDirectory("/uploads/posts");
                }

                if ($img){
                    if(file_exists(public_path() . '/' . $img)){
                        unlink(public_path() . '/' . $img);
                    }
                }

                $image_url=$combinedArray['image'];
                $imageName = 'img' . '-' . time().'-'.$post->id.'.jpg';
                $http=explode('/',pathinfo($image_url)['dirname']);
                if ($http[0]){

                    $localFileName=public_path("/uploads/posts/".$imageName);
                    file_put_contents($localFileName, fopen($image_url, 'r'));
                    $img = Image::make(public_path("/uploads/posts/".$imageName));
                    $img->resize(1024, null, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save();
                    $post->image= '/uploads/posts/' . $imageName;

                }else{
                    $post->image= null;
                }
                $post->save();
            }
        }
        else{
            $post=new Post();
            foreach ($combinedArray as $key=> $data) {

                $key2=$key;
                if ($key=='tags'){
                    $key="more";
                    $key2="published";
                }
                if (!isset($combinedArray['slug'])){
                    $post['slug'] = $slug;
                }
                $post[$key] = $combinedArray[$key2];
            }
            $post->save();

            if (isset($combinedArray['image'])) {

                if (!file_exists(public_path("/uploads/posts"))) {
                    Storage::disk('public')->makeDirectory("/uploads/posts");
                }

                $image_url=$combinedArray['image'];
                $imageName = 'img' . '-' . time().'-'.$post->id.'.jpg';
                $http=explode('/',pathinfo($image_url)['dirname']);
                if ($http[0]){

                    $localFileName=public_path("/uploads/posts/".$imageName);
                    file_put_contents($localFileName, fopen($image_url, 'r'));
                    $img = Image::make(public_path("/uploads/posts/".$imageName));
                    $img->resize(1024, null, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save();
                    $post->image= '/uploads/posts/' . $imageName;

                }else{
                    $post->image= null;
                }
                $post->save();
            }
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
                $Taggable=DB::table('taggables')->where(['tag_id'=>$tag_id,'taggable_id'=>$post->id])->first();
                if (!$Taggable){
                    DB::table('taggables')->insert(['tag_id'=>$tag_id,'taggable_id'=>$post->id,'taggable_type'=>'App\Models\Post']);
                }
            }
        }

        session()->put('ImportSuccess','نوشته ها با موفقیت آپلود شدند.');
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
}
