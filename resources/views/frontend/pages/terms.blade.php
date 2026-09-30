@extends('frontend.frontend-layout')

@section('title', 'Terms & Conditions - EmpireInnovation')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-12">

    <h1 class="text-3xl font-bold text-gray-900 mb-3">
        Terms & Conditions
    </h1>

    <p class="text-gray-500 mb-8">
        Last updated: {{ now()->format('F Y') }}
    </p>

    <div class="space-y-8 text-gray-700 leading-7">

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                1. Acceptance of Terms
            </h2>
            <p>
                By accessing or using EmpireInnovation, you agree to comply
                with these Terms & Conditions. If you do not agree with
                these terms, you can try any other related websites.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                2. Products and Services
            </h2>
            <p>
                We provide medical, surgical and healthcare-related products.
                Product descriptions, prices, availability and other
                information may be updated from time to time.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                3. Orders
            </h2>
            <p>
                When you place an order, you are responsible for providing
                accurate information including your contact, shipping and
                payment details.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                4. Payments
            </h2>
            <p>
                We may support payment methods such as Cash on Delivery,
                bank transfer and eSewa. Orders requiring online payment
                remain pending until the payment is successfully verified.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                5. Shipping and Delivery
            </h2>
            <p>
                Delivery times may vary depending on product availability,
                shipping location and other circumstances. Customers should
                provide accurate delivery information.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                6. Cancellation and Returns
            </h2>
            <p>
                Orders may be cancelled or returned according to the
                applicable policies displayed on our website and the
                condition of the purchased product.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                7. Account Responsibility
            </h2>
            <p>
                You are responsible for maintaining the confidentiality of
                your account credentials and for activities performed
                through your account.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                8. Prohibited Use
            </h2>
            <p>
                You must not use this website for unlawful activities,
                fraudulent transactions, unauthorized access, abuse of
                services or activities that may harm the website or other
                users.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                9. Changes to These Terms
            </h2>
            <p>
                We may update these Terms & Conditions when necessary.
                Updated terms will be published on this page.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                10. Contact
            </h2>
            <p>
                If you have questions regarding these terms, please contact
                EmpireInnovation through the contact information provided
                on our website.
            </p>
        </section>

    </div>
</div>
@endsection
