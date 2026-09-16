@extends('frontend.frontend-layout')

@section('title', 'Add Shipping Address - Empireinnovation')

@section('content')


<div class="min-h-screen bg-gray-50 py-10">

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-[#0F1A3A]">
                Account Settings
            </h1>

            <p class="mt-2 text-gray-600">
                Manage your account security and password.
            </p>
        </div>


        {{-- Success Message --}}
        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200
                        bg-green-50 px-5 py-4 text-green-700">
                {{ session('success') }}
            </div>
        @endif


        {{-- Error Messages --}}
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

            {{-- Left Side --}}
            <div class="bg-white rounded-2xl shadow-sm
                        border border-gray-200 p-6">

                <div class="text-center">

                    {{-- Avatar --}}
                    <div class="mx-auto w-24 h-24 rounded-full
                                bg-[#0F1A3A]
                                border-4 border-[#C9A84C]
                                flex items-center justify-center">

                        <span class="text-3xl font-bold text-white">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>

                    </div>

                    <h2 class="mt-5 text-xl font-bold text-gray-900">
                        {{ $user->name }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        {{ $user->email }}
                    </p>

                </div>


                {{-- Navigation --}}
                <div class="mt-8 pt-6 border-t border-gray-100">

                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center px-4 py-3 rounded-lg
                              text-gray-700 hover:bg-gray-100 transition">

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
                              rounded-lg bg-[#0F1A3A] text-white">

                        <svg class="w-5 h-5 mr-3"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-7a2 2 0 00-2-2H6a2 2 0 00-2 2v7a2 2 0 002 2z
                                     M8 10V7a4 4 0 118 0v3"/>

                        </svg>

                        Account Settings

                    </a>


                    <a href="{{ route('shipping-address.index') }}"
                       class="flex items-center px-4 py-3 mt-2
                              rounded-lg text-gray-700
                              hover:bg-gray-100 transition">

                        <svg class="w-5 h-5 mr-3"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M17.657 16.657L13.414 21
                                     a2 2 0 01-2.828 0l-4.243-4.343
                                     a8 8 0 1111.314 0z"/>

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>

                        </svg>

                        Shipping Addresses

                    </a>

                </div>

            </div>


            {{-- Password Section --}}
            <div class="lg:col-span-2">

                <div class="bg-white rounded-2xl shadow-sm
                            border border-gray-200">

                    <div class="px-6 py-5 border-b border-gray-200">

                        <h2 class="text-xl font-bold text-[#0F1A3A]">
                            Change Password
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Update your password to keep your account secure.
                        </p>

                    </div>


                    <form action="{{ route('settings.password') }}"
                          method="POST"
                          class="p-6">

                        @csrf
                        @method('PUT')


                        {{-- Current Password --}}
                        <div class="mb-6">

                            <label for="current_password"
                                   class="block text-sm font-semibold
                                          text-gray-700 mb-2">

                                Current Password

                            </label>

                            <input
                                type="password"
                                name="current_password"
                                id="current_password"
                                required
                                class="w-full px-4 py-3 rounded-lg
                                       border border-gray-300
                                       focus:border-[#C9A84C]
                                       focus:ring-2
                                       focus:ring-[#C9A84C]/20
                                       outline-none transition"
                                placeholder="Enter your current password"
                            >

                            @error('current_password')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- New Password --}}
                        <div class="mb-6">

                            <label for="password"
                                   class="block text-sm font-semibold
                                          text-gray-700 mb-2">

                                New Password

                            </label>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                class="w-full px-4 py-3 rounded-lg
                                       border border-gray-300
                                       focus:border-[#C9A84C]
                                       focus:ring-2
                                       focus:ring-[#C9A84C]/20
                                       outline-none transition"
                                placeholder="Enter your new password"
                            >

                            @error('password')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                            <p class="mt-2 text-xs text-gray-500">
                                Minimum 8 characters.
                            </p>

                        </div>


                        {{-- Confirm Password --}}
                        <div class="mb-6">

                            <label for="password_confirmation"
                                   class="block text-sm font-semibold
                                          text-gray-700 mb-2">

                                Confirm New Password

                            </label>

                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                required
                                class="w-full px-4 py-3 rounded-lg
                                       border border-gray-300
                                       focus:border-[#C9A84C]
                                       focus:ring-2
                                       focus:ring-[#C9A84C]/20
                                       outline-none transition"
                                placeholder="Confirm your new password"
                            >

                        </div>


                        {{-- Submit --}}
                        <div class="flex justify-end pt-6
                                    border-t border-gray-200">

                            <button
                                type="submit"
                                class="px-6 py-3 rounded-lg
                                       font-semibold
                                       bg-[#0F1A3A]
                                       text-white
                                       hover:bg-[#0A122A]
                                       transition">

                                Update Password

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


@endsection
