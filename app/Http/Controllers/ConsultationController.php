<?php

namespace App\Http\Controllers;

use App\Models\Occupation;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function index()
    {
        return view('consultations.index');
    }

    public function create()
    {
        $occupations = Occupation::orderBy('name')->get();

        return view('consultations.create', compact('occupations'));
    }

    public function show($id)
    {
        return view('consultations.show', ['consultationId' => $id]);
    }

    public function process($id)
    {
        return view('consultations.process', ['consultationId' => $id]);
    }
}
