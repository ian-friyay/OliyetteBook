<?php

declare(strict_types=1);

use App\Models\Booking;
use App\Models\Cottage;
use App\Models\LoginAttempt;
use App\Models\Room;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

function peso(float|int|string $amount): string
{
    return '₱' . number_format((float) $amount, 2);
}

function status_label(string $status): string
{
    return match ($status) {
        'pending' => 'Pending',
        'approved' => 'Approved',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'declined' => 'Declined',
        'cancelled' => 'Cancelled',
        'active' => 'Active',
        'deactivated' => 'Deactivated',
        'hidden' => 'Hidden',
        'removed' => 'Removed',
        'guest' => 'Guest',
        'owner' => 'Resort Owner',
        'admin' => 'Admin',
        default => ucfirst($status),
    };
}

function status_badge(string $status): string
{
    $class = match ($status) {
        'approved', 'confirmed', 'active' => 'badge--success',
        'pending' => 'badge--warning',
        'declined', 'cancelled', 'deactivated', 'removed' => 'badge--danger',
        'completed' => 'badge--completed',
        'hidden' => 'badge--muted',
        'owner' => 'badge--teal',
        'admin' => 'badge--primary',
        default => 'badge--muted',
    };

    return '<span class="badge ' . $class . '">' . e(status_label($status)) . '</span>';
}

function role_label(string $role): string
{
    return status_label($role);
}

function initials(string $firstName, string $lastName = ''): string
{
    $first = mb_substr(trim($firstName), 0, 1);
    $last = mb_substr(trim($lastName), 0, 1);

    return mb_strtoupper($first . $last);
}

function date_string(mixed $date): ?string
{
    if ($date === null || $date === '') {
        return null;
    }
    if ($date instanceof \DateTimeInterface) {
        return $date->format('Y-m-d');
    }

    return (string) $date;
}

function format_date(mixed $date, string $fallback = '—'): string
{
    $value = date_string($date);
    if (! $value) {
        return $fallback;
    }
    $ts = strtotime($value);

    return $ts ? date('M j, Y', $ts) : $fallback;
}

function format_time(mixed $time, string $fallback = ''): string
{
    if ($time === null || $time === '') {
        return $fallback;
    }
    $ts = strtotime((string) $time);

    return $ts ? date('g:i A', $ts) : $fallback;
}

function format_booking_range(mixed $checkIn, mixed $checkOut, mixed $checkInTime = null, mixed $checkOutTime = null): string
{
    $start = format_date($checkIn);
    if ($checkInTime) {
        $start .= ' ' . format_time($checkInTime);
    }
    $end = format_date($checkOut);
    if ($checkOutTime) {
        $end .= ' ' . format_time($checkOutTime);
    }

    return $start . ' – ' . $end;
}

function format_datetime(mixed $date, string $fallback = '—'): string
{
    if ($date === null || $date === '') {
        return $fallback;
    }
    if ($date instanceof \DateTimeInterface) {
        return $date->format('M j, Y g:i A');
    }
    $ts = strtotime((string) $date);

    return $ts ? date('M j, Y g:i A', $ts) : $fallback;
}

function format_phone(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if (strlen($digits) === 11 && str_starts_with($digits, '09')) {
        return substr($digits, 0, 4) . ' ' . substr($digits, 4, 3) . ' ' . substr($digits, 7, 4);
    }

    return $phone;
}

function is_valid_date(?string $value): bool
{
    if (! $value) {
        return false;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

    return $date !== false && $date->format('Y-m-d') === $value;
}

function nights_between(string $checkIn, string $checkOut): int
{
    $in = new DateTimeImmutable($checkIn);
    $out = new DateTimeImmutable($checkOut);
    $days = (int) $in->diff($out)->format('%a');

    return max(1, $days);
}

function safe_next(?string $next, string $fallback = '/'): string
{
    if (! $next || ! str_starts_with($next, '/') || str_starts_with($next, '//')) {
        return $fallback;
    }
    if (preg_match('#^/[a-z]+:#i', $next)) {
        return $fallback;
    }

    return $next;
}

function app_base_url(): string
{
    return rtrim((string) config('app.url', url('/')), '/');
}

function absolute_url(string $path): string
{
    return rtrim(app_base_url(), '/') . '/' . ltrim($path, '/');
}

function star_html(float $rating, int $reviewCount = 0): string
{
    $out = '<span class="stars" role="img" aria-label="Rated ' . number_format($rating, 1) . ' out of 5' . ($reviewCount ? ' from ' . $reviewCount . ' reviews' : '') . '">';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $class = 'star--full';
        } elseif ($rating >= $i - 0.5) {
            $class = 'star--half';
        } else {
            $class = 'star--empty';
        }
        $out .= '<span class="star ' . $class . '" aria-hidden="true">★</span>';
    }
    $out .= '</span>';

    return $out;
}

