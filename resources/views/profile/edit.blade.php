@extends('frontend.frontend-layout')

@section('title', 'Edit Profile - Empireinnovation')
<title>Edit Profile - EmpireInnovation</title>

@section('content')


<div class="min-h-screen bg-gray-50 py-10">

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Heading --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-[#0F1A3A]">
                My Profile
            </h1>

            <p class="mt-2 text-gray-600">
                Manage your personal information.
            </p>
        </div>


        {{-- Success Message --}}
        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200
                        bg-green-50 px-5 py-4 text-green-700">

                <div class="flex items-center">

                    <svg class="w-5 h-5 mr-3"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M5 13l4 4L19 7"/>

                    </svg>

                    {{ session('success') }}

                </div>
            </div>
        @endif


        {{-- Validation Errors --}}
        @if ($errors->any())

            <div class="mb-6 rounded-lg border border-red-200
                        bg-red-50 px-5 py-4 text-red-700">

                <p class="font-semibold mb-2">
                    Please fix the following errors:
                </p>

                <ul class="list-disc list-inside text-sm">

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif


        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">


            {{-- Profile Information Card --}}
            <div class="bg-white rounded-2xl shadow-sm
                        border border-gray-200 p-6">

                <div class="text-center">

                    {{-- Avatar --}}
                    <div class="mx-auto w-24 h-24 rounded-full
                                bg-[#0F1A3A]
                                flex items-center justify-center
                                border-4 border-[#C9A84C]">

                        <span class="text-3xl font-bold text-white">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>

                    </div>


                    {{-- Name --}}
                    <h2 class="mt-5 text-xl font-bold text-gray-900">
                        {{ $user->name }}
                    </h2>


                    {{-- Email --}}
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $user->email }}
                    </p>


                    {{-- Verification Status --}}
                    @if ($user->email_verified_at)

                        <span class="inline-flex items-center
                                     mt-4 px-3 py-1 rounded-full
                                     text-sm font-medium
                                     bg-green-100 text-green-700">

                            <svg class="w-4 h-4 mr-1"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M5 13l4 4L19 7"/>

                            </svg>

                            Verified Account

                        </span>

                    @else

                        <span class="inline-flex items-center
                                     mt-4 px-3 py-1 rounded-full
                                     text-sm font-medium
                                     bg-yellow-100 text-yellow-700">

                            Email Not Verified

                        </span>

                    @endif

                </div>


                {{-- Profile Navigation --}}
                <div class="mt-8 pt-6 border-t border-gray-100">

                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center px-4 py-3
                              rounded-lg bg-[#0F1A3A] text-white">

                        <svg class="w-5 h-5 mr-3"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                                     M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>

                        </svg>

                        Edit Profile

                    </a>


                    <a href="{{ route('settings') }}"
                       class="flex items-center px-4 py-3 mt-2
                              rounded-lg text-gray-700
                              hover:bg-gray-100 transition">

                        Account Settings

                    </a>


                    <a href="{{ route('shipping-address.index') }}"
                       class="flex items-center px-4 py-3 mt-2
                              rounded-lg text-gray-700
                              hover:bg-gray-100 transition">

                        Shipping Addresses

                    </a>

                </div>

            </div>


            {{-- Edit Profile Form --}}
            <div class="lg:col-span-2">

                <div class="bg-white rounded-2xl shadow-sm
                            border border-gray-200">

                    {{-- Form Header --}}
                    <div class="px-6 py-5 border-b border-gray-200">

                        <h2 class="text-xl font-bold text-[#0F1A3A]">
                            Personal Information
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Update your name and email address.
                        </p>

                    </div>


                    {{-- Form --}}
                    <form action="{{ route('profile.update') }}"
                          method="POST"
                          class="p-6">

                        @csrf

                        @method('PATCH')


                        {{-- Name --}}
                        <div class="mb-6">

                            <label for="name"
                                   class="block text-sm font-semibold
                                          text-gray-700 mb-2">

                                Full Name

                            </label>

                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name', $user->name) }}"
                                required
                                class="w-full px-4 py-3 rounded-lg
                                       border border-gray-300
                                       focus:border-[#C9A84C]
                                       focus:ring-2
                                       focus:ring-[#C9A84C]/20
                                       outline-none transition"
                                placeholder="Enter your full name"
                            >

                            @error('name')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Email --}}
                        <div class="mb-6">

                            <label for="email"
                                   class="block text-sm font-semibold
                                          text-gray-700 mb-2">

                                Email Address

                            </label>

                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email', $user->email) }}"
                                required
                                class="w-full px-4 py-3 rounded-lg
                                       border border-gray-300
                                       focus:border-[#C9A84C]
                                       focus:ring-2
                                       focus:ring-[#C9A84C]/20
                                       outline-none transition"
                                placeholder="Enter your email address"
                            >

                            @error('email')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Buttons --}}
                        <div class="flex items-center justify-end
                                    gap-4 pt-6
                                    border-t border-gray-200">

                            <a href="{{ route('home') }}"
                               class="px-5 py-3 rounded-lg
                                      text-gray-700
                                      hover:bg-gray-100 transition">

                                Cancel

                            </a>


                            <button
                                type="submit"
                                class="px-6 py-3 rounded-lg
                                       font-semibold
                                       bg-[#0F1A3A]
                                       text-white
                                       hover:bg-[#0A122A]
                                       hover:-translate-y-0.5
                                       transition shadow-sm">

                                Save Changes

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


@endsection
