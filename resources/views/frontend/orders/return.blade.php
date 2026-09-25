@extends('frontend.frontend-layout')

@section('title', 'Request Return - ' . ($order->tracking_number ?? $order->id))

<title>Return Order - EmpireInnovation</title>


@section('content')
<div class="py-8 px-4">
    <div class="max-w-3xl mx-auto">

        <a href="{{ route('orders.show', $order->id) }}"
           class="text-sm text-indigo-600 hover:text-indigo-800">
            <i class="fa-solid fa-arrow-left mr-2"></i>Back to Order
        </a>

        <div class="mt-6 mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Request Return</h1>
            <p class="mt-2 text-sm text-gray-500">
                Order #{{ $order->tracking_number ?? $order->id }}
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Order Summary</h2>

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <span class="block text-xs text-gray-400 uppercase">Order Date</span>
                    <span class="text-sm font-medium">
                        {{ $order->created_at->format('M d, Y') }}
                    </span>
                </div>

                <div>
                    <span class="block text-xs text-gray-400 uppercase">Order Status</span>
                    <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        {{ ucfirst($order->order_status) }}
                    </span>
                </div>

                <div>
                    <span class="block text-xs text-gray-400 uppercase">Refund Amount</span>
                    <span class="text-lg font-bold text-indigo-600">
                        Rs. {{ number_format($order->total_amount, 2) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

            <h2 class="text-lg font-semibold">Why are you returning this order?</h2>
            <p class="text-sm text-gray-500 mt-1 mb-6">
                Please provide a reason for your return request.
            </p>

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-xl text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('orders.returns.store', $order->id) }}" method="POST">
                @csrf

                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Return Reason
                </label>

                <textarea name="reason"
                          rows="6"
                          required
                          maxlength="1000"
                          placeholder="Please explain why you want to return this order..."
                          class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('reason') }}</textarea>

                <p class="mt-2 text-xs text-gray-400">Maximum 1000 characters.</p>

                <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700">
                    <strong>Refund Amount:</strong>
                    Rs. {{ number_format($order->total_amount, 2) }}<br>
                    <span class="text-xs">
                        Final refund will be processed after your request is reviewed and approved.
                    </span>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-end mt-6">
                    <a href="{{ route('orders.show', $order->id) }}"
                       class="px-5 py-2.5 text-center border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>

                    <button type="submit"
                            class="px-5 py-2.5 bg-amber-600 text-white rounded-xl text-sm font-semibold hover:bg-amber-700">
                        <i class="fa-solid fa-paper-plane mr-2"></i>
                        Submit Return Request
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection