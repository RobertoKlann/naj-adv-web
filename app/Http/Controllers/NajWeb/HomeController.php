<?php

namespace App\Http\Controllers\NajWeb;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\NajController;
use App\User;

class HomeController extends NajController {

    public function index() {
        return view('najWeb.home')->with('is_home', true);
    }

    public function indexUpdateSenha() {
        return view('najWeb.updateSenha');
    }

}