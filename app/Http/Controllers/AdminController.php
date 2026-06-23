<?php

namespace App\Http\Controllers;

use App\Jobs\TrainRfModel;
use App\Models\AlgorithmTestResult;
use App\Models\ModelTrainingRequest;
use App\Models\User;
use App\Models\Product;
use App\Models\Weight;
use App\Models\CaseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Admin Dashboard
     */
    public function dashboard()
    {
        $total_consultations = CaseModel::count();
        $total_products = Product::count();
        $active_products = Product::where('active', true)->count();
        $total_agents = User::where('role', 'agent')->count();
        $active_agents = User::where('role', 'agent')->where('active', true)->count();
        $total_clients = User::where('role', 'client')->count();

        $recent_consultations = CaseModel::with(['customer', 'product', 'agent'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('total_consultations', 'total_products', 'active_products', 'total_agents', 'active_agents', 'total_clients', 'recent_consultations'));
    }

    /**
     * Agent Management - Index
     */
    public function agentsIndex()
    {
        $agents = User::where('role', 'agent')
            ->withCount('consultations')
            ->latest()
            ->paginate(15);

        return view('admin.agents.index', compact('agents'));
    }

    /**
     * Agent Management - Create
     */
    public function agentsCreate()
    {
        return view('admin.agents.create');
    }

    /**
     * Agent Management - Store
     */
    public function agentsStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $agent = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'agent',
            'active' => true,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent created successfully!');
    }

    /**
     * Agent Management - Edit
     */
    public function agentsEdit($id)
    {
        $agent = User::where('role', 'agent')->findOrFail($id);
        return view('admin.agents.edit', compact('agent'));
    }

    /**
     * Agent Management - Update
     */
    public function agentsUpdate(Request $request, $id)
    {
        $agent = User::where('role', 'agent')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:8|confirmed',
            'active' => 'boolean',
        ]);

        $agent->name = $validated['name'];
        $agent->email = $validated['email'];

        if (!empty($validated['password'])) {
            $agent->password = Hash::make($validated['password']);
        }

        $agent->active = $request->has('active');
        $agent->save();

        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent updated successfully!');
    }

    /**
     * Agent Management - Delete
     */
    public function agentsDestroy($id)
    {
        $agent = User::where('role', 'agent')->findOrFail($id);
        $agent->delete();

        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent deleted successfully!');
    }

    /**
     * Product Management - Index
     */
    public function productsIndex()
    {
        $products = Product::withCount('cases')->latest()->get();
        return view('admin.products.index', compact('products'));
    }

    /**
     * Product Management - Create
     */
    public function productsCreate()
    {
        return view('admin.products.create');
    }

    /**
     * Product Management - Store
     */
    public function productsStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'categories' => 'required|array',
            'base_premium' => 'required|numeric|min:0',
            'active' => 'boolean',
        ]);

        Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'categories' => $validated['categories'],
            'base_premium' => $validated['base_premium'],
            'active' => $request->has('active'),
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully!');
    }

    /**
     * Product Management - Edit
     */
    public function productsEdit($id)
    {
        $product = Product::findOrFail($id);
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Product Management - Update
     */
    public function productsUpdate(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'categories' => 'required|array',
            'base_premium' => 'required|numeric|min:0',
            'active' => 'boolean',
        ]);

        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'categories' => $validated['categories'],
            'base_premium' => $validated['base_premium'],
            'active' => $request->has('active'),
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Product Management - Delete
     */
    public function productsDestroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully!');
    }

    /**
     * Weight Management - Index
     */
    public function weightsIndex()
    {
        $weights = Weight::orderBy('id')->get();
        return view('admin.weights.index', compact('weights'));
    }

    /**
     * Weight Management - Update
     */
    public function weightsUpdate(Request $request)
    {
        $validated = $request->validate([
            'weights' => 'required|array',
            'weights.*.id' => 'required|exists:weights,id',
            'weights.*.weight' => 'required|numeric|min:0|max:1',
        ]);

        foreach ($validated['weights'] as $weightData) {
            Weight::where('id', $weightData['id'])->update([
                'weight' => $weightData['weight']
            ]);
        }

        return redirect()->route('admin.weights.index')
            ->with('success', 'Feature weights updated successfully!');
    }

    /**
     * Train Random Forest Model
     */
    public function trainModel()
    {
        $request = ModelTrainingRequest::create(['status' => 'processing']);

        TrainRfModel::dispatch($request->id);

        return response()->json([
            'success' => true,
            'message' => 'Model training request accepted and is being processed',
            'data' => [
                'request_id' => $request->id,
                'status' => 'processing',
            ],
        ], 202);
    }

    /**
     * Poll the status of a previously submitted model training request
     */
    public function trainModelStatus($id)
    {
        $request = ModelTrainingRequest::find($id);

        if (!$request) {
            return response()->json([
                'success' => false,
                'message' => 'Training request not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'request_id' => $request->id,
                'status' => $request->status,
                'output' => $request->output,
                'error_message' => $request->error_message,
            ],
        ]);
    }

    public function metrics()
    {
        $nEstimators = AlgorithmTestResult::where('algorithm_name', 'random_forest')->whereNull('max_depth')->where('max_features', 'sqrt')->where('min_samples_leaf', '1')->where('split_ratio', '70_30')->orderBy('n_estimators', 'asc')->get(['n_estimators', 'f1_score']);
        $maxFeatures = AlgorithmTestResult::where('algorithm_name', 'random_forest')->where('n_estimators', 100)->whereNull('max_depth')->where('min_samples_leaf', '1')->where('split_ratio', '70_30')
            ->get(['max_features', 'f1_score', 'mrr']);
        $maxDepth = AlgorithmTestResult::where('algorithm_name', 'random_forest')->where('n_estimators', 100)->where('max_features', 'sqrt')->where('min_samples_leaf', '1')->where('split_ratio', '70_30')
            ->orderBy('max_depth', 'asc')
            ->get(['max_depth', 'f1_score']);
        $msl = AlgorithmTestResult::where('algorithm_name', 'random_forest')->where('n_estimators', '100')->whereNull('max_depth')->where('max_features', 'sqrt')->where('split_ratio', '70_30')
            ->get(['min_samples_leaf', 'f1_score']);

        $euclidean = AlgorithmTestResult::where('algorithm_name', 'euclidean')
            ->latest()
            ->first();

        $weighted = AlgorithmTestResult::where('algorithm_name', 'weighted_euclidean')
            ->latest()
            ->first();

        $randomForest = AlgorithmTestResult::where('algorithm_name', 'random_forest')
            ->where('n_estimators', 200)
            ->where('max_depth', 5)
            ->whereNull('max_features')
            ->where('min_samples_leaf', 1)
            ->where('split_ratio', '80_20')
            ->first();

        $comparison = collect([$euclidean, $weighted, $randomForest])->filter();
        return view('admin.visualization.index', [
            'nEstimatorsData' => $nEstimators->toJson(),
            'maxFeaturesData' => $maxFeatures->toJson(),
            'maxDepthData' => $maxDepth->toJson(),
            'mssData' => $msl->toJson(),
            'comparisonData' => $comparison->toJson()
        ]);
    }
}
