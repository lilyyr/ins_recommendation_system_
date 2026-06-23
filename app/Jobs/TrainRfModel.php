<?php

namespace App\Jobs;

use App\Models\ModelTrainingRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Process\Exceptions\ProcessTimedOutException;
use Throwable;

class TrainRfModel implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $requestId,
    ) {}

    public function handle(): void
    {
        $request = ModelTrainingRequest::find($this->requestId);
        if (!$request) {
            Log::error("TrainRfModel: request {$this->requestId} not found");
            return;
        }

        $pythonPath = env('PYTHON_PATH', 'python');
        $scriptPath = base_path('python/train_rf.py');

        try {
            $process = Process::timeout(600)->run([$pythonPath, $scriptPath]);
        } catch (ProcessTimedOutException $e) {
            $request->update([
                'status' => 'failed',
                'error_message' => 'Training took too long and was stopped (timeout after 600s).',
            ]);
            return;
        }

        if ($process->failed()) {
            $request->update([
                'status' => 'failed',
                'error_message' => $process->errorOutput() ?: $process->output(),
            ]);
            return;
        }

        $request->update([
            'status' => 'completed',
            'output' => explode("\n", trim($process->output())),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        ModelTrainingRequest::where('id', $this->requestId)->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
        Log::error('TrainRfModel job failed: ' . $exception->getMessage());
    }
}
