<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>Create Account · TicketHub</title>
    <script>
        try {
            const t = localStorage.getItem('tickethub-theme');
            document.documentElement.dataset.thTheme = t === 'light' || t === 'dark' ? t : matchMedia(
                '(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
        } catch {
            document.documentElement.dataset.thTheme = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
        }
    </script>
    @vite(['resources/css/tickethub-register.css', 'resources/js/tickethub-login.js', 'resources/js/tickethub-register.js'])
</head>

<body class="th-login">
    <div class="th-shell">
        <section class="th-form-section" aria-labelledby="welcome-title">
            <header class="th-header"><a class="th-brand" href="{{ url('/') }}" aria-label="TicketHub home"><svg
                        viewBox="0 0 40 40" fill="none" aria-hidden="true">
                        <rect x="2" y="2" width="36" height="36" rx="11" fill="#2563EB" />
                        <path d="M11 13h18v5a3 3 0 0 0 0 6v4H11v-4a3 3 0 0 0 0-6v-5Z" stroke="white"
                            stroke-width="1.8" />
                        <path d="M22 15v3m0 3v3m0 2v1" stroke="#BFDBFE" stroke-width="1.5" />
                    </svg><span>TicketHub</span></a></header>
            <main class="th-form-wrap">
                <div class="th-form-card">
                    <div class="th-eyebrow"><span></span> YOUR SUPPORT WORKSPACE</div>
                    <h1 id="welcome-title">Create your account</h1>
                    <p class="th-subtitle">Join TicketHub and manage your support tickets efficiently.</p>
                    <form method="POST" action="{{ route('register.post') }}" data-register-form>
                        @csrf
                        <div class="th-field"><label for="name">Full Name</label><input id="name"
                                name="name" type="text" autocomplete="name" value="{{ old('name') }}"
                                placeholder="Enter your full name" required autofocus aria-describedby="name-error"
                                @error('name') aria-invalid="true" @enderror>
                            <p id="name-error" class="th-error" data-error="name" aria-live="polite">
                                @error('name')
                                    {{ $message }}
                                @enderror
                            </p>
                        </div>
                        <div class="th-field"><label for="email">Email Address</label><input id="email"
                                name="email" type="email" autocomplete="email" inputmode="email"
                                value="{{ old('email') }}" placeholder="you@company.com" required
                                aria-describedby="email-error" @error('email') aria-invalid="true" @enderror>
                            <p id="email-error" class="th-error" data-error="email" aria-live="polite">
                                @error('email')
                                    {{ $message }}
                                @enderror
                            </p>
                        </div>
                        <div class="th-field"><label for="password">Password</label>
                            <div class="th-password-wrap"><input id="password" name="password" type="password"
                                    autocomplete="new-password" placeholder="Enter your password" required
                                    aria-describedby="password-error"
                                    @error('password') aria-invalid="true" @enderror><button class="th-password-toggle"
                                    type="button" data-password-toggle aria-controls="password"
                                    aria-label="Show password" aria-pressed="false"><svg viewBox="0 0 24 24"
                                        fill="none" aria-hidden="true">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" stroke="currentColor"
                                            stroke-width="1.5" />
                                        <circle cx="12" cy="12" r="3" stroke="currentColor"
                                            stroke-width="1.5" />
                                        <path data-eye-slash d="m4 4 16 16" stroke="currentColor" stroke-width="1.5"
                                            hidden />
                                    </svg></button></div>
                            <p id="password-error" class="th-error" data-error="password" aria-live="polite">
                                @error('password')
                                    {{ $message }}
                                @enderror
                            </p>
                        </div>
                        <div class="th-field"><label for="password_confirmation">Confirm Password</label>
                            <div class="th-password-wrap"><input id="password_confirmation" name="password_confirmation"
                                    type="password" autocomplete="new-password" placeholder="Confirm your password"
                                    required aria-describedby="password_confirmation-error"
                                    @error('password_confirmation') aria-invalid="true" @enderror><button
                                    class="th-password-toggle" type="button" data-confirm-toggle
                                    aria-controls="password_confirmation" aria-label="Show confirm password"
                                    aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                                            stroke="currentColor" stroke-width="1.5" />
                                        <circle cx="12" cy="12" r="3" stroke="currentColor"
                                            stroke-width="1.5" />
                                        <path data-eye-slash d="m4 4 16 16" stroke="currentColor" stroke-width="1.5"
                                            hidden />
                                    </svg></button></div>
                            <p id="password_confirmation-error" class="th-error" data-error="password_confirmation"
                                aria-live="polite">
                                @error('password_confirmation')
                                    {{ $message }}
                                @enderror
                            </p>
                        </div>
                        <button class="th-submit" type="submit">Create Account <span
                                aria-hidden="true">→</span></button>
                    </form>
                    <p class="th-signin">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
                    <p class="th-secure"><svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <rect x="5" y="9" width="10" height="8" rx="2" stroke="currentColor" />
                            <path d="M7 9V6a3 3 0 0 1 6 0v3" stroke="currentColor" />
                        </svg> A secure space for better support.</p>
                </div>
            </main>
            <footer class="th-footer">© {{ date('Y') }} TicketHub. All rights reserved.</footer>
        </section>
        <aside class="th-visual" aria-label="Organized support, connected conversations"> <button type="button"
                class="th-theme ml-auto" data-theme-toggle aria-label="Switch to dark theme"
                aria-pressed="false"><span data-theme-icon aria-hidden="true">☾</span></button>
            <div class="th-visual-top"><span class="th-label"><span></span> BUILT FOR BETTER SUPPORT</span><span
                    class="th-mini-mark" aria-hidden="true">✦</span></div><x-ticket-ecosystem />
            <div class="th-visual-copy">
                <p class="th-eyebrow">LESS FRICTION. MORE CONNECTION.</p>
                <h2>Every ticket.<br>A better experience.</h2>
                <p>Bring clarity to every conversation.<br>Keep your team connected and your customers heard.</p>
            </div>
            <div class="th-visual-bottom"><span><i></i> Connected. Organized. Resolved.</span><span
                    aria-hidden="true">01 / 01</span></div>
        </aside>
    </div>
</body>

</html>
