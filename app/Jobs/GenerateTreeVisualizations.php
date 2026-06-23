<?php

namespace App\Jobs;

use App\Models\TreeVisualizationRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Throwable;

class GenerateTreeVisualizations implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $requestId,
        public int $caseId,
        public int $numTrees = 3,
    ) {
    }

    public function handle(): void
    {
        $request = TreeVisualizationRequest::find($this->requestId);

        if (!$request) {
            Log::error("TreeVisualizationRequest #{$this->requestId} not found when job ran");
            return;
        }

        $pythonPath = env('PYTHON_PATH', 'python');
        $scriptPath = base_path('python/visualize_trees.py');

        try {
            $process = Process::timeout(180)->run([
                $pythonPath,
                $scriptPath,
                (string) $this->caseId,
                (string) $this->numTrees,
            ]);
        } catch (ProcessTimedOutException $e) {
            $request->update([
                'status' => 'failed',
                'error_message' => 'Tree visualization took too long to generate and was stopped (timeout after 180s).',
            ]);
            return;
        }

        $result = json_decode($process->output(), true);

        if (!is_array($result) || !($result['success'] ?? false)) {
            Log::error('Tree visualization failed', [
                'exit_code' => $process->exitCode(),
                'output' => $process->output(),
                'error_output' => $process->errorOutput(),
            ]);

            $request->update([
                'status' => 'failed',
                'error_message' => $result['error'] ?? ('Tree visualization script failed: ' . $process->errorOutput()),
            ]);
            return;
        }

        $request->update([
            'status' => 'completed',
            'num_trees' => $result['num_trees_visualized'],
            'trees' => $result['trees'],
        ]);
    }

    public function failed(Throwable $exception): void
    {
        TreeVisualizationRequest::where('id', $this->requestId)->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);

        Log::error('GenerateTreeVisualizations job failed: ' . $exception->getMessage());
    }
}
