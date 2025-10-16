<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MapsController extends Controller
{

    public function index()
    {
        $data['nama_menu'] = 'Maps';

        return view('grab', $data);
    }
}
