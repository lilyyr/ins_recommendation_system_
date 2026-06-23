<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobApplicationController extends Controller
{
    public function index()
    {
        return view('job-application.index');
    }

    public function adminIndex(Request $request)
    {
        $query = JobApplication::query();

        // Search and filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('full_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('phone', 'like', '%' . $searchTerm . '%');
            });
        }

        // Order by newest and paginate results
        $applications = $query->latest()->paginate(10);

        // Keep the search term in the pagination links
        $applications->appends($request->all());

        return view('job-application.admin-index', compact('applications'));
    }
    public function adminShow($id)
    {
        $application = JobApplication::findOrFail($id);

        return view('job-application.show', compact('application'));
    }

    public function downloadResume($id)
    {
        $application = JobApplication::findOrFail($id);

        if (!$application->resume_path || !Storage::disk('local')->exists($application->resume_path)) {
            abort(404, 'Resume not found.');
        }

        return Storage::disk('local')->download(
            $application->resume_path,
            $application->resume_original_name
        );
    }

    // Update status and admin notes
    public function adminUpdate(Request $request, $id)
    {
        $application = JobApplication::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,accepted,rejected',
            'admin_notes' => 'nullable|string'
        ]);

        $application->update($validated);

        return redirect()->back()->with('success', 'Application updated successfully');
    }

    public function adminDelete(Request $request, $id)
    {
        $application = JobApplication::findOrFail($id);
        if ($application->resume_path) {
            Storage::disk('local')->delete($application->resume_path);
        }

        $application->delete();
        return redirect()->back()->with('success', 'Application deleted successfully');
    }

    public function store(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'address' => 'required|string',
            'education_level' => 'required|in:high_school,diploma,bachelors,masters',
            'institution' => 'nullable|string|max:255',
            'work_experience' => 'nullable|string',
            'insurance_experience' => 'nullable|string',
            'motivation' => 'required|string',
            'resume' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        // Handle the file upload
        if ($request->hasFile('resume')) {
            $path = $request->file('resume')->store('resumes', 'local');
            
            $validated['resume_path'] = $path;
            $validated['resume_original_name'] = $request->file('resume')->getClientOriginalName();
        }

        unset($validated['resume']);

        // Save
        JobApplication::create($validated);

        return back()->with('application_success', 'Thank you! Your application has been submitted successfully.');
    }
}
