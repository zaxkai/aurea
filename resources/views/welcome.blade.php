@php
    $splashDestination = auth()->check()
        ? (auth()->user()->onboarding_completed_at ? route('dashboard') : route('profile-setup'))
        : route('register');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#ffffff">
        <link rel="icon" type="image/png" href="{{ asset('images/logo_aurea.png') }}">
        <title>Aurea</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root {
                color-scheme: light;
                font-family: 'Outfit', sans-serif;
                color: #000f2e;
                background: #ffffff;
            }

            *,
            *::before,
            *::after {
                box-sizing: border-box;
            }

            html,
            body {
                width: 100%;
                min-width: 320px;
                min-height: 100%;
                margin: 0;
                overflow: hidden;
            }

            .splash-stage {
                position: relative;
                display: grid;
                width: 100%;
                min-height: 100dvh;
                overflow: hidden;
                background: #ffffff;
            }

            .splash-screen {
                position: absolute;
                inset: 0;
                display: grid;
                place-items: center;
                background: #ffffff;
                transition: opacity 420ms ease, transform 680ms cubic-bezier(.2,.75,.2,1);
            }

            .splash-screen[hidden] {
                display: none;
            }

            .splash-trigger {
                position: absolute;
                inset: 0;
                display: grid;
                place-items: center;
                width: 100%;
                height: 100%;
                padding: 24px;
                border: 0;
                background: transparent;
                color: inherit;
                cursor: pointer;
            }

            .splash-lockup {
                display: flex;
                align-items: center;
                gap: 8px;
                transform: translateY(12px) scale(.96);
                opacity: 0;
                animation: lockup-arrive 900ms 120ms cubic-bezier(.2,.75,.2,1) forwards;
            }

            .splash-lockup img {
                display: block;
                width: 40px;
                height: 44px;
                object-fit: contain;
            }

            .splash-wordmark {
                margin: 0;
                color: #090b11;
                font-size: 36px;
                font-weight: 600;
                line-height: 1;
            }

            .splash-action {
                position: absolute;
                bottom: max(32px, 7vh);
                left: 50%;
                color: #778199;
                font-size: 13px;
                font-weight: 500;
                opacity: 0;
                transform: translate(-50%, 8px);
                animation: action-arrive 480ms 800ms ease forwards;
            }

            .splash-trigger:focus-visible {
                outline: 3px solid #2ee0e0;
                outline-offset: -8px;
            }

            .splash-screen.is-exiting {
                pointer-events: none;
                opacity: 0;
                transform: scale(1.025);
            }

            .sponsor-screen {
                opacity: 0;
                transform: scale(.975);
            }

            .sponsor-screen.is-visible {
                opacity: 1;
                transform: scale(1);
            }

            .sponsor-content {
                width: min(100% - 40px, 1080px);
                text-align: center;
            }

            .sponsor-heading {
                margin: 0 0 34px;
                color: #10131a;
                font-size: 16px;
                font-weight: 500;
                opacity: 0;
                transform: translateY(10px);
                animation: sponsor-arrive 460ms 160ms ease forwards;
            }

            .sponsor-logos {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: center;
                gap: 24px 34px;
            }

            .sponsor-logo {
                display: grid;
                place-items: center;
                width: 150px;
                height: 74px;
                opacity: 0;
                transform: translateY(18px) scale(.96);
                animation: sponsor-arrive 520ms var(--stagger) cubic-bezier(.2,.75,.2,1) forwards;
            }

            .sponsor-logo img {
                display: block;
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
            }

            @keyframes lockup-arrive {
                to { opacity: 1; transform: translateY(0) scale(1); }
            }

            @keyframes action-arrive {
                to { opacity: 1; transform: translate(-50%, 0); }
            }

            @keyframes sponsor-arrive {
                to { opacity: 1; transform: translateY(0) scale(1); }
            }

            @media (max-width: 640px) {
                .splash-lockup img { width: 36px; height: 40px; }
                .splash-wordmark { font-size: 32px; }
                .sponsor-heading { margin-bottom: 24px; }
                .sponsor-logos { gap: 14px 18px; }
                .sponsor-logo { width: min(38vw, 138px); height: 62px; }
            }

            @media (prefers-reduced-motion: reduce) {
                *, *::before, *::after {
                    scroll-behavior: auto !important;
                    animation-duration: 1ms !important;
                    animation-delay: 0ms !important;
                    transition-duration: 1ms !important;
                }
            }
        </style>
    </head>
    <body>
        <main class="splash-stage" data-destination="{{ $splashDestination }}">
            <section id="logo-screen" class="splash-screen" aria-label="Aurea">
                <button id="splash-start" type="button" class="splash-trigger" aria-label="Mulai pengalaman Aurea">
                    <span class="splash-lockup" aria-hidden="true">
                        <img src="{{ asset('images/logo_aurea.png') }}" alt="">
                        <span class="splash-wordmark">aurea</span>
                    </span>
                    <span class="splash-action" aria-hidden="true">Mulai</span>
                </button>
            </section>

            <section id="sponsor-screen" class="splash-screen sponsor-screen" aria-label="Supported by" aria-hidden="true" hidden>
                <div class="sponsor-content">
                    <h1 class="sponsor-heading">Supported by</h1>
                    <div class="sponsor-logos">
                        <div class="sponsor-logo" style="--stagger: 280ms"><img src="{{ asset('images/splash/1. LOGO JHIC 2.0 1.png') }}" alt="JHIC Innovation Competition"></div>
                        <div class="sponsor-logo" style="--stagger: 380ms"><img src="{{ asset('images/splash/2. Logo Jagoan Hosting 1.png') }}" alt="Jagoan Hosting"></div>
                        <div class="sponsor-logo" style="--stagger: 480ms"><img src="{{ asset('images/splash/3. KOMDIGI 1.png') }}" alt="Komdigi"></div>
                        <div class="sponsor-logo" style="--stagger: 580ms"><img src="{{ asset('images/splash/4. Garuda Spark Full Color 1.png') }}" alt="Garuda Spark"></div>
                        <div class="sponsor-logo" style="--stagger: 680ms"><img src="{{ asset('images/splash/5. LOGO NGALUP 1.png') }}" alt="Ngalup"></div>
                    </div>
                </div>
            </section>
        </main>

        <script>
            const splashStart = document.getElementById('splash-start');
            const logoScreen = document.getElementById('logo-screen');
            const sponsorScreen = document.getElementById('sponsor-screen');
            const splashDestination = document.querySelector('.splash-stage').dataset.destination;
            let splashStarted = false;

            splashStart.addEventListener('click', () => {
                if (splashStarted) {
                    return;
                }

                splashStarted = true;
                logoScreen.classList.add('is-exiting');
                logoScreen.setAttribute('aria-hidden', 'true');
                splashStart.disabled = true;
                sponsorScreen.hidden = false;
                sponsorScreen.setAttribute('aria-hidden', 'false');
                document.querySelector('.sponsor-heading').focus({ preventScroll: true });

                requestAnimationFrame(() => {
                    sponsorScreen.classList.add('is-visible');
                });

                window.setTimeout(() => {
                    window.location.assign(splashDestination);
                }, 3000);
            });
        </script>
    </body>
</html>
