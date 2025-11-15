<?php

namespace App\Services;

use App\Models\Resume;
use App\Models\AgentLog;
use Illuminate\Support\Facades\Http;

class ResumeAgentService
{
    public function runAgent(Resume $resume): array
    {
        $step = 1;

        \Log::info('🚀 runAgent start', [
            'resume_id' => $resume->id,
            'chars'     => strlen((string) $resume->text_content),
        ]);

        // Step 1: log input stats
        $this->log($resume, $step++, 'load_input', null, [
            'chars' => strlen((string) $resume->text_content),
        ]);

        $agentUrl = rtrim(config('services.agent.url'), '/') . '/match';

        $parsed = null;

        try {
            // Step 2: call Llama+LangChain microservice
            \Log::info('📡 Calling Llama agent', ['url' => $agentUrl]);

            $response = Http::post($agentUrl, [
                'resume_text' => $resume->text_content,
            ]);

            \Log::info('📡 Agent HTTP response', [
                'status' => $response->status(),
                'ok'     => $response->successful(),
            ]);

            $this->log($resume, $step++, 'llm_call', [
                'url' => $agentUrl,
                'body' => '[resume_text omitted for size]',
            ], $response->json());

            if ($response->successful()) {
                $parsed = $response->json();
            } else {
                \Log::error('❌ Agent returned non-200', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('🔥 Exception calling Llama agent', [
                'message' => $e->getMessage(),
            ]);
        }

        // Step 3: fallback stub if agent failed
        if (!$parsed || !is_array($parsed)) {
            \Log::warning('🟡 Using stub result (agent unavailable)');

            $parsed = [
                'candidate_summary' => 'Stub summary (agent unavailable).',
                'suggested_role'    => 'SD',
                'skill_gaps'        => [
                    'Need SAP SD configuration',
                    'Need hands-on pricing',
                    'Need SD–MM integration basics',
                ],
                'learning_tips'     => [
                    'Take SD config course',
                    'Set up SAP sandbox',
                    'Study integration scenarios',
                ],
            ];
        }

        // Step 4: log final structured result
        $this->log($resume, $step++, 'llm_result', null, $parsed);

        return [
            'resume_id'         => $resume->id,
            'candidate_summary' => $parsed['candidate_summary'] ?? '',
            'suggested_role'    => $parsed['suggested_role'] ?? '',
            'skill_gaps'        => $parsed['skill_gaps'] ?? [],
            'learning_tips'     => $parsed['learning_tips'] ?? [],
        ];
    }

    private function log(Resume $resume, int $step, string $tool, $input, $output)
    {
        AgentLog::create([
            'resume_id' => $resume->id,
            'step'      => $step,
            'tool'      => $tool,
            'input'     => is_string($input) ? $input : json_encode($input),
            'output'    => is_string($output) ? $output : json_encode($output),
            'meta'      => ['ts' => now()],
        ]);
    }
}
