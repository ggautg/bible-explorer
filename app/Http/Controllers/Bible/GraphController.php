<?php

namespace App\Http\Controllers\Bible;

use App\Http\Controllers\Controller;
use App\Services\BibleGraphService;

class GraphController extends Controller
{
    public function __construct(private BibleGraphService $graphService) {}

    public function show(string $book, int $chapter, int $verse)
    {
        return inertia('Bible/Graph', $this->graphService->buildGraph($book, $chapter, $verse));
    }
}