<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resume;
use App\Services\ResumeAgentService;

class MatchController extends Controller
{
    public function match(Request $request, ResumeAgentService $agent)
    {
        $request->validate([
            'resume_id' => 'required|exists:resumes,id',
        ]);

        $resume = Resume::findOrFail($request->resume_id);

        return response()->json(
            $agent->runAgent($resume)
        );
    }
}
