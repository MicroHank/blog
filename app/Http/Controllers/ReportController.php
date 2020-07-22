<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use League\Csv\Writer;
use League\Csv\Reader ;
use App\Models\User;
use PDF;

class ReportController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    public function csv()
    {
        $csv = Reader::createFromPath(base_path('public/csv/reader.csv'), 'r') ;
        $headers = $csv->fetchOne();
        $data = $csv->setOffset(1)->fetchAll() ;

        return view('report.csv', ['data' => $data]) ;
    }

    /**
     * Outout CSV file
     *
     * @return download
     */
    public function downloadCSV()
    {
        $csv = Writer::createFromFileObject(new \SplTempFileObject()) ;
        $csv->insertOne(['name', 'email', 'remember_token', 'created_at', 'api_token', 'api_token_expired', 'status', 'deleted_at']) ;
        $data = DB::table('users')->select(['name', 'email', 'remember_token', 'created_at', 'api_token', 'api_token_expired', 'status', 'deleted_at'])->get() ;
        $data = json_decode(json_encode($data, JSON_UNESCAPED_UNICODE), true) ;
        $csv->insertAll($data) ;
        $csv->output('user.csv') ;
    }

    public function pdf()
    {
        $users = User::get() ;
        return view('report.pdf', ['users' => $users]) ;
    }

    /**
     * Outout PDF file
     *
     * @return download
     */
    public function downloadPDF()
    {
        $users = User::get() ;
        $pdf = PDF::loadView('report.pdf_user', ['users' => $users]) ;
        $pdf->save(base_path('storage/app/public/filename.pdf')) ;
        return $pdf->download('users.pdf') ;
    }
}