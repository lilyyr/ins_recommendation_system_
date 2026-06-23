<?php

namespace App\Jobs;

use App\Models\CaseModel;
use App\Models\RecommendationRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Process\Exceptions\ProcessTimedOutException;
use Throwable;

class ProcessCbrRecommendation implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $requestId,
        public array $cbrInput,
        public array $caseData,
    ) {
    }

    public function handle(): void
    {
        $request = RecommendationRequest::find($this->requestId);

        if (!$request) {
            Log::error("RecommendationRequest #{$this->requestId} not found when job ran");
            return;
        }

        $cbrResult = $this->runCbrScript($this->cbrInput);

        if (!$cbrResult['success']) {
            $request->update([
                'status' => 'failed',
                'error_message' => $cbrResult['error'] ?? 'CBR script failed without an error message',
            ]);
            return;
        }

        $topRecommendation = $cbrResult['recommendations'][0];

        $case = CaseModel::create(array_merge($this->caseData, [
            'product_id' => $topRecommendation['product_id'],
            'health_risk_score' => $cbrResult['health_risk_score'],
            'feature_vector' => $cbrResult['feature_vector'],
            'euclidean_score' => $topRecommendation['euclidean_score'],
            'weighted_euclidean_score' => $topRecommendation['weighted_euclidean_score'],
            'random_forest_score' => $topRecommendation['random_forest_score'],
            'algorithm_details' => $cbrResult['algorithm_details'],
            'all_recommendations' => $cbrResult['recommendations'],
        ]));

        $request->update([
            'status' => 'completed',
            'case_id' => $case->id,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        RecommendationRequest::where('id', $this->requestId)->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);

        Log::error('ProcessCbrRecommendation job failed: ' . $exception->getMessage());
    }

    protected function runCbrScript(array $data): array
    {
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $inputFile = storage_path('app/temp/cbr_input_' . uniqid() . '.json');
        $outputFile = storage_path('app/temp/cbr_output_' . uniqid() . '.json');

        file_put_contents($inputFile, json_encode($data, JSON_PRETTY_PRINT));

        $pythonPath = env('PYTHON_PATH', 'python');
        $scriptPath = base_path('python/cbr_system.py');

        try {
            $process = Process::timeout(600)->run([$pythonPath, $scriptPath, $inputFile, $outputFile]);
        } catch (ProcessTimedOutException $e) {
            Log::error('CBR script timed out after 600s', ['input_file' => $inputFile]);
            @unlink($inputFile);

            return [
                'success' => false,
                'error' => 'CBR script took too long to respond and was stopped (timeout after 600s).',
            ];
        }

        if (!file_exists($outputFile)) {
            Log::error('Python execution failed - no output file produced', [
                'exit_code' => $process->exitCode(),
                'output' => $process->output(),
                'error_output' => $process->errorOutput(),
            ]);

            @unlink($inputFile);

            return [
                'success' => false,
                'error' => 'Python script failed: ' . ($process->errorOutput() ?: $process->output()),
            ];
        }

        if ($process->failed()) {
            Log::warning('CBR script exited with an error after writing its output file', [
                'exit_code' => $process->exitCode(),
                'error_output' => $process->errorOutput(),
            ]);
        }

        $result = json_decode(file_get_contents($outputFile), true);

        @unlink($inputFile);
        @unlink($outputFile);

        if (!is_array($result)) {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'CBR script reported failure without a clear error message',
            ];
        }

        return $result;
    }
}
