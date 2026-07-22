<?php

namespace App\Http\Controllers\Bible;

use App\Http\Controllers\Controller;
use App\Models\BibleBook;
use App\Services\BibleReferenceParser;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class SearchController extends Controller
{
    public function __construct(private BibleReferenceParser $parser) {}

    public function books(Request $request)
    {
        $query = $request->input('q', '');

        if (mb_strlen($query) < 1) {
            return response()->json([]);
        }

        $books = BibleBook::where('name', 'like', "%{$query}%")
            ->orderBy('order')
            ->limit(8)
            ->get(['code', 'name']);

        return response()->json($books);
    }

    public function resolve(Request $request)
    {
        try {
            return response()->json($this->parser->parse($request->input('ref', '')));
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}