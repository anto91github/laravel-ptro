<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BcasAkun;

class ExportNasabah extends Controller
{
    public function index(Request $request)
    {
        $bcas_akun = BcasAkun::all();
        // dd($bcas_akun);
        return view('ExportNasabah/index');
    }
}
