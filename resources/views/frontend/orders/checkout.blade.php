@extends('frontend.frontend-layout')

@section('title', 'Checkout')

@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8" x-data="checkoutPage()">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Checkout</h1>
        </div>

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('orders.store') }}" method="POST" id="checkout-form">
            @csrf
            <div class="flex flex-col lg:flex-row gap-8 items-start">
                
                <!-- Left Section: Shipping & Payment -->
                <div class="w-full lg:flex-1 space-y-6">
                    
                    <!-- Shipping Address Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900">Shipping Address</h2>
                            <button type="button" @click="openModal()" class="inline-flex items-center text-xs font-semibold uppercase tracking-wider text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1.5 rounded-lg transition-colors">
                                + Add New Address
                            </button>
                        </div>

                        <div>
                            <div id="no-address-msg" class="{{ $addresses->isNotEmpty() ? 'hidden' : '' }}">
                                <p class="text-sm text-gray-500 py-2">No shipping addresses found. Please add an address to proceed.</p>
                            </div>

                            <div id="address-list-container" class="space-y-3 {{ $addresses->isEmpty() ? 'hidden' : '' }}">
                            <!-- Shipping Addresses Loop -->
                                    @if(isset($addresses) && count($addresses) > 0)
                                        @foreach($addresses as $address)
                                            <label class="flex items-start space-x-3 p-4 border rounded-lg cursor-pointer bg-gray-50/50">
                                                <input type="radio" name="shipping_address_id" value="{{ $address->id }}" {{ $address->is_default_shipping ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 mt-1">
                                                <div>
                                                    <p class="font-semibold text-gray-800">{{ $address->name }} <span class="text-xs font-normal text-gray-500">({{ $address->address_type }})</span></p>
                                                    <p class="text-sm text-gray-600">{{ $address->address }}</p>
                                                    <p class="text-sm text-gray-600">{{ $address->region }} | Phone: {{ $address->phone }}</p>
                                                </div>
                                            </label>
                                        @endforeach
                                    @endif
                            </div>

                            @error('shipping_address_id')
                                <p class="text-red-500 text-xs font-medium mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Payment Method Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="pb-4 mb-4 border-b border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900">Payment Method</h2>
                        </div>
                        <div class="space-y-3">
                            <label class="relative flex items-center p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-indigo-500 transition-all bg-gray-50/50">
                                <input type="radio" name="payment_method" value="cod" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" checked>
                                <span class="ml-3 text-sm font-semibold text-gray-900">Cash on Delivery (COD)</span>
                            </label>
                            
                            <label class="relative flex items-center p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-indigo-500 transition-all bg-gray-50/50">
                                <input type="radio" name="payment_method" value="esewa" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                <span class="ml-3 text-sm font-semibold text-gray-900">Pay with eSewa (Online Payment)</span>
                            </label>

                            <label class="relative flex items-center p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-indigo-500 transition-all bg-gray-50/50">
                                <input type="radio" name="payment_method" value="bank" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                <span class="ml-3 text-sm font-semibold text-gray-900">Direct Bank Transfer</span>
                            </label>
                        </div>
                        @error('payment_method')
                            <p class="text-red-500 text-xs font-medium mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <!-- Right Section: Order Summary Sidebar -->
                <div class="w-full lg:w-96 lg:sticky lg:top-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="pb-4 mb-4 border-b border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                        </div>

                        <div class="space-y-4 mb-6">
                            @php $itemIndex = 0; @endphp
                            @if(isset($vendorTotal) && count($vendorTotal) > 0)
                                @foreach($vendorTotal as $dokanId =>$vendorGroup)
                                    <div class="pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                                        <div class="text-xs font-bold uppercase tracking-wider text-indigo-600 mb-2">
                                            Store: {{ $vendorGroup['dokan']->name ?? 'Default Store' }}
                                        </div>
                                        <div class="space-y-2 mb-3">
                                          <!-- Order Summary Loops -->
                                            @if(isset($vendorTotal) && count($vendorTotal) > 0)
                                                @foreach($vendorTotal as $dokanId => $vendorGroup)
                                                    <div class="pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                                                        <div class="text-xs font-bold uppercase tracking-wider text-indigo-600 mb-2">
                                                            Store: {{ $vendorGroup['dokan']->name ?? 'Default Store' }}
                                                        </div>
                                                        <div class="space-y-2 mb-3">
                                                            @if(isset($vendorGroup['items']) && count($vendorGroup['items']) > 0)
                                                                @foreach($vendorGroup['items'] as $item)
                                                                    <!-- Item loop content -->
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                        <div class="flex justify-between text-xs text-gray-500 font-medium">
                                            <span>Store Subtotal</span>
                                            <span>${{ number_format($vendorGroup['subtotal'] ?? 0, 2) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="pt-4 border-t border-gray-200 flex justify-between items-center mb-6">
                            <span class="text-base font-bold text-gray-900">Total Amount</span>
                            <span class="text-xl font-extrabold text-gray-900">${{ number_format($grandTotal ?? 0, 2) }}</span>
                        </div>

                        <button id="submit-order-btn" type="submit" class="w-full py-3.5 px-4 bg-gray-900 hover:bg-gray-800 text-white font-semibold rounded-xl shadow-sm transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed text-sm uppercase tracking-wider" {{ $addresses->isEmpty() ? 'disabled' : '' }}>
                            Place Order
                        </button>
                    </div>
                </div>

            </div>
        </form>

        <!-- Inline Add Address Modal -->
        <div x-show="showAddressModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative" @click.away="closeModal()">
                <div class="flex justify-between items-center mb-4 border-b pb-3">
                    <h3 class="text-lg font-bold text-gray-900">Add Shipping Address</h3>
                    <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="saveAddress()">
                    <div class="space-y-4 text-sm">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Recipient's Name *</label>
                            <input type="text" x-model="form.name" required placeholder="Input the real name" class="w-full border-gray-300 rounded-lg p-2.5 border focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Address Title / Type *</label>
                            <input type="text" x-model="form.address_type" required placeholder="e.g. Home, Office" class="w-full border-gray-300 rounded-lg p-2.5 border focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Contact Number *</label>
                            <input type="text" x-model="form.phone" required placeholder="e.g. +977 9800000000" class="w-full border-gray-300 rounded-lg p-2.5 border focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Region / City / District *</label>
                            <input type="text" x-model="form.region" required placeholder="Enter region or city" class="w-full border-gray-300 rounded-lg p-2.5 border focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Full Address *</label>
                            <textarea x-model="form.address" required rows="3" placeholder="Enter complete address detail..." class="w-full border-gray-300 rounded-lg p-2.5 border focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="closeModal()" class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-50 text-xs font-semibold uppercase tracking-wider">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold text-xs uppercase tracking-wider">Save Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function checkoutPage() {
    return {
        showAddressModal: false,
        form: {
            name: '',
            address_type: '',
            phone: '',
            region: '',
            address: ''
        },
        openModal() {
            this.showAddressModal = true;
        },
        closeModal() {
            this.showAddressModal = false;
            this.form = { name: '', address_type: '', phone: '', region: '', address: '' };
        },
        saveAddress() {
            fetch("{{ route('shipping-address.quick-store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify(this.form)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const address = data.address;
                    
                    const newAddressHtml = `
                        <label class="flex items-start space-x-3 p-4 border rounded-lg cursor-pointer bg-gray-50/50">
                            <input type="radio" name="shipping_address_id" value="${address.id}" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 mt-1" checked required>
                            <div>
                                <p class="font-semibold text-gray-800">${address.name} <span class="text-xs font-normal text-gray-500">(${address.address_type})</span></p>
                                <p class="text-sm text-gray-600">${address.address}</p>
                                <p class="text-sm text-gray-600">${address.region} | Phone: ${address.phone}</p>
                            </div>
                        </label>
                    `;

                    const container = document.getElementById('address-list-container');
                    container.classList.remove('hidden');
                    container.insertAdjacentHTML('beforeend', newAddressHtml);

                    document.getElementById('no-address-msg').classList.add('hidden');
                    document.getElementById('submit-order-btn').removeAttribute('disabled');

                    this.closeModal();
                } else {
                    alert(data.message || 'Error saving address.');
                }
            })
            .catch(error => console.error("Error adding address:", error));
        }
    };
}
</script>
@endsection