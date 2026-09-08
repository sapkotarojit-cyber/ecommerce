<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Empire Innovation - Multi-Seller Marketplace</title>
    <meta name="description" content="Shop medical and surgical supplies from thousands of trusted sellers on Empire Innovation">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Custom CSS Variables */
        :root {
            --color-primary: #0F1A3A;
            --color-primary-dark: #0A122A;
            --color-secondary: #C9A84C;
            --color-secondary-dark: #B08E35;
            --color-accent: #10B981;
            --color-dark: #1F2937;
            --color-light: #F9FAFB;
        }

        /* Custom Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        /* Custom Utility Classes */
        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-slideInLeft {
            animation: slideInLeft 0.6s ease-out;
        }

        .animate-slideInRight {
            animation: slideInRight 0.6s ease-out;
        }

        .animate-pulse-slow {
            animation: pulse 2s ease-in-out infinite;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
        }

        .btn-primary {
            transition: all 0.3s ease;
            background-color: var(--color-primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(15, 26, 58, 0.3);
            background-color: var(--color-primary-dark);
        }

        /* Gradient Backgrounds & Text */
        .gradient-bg {
            background: linear-gradient(135deg, #0f1a3a 0%, #1e293b 100%);
        }

        .gradient-text {
            background: linear-gradient(135deg, #0f1a3a 0%, #c9a84c 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        /* Seller Badge */
        .seller-badge {
            position: relative;
            overflow: hidden;
        }

        .seller-badge::before {
            content: 'Verified Seller';
            position: absolute;
            top: 10px;
            right: -35px;
            background: var(--color-accent);
            color: white;
            padding: 2px 35px;
            transform: rotate(45deg);
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Status Badges */
        .status-pending {
            background: #FEF3C7;
            color: #92400E;
        }

        .status-approved {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-rejected {
            background: #FEE2E2;
            color: #991B1B;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--color-primary);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--color-primary-dark);
        }

        /* Skeleton Loading */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        @media (max-width: 768px) {
            .mobile-stack {
                flex-direction: column;
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased min-h-screen flex flex-col justify-between">

    <x-frontend-header/>

    <main class="mb-auto">
        {{ $slot }}
    </main>

    <x-frontend-footer/>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Add to cart animation
            const addToCartButtons = document.querySelectorAll('.add-to-cart');
            addToCartButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const originalText = this.innerHTML;
                    this.innerHTML = '✓ Added!';
                    this.classList.add('bg-green-600');
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.classList.remove('bg-green-600');
                    }, 2000);
                });
            });

            // Seller registration modal trigger
            const applyButtons = document.querySelectorAll('.apply-seller');
            applyButtons.forEach(button => {
                button.addEventListener('click', function() {
                    window.location.href = "{{ route('dokan_registration') }}";
                });
            });
        });
    </script>
</body>
</html>