function icon(string $name, string $class = 'icon'): string
{
    $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'users' => '<circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0"/><circle cx="17.5" cy="9" r="2.2"/><path d="M15.5 20a4.8 4.8 0 0 1 6.5-4.5"/>',
        'user' => '<circle cx="12" cy="8" r="3.2"/><path d="M5 20a7 7 0 0 1 14 0"/>',
        'home' => '<path d="M4 11.5 12 4l8 7.5"/><path d="M6.5 10.5V20h11v-9.5"/><path d="M10 20v-5h4v5"/>',
        'bed' => '<path d="M3 18v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6"/><path d="M3 18h18M6 10V7a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v3"/><path d="M8 10V8h8v2"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/><path d="M8 14h3M13 14h3M8 17h3"/>',
        'star' => '<path d="m12 3.6 2.5 5.1 5.6.8-4 4 .9 5.6-5-2.7-5 2.7.9-5.6-4-4 5.6-.8Z"/>',
        'list' => '<path d="M8 6h13M8 12h13M8 18h13"/><circle cx="3.5" cy="6" r="1"/><circle cx="3.5" cy="12" r="1"/><circle cx="3.5" cy="18" r="1"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.2a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.2a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3 1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.2a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9v.1a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.2a1.7 1.7 0 0 0-1.5 1Z"/>',
        'logout' => '<path d="M14 8V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-3"/><path d="M9 12h11"/><path d="m17 8 4 4-4 4"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'moon' => '<path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5Z"/>',
        'eye' => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/><circle cx="12" cy="12" r="2.6"/>',
        'eye-off' => '<path d="M3 3l18 18"/><path d="M10.6 6.2A10.8 10.8 0 0 1 12 6c6.5 0 10 6 10 6a17 17 0 0 1-3.1 3.7"/><path d="M6.2 6.8A16.6 16.6 0 0 0 2 12s3.5 6 10 6a10.7 10.7 0 0 0 4.2-.8"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>',
        'arrow' => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
        'check' => '<path d="m4 12 5 5L20 6"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'pin' => '<path d="M12 21s6-5.2 6-10a6 6 0 1 0-12 0c0 4.8 6 10 6 10Z"/><circle cx="12" cy="11" r="2.2"/>',
        'money' => '<rect x="2.5" y="6" width="19" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 9v6M18 9v6"/>',
        'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 7 8.5 6 8.5-6"/>',
        'phone' => '<path d="M5 3h4l2 5-2.5 1.5a12 12 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 5a2 2 0 0 1 2-2Z"/>',
    ];

    $body = $paths[$name] ?? $paths['star'];

    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $body . '</svg>';
}

function password_toggle(): string
{
    return '<button type="button" class="pw-toggle" data-password-toggle aria-label="Show password" title="Show password">' . icon('eye') . '</button>';
}

function send_mail(string $to, string $subject, string $body): bool
{
    try {
        $driver = (string) config('booking.mail_driver');
        $mailer = array_key_exists($driver, (array) config('mail.mailers', [])) ? $driver : null;

        $fromAddress = (string) (config('mail.from.address') ?: config('booking.mail_from'));
        $fromName = (string) (config('mail.from.name') ?: config('app.name'));

        \Illuminate\Support\Facades\Mail::mailer($mailer)->raw(
            $body,
            function ($message) use ($to, $subject, $fromAddress, $fromName) {
                $message->to($to)->subject($subject);

                if ($fromAddress !== '') {
                    $message->from($fromAddress, $fromName);
                }
            }
        );

        return true;
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Failed to send mail to ' . $to . ': ' . $e->getMessage());

        return false;
    }
}

