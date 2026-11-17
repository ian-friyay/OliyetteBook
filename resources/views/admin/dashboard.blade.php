@include('partials.header')
<div class="app-shell">
    @include('partials.admin_nav')

    <section class="content">
        <div class="page-head">
            <div>
                <p class="eyebrow">Resort administration</p>
                <h1 class="page-title">Admin Dashboard</h1>
                <p class="page-sub">Approve accounts and listings, keep bookings moving, and moderate reviews.</p>
            </div>
            <a class="btn btn-ghost btn-sm" href="/admin/records">Reservation records</a>
        </div>

        <div class="stat-grid">
            <a class="stat-card reveal" href="/admin/owners">
                <span class="stat-card__value">{{ (int) $adminCounts['owners'] }}</span>
                <span class="stat-card__label">Pending owners</span>
                <span class="stat-card__note">Waiting for approval</span>
            </a>
            <a class="stat-card reveal stagger-1" href="/admin/cottages">
                <span class="stat-card__value">{{ (int) $adminCounts['cottages'] }}</span>
                <span class="stat-card__label">Pending cottages</span>
                <span class="stat-card__note">Not yet visible on Home</span>
            </a>
            <a class="stat-card reveal stagger-2" href="/admin/bookings">
                <span class="stat-card__value">{{ (int) $adminCounts['bookings'] }}</span>
                <span class="stat-card__label">Pending bookings</span>
                <span class="stat-card__note">Awaiting decision</span>
            </a>
            <a class="stat-card reveal stagger-3" href="/admin/reviews">
                <span class="stat-card__value">{{ (int) $adminCounts['reviews'] }}</span>
                <span class="stat-card__label">Pending reviews</span>
                <span class="stat-card__note">Waiting for moderation</span>
            </a>
        </div>

        <div class="panel reveal">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Accounts</p>
                    <h2>Pending Cottage Owner Approvals</h2>
                </div>
                <a class="btn btn-ghost btn-sm" href="/admin/owners">View all owners</a>
            </div>

            @if ($pendingOwners->isEmpty())
                <div class="empty-state">
                    <h3>No pending owner approvals</h3>
                    <p>New owner sign-ups will appear here.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Owner ID</th>
                                <th>Cottage Name</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingOwners as $owner)
                                <tr>
                                    <td>#{{ (int) $owner->id }}</td>
                                    <td>
                                        {{ $owner->first_cottage ?: 'No cottage yet' }}
                                        @if ((int) $owner->cottage_count > 0)
                                            <small class="text-muted">({{ (int) $owner->cottage_count }})</small>
                                        @endif
                                    </td>
                                    <td>{{ $owner->first_name }} {{ $owner->last_name }}</td>
                                    <td>{{ $owner->email }}</td>
                                    <td>{!! status_badge($owner->status) !!}</td>
                                    <td>
                                        <div class="table-actions">
                                            <form method="post" action="/admin/owners/action">
                                                @csrf
                                                <input type="hidden" name="owner_id" value="{{ (int) $owner->id }}">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="return" value="/admin">
                                                <button class="btn btn-success" type="submit">Approve</button>
                                            </form>
                                            <form method="post" action="/admin/owners/action"
                                                  data-confirm="Decline this owner account?">
                                                @csrf
                                                <input type="hidden" name="owner_id" value="{{ (int) $owner->id }}">
                                                <input type="hidden" name="action" value="decline">
                                                <input type="hidden" name="return" value="/admin">
                                                <button class="btn btn-danger" type="submit">Decline</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="panel reveal">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Reservations</p>
                    <h2>Bookings waiting for approval</h2>
                </div>
                <a class="btn btn-ghost btn-sm" href="/admin/bookings">All bookings</a>
            </div>

            @if ($pendingBookings->isEmpty())
                <div class="empty-state">
                    <h3>No pending bookings</h3>
                    <p>New reservations will appear here.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Receipt</th>
                                <th>Guest</th>
                                <th>Cottage</th>
                                <th>Dates</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingBookings as $booking)
                                <tr>
                                    <td>{{ $booking->receipt_no }}</td>
                                    <td>{{ $booking->first_name }} {{ $booking->last_name }}</td>
                                    <td>{{ $booking->cottage_name }}</td>
                                    <td>{{ format_date($booking->check_in) }} – {{ format_date($booking->check_out) }}</td>
                                    <td>{{ peso($booking->total_amount) }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <form method="post" action="/admin/bookings/action">
                                                @csrf
                                                <input type="hidden" name="booking_id" value="{{ (int) $booking->id }}">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="return" value="/admin">
                                                <button class="btn btn-success" type="submit">Approve</button>
                                            </form>
                                            <form method="post" action="/admin/bookings/action"
                                                  data-confirm="Decline this booking?">
                                                @csrf
                                                <input type="hidden" name="booking_id" value="{{ (int) $booking->id }}">
                                                <input type="hidden" name="action" value="decline">
                                                <input type="hidden" name="return" value="/admin">
                                                <button class="btn btn-danger" type="submit">Decline</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="panel reveal">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Inbox</p>
                    <h2>Recent inquiries</h2>
                </div>
            </div>

            @if ($recentInquiries->isEmpty())
                <div class="empty-state">
                    <h3>No messages yet</h3>
                    <p>Messages sent through the contact page will appear here.</p>
                </div>
            @else
                <div class="review-list">
                    @foreach ($recentInquiries as $inquiry)
                        <article class="review-card">
                            <div class="review-card__head">
                                <div>
                                    <strong>{{ $inquiry->name }}</strong>
                                    <small>{{ $inquiry->email }} · {{ format_datetime($inquiry->created_at) }}</small>
                                </div>
                                @if ($inquiry->is_read)
                                    <span class="badge badge--muted">Read</span>
                                @else
                                    <span class="badge badge--warning">New</span>
                                @endif
                            </div>
                            <p>{{ $inquiry->message }}</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@include('partials.footer')
