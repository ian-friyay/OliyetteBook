@php
    $user = auth()->user();
    $path = trim(request()->path(), '/');
    $isAdminArea = str_starts_with($path, 'admin');
    $minimalHeader = $minimalHeader ?? false;
    $pageTitle = $pageTitle ?? config('app.name');
    $bodyClass = $bodyClass ?? '';
    $flashes = get_flashes();
    $role = $user->role ?? null;

    $fullNav = [
        ['label' => 'Home', 'href' => '/', 'key' => 'home', 'roles' => ['guest', 'owner']],
        ['label' => 'Profiles', 'href' => '/profile', 'key' => 'profile', 'roles' => ['guest', 'owner']],
        ['label' => 'Booking', 'href' => '/booking', 'key' => 'booking', 'roles' => ['guest']],
        ['label' => 'User Reviews', 'href' => '/reviews', 'key' => 'reviews', 'roles' => ['guest', 'owner']],
    ];

    $currentKey = $isAdminArea ? 'admin' : ($path === '' ? 'home' : $path);

    $visibleNav = array_values(array_filter(
        $fullNav,
        static fn (array $item): bool => in_array($role, $item['roles'], true)
    ));

@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1C3F60">
    <title>{{ $pageTitle }} · {{ config('app.name') }}</title>
    <link rel="icon" href="/assets/img/logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/assets/css/styles.css?v={{ filemtime(public_path('assets/css/styles.css')) }}">
</head>
<body class="{{ $bodyClass }}">
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="{{ $role === 'admin' ? '/admin' : '/' }}" aria-label="{{ config('app.name') }} {{ $role === 'admin' ? 'admin' : 'home' }}">
            <img src="/assets/img/logo.svg" alt="" width="34" height="34">
            <span class="brand-text">{{ config('app.name') }}</span>
        </a>

        @if ($minimalHeader)
        @else
            @if ($visibleNav)
                <span class="header-divider" aria-hidden="true"></span>

                <nav class="main-nav" aria-label="Main navigation">
                    @foreach ($visibleNav as $item)
                        @php $active = $currentKey === $item['key']; @endphp
                        <a class="nav-link{{ $active ? ' is-active' : '' }}"
                           href="{{ $item['href'] }}"
                           @if ($active) aria-current="page" @endif>{{ $item['label'] }}</a>
                    @endforeach
                </nav>
            @endif

            <div class="header-actions">
                <button class="icon-btn theme-toggle" type="button" data-theme-toggle
                        aria-label="Toggle dark mode" title="Toggle dark mode">
                    <span class="theme-icon theme-icon--sun">{!! icon('sun') !!}</span>
                    <span class="theme-icon theme-icon--moon">{!! icon('moon') !!}</span>
                </button>
                @if ($user)
                    <a class="btn btn-accent btn-sm" href="/logout">Log Out</a>
                @else
                    <a class="btn btn-outline-accent btn-sm" href="/login">Log In</a>
                    <a class="btn btn-accent btn-sm" href="/signup">Sign Up</a>
                @endif
            </div>

            <button class="nav-toggle" type="button" data-nav-toggle
                    aria-controls="mobileNav" aria-expanded="false" aria-label="Open menu">
                {!! icon('menu') !!}
            </button>
        @endif
    </div>
</header>

@if (! $minimalHeader)
    <div class="mobile-drawer" id="mobileNav" hidden>
        <div class="mobile-drawer__head">
            <span class="brand-text">{{ config('app.name') }}</span>
            <button class="icon-btn" type="button" data-nav-close aria-label="Close menu">
                {!! icon('close') !!}
            </button>
        </div>

        @if ($user)
            <div class="mobile-user">
                <span class="avatar avatar--sm">{{ initials($user->first_name, $user->last_name) }}</span>
                <span>
                    <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>
                    <small>{{ role_label($user->role) }}</small>
                </span>
            </div>
        @endif

        <nav class="mobile-nav" aria-label="Mobile navigation">
            @foreach ($visibleNav as $item)
                @php $active = $currentKey === $item['key']; @endphp
                <a class="mobile-nav__link{{ $active ? ' is-active' : '' }}" href="{{ $item['href'] }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="mobile-drawer__foot">
            <button class="btn btn-ghost btn-block theme-toggle" type="button" data-theme-toggle>
                <span class="theme-icon theme-icon--sun">{!! icon('sun') !!}</span>
                <span class="theme-icon theme-icon--moon">{!! icon('moon') !!}</span>
                Toggle theme
            </button>
            @if ($user)
                <a class="btn btn-accent btn-block" href="/logout">Log Out</a>
            @else
                <a class="btn btn-accent btn-block" href="/signup">Sign Up</a>
                <a class="btn btn-ghost btn-block" href="/login">Log In</a>
            @endif
        </div>
    </div>
    <div class="drawer-backdrop" data-nav-close hidden></div>
@endif

@if ($flashes)
    <div class="flash-stack" role="status" aria-live="polite">
        @foreach ($flashes as $flash)
            <div class="flash flash--{{ $flash['type'] }}" data-flash>
                <span>{{ $flash['message'] }}</span>
                <button type="button" class="flash__close" data-flash-close aria-label="Dismiss message">
                    {!! icon('close') !!}
                </button>
            </div>
        @endforeach
    </div>
@endif

<main id="main" class="page">
