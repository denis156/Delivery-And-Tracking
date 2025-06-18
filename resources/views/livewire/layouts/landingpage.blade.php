<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ config('landingpage.theme.default') }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('landingpage.company_name') . ' - ' . config('landingpage.company_tagline') }}</title>

    <!-- SEO Meta Tags -->
    <meta name="description"
        content="{{ $description ?? config('landingpage.company_description') . ' - ' . config('landingpage.company_tagline') }}">
    <meta name="keywords" content="{{ config('landingpage.seo.keywords') }}">
    <meta name="author" content="{{ config('landingpage.seo.author') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $title ?? config('landingpage.company_name') }}">
    <meta property="og:description" content="{{ $description ?? config('landingpage.company_description') }}">
    <meta property="og:image" content="{{ asset(config('landingpage.seo.og_image')) }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $title ?? config('landingpage.company_name') }}">
    <meta property="twitter:description" content="{{ $description ?? config('landingpage.company_description') }}">
    <meta property="twitter:image" content="{{ asset(config('landingpage.seo.og_image')) }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Styles -->
    @vite(['resources/js/app.js'])
    @livewireStyles

    <!-- Smart Scroll Animation Styles with Auto Out Effects -->
    <style>

    </style>

    <!-- Additional Head Content -->
    {{ $head ?? '' }}
</head>

<body class="font-sans antialiased min-h-dvh bg-base-100 text-base-content" x-data="scrollAnimations()"
    x-init="initScrollAnimations()">

    <!-- Navigation -->
    @include('livewire.landing-page.partials.navbar')

    <!-- Main Content -->
    <main class="min-h-dvh">
        {{ $slot }}
    </main>

    <!-- Footer -->
    @include('livewire.landing-page.partials.footer')

    <!-- Floating Action Button -->
    <livewire:landing-page.components.floating-action-button />

    <!-- Scripts -->
    @livewireScripts

    <!-- Smart Scroll Animation Script with Auto Out Detection -->
    <script>
        function scrollAnimations() {
            return {
                observedElements: new Map(),

                initScrollAnimations() {
                    // Enhanced Intersection Observer untuk detect masuk/keluar viewport
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            const element = entry.target;

                            if (entry.isIntersecting) {
                                // Element masuk viewport - Trigger IN animation
                                this.animateIn(element);
                            } else {
                                // Element keluar viewport - Trigger OUT animation
                                this.animateOut(element);
                            }
                        });
                    }, {
                        threshold: 0.1, // Trigger ketika 10% element visible
                        rootMargin: '0px 0px -50px 0px' // Trigger sedikit lebih awal
                    });

                    // Observe semua elements dengan scroll-animate
                    this.$nextTick(() => {
                        document.querySelectorAll('.scroll-animate').forEach(el => {
                            observer.observe(el);

                            // Initialize element state
                            this.observedElements.set(el, {
                                hasAnimated: false,
                                isVisible: false
                            });
                        });
                    });

                    // Handle elements yang sudah visible saat page load
                    setTimeout(() => {
                        document.querySelectorAll('.scroll-animate').forEach(el => {
                            const rect = el.getBoundingClientRect();
                            const isVisible = rect.top < window.innerHeight && rect.bottom > 0;

                            if (isVisible) {
                                this.animateIn(el);
                            }
                        });
                    }, 100);

                    // Navbar scroll behavior
                    this.initNavbarBehavior();

                    // Smooth scrolling for anchor links
                    this.initSmoothScrolling();
                },

                animateIn(element) {
                    // Remove any existing animation classes
                    element.classList.remove('animate-out');

                    // Add animate-in class
                    element.classList.add('animate-in');

                    // Update element state
                    const elementData = this.observedElements.get(element) || {};
                    elementData.hasAnimated = true;
                    elementData.isVisible = true;
                    this.observedElements.set(element, elementData);

                    // Add animation-complete class setelah animasi selesai
                    setTimeout(() => {
                        if (element.classList.contains('animate-in')) {
                            element.classList.add('animation-complete');
                        }
                    }, 700); // 600ms animation + 100ms buffer
                },

                animateOut(element) {
                    // Remove animate-in classes
                    element.classList.remove('animate-in', 'animation-complete');

                    // Add animate-out class - CSS sudah handle auto opposite animation
                    element.classList.add('animate-out');

                    // Update element state
                    const elementData = this.observedElements.get(element) || {};
                    elementData.isVisible = false;
                    this.observedElements.set(element, elementData);

                    // Debug log untuk testing
                    console.log('Animating out:', element.className);
                },

                initNavbarBehavior() {
                    let lastScrollTop = 0;
                    const navbar = document.querySelector('.navbar');

                    window.addEventListener('scroll', () => {
                        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                        if (scrollTop > lastScrollTop && scrollTop > 100) {
                            navbar.style.transform = 'translateY(-100%)';
                        } else {
                            navbar.style.transform = 'translateY(0)';
                        }

                        lastScrollTop = scrollTop;
                    });
                },

                initSmoothScrolling() {
                    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                        anchor.addEventListener('click', function(e) {
                            e.preventDefault();
                            const target = document.querySelector(this.getAttribute('href'));
                            if (target) {
                                target.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'start'
                                });
                            }
                        });
                    });
                },

                // Debug utility - bisa dihapus nanti
                debugAnimations() {
                    console.log('Current animated elements:', this.observedElements);
                },

                // Manual trigger utilities
                triggerAnimation(selector, type = 'in') {
                    const elements = document.querySelectorAll(selector);
                    elements.forEach(el => {
                        if (type === 'in') {
                            this.animateIn(el);
                        } else if (type === 'out') {
                            this.animateOut(el);
                        } else if (type === 'reset') {
                            el.classList.remove('animate-in', 'animate-out', 'animation-complete');
                            const elementData = this.observedElements.get(el) || {};
                            elementData.hasAnimated = false;
                            elementData.isVisible = false;
                            this.observedElements.set(el, elementData);
                        }
                    });
                }
            }
        }

        // Debug function untuk testing - bisa dijalankan di console
        function testScrollAnimations() {
            console.log('=== Testing Scroll Animations ===');

            // Test animate out semua elements
            document.querySelectorAll('.scroll-animate').forEach((el, index) => {
                setTimeout(() => {
                    console.log(`Testing element ${index}:`, el.className);
                    el.classList.remove('animate-in', 'animation-complete');
                    el.classList.add('animate-out');
                }, index * 200);
            });

            // Reset setelah 3 detik
            setTimeout(() => {
                document.querySelectorAll('.scroll-animate').forEach(el => {
                    el.classList.remove('animate-out');
                    el.classList.add('animate-in', 'animation-complete');
                });
                console.log('Reset complete');
            }, 3000);
        }
    </script>

    <!-- Additional Scripts -->
    {{ $scripts ?? '' }}

    <!-- Analytics -->
    @if (config('services.analytics.google_tag_id'))
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.analytics.google_tag_id') }}">
        </script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', '{{ config('services.analytics.google_tag_id') }}');
        </script>
    @endif
</body>

</html>
