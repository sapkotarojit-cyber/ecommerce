@extends('frontend.frontend-layout')

@section('title', 'Checkout')
<title>Checkout - EmpireInnovation</title>

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
        @if(session('shipping_quote_error'))
            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm font-medium">
                {{ session('shipping_quote_error') }}
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
                            

                            <div id="address-list-container" class="space-y-3">
    @if(isset($addresses) && $addresses->count() > 0)
        @foreach($addresses as $address)
            <label class="flex items-start space-x-3 p-4 border rounded-lg cursor-pointer bg-gray-50/50">
                <input type="radio" name="shipping_address_id" value="{{ $address->id }}" {{ (int) ($selectedAddressId ?? 0) === (int) $address->id ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 mt-1" onchange="refreshShippingQuote()">
                <div>
                    <p class="font-semibold text-gray-800">{{ $address->name }} <span class="text-xs font-normal text-gray-500">({{ $address->address_type }})</span></p>
                    <p class="text-sm text-gray-600">{{ $address->address }}</p>
                    <p class="text-sm text-gray-600">{{ $address->region }} | Phone: {{ $address->phone }}</p>
                </div>
            </label>
        @endforeach
    @else
        <p class="text-sm text-gray-500 py-2">No shipping addresses found. Please add an address to proceed.</p>
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
    @if(isset($vendorTotal) && is_array($vendorTotal) && count($vendorTotal) > 0)
        @foreach($vendorTotal as $dokanId => $vendorGroup)
            <div class="pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                <div class="text-xs font-bold uppercase tracking-wider text-indigo-600 mb-2 flex items-center">
                    Store: {{ $vendorGroup['dokan']->company_name ?? $vendorGroup['dokan']->name ?? 'Default Store' }}
                </div>
                <div class="space-y-2 mb-3">
                    @if(isset($vendorGroup['items']) && count($vendorGroup['items']) > 0)
                        @foreach($vendorGroup['items'] as $item)
                            @php
                                $price = $item->varient->price ?? 0;
                                $discount = $item->varient->discount ?? 0;
                                $finalPrice = $price - ($price * $discount / 100);
                            @endphp
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600 truncate pr-2">
                                    {{ $item->product->title ?? 'Product' }} <span class="text-xs text-gray-400">x{{ $item->qty }}</span>
                                </span>
                                <span class="font-medium text-gray-900">Rs. {{ number_format($finalPrice * $item->qty, 2) }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="flex justify-between text-xs text-gray-500 font-medium bg-gray-50 p-2 rounded">
                    <span>Store Subtotal</span>
                    <span>Rs. {{ number_format($vendorGroup['subtotal'] ?? 0, 2) }}</span>
                </div>
            </div>
        @endforeach
    @endif
</div>

                        <div class="pt-4 border-t border-gray-200 space-y-3 mb-6">
                            <div class="flex justify-between text-sm text-gray-600"><span>Subtotal</span><span>Rs. {{ number_format($grandTotal ?? 0, 2) }}</span></div>
                            <div class="flex justify-between text-sm text-gray-600"><span>Shipping (<span id="shipping-zone">{{ str_replace('_', ' ', $shippingZone ?? 'region not identified') }}</span>)</span><span id="shipping-fee">Rs. {{ number_format($shippingFee ?? 0, 2) }}</span></div>
                            <p id="shipping-quote-error" class="hidden text-xs text-red-600"></p>
                            <div class="flex justify-between items-center border-t border-gray-200 pt-3"><span class="text-base font-bold text-gray-900">Total Amount</span><span id="checkout-total" class="text-xl font-extrabold text-indigo-600">Rs. {{ number_format(($grandTotal ?? 0) + ($shippingFee ?? 0), 2) }}</span></div>
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
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">
                        Address Title / Type *
                    </label>

                    <select
                        x-model="form.address_type"
                        required
                        class="w-full border-gray-300 rounded-lg p-2.5 border focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Select address type</option>
                        <option value="Home">Home</option>
                        <option value="Office">Office</option>
                    </select>
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
                        <button type="submit" :disabled="savingAddress" :class="{ 'opacity-50 cursor-not-allowed': savingAddress }" class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold text-xs uppercase tracking-wider"><span x-text="savingAddress ? 'Saving...' : 'Save Address'"></span></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
async function refreshShippingQuote() {
    const selected = document.querySelector('input[name="shipping_address_id"]:checked');
    const feeNode = document.getElementById('shipping-fee');
    const zoneNode = document.getElementById('shipping-zone');
    const totalNode = document.getElementById('checkout-total');
    const errorNode = document.getElementById('shipping-quote-error');
    const submit = document.getElementById('submit-order-btn');
    if (!selected) return;
    errorNode.classList.add('hidden');
    feeNode.textContent = 'Calculating...';
    submit.disabled = true;
    try {
        const response = await fetch("{{ route('orders.shipping-quote') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({shipping_address_id: Number(selected.value)})
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Unable to calculate shipping for this address.');
        const fee = Number(data.shipping_fee);
        if (!Number.isFinite(fee) || fee < 0) throw new Error('The server returned an invalid shipping fee.');
        feeNode.textContent = 'Rs. ' + fee.toFixed(2);
        zoneNode.textContent = String(data.zone || '').replaceAll('_', ' ');
        totalNode.textContent = 'Rs. ' + ({{ (float) ($grandTotal ?? 0) }} + fee).toFixed(2);
        submit.disabled = false;
    } catch (error) {
        feeNode.textContent = 'Unavailable';
        zoneNode.textContent = 'not available';
        errorNode.textContent = error.message || 'Unable to calculate shipping.';
        errorNode.classList.remove('hidden');
        submit.disabled = true;
    }
}
document.addEventListener('DOMContentLoaded', refreshShippingQuote);
function checkoutPage() {
    return {
        showAddressModal: false,
        savingAddress: false,
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
            if (this.savingAddress) return;
            this.showAddressModal = false;
            this.form = {
                name: '',
                address_type: '',
                phone: '',
                region: '',
                address: ''
            };
        },

        async saveAddress() {
            if (this.savingAddress) return;
            this.savingAddress = true;

            try {
                const response = await fetch("{{ route('shipping-address.quick-store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (!response.ok || !data.success || !data.address || !data.address.id) {
                    let message = data.message || 'Unable to save shipping address.';
                    if (data.errors) {
                        message = Object.values(data.errors).flat().join('\n');
                    }
                    throw new Error(message);
                }

                const address = data.address;
                const container = document.getElementById('address-list-container');
                if (!container) {
                    throw new Error('Could not find the shipping address list. Please reload checkout.');
                }

                // Do not append a second card if this address is already in the list.
                let radio = Array.from(container.querySelectorAll('input[name="shipping_address_id"]'))
                    .find(input => input.value === String(address.id));

                if (!radio) {
                    const label = document.createElement('label');
                    label.className = 'flex items-start space-x-3 p-4 border rounded-lg cursor-pointer bg-gray-50/50';

                    radio = document.createElement('input');
                    radio.type = 'radio';
                    radio.name = 'shipping_address_id';
                    radio.value = String(address.id);
                    radio.className = 'h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 mt-1';
                    radio.required = true;
                    radio.addEventListener('change', refreshShippingQuote);

                    const details = document.createElement('div');
                    const nameLine = document.createElement('p');
                    nameLine.className = 'font-semibold text-gray-800';
                    nameLine.append(document.createTextNode(address.name || ''));

                    const type = document.createElement('span');
                    type.className = 'text-xs font-normal text-gray-500';
                    type.textContent = ` (${address.address_type || ''})`;
                    nameLine.appendChild(type);

                    const addressLine = document.createElement('p');
                    addressLine.className = 'text-sm text-gray-600';
                    addressLine.textContent = address.address || '';

                    const regionLine = document.createElement('p');
                    regionLine.className = 'text-sm text-gray-600';
                    regionLine.textContent = `${address.region || ''} | Phone: ${address.phone || ''}`;

                    details.append(nameLine, addressLine, regionLine);
                    label.append(radio, details);
                    container.appendChild(label);
                }

                container.classList.remove('hidden');
                const emptyMessage = document.getElementById('no-address-msg');
                if (emptyMessage) emptyMessage.classList.add('hidden');

                // Select it so shipping is quoted for the correct address.
                radio.checked = true;
                this.showAddressModal = false;
                this.form = { name: '', address_type: '', phone: '', region: '', address: '' };

                alert(data.created === false
                    ? 'This shipping address already exists and has been selected.'
                    : (data.message || 'Shipping address has been created successfully.'));

                await refreshShippingQuote();
            } catch (error) {
                console.error('Error saving shipping address:', error);
                alert(error.message || 'An error occurred while saving the shipping address. Please try again.');
            } finally {
                this.savingAddress = false;
            }
        }
    };
}
</script>
@endsection