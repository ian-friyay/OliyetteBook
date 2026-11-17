@php
    $navItems = [
        ['tab' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
    ];
    if ($role === 'owner') {
        $navItems[] = ['tab' => 'cottages', 'label' => 'My Cottages', 'icon' => 'home'];
    }
    $navItems[] = ['tab' => 'bookings', 'label' => 'Bookings', 'icon' => 'calendar'];
    if ($role === 'owner') {
        $navItems[] = ['tab' => 'efficiency', 'label' => 'Efficiency', 'icon' => 'chart'];
    }
    $navItems[] = ['tab' => 'settings', 'label' => 'Settings', 'icon' => 'settings'];
@endphp
@include('partials.header')
<div class="app-shell">
    <aside class="sidebar reveal">
        <div class="sidebar-profile">
            <span class="avatar">{{ initials($profile->first_name, $profile->last_name) }}</span>
            <div class="sidebar-profile__meta">
                <strong>{{ $profile->first_name }} {{ $profile->last_name }}</strong>
                <small>{{ $profile->email }}</small>
                <span class="badge {{ $role === 'owner' ? 'badge--teal' : ($role === 'admin' ? 'badge--primary' : 'badge--muted') }}">
                    {{ role_label($role) }}
                </span>
            </div>
        </div>

        <nav class="sidebar-nav" aria-label="Profile navigation">
            @foreach ($navItems as $item)
                <a class="sidebar-link{{ $tab === $item['tab'] ? ' is-active' : '' }}"
                   href="/profile?tab={{ $item['tab'] }}"
                   @if ($tab === $item['tab']) aria-current="page" @endif>
                    <span class="nav-icon">{!! icon($item['icon']) !!}</span>
                    <span class="sidebar-link__label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="sidebar-footer">
            <a class="sidebar-link sidebar-link--logout" href="/logout">
                <span class="nav-icon">{!! icon('logout') !!}</span>
                <span>Log Out</span>
            </a>
        </div>
    </aside>

    <section class="content">
        @if ($tab === 'dashboard')
            <div class="page-head">
                <div>
                    <p class="eyebrow">Member area</p>
                    <h1 class="page-title">Hello, {{ $profile->first_name }}</h1>
                    <p class="page-sub">Here is a quick look at your reservations and account.</p>
                </div>
                <a class="btn btn-primary" href="/booking">Book a cottage</a>
            </div>

            <div class="stat-grid">
                <div class="stat-card reveal">
                    <span class="stat-card__value">{{ (int) $stats['total'] }}</span>
                    <span class="stat-card__label">Total bookings</span>
                </div>
                <div class="stat-card reveal stagger-1">
                    <span class="stat-card__value">{{ (int) $stats['pending'] }}</span>
                    <span class="stat-card__label">Pending approval</span>
                </div>
                <div class="stat-card reveal stagger-2">
                    <span class="stat-card__value">{{ (int) $stats['completed'] }}</span>
                    <span class="stat-card__label">Completed stays</span>
                </div>
                <div class="stat-card reveal stagger-3">
                    <span class="stat-card__value">{{ peso($stats['spent']) }}</span>
                    <span class="stat-card__label">Approved value</span>
                </div>
            </div>

            <div class="panel reveal">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Up next</p>
                        <h2>Your next stay</h2>
                    </div>
                </div>

                @if ($nextStay)
                    <div class="summary-list">
                        <div class="summary-list__row"><span>Cottage</span><span>{{ $nextStay->cottage_name }}</span></div>
                        <div class="summary-list__row"><span>Receipt</span><span>{{ $nextStay->receipt_no }}</span></div>
                        <div class="summary-list__row"><span>Dates</span><span>{{ format_booking_range($nextStay->check_in, $nextStay->check_out, $nextStay->check_in_time, $nextStay->check_out_time) }}</span></div>
                        <div class="summary-list__row"><span>Total</span><span class="text-accent">{{ peso($nextStay->total_amount) }}</span></div>
                        <div class="summary-list__row"><span>Status</span><span>{!! status_badge($nextStay->status) !!}</span></div>
                    </div>
                    <div class="mt-2">
                        <a class="btn btn-ghost btn-sm" href="/profile?tab=bookings">View all bookings</a>
                    </div>
                @else
                    <div class="empty-state">
                        <h3>No upcoming stay yet</h3>
                        <p>Pick a cottage and reserve your dates whenever you are ready.</p>
                        <a class="btn btn-primary" href="/booking">Book a cottage</a>
                    </div>
                @endif
            </div>

            @if (count($bookings))
                <div class="panel reveal">
                    <div class="section-heading">
                        <h2>Recent bookings</h2>
                        <a class="btn btn-ghost btn-sm" href="/profile?tab=bookings">See all</a>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Receipt</th>
                                    <th>Cottage</th>
                                    <th>Dates</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (array_slice($bookings->all(), 0, 5) as $booking)
                                    <tr>
                                        <td>{{ $booking->receipt_no }}</td>
                                        <td>{{ $booking->cottage_name }}</td>
                                        <td>{{ format_booking_range($booking->check_in, $booking->check_out, $booking->check_in_time, $booking->check_out_time) }}</td>
                                        <td>{{ peso($booking->total_amount) }}</td>
                                        <td>{!! status_badge($booking->status) !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        @elseif ($tab === 'bookings')
            <div class="page-head">
                <div>
                    <p class="eyebrow">Reservations</p>
                    <h1 class="page-title">My Bookings</h1>
                    <p class="page-sub">Track your confirmation cards, cancel a reservation, or review a completed stay.</p>
                </div>
                <a class="btn btn-primary" href="/booking">Book a cottage</a>
            </div>

            @if (count($bookings) === 0)
                <div class="card empty-state">
                    <h3>You have no bookings yet</h3>
                    <p>Your confirmation cards will appear here after you reserve a cottage.</p>
                    <a class="btn btn-primary" href="/booking">Find a cottage</a>
                </div>
            @else
                <div class="booking-grid">
                    @foreach ($bookings as $index => $booking)
                        @php
                            $photo = $booking->photo ?: '/assets/img/fallback.svg';
                            $nights = nights_between((string) date_string($booking->check_in), (string) date_string($booking->check_out));
                            $guestCount = (int) $booking->adults + (int) $booking->children;
                        @endphp
                        <article class="booking-card reveal stagger-{{ ($index % 3) + 1 }}">
                            <div class="booking-card__head">
                                <div>
                                    <small>Booking confirmation</small>
                                    <h3>Receipt {{ $booking->receipt_no }}</h3>
                                </div>
                                {!! status_badge($booking->status) !!}
                            </div>
                            <div class="booking-card__media">
                                <img src="{{ $photo }}" alt="{{ $booking->cottage_name }}" loading="lazy">
                            </div>
                            <div class="booking-card__body">
                                <div class="detail-grid">
                                    <div class="detail-block">
                                        <h4>Guest information</h4>
                                        <p>{{ $profile->first_name }} {{ $profile->last_name }}</p>
                                        <p class="text-muted">{{ $profile->email }}</p>
                                        <p class="text-muted">{{ format_phone($profile->phone) }}</p>
                                    </div>
                                    <div class="detail-block">
                                        <h4>Cottage details</h4>
                                        <p>{{ $booking->cottage_name }}</p>
                                        <p class="text-muted">{{ $booking->cottage_location ?: 'Oliyette Resort' }}</p>
                                        <p class="text-muted">Up to {{ (int) $booking->capacity }} guests</p>
                                    </div>
                                    <div class="detail-block">
                                        <h4>Booking details</h4>
                                        <p>{{ format_booking_range($booking->check_in, $booking->check_out, $booking->check_in_time, $booking->check_out_time) }}</p>
                                        <p class="text-muted">{{ (string) date_string($booking->check_in) === (string) date_string($booking->check_out) ? '1 day (day use)' : $nights . ' night' . ($nights === 1 ? '' : 's') }} · {{ $guestCount }} guest{{ $guestCount === 1 ? '' : 's' }}</p>
                                        <p class="text-muted">{{ $booking->payment_method === 'gcash' ? 'GCash' : 'Pay at resort' }}</p>
                                    </div>
                                    <div class="detail-block">
                                        <h4>Booked on</h4>
                                        <p>{{ format_datetime($booking->created_at) }}</p>
                                    </div>
                                    @if (! empty($booking->special_requests))
                                        <div class="detail-block detail-block--wide">
                                            <h4>Special requests</h4>
                                            <p>{{ $booking->special_requests }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="booking-card__foot">
                                <div class="total-line">
                                    <span>Total Amount:</span>
                                    <span class="amount">{{ peso($booking->total_amount) }}</span>
                                </div>
                                <div class="booking-actions">
                                    @if (in_array($booking->status, ['pending', 'approved'], true))
                                        <form class="inline-form" method="post" action="/actions/cancel-booking"
                                              data-confirm="Cancel this reservation? This cannot be undone.">
                                            @csrf
                                            <input type="hidden" name="booking_id" value="{{ (int) $booking->id }}">
                                            <button class="btn btn-ghost btn-sm" type="submit">Cancel Reservation</button>
                                        </form>
                                    @endif

                                    @if ($booking->status === 'completed' && ! $booking->review_id)
                                        <a class="btn btn-secondary btn-sm" href="/reviews?booking={{ (int) $booking->id }}">Write Review</a>
                                    @elseif ($booking->review_id)
                                        <span class="badge {{ $booking->review_status === 'approved' ? 'badge--success' : ($booking->review_status === 'removed' ? 'badge--danger' : 'badge--warning') }}">
                                            Review {{ status_label((string) $booking->review_status) }}
                                        </span>
                                    @endif

                                    @if ($booking->status === 'approved')
                                        <span class="helper">Show receipt {{ $booking->receipt_no }} on arrival.</span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

        @elseif ($tab === 'cottages' && $role === 'owner')
            <div class="page-head">
                <div>
                    <p class="eyebrow">Resort owner</p>
                    <h1 class="page-title">My Cottages</h1>
                    <p class="page-sub">Add or edit your cottages. New listings wait for admin approval before going live.</p>
                </div>
            </div>

            @if (! empty($cottageErrors['form']))
                <p class="form-error" role="alert">{{ $cottageErrors['form'] }}</p>
            @endif

            <div class="panel reveal">
                <div class="section-heading">
                    <h2>{{ $cottageForm['id'] > 0 ? 'Edit cottage' : 'List a new cottage' }}</h2>
                    @if ($cottageForm['id'] > 0)
                        <a class="btn btn-ghost btn-sm" href="/profile?tab=cottages">Cancel edit</a>
                    @endif
                </div>

                <form class="form" method="post" action="/profile?tab=cottages" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="form" value="cottage">
                    <input type="hidden" name="cottage_id" value="{{ (int) $cottageForm['id'] }}">

                    <div class="form-grid">
                        <div class="field">
                            <label for="cottage_name">Cottage Name</label>
                            <input type="text" id="cottage_name" name="name" value="{{ $cottageForm['name'] }}"
                                   maxlength="100" required @if (isset($cottageErrors['name'])) aria-invalid="true" @endif>
                            @if (isset($cottageErrors['name']))
                                <p class="field-error">{{ $cottageErrors['name'] }}</p>
                            @endif
                        </div>
                        <div class="field">
                            <label for="cottage_location">Location</label>
                            <input type="text" id="cottage_location" name="location" value="{{ $cottageForm['location'] }}"
                                   maxlength="100" @if (isset($cottageErrors['location'])) aria-invalid="true" @endif>
                            @if (isset($cottageErrors['location']))
                                <p class="field-error">{{ $cottageErrors['location'] }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="field">
                        <label for="cottage_description">Description</label>
                        <textarea id="cottage_description" name="description">{{ $cottageForm['description'] }}</textarea>
                    </div>

                    <div class="form-grid">
                        <div class="field">
                            <label for="cottage_capacity">Capacity</label>
                            <input type="number" id="cottage_capacity" name="capacity" min="1" max="50"
                                   value="{{ (int) $cottageForm['capacity'] }}" required @if (isset($cottageErrors['capacity'])) aria-invalid="true" @endif>
                            @if (isset($cottageErrors['capacity']))
                                <p class="field-error">{{ $cottageErrors['capacity'] }}</p>
                            @endif
                        </div>
                        <div class="field">
                            <label for="cottage_rate">Rate per Day (₱)</label>
                            <input type="number" id="cottage_rate" name="rate_per_day" min="1" step="0.01"
                                   value="{{ (string) $cottageForm['rate_per_day'] }}" required @if (isset($cottageErrors['rate_per_day'])) aria-invalid="true" @endif>
                            @if (isset($cottageErrors['rate_per_day']))
                                <p class="field-error">{{ $cottageErrors['rate_per_day'] }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="field">
                        <label for="cottage_amenities">Amenities</label>
                        <input type="text" id="cottage_amenities" name="amenities" value="{{ $cottageForm['amenities'] }}"
                               placeholder="Air conditioning, Wi-Fi, Beach access">
                    </div>

                    <div class="field">
                        <label for="cottage_photo">Cottage Photo</label>
                        <input type="file" id="cottage_photo" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
                        <p class="helper">JPG, PNG, WEBP, or GIF. Maximum 3 MB.{{ $cottageForm['photo'] ? ' A photo is already on file.' : '' }}</p>
                        @if (isset($cottageErrors['photo']))
                            <p class="field-error">{{ $cottageErrors['photo'] }}</p>
                        @endif
                        @if ($cottageForm['photo'])
                            <img class="mt-1" src="{{ $cottageForm['photo'] }}" alt="Current cottage photo"
                                 style="max-width:220px;border-radius:12px;">
                        @endif
                    </div>

                    <button class="btn btn-primary" type="submit">
                        {{ $cottageForm['id'] > 0 ? 'Save Changes' : 'Submit Cottage' }}
                    </button>
                </form>
            </div>

            <div class="panel reveal">
                <div class="section-heading">
                    <h2>Your listings</h2>
                </div>

                @if ($ownerCottages->isEmpty())
                    <div class="empty-state">
                        <h3>No cottages listed yet</h3>
                        <p>Use the form above to submit your first cottage.</p>
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Cottage</th>
                                    <th>Location</th>
                                    <th>Rate</th>
                                    <th>Bookings</th>
                                    <th>Rating</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ownerCottages as $cottage)
                                    <tr>
                                        <td>{{ $cottage->code }}</td>
                                        <td>{{ $cottage->name }}</td>
                                        <td>{{ $cottage->location ?: '—' }}</td>
                                        <td>{{ peso($cottage->rate_per_day) }}</td>
                                        <td>{{ (int) $cottage->booking_count }}</td>
                                        <td>
                                            @if ((int) $cottage->review_count > 0)
                                                {{ number_format((float) $cottage->avg_rating, 1) }} ({{ (int) $cottage->review_count }})
                                            @else
                                                <span class="text-muted">No reviews</span>
                                            @endif
                                        </td>
                                        <td>{!! status_badge($cottage->status) !!}</td>
                                        <td>
                                            <div class="table-actions">
                                                <a class="btn btn-ghost btn-sm" href="/profile?tab=cottages&edit={{ (int) $cottage->id }}">Edit</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        @elseif ($tab === 'efficiency' && $role === 'owner')
            <div class="page-head">
                <div>
                    <p class="eyebrow">Resort owner</p>
                    <h1 class="page-title">Efficiency</h1>
                    <p class="page-sub">Bookings and revenue for your cottages over the last six months.</p>
                </div>
            </div>

            <div class="stat-grid">
                <div class="stat-card reveal">
                    <span class="stat-card__value">{{ (int) $ownerStats['total'] }}</span>
                    <span class="stat-card__label">Cottages</span>
                    <span class="stat-card__note">{{ (int) $ownerStats['active'] }} active</span>
                </div>
                <div class="stat-card reveal stagger-1">
                    <span class="stat-card__value">{{ (int) $ownerStats['bookings'] }}</span>
                    <span class="stat-card__label">Bookings</span>
                </div>
                <div class="stat-card reveal stagger-2">
                    <span class="stat-card__value">{{ peso($ownerStats['revenue']) }}</span>
                    <span class="stat-card__label">Approved revenue</span>
                </div>
                <div class="stat-card reveal stagger-3">
                    <span class="stat-card__value">{{ $ownerStats['rating'] > 0 ? number_format($ownerStats['rating'], 1) : '—' }}</span>
                    <span class="stat-card__label">Average rating</span>
                </div>
            </div>

            <div class="panel reveal">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Last 6 months</p>
                        <h2>Bookings per month</h2>
                    </div>
                </div>
                <div class="bar-chart" role="img" aria-label="Bookings per month bar chart">
                    @foreach ($months as $month)
                        @php $height = $month['bookings'] > 0 ? max(6, (int) round($month['bookings'] / $maxMonthBookings * 100)) : 2; @endphp
                        <div class="bar-col">
                            <span class="bar-value">{{ (int) $month['bookings'] }}</span>
                            <div class="bar-track">
                                <div class="bar-fill" style="--bar-height: {{ $height }}%"></div>
                            </div>
                            <span class="bar-label">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="panel reveal">
                <div class="section-heading">
                    <h2>Revenue by month</h2>
                </div>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Bookings</th>
                                <th>Approved revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($months as $month)
                                <tr>
                                    <td>{{ $month['label'] }}</td>
                                    <td>{{ (int) $month['bookings'] }}</td>
                                    <td>{{ peso($month['revenue']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @elseif ($tab === 'settings')
            <div class="page-head">
                <div>
                    <p class="eyebrow">Account</p>
                    <h1 class="page-title">Settings</h1>
                    <p class="page-sub">Keep your contact details current and your password secure.</p>
                </div>
            </div>

            <div class="panel reveal">
                <div class="section-heading">
                    <h2>Profile details</h2>
                </div>

                <form class="form" method="post" action="/profile?tab=settings" novalidate>
                    @csrf
                    <input type="hidden" name="form" value="profile">

                    <div class="form-grid">
                        <div class="field">
                            <label for="settings_first_name">First Name</label>
                            <input type="text" id="settings_first_name" name="first_name"
                                   value="{{ $profile->first_name }}" maxlength="60" required @if (isset($settingsErrors['first_name'])) aria-invalid="true" @endif>
                            @if (isset($settingsErrors['first_name']))
                                <p class="field-error">{{ $settingsErrors['first_name'] }}</p>
                            @endif
                        </div>
                        <div class="field">
                            <label for="settings_last_name">Last Name</label>
                            <input type="text" id="settings_last_name" name="last_name"
                                   value="{{ $profile->last_name }}" maxlength="60" required @if (isset($settingsErrors['last_name'])) aria-invalid="true" @endif>
                            @if (isset($settingsErrors['last_name']))
                                <p class="field-error">{{ $settingsErrors['last_name'] }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="field">
                            <label for="settings_email">Email</label>
                            <input type="email" id="settings_email" value="{{ $profile->email }}" disabled>
                            <p class="helper">Contact the admin if you need to change your email.</p>
                        </div>
                        <div class="field">
                            <label for="settings_phone">Phone Number</label>
                            <input type="tel" id="settings_phone" name="phone" value="{{ format_phone($profile->phone) }}"
                                   placeholder="09XX XXX XXXX" data-phone required @if (isset($settingsErrors['phone'])) aria-invalid="true" @endif>
                            @if (isset($settingsErrors['phone']))
                                <p class="field-error">{{ $settingsErrors['phone'] }}</p>
                            @endif
                        </div>
                    </div>

                    <button class="btn btn-primary" type="submit">Save Profile</button>
                </form>
            </div>

            <div class="panel reveal">
                <div class="section-heading">
                    <h2>Change password</h2>
                </div>

                <form class="form" method="post" action="/profile?tab=settings" novalidate>
                    @csrf
                    <input type="hidden" name="form" value="password">

                    <div class="field">
                        <label for="current_password">Current Password</label>
                        <div class="password-field">
                            <input type="password" id="current_password" name="current_password" autocomplete="current-password"
                                   required @if (isset($passwordErrors['current_password'])) aria-invalid="true" @endif>
                            {!! password_toggle() !!}
                        </div>
                        @if (isset($passwordErrors['current_password']))
                            <p class="field-error">{{ $passwordErrors['current_password'] }}</p>
                        @endif
                    </div>

                    <div class="form-grid">
                        <div class="field">
                            <label for="new_password">New Password</label>
                            <div class="password-field">
                                <input type="password" id="new_password" name="new_password" autocomplete="new-password"
                                       minlength="8" required @if (isset($passwordErrors['new_password'])) aria-invalid="true" @endif>
                                {!! password_toggle() !!}
                            </div>
                            @if (isset($passwordErrors['new_password']))
                                <p class="field-error">{{ $passwordErrors['new_password'] }}</p>
                            @endif
                        </div>
                        <div class="field">
                            <label for="confirm_password">Confirm New Password</label>
                            <div class="password-field">
                                <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password"
                                       minlength="8" required @if (isset($passwordErrors['confirm_password'])) aria-invalid="true" @endif>
                                {!! password_toggle() !!}
                            </div>
                            @if (isset($passwordErrors['confirm_password']))
                                <p class="field-error">{{ $passwordErrors['confirm_password'] }}</p>
                            @endif
                        </div>
                    </div>

                    <button class="btn btn-secondary" type="submit">Update Password</button>
                </form>
            </div>

            <div class="panel reveal">
                <h2>Data privacy</h2>
                <p class="text-muted">OliyetteBook only collects the details needed to manage your reservation. Read the <a href="/contact#privacy">Data Privacy notice</a> for more information.</p>
            </div>
        @endif
    </section>
</div>
@include('partials.footer')
