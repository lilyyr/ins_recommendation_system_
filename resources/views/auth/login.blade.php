@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-block mb-4">
                <div class="w-29 h-20 rounded-lg overflow-hidden bg-white flex items-center justify-center">
                    <img
                        src="{{ asset('images/marvel_logo.jpeg') }}"
                        alt="Insurance CBR Logo"
                        class="w-full h-full object-cover"
                    >
                </div>
            </div>
            <h2 class="text-3xl font-bold text-gray-900">Welcome Back</h2>
            <p class="text-gray-600 mt-2">Sign in to your account</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white border border-gray-200 p-8 shadow-sm">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-6">
                    <label for="email" class="block text-gray-900 text-sm font-semibold mb-2">
                        Email Address
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="w-full px-4 py-3 border border-gray-300 focus:outline-none focus:border-gold-500 transition @error('email') border-red-500 @enderror"
                    >
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-gray-900 text-sm font-semibold mb-2">
                        Password
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="w-full px-4 py-3 border border-gray-300 focus:outline-none focus:border-gold-500 transition @error('password') border-red-500 @enderror"
                    >
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full bg-black text-white py-4 hover:bg-gray-900 transition font-semibold text-lg"
                >
                    Sign In
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
