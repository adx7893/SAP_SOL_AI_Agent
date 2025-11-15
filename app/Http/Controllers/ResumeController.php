<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resume;

class ResumeController extends Controller
{
   public function upload(Request $request)
{
    try {
        \Log::info("📥 Hitting /api/resume endpoint");

        $request->validate([
            'resume' => 'required|file|max:5120',
        ]);

        \Log::info("📄 File validated", [
            'mime' => $request->file('resume')->getMimeType(),
            'name' => $request->file('resume')->getClientOriginalName(),
        ]);

        $file = $request->file('resume');
        $path = $file->store('resumes');

        \Log::info("💾 File stored successfully", [
            'path' => $path
        ]);

        $text = 'Placeholder extracted text from ' . $file->getClientOriginalName();

        $resume = Resume::create([
            'original_filename' => $file->getClientOriginalName(),
            'stored_path'       => $path,
            'mime_type'         => $file->getMimeType(),
            'text_content'      => $text,
        ]);

        \Log::info("🟢 Resume DB record created", [
            'resume_id' => $resume->id,
        ]);

        return response()->json([
            'message'   => 'Resume uploaded successfully',
            'resume_id' => $resume->id,
        ]);

    } catch (\Throwable $e) {

        \Log::error("❌ ERROR IN /api/resume", [
            'error' => $e->getMessage(),
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'error' => 'Internal Server Error',
            'details' => $e->getMessage()
        ], 500);
    }
}

}
