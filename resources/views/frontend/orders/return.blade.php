@extends('frontend.frontend-layout')

@section('title', 'Request Return - ' . ($order->tracking_number ?? $order->id))

<title>Return Details - EmpireInnovation</title>


@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">

        <!-- Back -->
        <div class="mb-6">
            <a href="{{ route('orders.show', $order->id) }}"
               class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                Back to Order
            </a>
        </div>

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                Request Return
            </h1>

            <p class="mt-2 text-sm text-gray-500">
                Order #{{ $order->tracking_number ?? $order->id }}
            </p>
        </div>

        <!-- Order Summary -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">

            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                Order Summary
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <div>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">
                        Order Date
                    </span>

                    <span class="text-sm font-medium text-gray-900">
                        {{ $order->created_at->format('M d, Y') }}
                    </span>
                </div>

                <div>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">
                        Order Status
                    </span>

                    <span class="inline-flex mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        {{ ucfirst($order->order_status) }}
                    </span>
                </div>

                <div>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">
                        Refund Amount
                    </span>

                    <span class="text-lg font-bold text-indigo-600">
                        Rs. {{ number_format($order->total_amount, 2) }}
                    </span>
                </div>

            </div>
        </div>

        <!-- Return Form -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

            <h2 class="text-lg font-semibold text-gray-900 mb-2">
                Why are you returning this order?
            </h2>

            <p class="text-sm text-gray-500 mb-6">
                Please provide a reason for your return request. Our team will review your request.
            </p>

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('orders.return.submit', $order->id) }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label for="reason"
                           class="block text-sm font-semibold text-gray-700 mb-2">
                        Return Reason
                    </label>

                    <textarea
                        name="reason"
                        id="reason"
                        rows="6"
                        required
                        maxlength="1000"
                        placeholder="Please explain why you want to return this order..."
                        class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    >{{ old('reason') }}</textarea>

                    <p class="mt-2 text-xs text-gray-400">
                        Maximum 1000 characters.
                    </p>
                </div>

                <!-- Refund Notice -->
                <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-info text-amber-600 mt-1"></i>

                        <div>
                            <p class="text-sm font-semibold text-amber-800">
                                Refund Information
                            </p>

                            <p class="text-sm text-amber-700 mt-1">
                                Requested refund amount:
                                <strong>
                                    Rs. {{ number_format($order->total_amount, 2) }}
                                </strong>
                            </p>

                            <p class="text-xs text-amber-600 mt-1">
                                The final refund will be processed after your return request is reviewed and approved.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">

                    <a href="{{ route('orders.show', $order->id) }}"
                       class="inline-flex justify-center items-center px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>

                    <button type="submit"
                            class="inline-flex justify-center items-center px-5 py-2.5 bg-amber-600 text-white rounded-xl text-sm font-semibold hover:bg-amber-700 transition-colors">
                        <i class="fa-solid fa-paper-plane mr-2"></i>
                        Submit Return Request
                    </button>

                </div>

            </form>
        </div>

    </div>
</div>
@endsection