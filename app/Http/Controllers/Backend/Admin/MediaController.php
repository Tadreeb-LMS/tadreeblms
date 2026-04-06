<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Models\Media;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MediaController extends Controller
{
    public function destroy(Request $request)
    {
        $media_id = $request->media_id;
        $media = Media::find($media_id);

        if ($media) {
            // Delete related data.
            $filename = $media->file_name;

            $media->delete();

            // Delete file from local uploads if present.
            $destinationPath = public_path() . '/storage/uploads/'.$filename;
            if (file_exists($destinationPath)) {
                unlink($destinationPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Deleted successfully',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }
    }
}
