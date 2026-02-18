<?php

namespace App\Http\Traits;

use App\Models\Media;
use CustomHelper;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait FileUploadTrait
{

    /**
     * File upload trait used in controllers to upload files
     */
    public function saveFiles(Request $request)
    {
        ini_set('memory_limit', '-1');
        if (!file_exists(public_path('storage/uploads'))) {
            mkdir(public_path('storage/uploads'), 0777);
            mkdir(public_path('storage/uploads/thumb'), 0777);
        }

        $finalRequest = $request;

        foreach ($request->all() as $key => $value) {
            if ($request->hasFile($key)) {
                if ($request->has($key . '_max_width') && $request->has($key . '_max_height')) {
                    // Check file width
                    $extension = array_last(explode('.', $request->file($key)->getClientOriginalName()));
                    $name = array_first(explode('.', $request->file($key)->getClientOriginalName()));
                    $filename = time() . '-' . Str::slug($name) . '.' . $extension;
                    $file = $request->file($key);
                    $image = Image::make($file);
                    if (!file_exists(public_path('storage/uploads/thumb'))) {
                        mkdir(public_path('storage/uploads/thumb'), 0777, true);
                    }

                    Image::make($file)->resize(50, 50)->save(public_path('storage/uploads/thumb') . '/' . $filename);

                    $width = $image->width();
                    $height = $image->height();
                    if ($width > $request->{$key . '_max_width'} && $height > $request->{$key . '_max_height'}) {
                        $image->resize($request->{$key . '_max_width'}, $request->{$key . '_max_height'});
                    } elseif ($width > $request->{$key . '_max_width'}) {
                        $image->resize($request->{$key . '_max_width'}, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } elseif ($height > $request->{$key . '_max_width'}) {
                        $image->resize(null, $request->{$key . '_max_height'}, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    }
                    $image->save(public_path('storage/uploads') . '/' . $filename);
                    $finalRequest = new Request(array_merge($finalRequest->all(), [$key => $filename]));
                } else {

                    $extension = array_last(explode('.', $request->file($key)->getClientOriginalName()));
                    $name = array_first(explode('.', $request->file($key)->getClientOriginalName()));
                    $filename = time() . '-' . Str::slug($name) . '.' . $extension;
                    $request->file($key)->move(public_path('storage/uploads'), $filename);
                    $finalRequest = new Request(array_merge($finalRequest->all(), [$key => $filename]));
                }
            }
        }
        return $finalRequest;
    }


    public function saveFilesOptimize(Request $request)
    {
        try {
            ini_set('memory_limit', '-1');
            
            // Ensure /public/uploads and /public/uploads/thumb exist
            if (!file_exists(public_path('site-upload'))) {
                mkdir(public_path('site-upload'), 0777, true);
                mkdir(public_path('site-upload/thumb'), 0777, true);
            }

            $finalRequest = $request;

            foreach ($request->all() as $key => $value) {
                if ($request->hasFile($key)) {
                    $file = $request->file($key);
                    $extension = $file->getClientOriginalExtension();
                    $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $filename = time() . '-' . Str::slug($name) . '.' . $extension;

                    if ($request->has($key . '_max_width') && $request->has($key . '_max_height')) {
                        $image = \Image::make($file);

                        if (!file_exists(public_path('site-upload/thumb'))) {
                            mkdir(public_path('site-upload/thumb'), 0777, true);
                        }

                        // Save thumbnail
                        \Image::make($file)->resize(50, 50)->save(public_path('site-upload/thumb') . '/' . $filename);

                        // Resize if needed
                        $width = $image->width();
                        $height = $image->height();

                        if ($width > $request->{$key . '_max_width'} && $height > $request->{$key . '_max_height'}) {
                            $image->resize($request->{$key . '_max_width'}, $request->{$key . '_max_height'});
                        } elseif ($width > $request->{$key . '_max_width'}) {
                            $image->resize($request->{$key . '_max_width'}, null, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                        } elseif ($height > $request->{$key . '_max_height'}) {
                            $image->resize(null, $request->{$key . '_max_height'}, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                        }

                        $image->save(public_path('site-upload') . '/' . $filename);
                    } else {
                        // Normal save
                        $file->move(public_path('site-upload'), $filename);
                    }

                    $finalRequest = new Request(array_merge($finalRequest->all(), [$key => 'site-upload/'.$filename]));
                }
            }

            return $finalRequest;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    public function saveAllFiles_local(Request $request, $downloadable_file_input = null, $model_type = null, $model = null)
    {

        try {
            //throw new Exception("Intentional error for testing.");
            if (!file_exists(public_path('storage/uploads'))) {
                mkdir(public_path('storage/uploads'), 0777);
                mkdir(public_path('storage/upload/thumb'), 0777);
            }
            $finalRequest = $request;
    
            foreach ($request->all() as $key => $value) {
    
                if ($request->hasFile($key)) {
    
                    if ($key == $downloadable_file_input) {
                        foreach ($request->file($key) as $item) {
                            $extension = array_last(explode('.', $item->getClientOriginalName()));
                            $name = array_first(explode('.', $item->getClientOriginalName()));
                            $filename = time() . '-' . Str::slug($name) . '.' . $extension;
                            $size = $item->getSize() / 1024;
                            $item->move(public_path('storage/uploads'), $filename);
                            Media::create([
                                'model_type' => $model_type,
                                'model_id' => $model->id,
                                'name' => $filename,
                                'url' => asset('storage/uploads/' . $filename),
                                'type' => $item->getClientMimeType(),
                                'file_name' => $filename,
                                'size' => $size,
                            ]);
                        }
                        $finalRequest = $finalRequest = new Request($request->except($downloadable_file_input));
    
    
                    } else {
                        if ($key != 'video_file') {
                            if ($key == 'add_pdf') {
                                $file = $request->file($key);
    
                                $extension = array_last(explode('.', $request->file($key)->getClientOriginalName()));
                                $name = array_first(explode('.', $request->file($key)->getClientOriginalName()));
                                $filename = time() . '-' . Str::slug($name) . '.' . $extension;
    
                                $size = $file->getSize() / 1024;
                                $file->move(public_path('storage/uploads'), $filename);
                                Media::create([
                                    'model_type' => $model_type,
                                    'model_id' => $model->id,
                                    'name' => $filename,
                                    'url' => asset('storage/uploads/' . $filename),
                                    'type' => 'lesson_pdf',
                                    'file_name' => $filename,
                                    'size' => $size,
                                ]);
                                $finalRequest = new Request(array_merge($finalRequest->all(), [$key => $filename]));
                            } elseif ($key == 'add_audio') {
                                $file = $request->file($key);
    
                                $extension = array_last(explode('.', $request->file($key)->getClientOriginalName()));
                                $name = array_first(explode('.', $request->file($key)->getClientOriginalName()));
                                $filename = time() . '-' . Str::slug($name) . '.' . $extension;
    
                                $size = $file->getSize() / 1024;
                                $file->move(public_path('storage/uploads'), $filename);
                                Media::create([
                                    'model_type' => $model_type,
                                    'model_id' => $model->id,
                                    'name' => $filename,
                                    'type' => 'lesson_audio',
                                    'file_name' => $filename,
                                    'url' => asset('storage/uploads/' . $filename),
                                    'size' => $size,
                                ]);
                                $finalRequest = new Request(array_merge($finalRequest->all(), [$key => $filename]));
                            } else {
                                if(is_array($request->file($key))){
                                    foreach($request->file($key) as $f){
                                        $extension = array_last(explode('.', $f->getClientOriginalName()));
                                        $name = array_first(explode('.', $f->getClientOriginalName()));
                                        $filename = time() . '-' . Str::slug($name) . '.' . $extension;
                                        $f->move(public_path('storage/uploads'), $filename);
                                        $finalRequest = new Request(array_merge($finalRequest->all(), [$key => $filename]));
                                        $model->lesson_image = $filename;
                                        $model->save();
    
                                    }
                                    
                                }else{
                                    $extension = array_last(explode('.', $request->file($key)->getClientOriginalName()));
                                    $name = array_first(explode('.', $request->file($key)->getClientOriginalName()));
                                    $filename = time() . '-' . Str::slug($name) . '.' . $extension;
        
                                    $request->file($key)->move(public_path('storage/uploads'), $filename);
                                    $finalRequest = new Request(array_merge($finalRequest->all(), [$key => $filename]));
                                    $model->lesson_image = $filename;
                                    $model->save();
                                }
                                
                            }
    
                        }
                    }
                }
            }
            return $finalRequest;
        } catch (Exception $e) {
            throw new Exception("Intentional error for testing.");
        }
        

        
    }

    public function saveAllFiles(Request $request, $downloadable_file_input = null, $model_type = null, $model = null)
    {
        //dd("lesson-1");
        try {
            $finalRequest = $request;

            foreach ($request->all() as $key => $value) {
                if (!$request->hasFile($key)) {
                    continue;
                }

                // Handle downloadable multi-file input
                if ($key === $downloadable_file_input) {
                    foreach ($request->file($key) as $item) {
                        $extension = $item->getClientOriginalExtension();
                        $name = pathinfo($item->getClientOriginalName(), PATHINFO_FILENAME);
                        $filename = time() . '-' . Str::slug($name) . '.' . $extension;
                        $size = $item->getSize() / 1024;

                        // Upload to S3
                        $aws_url = CustomHelper::uploadToS3($item, $filename, '');
                        $url = '';//Storage::disk('s3')->url("videos/{$filename}");

                        // Save to Media model
                        Media::create([
                            'model_type' => $model_type,
                            'model_id' => $model->id,
                            'name' => $filename,
                            'url' => $url,
                            'aws_url' => $aws_url,
                            'type' => $item->getClientMimeType(),
                            'file_name' => $filename,
                            'size' => $size,
                        ]);
                    }

                    $finalRequest = new Request($request->except($downloadable_file_input));
                    continue;
                }

                // Handle other file types
                $file = $request->file($key);
                if (is_array($file)) {
                    foreach ($file as $f) {
                        $extension = $f->getClientOriginalExtension();
                        $name = pathinfo($f->getClientOriginalName(), PATHINFO_FILENAME);
                        $filename = time() . '-' . Str::slug($name) . '.' . $extension;

                        $aws_url = CustomHelper::uploadToS3($f, $filename, '');
                        $url = ''; //Storage::disk('s3')->url("videos/{$filename}");

                        $model->url = $url;
                        $model->aws_url = $aws_url;
                        $model->save();
                        $finalRequest = new Request(array_merge($finalRequest->all(), [$key => $filename]));
                    }
                } else {
                    $extension = $file->getClientOriginalExtension();
                    $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $filename = time() . '-' . Str::slug($name) . '.' . $extension;
                    $size = $file->getSize() / 1024;

                    $aws_url = CustomHelper::uploadToS3($file, $filename, '');
                    $url = ''; //Storage::disk('s3')->url("videos/{$filename}");

                    if ($key == 'add_pdf') {
                        $type = 'lesson_pdf';
                    } elseif ($key == 'add_audio') {
                        $type = 'lesson_audio';
                    } else {
                        $type = 'lesson_image';
                    }

                    Media::create([
                        'model_type' => $model_type,
                        'model_id' => $model->id,
                        'name' => $filename,
                        'url' => $url,
                        'aws_url' => $aws_url,
                        'type' => $type,
                        'file_name' => $filename,
                        'size' => $size,
                    ]);

                    $model->lesson_image = $url;
                    $model->save();

                    $finalRequest = new Request(array_merge($finalRequest->all(), [$key => $filename]));
                }
            }

            return $finalRequest;
        } catch (\Exception $e) {
            \Log::error('File Upload Error: ' . $e->getMessage());
            throw $e;
        }
    }


    public function saveLogos(Request $request)
    {
        if (!file_exists(public_path('storage/logos'))) {
            mkdir(public_path('storage/logos'), 0777);
        }
        $finalRequest = $request;

        foreach ($request->all() as $key => $value) {
            if ($request->hasFile($key)) {
                $extension = array_last(explode('.', $request->file($key)->getClientOriginalName()));
                $name = array_first(explode('.', $request->file($key)->getClientOriginalName()));
                $filename = time() . '-' . Str::slug($name) . '.' . $extension;
                $request->file($key)->move(public_path('storage/logos'), $filename);
                $finalRequest = new Request(array_merge($finalRequest->all(), [$key => $filename]));

            }
        }

        return $finalRequest;
    }
}