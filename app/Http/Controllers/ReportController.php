<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Envía una publicación ofensiva a revisión del admin.
     */
    public function store(Request $request, Post $post): RedirectResponse
    {
        $viewer = $request->user();

        abort_unless(! $post->author->isAdmin(), 404);
        abort_if($post->user_id === $viewer->id, 422, 'No podés reportar tu propia publicación.');

        $reason = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ])['reason'];

        Report::query()->firstOrCreate(
            ['reporter_id' => $viewer->id, 'post_id' => $post->id],
            ['reason' => trim($reason)],
        );

        return back()->with('toast', ['type' => 'success', 'message' => 'Reporte enviado a revisión.']);
    }
}
