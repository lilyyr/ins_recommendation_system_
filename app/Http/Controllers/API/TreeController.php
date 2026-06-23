<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateTreeVisualizations;
use App\Models\CaseModel;
use App\Models\TreeVisualizationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TreeController extends Controller
{
    /**
     * Queue tree visualization generation
     */
    public function generateTrees($caseId)
    {
        $consultation = CaseModel::findOrFail($caseId);

        if (Auth::user()->role === 'agent' && $consultation->agent_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $request = TreeVisualizationRequest::create([
            'case_id' => $caseId,
            'status' => 'processing',
        ]);

        GenerateTreeVisualizations::dispatch($request->id, (int) $caseId);

        return response()->json([
            'success' => true,
            'message' => 'Tree visualization request accepted and is being processed',
            'data' => [
                'request_id' => $request->id,
                'status' => 'processing',
            ],
            'links' => [
                'check_status' => route('api.visualizations.requests.show', $request->id),
            ]
        ], 202);
    }

    /**
     * Poll the status of a previously submitted tree visualization request
     */
    public function getRequestStatus($id)
    {
        $request = TreeVisualizationRequest::find($id);

        if (!$request) {
            return response()->json([
                'success' => false,
                'message' => 'Tree visualization request not found'
            ], 404);
        }

        $consultation = CaseModel::find($request->case_id);

        if ($consultation && Auth::user()->role === 'agent' && $consultation->agent_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'request_id' => $request->id,
                'status' => $request->status,
                'case_id' => $request->case_id,
                'trees' => $request->trees,
                'num_trees' => $request->num_trees,
                'error_message' => $request->error_message,
            ]
        ]);
    }

    /**
     * Get tree visualization status
     */
    public function getTreeStatus($caseId)
    {
        $treePath = public_path("tree_visualizations/tree_{$caseId}_1.png");

        return response()->json([
            'success' => true,
            'data' => [
                'case_id' => $caseId,
                'trees_generated' => file_exists($treePath),
                'tree_urls' => file_exists($treePath) ? [
                    asset("tree_visualizations/tree_{$caseId}_1.png"),
                    asset("tree_visualizations/tree_{$caseId}_2.png"),
                    asset("tree_visualizations/tree_{$caseId}_3.png"),
                ] : []
            ]
        ]);
    }
}
