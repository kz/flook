<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
    public function search($request)
    {
        // Ensure that the search query exists.
        $this->validate($request, [
            'query' => 'required|max:128'
        ]);



    }
}
