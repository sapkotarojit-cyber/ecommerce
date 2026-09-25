@extends('frontend.frontend-layout')

@section('title', 'Become a Vendor - Empireinnovation')

@section('content')
<section class="py-8 md:py-12">
<div class="max-w-4xl mx-auto px-4">
<div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">

<div class="text-center mb-8">
    <div class="w-16 h-16 bg-[#c9a84c]/10 rounded-full flex items-center justify-center mx-auto mb-3">
        <i class="fas fa-store text-2xl text-[#c9a84c]"></i>
    </div>
    <h1 class="text-2xl font-bold text-[#1a2a6c]">Become a Vendor</h1>
    <p class="text-gray-500 mt-1">Start selling on Empireinnovation marketplace</p>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-600 p-3 rounded-lg mb-5 text-sm">
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-600 p-3 rounded-lg mb-5 text-sm">
    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="bg-red-50 border border-red-200 text-red-600 p-3 rounded-lg mb-5 text-sm">
    <ul class="list-disc list-inside">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('dokan_registration_submit') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
@csrf

{{-- BASIC INFORMATION --}}
<div>
<h2 class="section-title"><i class="fas fa-building"></i> Basic Information</h2>

<div class="grid md:grid-cols-2 gap-4">

<div>
<label>Company Name <b>*</b></label>
<input name="company_name" value="{{ old('company_name') }}" placeholder="Business Name" required>
</div>

<div>
<label>Contact Person <b>*</b></label>
<input name="name" value="{{ old('name') }}" placeholder="Full Name" required>
</div>

<div>
<label>Business Email <b>*</b></label>
<input type="email" name="email" value="{{ old('email') }}" placeholder="business@example.com" required>
</div>

<div>
<label>Registration Number <b>*</b></label>
<input name="reg_no" value="{{ old('reg_no') }}" placeholder="Company Registration Number" required>
</div>

<div>
<label>Contact Number <b>*</b></label>
<input name="contact_number" value="{{ old('contact_number') }}" placeholder="+977-98XXXXXXXX" required>
</div>

</div>
</div>

{{-- BUSINESS ADDRESS --}}
<div>
<h2 class="section-title"><i class="fas fa-location-dot"></i> Business Address</h2>

<div class="grid md:grid-cols-2 gap-4">

<div>
<label>Business Location <b>*</b></label>
<input name="business_location" value="{{ old('business_location') }}" placeholder="City / District / Municipality" required>
</div>

<div>
<label>Business Address <b>*</b></label>
<input name="business_address" value="{{ old('business_address') }}" placeholder="Complete Business Address" required>
</div>

</div>
</div>

{{-- BUSINESS INFORMATION --}}
<div>
<h2 class="section-title"><i class="fas fa-file-lines"></i> Business Information</h2>

<div class="grid md:grid-cols-2 gap-4">

<div>
<label>Business Registration No. <b>*</b></label>
<input name="business_reg_no" value="{{ old('business_reg_no') }}" placeholder="Business Registration Number" required>
</div>

<div>
<label>PAN Number <b>*</b></label>
<input name="pan_no" value="{{ old('pan_no') }}" placeholder="Permanent Account Number" required>
</div>

<div class="md:col-span-2">
<label>Business Information Document <b>*</b></label>
<input type="file" name="business_document" accept=".pdf,.jpg,.jpeg,.png" required class="file-input">
<p class="hint">PDF, JPG, JPEG or PNG — Maximum 5MB</p>
</div>

</div>
</div>

{{-- BANK INFORMATION --}}
<div>
<h2 class="section-title"><i class="fas fa-building-columns"></i> Bank Information</h2>

<div class="grid md:grid-cols-2 gap-4">

<div>
<label>Bank Name <b>*</b></label>
<input name="bank_name" value="{{ old('bank_name') }}" placeholder="Bank Name" required>
</div>

<div>
<label>Account Holder Name <b>*</b></label>
<input name="bank_account_name" value="{{ old('bank_account_name') }}" placeholder="Account Holder Name" required>
</div>

<div>
<label>Account Number <b>*</b></label>
<input name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="Account Number" required>
</div>

<div>
<label>Bank Branch <b>*</b></label>
<input name="bank_branch" value="{{ old('bank_branch') }}" placeholder="Branch Name" required>
</div>

<div class="md:col-span-2">
<label>Bank Information Document <b>*</b></label>
<input type="file" name="bank_document" accept=".pdf,.jpg,.jpeg,.png" required class="file-input">
<p class="hint">PDF, JPG, JPEG or PNG — Maximum 5MB</p>
</div>

</div>
</div>

{{-- LOGO --}}
<div>
<h2 class="section-title"><i class="fas fa-image"></i> Company Logo</h2>

<div class="flex items-center gap-4">
<div id="logoPreview" class="w-20 h-20 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center overflow-hidden shrink-0">
<i class="fas fa-image text-xl text-gray-400"></i>
</div>

<div class="flex-1">
<input type="file" id="logo" name="logo" accept=".jpg,.jpeg,.png,.webp" required class="file-input">
<p class="hint">JPG, JPEG, PNG or WEBP — Maximum 2MB</p>
</div>
</div>
</div>

{{-- TERMS --}}
<div class="flex items-start gap-2">
<input type="checkbox" name="terms" value="1" required class="mt-1">
<label class="!mb-0 text-sm text-gray-600">
    I agree to the Terms and Conditions and Vendor Policy. <b>*</b>
</label>
</div>

{{-- BUTTONS --}}
<div class="flex flex-col sm:flex-row gap-3">
<button type="submit" class="flex-1 px-6 py-3 bg-[#1a2a6c] text-white font-semibold rounded-lg hover:bg-[#2a3a7c]">
    <i class="fas fa-paper-plane mr-2"></i> Submit Application
</button>

<a href="{{ route('home') }}" class="flex-1 px-6 py-3 border border-gray-300 text-gray-600 font-semibold rounded-lg hover:bg-gray-50 text-center">
    Cancel
</a>
</div>

</form>
</div>
</div>
</section>

<style>
.section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a2a6c;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: .6rem;
    margin-bottom: 1rem;
}
.section-title i {
    color: #c9a84c;
    margin-right: .5rem;
}
form label {
    display: block;
    font-size: .875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: .3rem;
}
form label b {
    color: #ef4444;
}
form input:not([type="checkbox"]):not([type="file"]) {
    width: 100%;
    padding: .55rem .9rem;
    border: 1px solid #d1d5db;
    border-radius: .5rem;
    outline: none;
}
form input:focus {
    border-color: #c9a84c;
    box-shadow: 0 0 0 2px rgba(201,168,76,.15);
}
.file-input {
    width: 100%;
    font-size: .875rem;
    color: #6b7280;
}
.file-input::file-selector-button {
    margin-right: 1rem;
    padding: .5rem 1rem;
    border: 0;
    border-radius: .5rem;
    background: #c9a84c;
    color: #1a2a6c;
    font-weight: 600;
}
.hint {
    font-size: .75rem;
    color: #9ca3af;
    margin-top: .25rem;
}
</style>

<script>
document.getElementById('logo')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('logoPreview').innerHTML =
            `<img src="${e.target.result}" class="w-full h-full object-cover">`;
    };
    reader.readAsDataURL(file);
});
</script>

@endsection