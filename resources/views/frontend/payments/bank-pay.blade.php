@extends('frontend.frontend-layout')

@section('title', 'Direct Bank Transfer - Empireinnovation')
<title>BankPay - EmpireInnovation</title>

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
        <!-- Header -->
        <div class="bg-gray-900 text-white px-6 py-4">
            <h4 class="text-lg font-semibold m-0">Direct Bank Transfer / QR Payment</h4>
        </div>

        <div class="p-8 text-center space-y-6">
            <!-- Tracking and Amount -->
            <div>
                <p class="text-sm text-gray-500 mb-1">Master Tracking Reference: <span class="font-medium text-gray-800">{{ $masterTracking }}</span></p>
                <h3 class="text-2xl font-bold text-green-600">Total Amount to Pay: NPR {{ number_format($totalAmount, 2) }}</h3>
            </div>

            <p class="text-gray-600 max-w-xl mx-auto text-sm">
                Please scan the QR code below using your mobile banking app or transfer funds to one of our official bank accounts listed below.
            </p>

            <!-- QR Code Section -->
            <div class="flex justify-center my-4">
                <div class="p-3 bg-white border border-gray-200 rounded-lg shadow-sm inline-block">
                    <img src="{{ asset('images/bank-qr.png') }}" alt="Payment QR Code" class="h-48 w-48 object-contain mx-auto">
                </div>
            </div>

            <!-- Bank Account Details Options -->
            <div class="text-left bg-gray-50 p-6 rounded-lg border border-gray-200 max-w-xl mx-auto">
                <h5 class="font-semibold text-gray-800 mb-3 text-base">Bank Account Options:</h5>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li><strong class="text-gray-900">Bank Name:</strong> Example Commercial Bank Ltd.</li>
                    <li><strong class="text-gray-900">Account Name:</strong> Your Company Name</li>
                    <li><strong class="text-gray-900">Account Number:</strong> 01234567890123</li>
                    <li><strong class="text-gray-900">Branch:</strong> Kathmandu, Nepal</li>
                </ul>
            </div>

            <!-- Action Form -->
            <form action="{{ route('bank.success') }}" method="POST" enctype="multipart/form-data" class="text-left max-w-xl mx-auto space-y-5 pt-4 border-t border-gray-100">
                @csrf
                
                <!-- Screenshot Note -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded text-blue-700 text-sm">
                    <strong>Note:</strong> Please take a screenshot of your successful transaction receipt before submitting.
                </div>

                <!-- Upload Payment Receipt -->
                <div>
                    <label for="payment_receipt" class="block text-sm font-semibold text-gray-700 mb-1">
                        Upload Payment Receipt <span class="text-red-500">*</span>
                    </label>
                    <input type="file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-900 file:text-white hover:file:bg-gray-800 border border-gray-300 rounded-md cursor-pointer bg-gray-50 focus:outline-none" id="payment_receipt" name="payment_receipt" accept="image/*,application/pdf" required>
                    <p class="mt-1 text-xs text-gray-500">Upload your transaction screenshot or receipt (Image or PDF, max 2MB).</p>
                </div>

                <!-- Terms and Conditions Agreement -->
                <div class="flex items-center space-x-2">
                    <input type="checkbox" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded cursor-pointer" id="terms" name="terms" value="1" required>
                    <label for="terms" class="text-sm text-gray-700 cursor-pointer">I agree to the terms and conditions</label>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2.5 px-4 rounded-lg shadow transition duration-150 text-center">
                        Confirm Payment
                    </button>
                    <a href="{{ route('bank.failure') }}" class="flex-1 bg-white border border-red-500 text-red-600 hover:bg-red-50 font-medium py-2.5 px-4 rounded-lg transition duration-150 text-center">
                        Cancel Transfer
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection