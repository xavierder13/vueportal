<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Brand;
use Validator;
use DB;
use Carbon\Carbon;

class TrainingController extends Controller
{
    public function upload(Request $request)
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
                ],
                [
                    'file' => 'required|in:mp4,',
                ], 
                [
                    'file.required' => 'File is required',
                    'file.in' => 'File type must be mp4.',
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
        $filename = time().$uploadedFile->getClientOriginalName();
      
        // $uploadedFile->move(public_path() . '/wysiwyg' . '/' . $file_date, $filename);

        return response()->json([
            'success' => 'File successfully uploaded'
        ]);
    }
}
