<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AccessModule;
use App\AccessChart;
use App\AccessChartUserMap;
use App\AccessLevel;
use App\User;
use Validator;
use DB;

class AccessChartController extends Controller
{
    public function index()
    {
        $access_charts = AccessChart::with('access_chart_user_maps')
                                    ->with('access_chart_user_maps.user')
                                    ->with('access_chart_user_maps.user.branch')
                                    ->with('access_module')
                                    ->get();
        
        $access_modules = AccessModule::all();
        $users = User::all();
        
        return response()->json(['access_charts' => $access_charts, 'access_modules' => $access_modules, 'users' => $users], 200);
    }
}
