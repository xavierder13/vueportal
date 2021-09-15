<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\TrainingFile;
use App\FileHasPermission;
use App\Position;
use Validator;
use DB;
use Carbon\Carbon;
use Auth;

class TrainingController extends Controller
{   
    public function get_videos() {
        $videos = TrainingFile::where('file_type', '=', 'mp4')->get();
        $positions = Position::all();
        return response()->json(['videos' => $videos, 'positions' => $positions], 200);
    }

    public function upload_video(Request $request)
    {   
        try {
            $file_extension = '';
            $path = '';
            if($request->file('file'))
            {   
                $path = $request->file('file')->getRealPath();
                $file_extension = $request->file('file')->getClientOriginalExtension();
            }

            $validator = Validator::make(
                [
                    'file' => strtolower($file_extension),
                    'title' => $request->get('title'),
                    'permitted_positions' => $request->get('permitted_positions'),
                ],
                [
                    'file' => 'required|in:mp4,',
                    'title' => 'required',
                    'permitted_positions' => 'required'
                ], 
                [
                    'file.required' => 'File is required',
                    'file.in' => 'File type must be mp4.',
                    'title.required' => 'Title is required',
                    'permitted_positions.required' => 'Permitted Positions is required',
                ]
            );  
            
            if($validator->fails())
            {
                return response()->json($validator->errors(), 200);
            }
    
            if ($request->file('file')) {

            }
            else
            {
                return response()->json(['error_empty' => 'File is empty'], 200);
            }
          
        } catch (\Exception $e) {
          
            return response()->json(['error' => $e->getMessage()], 200);
        }

        $file_date = Carbon::now()->format('Y-m-d');
        $uploadedFile = $request->file('file');
        $file_name = time().$uploadedFile->getClientOriginalName();
        $file_path = '/wysiwyg' . '/' . $file_date;

        // $uploadedFile->move(public_path() . $file_path, $file_name);
        
        $training_video = new TrainingFile();
        $training_video->user_id = Auth::user()->id;
        $training_video->file_name = $file_name;
        $training_video->file_path = $file_path;
        $training_video->file_type = $file_extension;
        $training_video->title = $request->get('title');
        $training_video->description = $request->get('description');
        $training_video->save();

        $permitted_positions = explode(",", $request->get('permitted_positions'));

        foreach ($permitted_positions as $key => $value) {
            $file_has_permissions = new FileHasPermission();
            $file_has_permissions->position_id = $value;
            $file_has_permissions->training_file_id = $training_video->id;
            $file_has_permissions->save();
        }

        return response()->json([
            'success' => 'File successfully uploaded'
        ]);
    }
}