function save_uploaded_photo(?UploadedFile $file, string $prefix = 'cottage'): ?string
{
    if (! $file) {
        return null;
    }
    if (! $file->isValid()) {
        throw new RuntimeException('The photo could not be uploaded. Please try again.');
    }
    if ($file->getSize() > config('booking.max_photo_bytes')) {
        throw new RuntimeException('Photos must be 5 MB or smaller.');
    }

    $mime = (string) $file->getMimeType();
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (! isset($allowed[$mime])) {
        throw new RuntimeException('Please upload a JPG, PNG, WEBP, or GIF image.');
    }

    $dir = config('booking.upload_dir');
    if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
        throw new RuntimeException('The uploads folder is not writable.');
    }

    $prefix = preg_replace('/[^a-z0-9_-]/i', '', $prefix) ?: 'photo';
    $name = $prefix . '-' . date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
    $file->move($dir, $name);

    return config('booking.upload_url') . '/' . $name;
}

function remove_uploaded_photo(?string $path): void
{
    $prefix = config('booking.upload_url') . '/';
    if (! $path || ! str_starts_with($path, $prefix)) {
        return;
    }
    $file = config('booking.upload_dir') . DIRECTORY_SEPARATOR . basename($path);
    if (is_file($file)) {
        @unlink($file);
    }
}

function generate_cottage_code(): string
{
    for ($i = 0; $i < 25; $i++) {
        $code = 'CTG-' . str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        if (! Cottage::where('code', $code)->exists()) {
            return $code;
        }
    }
    throw new RuntimeException('Could not generate a unique cottage code.');
}

function generate_room_code(): string
{
    for ($i = 0; $i < 25; $i++) {
        $code = 'RM-' . str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        if (! Room::where('code', $code)->exists()) {
            return $code;
        }
    }
    throw new RuntimeException('Could not generate a unique room code.');
}

function generate_receipt_no(): string
{
    for ($i = 0; $i < 25; $i++) {
        $no = (string) random_int(100000, 999999);
        if (! Booking::where('receipt_no', $no)->exists()) {
            return $no;
        }
    }
    throw new RuntimeException('Could not generate a receipt number.');
}

function home_for_role(string $role): string
{
    return $role === 'admin' ? route('admin.dashboard') : '/';
}

function auth_user(): ?\App\Models\User
{
    return auth()->user();
}

function client_ip(): string
{
    return substr((string) (request()->ip() ?? 'unknown'), 0, 45);
}

function login_is_locked(string $email): bool
{
    $since = Carbon::now()->subMinutes((int) config('booking.login_lock_minutes'));

    $attempts = LoginAttempt::where('email', $email)
        ->where('ip_address', client_ip())
        ->where('attempted_at', '>=', $since)
        ->count();

    return $attempts >= (int) config('booking.login_max_attempts');
}

function record_failed_login(string $email): void
{
    LoginAttempt::create([
        'email' => $email,
        'ip_address' => client_ip(),
        'attempted_at' => Carbon::now(),
    ]);

    LoginAttempt::where('attempted_at', '<', Carbon::now()->subDay())->delete();
}

function clear_login_attempts(string $email): void
{
    LoginAttempt::where('email', $email)->where('ip_address', client_ip())->delete();
}

function flash(string $type, string $message): void
{
    $flashes = session()->get('flashes', []);
    $flashes[] = ['type' => $type, 'message' => $message];
    session()->flash('flashes', $flashes);
}

function get_flashes(): array
{
    return session()->pull('flashes', []);
}

function active_cottages()
{
    return \Illuminate\Support\Facades\DB::table('cottages as c')
        ->select('c.*')
        ->selectRaw('COALESCE(AVG(r.rating), 0) AS avg_rating')
        ->selectRaw('COUNT(r.id) AS review_count')
        ->selectRaw('(SELECT path FROM cottage_photos p WHERE p.cottage_id = c.id ORDER BY sort_order, id LIMIT 1) AS photo')
        ->leftJoin('reviews as r', function ($join) {
            $join->on('r.cottage_id', '=', 'c.id')->where('r.status', '=', 'approved');
        })
        ->where('c.status', 'active')
        ->groupBy('c.id')
        ->orderBy('c.id')
        ->get();
}
