<?php

namespace App\Http\Controllers\ESakip;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EvaluasiController extends Controller
{
    public function index()
    {
        return view('esakip.evaluasi');
    }
}
