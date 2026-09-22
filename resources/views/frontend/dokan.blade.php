<!-- resources/views/frontend/dokan.blade.php -->
@extends('frontend.frontend-layout')

@section('title', 'Become a Vendor - Empireinnovation')
<title>Become a Vendor - EmpireInnovation</title>

@section('content')
<section class="py-8 md:py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-[#c9a84c]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-store text-3xl text-[#c9a84c]"></i>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-[#1a2a6c]">Become a Vendor</h1>
                <p class="text-gray-500 mt-2">Start selling your products on Empireinnovation marketplace</p>
            </div>
            
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-6 text-sm">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6 text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('dokan_registration_submit') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">Company Name <span class="text-red-500">*</span></label>
                        <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                               placeholder="Your Business Name" required>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Business Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                               placeholder="business@example.com" required>
                    </div>
                </div>
                
                <div>
                    <label for="reg_no" class="block text-sm font-medium text-gray-700 mb-1">Registration Number <span class="text-red-500">*</span></label>
                    <input type="text" id="reg_no" name="reg_no" value="{{ old('reg_no') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="Company Registration Number" required>
                </div>
                
                <div>
                    <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-1">Contact Number <span class="text-red-500">*</span></label>
                    <input type="text" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="+977-98XXXXXXXX" required>
                </div>
                
                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">Company Logo <span class="text-red-500">*</span></label>
                    <div class="flex items-center space-x-4">
                        <div class="w-24 h-24 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50" id="logoPreview">
                            <i class="fas fa-image text-2xl text-gray-400"></i>
                        </div>
                        <div class="flex-1">
                            <input type="file" id="logo" name="logo" accept="image/*" 
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#c9a84c] file:text-[#1a2a6c] hover:file:bg-[#dbb95c] transition-all" required>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, SVG (Max 2MB)</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-start space-x-2">
                    <input type="checkbox" id="terms" name="terms" value="1" 
                           class="mt-1 rounded text-[#c9a84c] focus:ring-[#c9a84c]" required>
                    <label for="terms" class="text-sm text-gray-600">
                        I agree to the <a href="#" class="text-[#c9a84c] hover:text-[#b8963a] font-medium">Terms and Conditions</a> and <a href="#" class="text-[#c9a84c] hover:text-[#b8963a] font-medium">Vendor Policy</a>.
                        <span class="text-red-500">*</span>
                    </label>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-[#1a2a6c] text-white font-semibold rounded-lg hover:bg-[#2a3a7c] transition-all">
                        <i class="fas fa-paper-plane mr-2"></i> Submit Application
                    </button>
                    <a href="{{ route('home') }}" class="flex-1 px-6 py-3 border border-gray-300 text-gray-600 font-semibold rounded-lg hover:bg-gray-50 transition-all text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    document.getElementById('logo')?.addEventListener('change', function(e) {
        const preview = document.getElementById('logoPreview');
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover rounded-lg">`;
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection