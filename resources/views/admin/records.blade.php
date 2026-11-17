@include('partials.header')
<div class="app-shell">
    @include('partials.admin_nav')

    <section class="content">
        <div class="page-head">
            <div>
                <p class="eyebrow">Reports</p>
                <h1 class="page-title">Reservation Records</h1>
                <p class="page-sub">Filter reservations by the date they were booked, then export the list if needed.</p>
            </div>
            <a class="btn btn-ghost btn-sm" href="{{ $exportUrl }}">Export CSV</a>
        </div>

        <form class="filter-bar" method="get" action="/admin/records">
            <div class="field">
                <label for="from">From</label>
                <input type="date" id="from" name="from" value="{{ $from }}">
            </div>
            <div class="field">
                <label for="to">To</label>
                <input type="date" id="to" name="to" value="{{ $to }}">
            </div>
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="all"{{ $statusFilter === 'all' ? ' selected' : '' }}>All statuses</option>
                    <option value="pending"{{ $statusFilter === 'pending' ? ' selected' : '' }}>Pending</option>
                    <option value="approved"{{ $statusFilter === 'approved' ? ' selected' : '' }}>Approved</option>
                    <option value="completed"{{ $statusFilter === 'completed' ? ' selected' : '' }}>Completed</option>
                    <option value="declined"{{ $statusFilter === 'declined' ? ' selected' : '' }}>Declined</option>
                    <option value="cancelled"{{ $statusFilter === 'cancelled' ? ' selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <button class="btn btn-primary" type="submit">Apply Filter</button>
            <a class="btn btn-ghost" href="/admin/records">Reset</a>
        </form>

        <div class="record-totals">
            <span>Records: <strong>{{ $totalCount }}</strong></span>
            <span>Approved revenue: <strong>{{ peso($revenue) }}</strong></span>
            <span>Pending value: <strong>{{ peso($pendingValue) }}</strong></span>
            <span>Cancelled / declined: <strong>{{ $cancelled }}</strong></span>
        </div>

        <div class="panel reveal">
            <div class="section-heading">
                <h2>{{ format_date($from) }} – {{ format_date($to) }}</h2>
                <span class="helper">{{ $totalCount }} record{{ $totalCount === 1 ? '' : 's' }}</span>
            </div>

            @if ($records->isEmpty())
                <div class="empty-state">
                    <h3>No reservation records</h3>
                    <p>Try a wider date range or a different status.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Receipt</th>
                                <th>Guest</th>
                                <th>Cottage</th>
                                <th>Stay dates</th>
                                <th>Guests</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Booked</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($records as $record)
                                <tr>
                                    <td>{{ $record->receipt_no }}</td>
                                    <td>
                                        {{ $record->first_name }} {{ $record->last_name }}<br>
                                        <small class="text-muted">{{ $record->email }}</small>
                                    </td>
                                    <td>{{ $record->cottage_name }}</td>
                                    <td>{{ format_booking_range($record->check_in, $record->check_out, $record->check_in_time, $record->check_out_time) }}</td>
                                    <td>{{ (int) $record->adults }} adult{{ (int) $record->adults === 1 ? '' : 's' }}{{ (int) $record->children > 0 ? ', ' . (int) $record->children . ' child' . ((int) $record->children === 1 ? '' : 'ren') : '' }}</td>
                                    <td>{{ peso($record->total_amount) }}</td>
                                    <td>{!! status_badge($record->status) !!}</td>
                                    <td>{{ format_datetime($record->created_at) }}</td>
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
