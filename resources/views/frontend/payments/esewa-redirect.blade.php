@extends('frontend.frontend-layout')

@section('title', 'Redirecting to eSewa - Empireinnovation')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-16 text-center">
    <div class="bg-white p-8 rounded-xl shadow-sm max-w-md mx-auto">
        <div class="w-12 h-12 border-4 border-[#c9a84c] border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <h2 class="text-xl font-bold text-[#1a2a6c] mb-2">Connecting to eSewa...</h2>
        <p class="text-gray-500 text-sm mb-6">Please do not refresh or close this window while we redirect you to the payment gateway.</p>

        <!-- Form that auto-submits to eSewa -->
        <form id="esewa-form" action="{{ $data['gateway_url'] }}" method="POST">
            <input type="hidden" name="amount" value="{{ $data['amount'] }}">
            <input type="hidden" name="tax_amount" value="{{ $data['tax_amount'] }}">
            <input type="hidden" name="total_amount" value="{{ $data['total_amount'] }}">
            <input type="hidden" name="transaction_uuid" value="{{ $data['transaction_uuid'] }}">
            <input type="hidden" name="product_code" value="{{ $data['product_code'] }}">
            <input type="hidden" name="product_service_charge" value="{{ $data['product_service_charge'] }}">
            <input type="hidden" name="product_delivery_charge" value="{{ $data['product_delivery_charge'] }}">
            <input type="hidden" name="success_url" value="{{ $data['success_url'] }}">
            <input type="hidden" name="failure_url" value="{{ $data['failure_url'] }}">
            <input type="hidden" name="signed_field_names" value="{{ $data['signed_field_names'] }}">
            <input type="hidden" name="signature" value="{{ $data['signature'] }}">
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Automatically submit the form to eSewa on page load
        document.getElementById('esewa-form').submit();
    });
</script>
@endsection