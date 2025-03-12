<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExportNasabah extends Controller
{
    public function index(Request $request)
    {
        return view('ExportNasabah/index');
    }
}
