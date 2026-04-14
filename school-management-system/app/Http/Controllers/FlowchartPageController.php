<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class FlowchartPageController extends Controller
{
    public function index(): View
    {
        return view('flowcharts.editor');
    }
}

