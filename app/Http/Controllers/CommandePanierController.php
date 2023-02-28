<?php

namespace App\Http\Controllers;

use App\Models\CommandePanier;
use Illuminate\Http\Request;

class CommandePanierController extends Controller
{
    //
    public function index()
    {
        return CommandePanier::all();
    }
}
