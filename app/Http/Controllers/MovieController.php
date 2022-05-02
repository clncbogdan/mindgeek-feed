<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Repositories\MovieRepository;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    protected MovieRepository $movieRepository;

    public function __construct(MovieRepository $movieRepository)
    {
        $this->movieRepository = $movieRepository;
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        return response()->json($this->movieRepository->all());
    }

    /**
     * Display the specified resource.
     *
     */
    public function show($id)
    {
        return response()->json($this->movieRepository->get($id));
    }
}
