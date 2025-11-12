<?php

namespace App\Http\Controllers\ESakip;

use App\Http\Controllers\Controller;
use App\Models\Program;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::with('kegiatans.subKegiatans')->get();
        return view('esakip.program.index', compact('programs'));
    }
}
