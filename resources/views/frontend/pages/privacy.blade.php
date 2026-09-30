@extends('frontend.frontend-layout')

@section('title', 'Privacy Policy - EmpireInnovation')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-12">

    <h1 class="text-3xl font-bold text-gray-900 mb-3">
        Privacy Policy
    </h1>

    <p class="text-gray-500 mb-8">
        Last updated: {{ now()->format('F Y') }}
    </p>

    <div class="space-y-8 text-gray-700 leading-7">

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                1. Introduction
            </h2>
            <p>
                EmpireInnovation respects your privacy and is committed to
                protecting the personal information you provide when using
                our website and services.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                2. Information We Collect
            </h2>
            <p>
                We may collect information such as your name, email address,
                phone number, shipping address, account information,
                order information and payment-related information required
                to process your orders.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                3. How We Use Your Information
            </h2>
            <p>
                Your information may be used to process orders, provide
                customer support, deliver products, verify payments,
                maintain accounts, improve our services and communicate
                important information about your orders.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                4. Payment Information
            </h2>
            <p>
                Payments may be processed through third-party payment
                providers. We do not intend to store sensitive payment
                credentials such as payment-provider passwords.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                5. Cookies and Sessions
            </h2>
            <p>
                Our website may use cookies and session technologies to
                maintain authentication, shopping-cart functionality,
                preferences and other essential website features.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                6. Data Security
            </h2>
            <p>
                We take reasonable technical and organizational measures
                to protect personal information from unauthorized access,
                alteration, disclosure or destruction.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                7. Third-Party Services
            </h2>
            <p>
                Our website may integrate with third-party services such
                as payment providers, authentication providers and
                delivery or communication services. Their handling of
                information may also be governed by their own privacy
                policies.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                8. Data Retention
            </h2>
            <p>
                We retain information for as long as reasonably necessary
                for providing services, processing orders, maintaining
                records, resolving disputes and meeting applicable
                obligations.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                9. Your Rights
            </h2>
            <p>
                Depending on applicable law, you may have rights regarding
                access, correction or deletion of certain personal
                information. Contact us if you wish to make a privacy-related
                request.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                10. Contact
            </h2>
            <p>
                For privacy-related questions or requests, please contact
                EmpireInnovation using the contact information provided
                on our website.
            </p>
        </section>

    </div>
</div>
@endsection
