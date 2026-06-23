@extends('layouts.app')

@section('title', 'Home - Marvel Agency')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- General About Section -->
    <div class="text-center py-20">
        <div class="inline-block mb-6">
            <div class="w-24 h-24 rounded-lg overflow-hidden bg-white flex items-center justify-center shadow-lg border border-gray-100">
                <img
                    src="{{ asset('images/marvel_logo.jpeg') }}"
                    alt="Marvel Agency Logo"
                    class="w-full h-full object-cover"
                >
            </div>
        </div>
        <h1 class="text-5xl font-bold text-gray-900 mb-4 tracking-tight">Marvel Agency</h1>
        <p class="text-2xl text-gold-500 font-semibold mb-6">
            Building People, Protecting Families, Creating Legacy.
        </p>
        <p class="text-lg text-gray-600 mb-10 max-w-3xl mx-auto leading-relaxed">
            Established in 2024 in Surabaya, we are an independent insurance agency committed to more than just sales. We focus on human resource development, leadership, and building a sustainable, duplicatable business model.
        </p>

        @auth
            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : (auth()->user()->isAgent() ? route('consultations.index') : route('client.consultations')) }}"
                class="inline-block px-8 py-4 bg-black text-white hover:bg-gray-800 transition text-lg font-semibold rounded shadow-md">
                Go to Dashboard
            </a>
        @else
            <a href="{{ route('login') }}" class="inline-block px-8 py-4 gold-gradient text-black hover:opacity-90 transition text-lg font-semibold rounded shadow-md border border-gold-500">
                Join Our Journey
            </a>
        @endauth
    </div>

    <hr class="border-gray-200 mb-16">

    <!-- Milestone & Philosophy -->
    <div class="text-center mb-20 max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">Our Philosophy</h2>
        <p class="text-gray-600 text-lg mb-6">
            At Marvel Agency, we believe that success is never a solo journey. It is built through collaboration, strong character, perseverance, and a continuous desire to learn. We don't just build productive agents; we build individuals capable of becoming leaders, inspirations, and blessings to their families and communities.
        </p>
        <div class="bg-gray-50 border-l-4 border-gold-500 p-6 rounded-r-lg inline-block text-left">
            <p class="text-gray-800 font-medium">
                <span class="text-black font-bold">🏆 Milestone:</span> In our first year, Marvel Agency successfully achieved a production of <span class="text-gold-500 font-bold">Rp 3 Billion</span>. We continue moving toward greater growth through a culture of learning, collaboration, and a shared passion to grow together.
            </p>
        </div>
    </div>

    <!-- Core Values / Mission -->
    <div class="grid md:grid-cols-3 gap-8 mb-20">
        <div class="text-center p-8 border border-gray-200 hover:border-gold-500 transition duration-300 rounded-lg bg-white shadow-sm hover:shadow-md">
            <div class="w-16 h-16 bg-black rounded-full flex items-center justify-center mx-auto mb-6">
                <span class="text-gold-500 text-2xl">🛡️</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">Professional Protection</h3>
            <p class="text-gray-600">Helping families secure the right financial protection through professional and trusted insurance solutions.</p>
        </div>

        <div class="text-center p-8 border border-gray-200 hover:border-gold-500 transition duration-300 rounded-lg bg-white shadow-sm hover:shadow-md">
            <div class="w-16 h-16 bg-black rounded-full flex items-center justify-center mx-auto mb-6">
                <span class="text-gold-500 text-2xl">📈</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">Leadership & Growth</h3>
            <p class="text-gray-600">Developing agents into competent Business Partners with uncompromising integrity and strong leadership skills.</p>
        </div>

        <div class="text-center p-8 border border-gray-200 hover:border-gold-500 transition duration-300 rounded-lg bg-white shadow-sm hover:shadow-md">
            <div class="w-16 h-16 bg-black rounded-full flex items-center justify-center mx-auto mb-6">
                <span class="text-gold-500 text-2xl">🤝</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">Sustainable Duplication</h3>
            <p class="text-gray-600">Creating a collaborative culture of learning and building a duplication system so every team member can reach their highest potential.</p>
        </div>
    </div>

    <!-- Founders -->
    <div class="text-center mb-20 max-w-5xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Meet Our Leaders</h2>
        <p class="text-gray-600 text-lg mb-12 max-w-2xl mx-auto">
            The driving force behind Marvel Agency's commitment to growth, integrity, and building a lasting legacy.
        </p>

        <div class="grid md:grid-cols-2 gap-12">
            <!-- Owner 1 -->
            <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-gold-500 transition duration-300">
                <div class="w-32 h-32 mx-auto rounded-full overflow-hidden mb-6 border-4 border-gold-500 bg-gray-50 flex items-center justify-center">
                    <img src="{{ asset('images/man.png') }}" alt="Mimi" class="w-full h-full object-cover text-gray-400" onerror="this.outerHTML='<span class=\'text-4xl\'>👩🏻</span>'">
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-1">Yessup H Wikarsa</h3>
                <p class="text-gold-500 font-semibold mb-4 uppercase tracking-wide text-sm">Founder & Business Partner</p>
                <p class="text-gray-600 italic leading-relaxed">
                    "I believe in being the 'glue' that binds our team together. My passion is building people up so they can protect families and achieve a better life."
                </p>
            </div>

            <!-- Owner 2 -->
            <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-gold-500 transition duration-300">
                <div class="w-32 h-32 mx-auto rounded-full overflow-hidden mb-6 border-4 border-gold-500 bg-gray-50 flex items-center justify-center">
                    <img src="{{ asset('images/woman.jpeg') }}" alt="Second Owner" class="w-full h-full object-cover text-gray-400" onerror="this.outerHTML='<span class=\'text-4xl\'>👨🏻</span>'">
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-1">Mimi Joemana</h3>
                <p class="text-gold-500 font-semibold mb-4 uppercase tracking-wide text-sm">Founder & Business Partner</p>
                <p class="text-gray-600 italic leading-relaxed">
                    "Committed to fostering a culture of continuous learning and developing systems where every partner can duplicate success and reach their highest potential."
                </p>
            </div>
        </div>
    </div>

    <!-- Vision -->
    <div class="bg-black text-white p-12 rounded-xl mb-20 relative overflow-hidden shadow-xl">
        <div class="absolute top-0 left-0 w-2 h-full bg-gold-500"></div>

        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-gold-500 text-sm font-bold uppercase tracking-widest mb-2">Our Vision</h2>
            <p class="text-xl md:text-2xl font-light leading-relaxed mb-12">
                "To be an agency that births Business Partners who grow, succeed in sales, excel in team building, and create a positive impact on countless families."
            </p>

            <hr class="border-gray-800 mb-12">

            <blockquote class="text-2xl md:text-3xl font-serif italic text-white leading-snug">
                "Marvel Agency is not just a place to sell insurance.<br>
                <span class="text-gold-500 font-bold not-italic mt-4 block text-xl md:text-2xl">
                    It is a place to grow, learn to lead, build teams, and create a better future together.
                </span>
            </blockquote>
        </div>
    </div>
</div>
@endsection
