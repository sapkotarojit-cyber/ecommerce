@extends('frontend.frontend-layout')

@section('title', 'Too Many Requests')

@section('content')
<section class="py-16 md:py-24">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">

        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">

            {{-- Icon --}}
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-5">
                <i class="fas fa-clock text-3xl text-red-500"></i>
            </div>

            {{-- Title --}}
            <h2 class="text-2xl font-bold text-[#1a2a6c]">
                Too Many Requests
            </h2>

            {{-- Message --}}
            <p class="text-gray-500 mt-2">
                Please wait
                <span
                    id="countdown"
                    class="font-semibold text-[#c9a84c]"
                >
                    {{ $retryAfter }}
                </span>
                seconds before trying again.
            </p>

            {{-- Try Again --}}
            <button
                id="retryButton"
                type="button"
                disabled
                onclick="window.history.back()"
                class="w-full mt-6 px-4 py-3 bg-gray-400 text-white font-semibold rounded-lg cursor-not-allowed transition-all"
            >
                Try Again
            </button>

            {{-- Back to Login --}}
            <a
                href="{{ route('login') }}"
                class="block mt-5 text-[#c9a84c] font-semibold hover:text-[#b8963a]"
            >
                Back to Login
            </a>

        </div>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let remaining = Number(@json($retryAfter));

    const countdown = document.getElementById('countdown');
    const retryButton = document.getElementById('retryButton');

    function updateCountdown() {

        if (remaining <= 0) {

            countdown.textContent = '0';

            retryButton.disabled = false;

            retryButton.classList.remove(
                'bg-gray-400',
                'cursor-not-allowed'
            );

            retryButton.classList.add(
                'bg-[#1a2a6c]',
                'hover:bg-[#2a3a7c]',
                'cursor-pointer'
            );

            retryButton.textContent = 'Try Again';

            return;
        }

        countdown.textContent = remaining;

        remaining--;
    }

    updateCountdown();

    const timer = setInterval(function () {

        updateCountdown();

        if (remaining <= 0) {
            clearInterval(timer);
        }

    }, 1000);
});
</script>
@endsection