@extends('layouts.app')

@section('title', 'Application Details: ' . $application->full_name)

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.job-application.admin.index') }}" class="text-blue-600 hover:text-blue-800 font-medium flex items-center gap-2">
            &larr; Back to Applications
        </a>
        
        @if(session('success'))
            <span class="bg-green-100 text-green-800 text-sm font-medium px-4 py-2 rounded">
                {{ session('success') }}
            </span>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ $application->full_name }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider">Email</p>
                        <p class="text-gray-900"><a href="mailto:{{ $application->email }}" class="text-blue-600 hover:underline">{{ $application->email }}</a></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider">Phone</p>
                        <p class="text-gray-900">{{ $application->phone }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider">Date of Birth</p>
                        <p class="text-gray-900">{{ $application->date_of_birth->format('F d, Y') }} ({{ $application->date_of_birth->age }} years old)</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider">Applied On</p>
                        <p class="text-gray-900">{{ $application->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider">Address</p>
                        <p class="text-gray-900">{{ $application->address }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-4">Education & Experience</h3>
                
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider">Education Level</p>
                            <p class="text-gray-900 capitalize">{{ str_replace('_', ' ', $application->education_level) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider">Institution</p>
                            <p class="text-gray-900">{{ $application->institution ?: 'N/A' }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider mb-1">Work Experience</p>
                        <div class="bg-gray-50 p-4 rounded text-gray-800 whitespace-pre-wrap">{{ $application->work_experience ?: 'None provided.' }}</div>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider mb-1">Insurance Experience</p>
                        <div class="bg-gray-50 p-4 rounded text-gray-800 whitespace-pre-wrap">{{ $application->insurance_experience ?: 'None provided.' }}</div>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider mb-1">Motivation / Cover Letter</p>
                        <div class="bg-blue-50 border border-blue-100 p-4 rounded text-gray-800 whitespace-pre-wrap">{{ $application->motivation }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-blue-500">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Applicant Resume</h3>
                
                @if($application->resume_path)
                    <div class="flex items-center gap-3 bg-gray-50 p-3 rounded border">
                        <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                        <div class="overflow-hidden">
                            <p class="text-sm font-semibold text-gray-700 truncate" title="{{ $application->resume_original_name }}">{{ $application->full_name }}'s CV</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.job-application.resume.download', $application->id) }}" class="mt-4 block w-full text-center bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 font-semibold transition">
                        Download PDF
                    </a>
                @else
                    <p class="text-gray-500 italic text-center py-4 bg-gray-50 rounded">No resume attached to this application.</p>
                @endif
            </div>

            <form action="{{ route('admin.job-application.admin.update', $application->id) }}" method="POST" class="bg-white rounded-lg shadow-md p-6 border-t-4 border-gray-800">
                @csrf
                @method('PUT')
                
                <h3 class="text-lg font-bold text-gray-900 mb-4">Admin Controls</h3>

                <div class="mb-4">
                    <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Application Status</label>
                    <select name="status" id="status" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="pending" {{ $application->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="reviewed" {{ $application->status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                        <option value="accepted" {{ $application->status === 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="rejected" {{ $application->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label for="admin_notes" class="block text-sm font-semibold text-gray-700 mb-2">Internal Notes (Private)</label>
                    <textarea 
                        name="admin_notes" 
                        id="admin_notes" 
                        rows="5" 
                        placeholder="Add notes from interviews, reference checks, etc..."
                        class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                    >{{ $application->admin_notes }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">These notes are only visible to administrators.</p>
                </div>

                <button type="submit" class="w-full bg-gray-900 text-white px-4 py-2 rounded hover:bg-gray-800 font-semibold transition">
                    Save Changes
                </button>
            </form>
            
        </div>
    </div>
</div>
@endsection