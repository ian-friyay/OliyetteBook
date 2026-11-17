@include('partials.header')
<div class="app-shell">
    @include('partials.admin_nav')

    <section class="content">
        <div class="page-head">
            <div>
                <p class="eyebrow">Accounts</p>
                <h1 class="page-title">Cottage Owners</h1>
                <p class="page-sub">Approve new owners, deactivate accounts, or reactivate them.</p>
            </div>
        </div>

        <div class="tabs">
            <a class="tab{{ $filter === 'all' ? ' is-active' : '' }}" href="/admin/owners">All</a>
            <a class="tab{{ $filter === 'pending' ? ' is-active' : '' }}" href="/admin/owners?status=pending">Pending</a>
            <a class="tab{{ $filter === 'active' ? ' is-active' : '' }}" href="/admin/owners?status=active">Active</a>
            <a class="tab{{ $filter === 'deactivated' ? ' is-active' : '' }}" href="/admin/owners?status=deactivated">Deactivated</a>
        </div>

        <div class="panel reveal">
            <div class="section-heading">
                <h2>{{ $filter === 'all' ? 'All owner accounts' : ucfirst($filter) . ' owners' }}</h2>
                <span class="helper">{{ $owners->count() }} account{{ $owners->count() === 1 ? '' : 's' }}</span>
            </div>

            @if ($owners->isEmpty())
                <div class="empty-state">
                    <h3>No owners found</h3>
                    <p>Try a different filter.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Owner</th>
                                <th>Contact</th>
                                <th>Cottages</th>
                                <th>Registered</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($owners as $owner)
                                <tr>
                                    <td>#{{ (int) $owner->id }}</td>
                                    <td>{{ $owner->first_name }} {{ $owner->last_name }}</td>
                                    <td>
                                        {{ $owner->email }}<br>
                                        <small class="text-muted">{{ format_phone($owner->phone) }}</small>
                                    </td>
                                    <td>
                                        {{ (int) $owner->active_count }} active<br>
                                        <small class="text-muted">{{ (int) $owner->cottage_count }} total</small>
                                    </td>
                                    <td>{{ format_date($owner->created_at) }}</td>
                                    <td>{!! status_badge($owner->status) !!}</td>
                                    <td>
                                        <div class="table-actions">
                                            @if ($owner->status === 'pending')
                                                <form method="post" action="/admin/owners/action">
                                                    @csrf
                                                    <input type="hidden" name="owner_id" value="{{ (int) $owner->id }}">
                                                    <input type="hidden" name="action" value="approve">
                                                    <input type="hidden" name="return" value="/admin/owners?status={{ $filter }}">
                                                    <button class="btn btn-success" type="submit">Approve</button>
                                                </form>
                                                <form method="post" action="/admin/owners/action"
                                                      data-confirm="Decline this owner account?">
                                                    @csrf
                                                    <input type="hidden" name="owner_id" value="{{ (int) $owner->id }}">
                                                    <input type="hidden" name="action" value="decline">
                                                    <input type="hidden" name="return" value="/admin/owners?status={{ $filter }}">
                                                    <button class="btn btn-danger" type="submit">Decline</button>
                                                </form>
                                            @elseif ($owner->status === 'active')
                                                <form method="post" action="/admin/owners/action"
                                                      data-confirm="Deactivate this owner account?">
                                                    @csrf
                                                    <input type="hidden" name="owner_id" value="{{ (int) $owner->id }}">
                                                    <input type="hidden" name="action" value="deactivate">
                                                    <input type="hidden" name="return" value="/admin/owners?status={{ $filter }}">
                                                    <button class="btn btn-danger" type="submit">Deactivate</button>
                                                </form>
                                            @else
                                                <form method="post" action="/admin/owners/action">
                                                    @csrf
                                                    <input type="hidden" name="owner_id" value="{{ (int) $owner->id }}">
                                                    <input type="hidden" name="action" value="reactivate">
                                                    <input type="hidden" name="return" value="/admin/owners?status={{ $filter }}">
                                                    <button class="btn btn-success" type="submit">Reactivate</button>
                                                </form>
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
