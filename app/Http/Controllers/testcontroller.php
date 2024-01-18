<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function logTest()
    {
        \Log::channel('axiom')->info('Testing Axiom logger!');
        return 'Log sent to Axiom';
    }
}
