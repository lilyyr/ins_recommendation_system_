@extends('layouts.app')

@section('title', 'Careers - Join Our Team')

@section('content')
<div class="max-w-4xl mx-auto px-4">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Join Our Team</h1>
        <p class="text-xl text-gray-600">
            Become part of our innovative insurance platform
        </p>
    </div>

    <!-- Job Description -->
    <div class="bg-white rounded-lg shadow-md p-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Insurance Agent Position</h2>

        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2">About the Role</h3>
            <p class="text-gray-600 mb-4">
                We're looking for passionate insurance agents to join our team and use our cutting-edge CBR system
                to provide personalized insurance recommendations to clients.
            </p>
        </div>

        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2">Responsibilities</h3>
            <ul class="list-disc list-inside text-gray-600 space-y-1">
                <li>Conduct customer consultations using our CBR platform</li>
                <li>Analyze customer needs and recommend suitable insurance products</li>
                <li>Build and maintain strong client relationships</li>
                <li>Meet sales targets and performance metrics</li>
                <li>Stay updated on insurance products and industry trends</li>
            </ul>
        </div>

        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2">Requirements</h3>
            <ul class="list-disc list-inside text-gray-600 space-y-1">
                <li>Minimum high school diploma (Bachelor's degree preferred)</li>
                <li>Experience in sales or customer service (insurance experience is a plus)</li>
                <li>Excellent communication and interpersonal skills</li>
                <li>Self-motivated and target-oriented</li>
                <li>Computer literate and comfortable with technology</li>
            </ul>
        </div>

        <div>
            <h3 class="text-lg font-semibold mb-2">What We Offer</h3>
            <ul class="list-disc list-inside text-gray-600 space-y-1">
                <li>Competitive salary + commission structure</li>
                <li>Comprehensive training on our CBR system</li>
                <li>Professional development opportunities</li>
                <li>Flexible working arrangements</li>
                <li>Health insurance benefits</li>
            </ul>
        </div>
    </div>

    <!-- Application Form -->
    <div class="bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Apply Now</h2>

        @if(session('application_success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded mb-6">
                {{ session('application_success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('job-application.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Full Name -->
                <div>
                    <label for="full_name" class="block text-gray-700 text-sm font-semibold mb-2">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        value="{{ old('full_name') }}"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('full_name') border-red-500 @enderror"
                    >
                    @error('full_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-gray-700 text-sm font-semibold mb-2">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                    >
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-gray-700 text-sm font-semibold mb-2">
                        Phone Number <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="{{ old('phone') }}"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror"
                    >
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date of Birth -->
                <div>
                    <label for="date_of_birth" class="block text-gray-700 text-sm font-semibold mb-2">
                        Date of Birth <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="date"
                        id="date_of_birth"
                        name="date_of_birth"
                        value="{{ old('date_of_birth') }}"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('date_of_birth') border-red-500 @enderror"
                    >
                    @error('date_of_birth')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Address -->
            <div class="mt-6">
                <label for="address" class="block text-gray-700 text-sm font-semibold mb-2">
                    Address <span class="text-red-500">*</span>
                </label>
                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('address') border-red-500 @enderror"
                >{{ old('address') }}</textarea>
                @error('address')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Education Level -->
            <div class="mt-6">
                <label for="education_level" class="block text-gray-700 text-sm font-semibold mb-2">
                    Education Level <span class="text-red-500">*</span>
                </label>
                <select
                    id="education_level"
                    name="education_level"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('education_level') border-red-500 @enderror"
                >
                    <option value="">Select education level</option>
                    <option value="high_school" {{ old('education_level') == 'high_school' ? 'selected' : '' }}>High School</option>
                    <option value="diploma" {{ old('education_level') == 'diploma' ? 'selected' : '' }}>Diploma (D3)</option>
                    <option value="bachelors" {{ old('education_level') == 'bachelors' ? 'selected' : '' }}>Bachelor's Degree (S1)</option>
                    <option value="masters" {{ old('education_level') == 'masters' ? 'selected' : '' }}>Master's Degree (S2)</option>
                </select>
                @error('education_level')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Institution -->
            <div class="mt-6">
                <label for="institution" class="block text-gray-700 text-sm font-semibold mb-2">
                    Educational Institution
                </label>
                <input
                    type="text"
                    id="institution"
                    name="institution"
                    value="{{ old('institution') }}"
                    placeholder="e.g., Petra Christian University"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('institution') border-red-500 @enderror"
                >
                @error('institution')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Work Experience -->
            <div class="mt-6">
                <label for="work_experience" class="block text-gray-700 text-sm font-semibold mb-2">
                    Work Experience
                </label>
                <textarea
                    id="work_experience"
                    name="work_experience"
                    rows="4"
                    placeholder="Describe your work history, positions held, and responsibilities..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('work_experience') border-red-500 @enderror"
                >{{ old('work_experience') }}</textarea>
                @error('work_experience')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Insurance Experience -->
            <div class="mt-6">
                <label for="insurance_experience" class="block text-gray-700 text-sm font-semibold mb-2">
                    Insurance Industry Experience
                </label>
                <textarea
                    id="insurance_experience"
                    name="insurance_experience"
                    rows="4"
                    placeholder="Describe any experience you have in insurance, sales, or related fields..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('insurance_experience') border-red-500 @enderror"
                >{{ old('insurance_experience') }}</textarea>
                @error('insurance_experience')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Motivation -->
            <div class="mt-6">
                <label for="motivation" class="block text-gray-700 text-sm font-semibold mb-2">
                    Why do you want to join our team? <span class="text-red-500">*</span>
                </label>
                <textarea
                    id="motivation"
                    name="motivation"
                    rows="5"
                    required
                    placeholder="Tell us why you're interested in this position and what you can bring to our team..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('motivation') border-red-500 @enderror"
                >{{ old('motivation') }}</textarea>
                @error('motivation')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Resume Upload -->
            <div class="mt-6">
                <label for="resume" class="block text-gray-700 text-sm font-semibold mb-2">
                    Upload Resume/CV (PDF, max 2MB)
                </label>
                <input
                    type="file"
                    id="resume"
                    name="resume"
                    accept=".pdf"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('resume') border-red-500 @enderror"
                >
                <p class="text-xs text-gray-500 mt-1">Optional. PDF format only, maximum 2MB.</p>
                @error('resume')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="mt-8">
                <button
                    type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700 transition font-semibold text-lg"
                >
                    Submit Application
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
