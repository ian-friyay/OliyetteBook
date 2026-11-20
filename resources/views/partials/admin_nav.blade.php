@php
    $adminPage = $adminPage ?? 'dashboard';

    if (! isset($adminCounts) || ! is_array($adminCounts)) {
        $adminCounts = [
            'owners' => (int) \Illuminate\Support\Facades\DB::table('users')->where('role', 'owner')->where('status', 'pending')->count(),
            'cottages' => (int) \Illuminate\Support\Facades\DB::table('cottages')->where('status', 'pending')->count(),
            'bookings' => (int) \Illuminate\Support\Facades\DB::table('bookings')->where('status', 'pending')->count(),
            'reviews' => (int) \Illuminate\Support\Facades\DB::table('reviews')->where('status', 'pending')->count(),
        ];
    }

    $user = auth()->user();

    $links = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => '/admin', 'icon' => 'dashboard', 'count' => null],
        ['key' => 'owners', 'label' => 'Owners', 'href' => '/admin/owners', 'icon' => 'users', 'count' => $adminCounts['owners']],
        ['key' => 'cottages', 'label' => 'Cottages', 'href' => '/admin/cottages', 'icon' => 'home', 'count' => $adminCounts['cottages']],
        ['key' => 'bookings', 'label' => 'Bookings', 'href' => '/admin/bookings', 'icon' => 'calendar', 'count' => $adminCounts['bookings']],
        ['key' => 'users', 'label' => 'Users', 'href' => '/admin/users', 'icon' => 'user', 'count' => null],
        ['key' => 'reviews', 'label' => 'User Reviews', 'href' => '/admin/reviews', 'icon' => 'star', 'count' => $adminCounts['reviews']],
        ['key' => 'records', 'label' => 'Records', 'href' => '/admin/records', 'icon' => 'list', 'count' => null],
    ];
@endphp
<aside class="sidebar admin-sidebar reveal">
    <div class="sidebar-profile">
        <span class="avatar">{{ initials($user->first_name ?? 'A', $user->last_name ?? '') }}</span>
        <div class="sidebar-profile__meta">
            <strong>{{ trim(($user->first_name ?? 'Admin') . ' ' . ($user->last_name ?? '')) }}</strong>
            <small>{{ $user->email ?? '' }}</small>
            <span class="badge badge--primary">Admin</span>
        </div>
    </div>

    <nav class="sidebar-nav" aria-label="Admin navigation">
        @foreach ($links as $link)
            <a class="sidebar-link{{ $adminPage === $link['key'] ? ' is-active' : '' }}"
               href="{{ $link['href'] }}"
               @if ($adminPage === $link['key']) aria-current="page" @endif>
                <span class="nav-icon">{!! icon($link['icon']) !!}</span>
                <span class="sidebar-link__label">{{ $link['label'] }}</span>
                @if ($link['count'] !== null && $link['count'] > 0)
                    <span class="count-bubble">{{ (int) $link['count'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    <a class="sidebar-link sidebar-link--logout" href="/logout">
        <span class="nav-icon">{!! icon('logout') !!}</span>
        <span>Log Out</span>
    </a>
</aside>
