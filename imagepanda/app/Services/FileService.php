<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Image;

class FileService
{
    public function updateFile($model, $request, $type)
    {               
       
        if (!empty($model->file)) {
            $currentFile = public_path() . $model->file;            

            if (file_exists($currentFile) && $currentFile != public_path() . '/user-placeholder.png') {
                unlink($currentFile);
            }
        }

        $file = null;
        if ($type === "user") {
            $file = Image::make($request->file('file'))->resize(400, 400);
        } else {
            $file = Image::make($request->file('file'));
        }

        $ext = $request->file('file');
        $extension = $ext->getClientOriginalExtension();
        $name = time() . '.' . $extension;

        switch (env('FILESYSTEM_DISK')) {

            case 's3':

                try {

                    // we save the image files to AWS S3 ('File/' directory)            
                    $path = Storage::disk('s3')->put('File/' . $name, file_get_contents($request->file('file')));
                
                } catch (\Throwable $th) {
                    
                    // throw $th;
                    
                }
                    
                $path = Storage::disk('s3')->url('File/' . $name);
        
                $model->file = $path; 
        
                return $model;

            case 'local':

                $file = null;

                if ($type === "user") 
                {

                    $file = Image::make($request->file('file'))->resize(400, 400);

                } 
                else 
                {

                    $file = Image::make($request->file('file'));

                }

                $ext = $request->file('file');

                $extension = $ext->getClientOriginalExtension();
                $name = time() . '.' . $extension;
                $file->save(public_path() . '/File/' . $name);
                $model->file = '/File/' .$name;

                return $model;

            case 'public': 
                
                // use it when the site is on live server and the 
                // public Upload directory is in public_html folder

                $uploads_dir = dirname(__DIR__, 3);

                $file = null;

                if ($type === "user") 
                {

                    $file = Image::make($request->file('file'))->resize(400, 400);

                } 
                else 
                {

                    $file = Image::make($request->file('file'));

                }

                $ext = $request->file('file');

                $extension = $ext->getClientOriginalExtension();
                $name = time() . '.' . $extension;
                $file->save(storage_path('app/public') . '/storage/' . $name);
                $model->file = '/storage/' . $name;

                return $model;
        
        }
    }
}