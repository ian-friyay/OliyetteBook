@include('partials.header')
<div class="app-shell">
    @include('partials.admin_nav')

    <section class="content">
        <div class="page-head">
            <div>
                <p class="eyebrow">Reservations</p>
                <h1 class="page-title">Bookings</h1>
                <p class="page-sub">Approve or decline new reservations, and mark approved stays as completed.</p>
            </div>
        </div>

        <form class="filter-bar" method="get" action="/admin/bookings">
            <div class="field">
                <label for="q">Search</label>
                <input type="search" id="q" name="q" value="{{ $q }}" placeholder="Receipt, guest, or cottage">
            </div>
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="all"{{ $filter === 'all' ? ' selected' : '' }}>All statuses</option>
                    <option value="pending"{{ $filter === 'pending' ? ' selected' : '' }}>Pending</option>
                    <option value="approved"{{ $filter === 'approved' ? ' selected' : '' }}>Approved</option>
                    <option value="completed"{{ $filter === 'completed' ? ' selected' : '' }}>Completed</option>
                    <option value="declined"{{ $filter === 'declined' ? ' selected' : '' }}>Declined</option>
                    <option value="cancelled"{{ $filter === 'cancelled' ? ' selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <button class="btn btn-primary" type="submit">Filter</button>
            @if ($filter !== 'all' || $q !== '')
                <a class="btn btn-ghost" href="/admin/bookings">Reset</a>
            @endif
        </form>

        <div class="panel reveal">
            <div class="section-heading">
                <h2>Reservation list</h2>
                <span class="helper">{{ $bookings->count() }} shown</span>
            </div>

            @if ($bookings->isEmpty())
                <div class="empty-state">
                    <h3>No bookings found</h3>
                    <p>Try a different status or search term.</p>
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
                                <th>Guests</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Booked</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                <tr>
                                    <td>{{ $booking->receipt_no }}</td>
                                    <td>
                                        {{ $booking->first_name }} {{ $booking->last_name }}<br>
                                        <small class="text-muted">{{ $booking->email }}</small>
                                    </td>
                                    <td>{{ $booking->cottage_name }}<br><small class="text-muted">{{ $booking->location ?: '—' }}</small></td>
                                    <td>{{ format_booking_range($booking->check_in, $booking->check_out, $booking->check_in_time, $booking->check_out_time) }}</td>
                                    <td>{{ (int) $booking->adults }} adult{{ (int) $booking->adults === 1 ? '' : 's' }}{{ (int) $booking->children > 0 ? ', ' . (int) $booking->children . ' child' . ((int) $booking->children === 1 ? '' : 'ren') : '' }}</td>
                                    <td>{{ peso($booking->total_amount) }}</td>
                                    <td>{!! status_badge($booking->status) !!}</td>
                                    <td>{{ format_date($booking->created_at) }}</td>
                                    <td>
                                        <div class="table-actions">
                                            @if ($booking->status === 'pending')
                                                <form method="post" action="/admin/bookings/action">
                                                    @csrf
                                                    <input type="hidden" name="booking_id" value="{{ (int) $booking->id }}">
                                                    <input type="hidden" name="action" value="approve">
                                                    <input type="hidden" name="return" value="{{ $returnUrl }}">
                                                    <button class="btn btn-success" type="submit">Approve</button>
                                                </form>
                                                <form method="post" action="/admin/bookings/action"
                                                      data-confirm="Decline this booking?">
                                                    @csrf
                                                    <input type="hidden" name="booking_id" value="{{ (int) $booking->id }}">
                                                    <input type="hidden" name="action" value="decline">
                                                    <input type="hidden" name="return" value="{{ $returnUrl }}">
                                                    <button class="btn btn-danger" type="submit">Decline</button>
                                                </form>
                                            @elseif ($booking->status === 'approved')
                                                <form method="post" action="/admin/bookings/action">
                                                    @csrf
                                                    <input type="hidden" name="booking_id" value="{{ (int) $booking->id }}">
                                                    <input type="hidden" name="action" value="complete">
                                                    <input type="hidden" name="return" value="{{ $returnUrl }}">
                                                    <button class="btn btn-primary" type="submit">Mark Completed</button>
                                                </form>
                                                <form method="post" action="/admin/bookings/action"
                                                      data-confirm="Cancel this approved booking?">
                                                    @csrf
                                                    <input type="hidden" name="booking_id" value="{{ (int) $booking->id }}">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <input type="hidden" name="return" value="{{ $returnUrl }}">
                                                    <button class="btn btn-danger" type="submit">Cancel</button>
                                                </form>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
</div>
@include('partials.footer')
