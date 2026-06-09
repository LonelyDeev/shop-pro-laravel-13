<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class UsersImport implements ToModel,WithUpserts,WithBatchInserts,WithChunkReading,WithStartRow
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request    = $request;
    }

    public function  model(array $file)
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

        if ($combinedArray['mobile']){
            $username=$combinedArray['mobile'];
            $user=User::where('mobile',$username)->first();
        }elseif ($combinedArray['email']){
            $username=$combinedArray['email'];
            $user=User::where('email',$username)->first();
        }

       if ($user){
           $img=$user->image;
           foreach ($combinedArray as $key=> $data) {

               if ($key=='username'){
                   $user['username']=$combinedArray['username'];
               }else{
                   $user['username']=$combinedArray['mobile'];
               }
               $user[$key] = $combinedArray[$key];

           }
           $user->save();

           if (isset($combinedArray['image'])) {

               if ($img){
                   if(file_exists(public_path() . '/' . $img)){
                       unlink(public_path() . '/' . $img);
                   }
               }

               if (!file_exists(public_path("/uploads/users/avatars"))) {
                   Storage::disk('public')->makeDirectory("/uploads/users/avatars");
               }
               $image_url = $combinedArray['image'];
               $imageName = 'img' . '-' . time() . '-' . $user->id . '.jpg';
               $http = explode('/', pathinfo($image_url)['dirname']);
               if ($http[0]) {
                   $localFileName = public_path("/uploads/users/avatars/" . $imageName);
                   file_put_contents($localFileName, fopen($image_url, 'r'));
                   $img = Image::make(public_path("/uploads/users/avatars/" . $imageName));
                   $img->resize(1024, null, function ($constraint) {
                       $constraint->aspectRatio();
                   })->save();
                   $user->image= '/uploads/users/avatars/' . $imageName;
               }else{
                   $user->image=null;
               }
               $user->save();
           }
       }
       else {
           $user = new User();
           foreach ($combinedArray as $key => $data) {

               if ($key == 'username') {
                   $user['username'] = $combinedArray['username'];
               } else {
                   $user['username'] = $combinedArray['mobile'];
               }
               $user[$key] = $combinedArray[$key];

           }
           $user->save();

           if (isset($combinedArray['image'])) {

               if (!file_exists(public_path("/uploads/users/avatars"))) {
                   Storage::disk('public')->makeDirectory("/uploads/users/avatars");
               }
               $image_url = $combinedArray['image'];
               $imageName = 'img' . '-' . time() . '-' . $user->id . '.jpg';
               $http = explode('/', pathinfo($image_url)['dirname']);
               if ($http[0]) {
                   $localFileName = public_path("/uploads/users/avatars/" . $imageName);
                   file_put_contents($localFileName, fopen($image_url, 'r'));
                   $img = Image::make(public_path("/uploads/users/avatars/" . $imageName));
                   $img->resize(1024, null, function ($constraint) {
                       $constraint->aspectRatio();
                   })->save();
                   $user->image= '/uploads/users/avatars/' . $imageName;

               }else{
                   $user->image= null;
               }
               $user->save();
           }
       }

        if (!Hash::isHashed($user->password)){
            $user->update([
                'password'=>Hash::make($user->password),
            ]);
        }
      session()->put('ImportSuccess','کاربران با موفقیت آپلود شدند.');

    }

    public function uniqueBy()
    {
        return 'mobile';
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
