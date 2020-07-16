<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SeederController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $class = $request->input('class') ;
            $output = Artisan::call('db:seed', ['--class' => $class]) ;
            return redirect()->route('dashboard')->with('message', $class.": Feed Data OK") ;
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('message', $e->getMessage()) ;
        }
    }
}
