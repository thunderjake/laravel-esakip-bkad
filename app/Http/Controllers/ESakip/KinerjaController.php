<?php

namespace App\Http\Controllers\ESakip;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KinerjaController extends Controller
{
    public function index()
    {
        return view('esakip.kinerja');
    }
}
