<!-- resources/views/frontend/shipping-address/index.blade.php -->
@extends('frontend.frontend-layout')

@section('title', 'Shipping Addresses - Empireinnovation')

@section('content')
<section class="py-8 md:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[#1a2a6c]">Shipping Addresses</h1>
                <p class="text-gray-500 text-sm">Manage your shipping addresses</p>
            </div>
            <a href="{{ route('shipping-address.create') }}" class="inline-flex items-center px-4 py-2 bg-[#c9a84c] text-[#1a2a6c] font-semibold rounded-lg hover:bg-[#dbb95c] transition-all">
                <i class="fas fa-plus mr-2"></i> Add New Address
            </a>
        </div>
        
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-4 text-sm">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4 text-sm">
                <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            </div>
        @endif
        
        @if($addresses->count() > 0)
            <div class="grid gap-4">
                @foreach($addresses as $address)
                    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-all">
                        <div class="flex flex-col sm:flex-row justify-between items-start gap-3">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <h3 class="font-semibold text-[#1a2a6c]">{{ $address->title }}</h3>
                                    @if($address->is_default)
                                        <span class="bg-[#c9a84c] text-[#1a2a6c] text-xs font-bold px-3 py-1 rounded-full">Default</span>
                                    @endif
                                </div>
                                <p class="text-gray-600 text-sm mt-1">{{ $address->full_address }}</p>
                                <p class="text-gray-500 text-sm mt-1"><i class="fas fa-phone mr-2"></i> {{ $address->contact_no }}</p>
                            </div>
                            <div class="flex items-center space-x-2 flex-shrink-0">
                                @if(!$address->is_default)
                                    <form action="{{ route('shipping-address.set-default', $address) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-sm text-[#c9a84c] hover:text-[#b8963a] font-medium">Set Default</button>
                                    </form>
                                @endif
                                <a href="{{ route('shipping-address.edit', $address) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('shipping-address.destroy', $address) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this address?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-6">
                {{ $addresses->links() }}
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <i class="fas fa-map-pin text-5xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-semibold text-[#1a2a6c]">No Addresses Yet</h3>
                <p class="text-gray-500 text-sm mt-1">Add your first shipping address to start ordering</p>
                <a href="{{ route('shipping-address.create') }}" class="inline-flex items-center px-4 py-2 bg-[#c9a84c] text-[#1a2a6c] font-semibold rounded-lg hover:bg-[#dbb95c] transition-all mt-4">
                    <i class="fas fa-plus mr-2"></i> Add Address
                </a>
            </div>
        @endif
    </div>
</section>
@endsection