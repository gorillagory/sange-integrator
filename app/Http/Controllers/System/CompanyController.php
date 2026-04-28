<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function index()
    {
        // Fetch all companies from the Control DB
        $companies = Company::orderBy('created_at', 'desc')->paginate(10);

        return Inertia::render('System/Companies/Index', [
            'companies' => $companies
        ]);
    }
}
