<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @auth
        <meta name="api-token" content="{{ auth()->user()->createToken('web-token')->plainTextToken }}">
    @endauth

    <title>@yield('title', 'Insurance CBR System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: {
                            400: '#FFD700',
                            500: '#D4AF37',
                            600: '#B8960F',
                        },
                        dark: {
                            900: '#000000',
                            800: '#0a0a0a',
                            700: '#1a1a1a',
                            600: '#2a2a2a',
                            500: '#3a3a3a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .gold-gradient {
            background: linear-gradient(135deg, #D4AF37 0%, #FFD700 100%);
        }

        .dark-gradient {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
        }

        .gold-border-glow {
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
        }

        .hover-gold:hover {
            border-color: #D4AF37;
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.2);
        }
    </style>
</head>

<body class="bg-white text-gray-900 antialiased">
    <!-- Navigation -->
    <nav class="bg-black border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg overflow-hidden bg-white flex items-center justify-center">
                            <img src="{{ asset('images/marvel_logo.jpeg') }}" alt="Insurance CBR Logo"
                                class="w-full h-full object-cover">
                        </div>
                        <span class="text-white font-semibold text-lg tracking-tight">Marvel Agency</span>
                    </a>
                </div>

                @auth
                    <div class="flex items-center space-x-1">
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}"
                                class="px-4 py-2 text-sm text-gray-300 hover:text-gold-500 transition">Dashboard</a>
                            <a href="{{ route('admin.agents.index') }}"
                                class="px-4 py-2 text-sm text-gray-300 hover:text-gold-500 transition">Agents</a>
                            <a href="{{ route('admin.products.index') }}"
                                class="px-4 py-2 text-sm text-gray-300 hover:text-gold-500 transition">Products</a>
                            <a href="{{ route('admin.weights.index') }}"
                                class="px-4 py-2 text-sm text-gray-300 hover:text-gold-500 transition">Weights</a>
                            <a href="{{ route('admin.metrics.index') }}"
                                class="px-4 py-2 text-sm text-gray-300 hover:text-gold-500 transition">Visualization</a>
                            <a href="{{ route('admin.job-application.admin.index') }}"
                                class="px-4 py-2 text-sm text-gray-300 hover:text-gold-500 transition">Applicators</a>
                        @endif

                        @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
                            <a href="{{ route('consultations.index') }}"
                                class="px-4 py-2 text-sm text-gray-300 hover:text-gold-500 transition">Consultations</a>
                            <a href="{{ route('clients.index') }}"
                                class="px-4 py-2 text-sm text-gray-300 hover:text-gold-500 transition">Clients</a>
                        @endif

                        @if(auth()->user()->isClient())
                            <a href="{{ route('client.consultations') }}"
                                class="px-4 py-2 text-sm text-gray-300 hover:text-gold-500 transition">My Recommendations</a>
                        @endif

                        <div class="ml-4 pl-4 border-l border-gray-700 flex items-center space-x-3">
                            <span class="text-gray-400 text-sm">{{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 text-sm bg-dark-700 text-white hover:bg-dark-600 rounded transition">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="flex items-center">
                        <a href="{{ route('job-application.index') }}"
                            class="px-6 mx-1 py-2 text-sm text-gray-300 bg-gray-500 rounded">Apply</a>
                        <a href=" {{ route('login') }}"
                            class="px-6 py-2 text-sm text-white bg-gold-500 hover:bg-gold-600 rounded transition">
                            Login
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-white border-l-4 border-gold-500 p-4 shadow">
                <p class="text-gray-900">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-white border-l-4 border-red-500 p-4 shadow">
                <p class="text-gray-900">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="py-8 min-h-screen">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-black border-t border-gray-800 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <p class="text-gray-400 text-sm">© 2026 Marvel Agency. All rights reserved.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
