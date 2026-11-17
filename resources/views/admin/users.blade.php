@include('partials.header')
<div class="app-shell">
    @include('partials.admin_nav')

    <section class="content">
        <div class="page-head">
            <div>
                <p class="eyebrow">Accounts</p>
                <h1 class="page-title">Guest &amp; Owner Accounts</h1>
                <p class="page-sub">Review accounts, deactivate abusive users, or restore access.</p>
            </div>
        </div>

        <form class="filter-bar" method="get" action="/admin/users">
            <div class="field">
                <label for="q">Search</label>
                <input type="search" id="q" name="q" value="{{ $q }}" placeholder="Name, email, or phone">
            </div>
            <div class="field">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="all"{{ $roleFilter === 'all' ? ' selected' : '' }}>All roles</option>
                    <option value="guest"{{ $roleFilter === 'guest' ? ' selected' : '' }}>Guest</option>
                    <option value="owner"{{ $roleFilter === 'owner' ? ' selected' : '' }}>Resort Owner</option>
                </select>
            </div>
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="all"{{ $statusFilter === 'all' ? ' selected' : '' }}>All statuses</option>
                    <option value="active"{{ $statusFilter === 'active' ? ' selected' : '' }}>Active</option>
                    <option value="pending"{{ $statusFilter === 'pending' ? ' selected' : '' }}>Pending</option>
                    <option value="deactivated"{{ $statusFilter === 'deactivated' ? ' selected' : '' }}>Deactivated</option>
                </select>
            </div>
            <button class="btn btn-primary" type="submit">Filter</button>
            @if ($roleFilter !== 'all' || $statusFilter !== 'all' || $q !== '')
                <a class="btn btn-ghost" href="/admin/users">Reset</a>
            @endif
        </form>

        <div class="panel reveal">
            <div class="section-heading">
                <h2>User list</h2>
                <span class="helper">{{ $users->count() }} shown</span>
            </div>

            @if ($users->isEmpty())
                <div class="empty-state">
                    <h3>No users found</h3>
                    <p>Try a different filter or search term.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Bookings</th>
                                <th>Joined</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $account)
                                <tr>
                                    <td>#{{ (int) $account->id }}</td>
                                    <td>{{ $account->first_name }} {{ $account->last_name }}</td>
                                    <td>
                                        {{ $account->email }}<br>
                                        <small class="text-muted">{{ format_phone($account->phone) }}</small>
                                    </td>
                                    <td>{!! status_badge($account->role) !!}</td>
                                    <td>{{ (int) $account->booking_count }}</td>
                                    <td>{{ format_date($account->created_at) }}</td>
                                    <td>{!! status_badge($account->status) !!}</td>
                                    <td>
                                        <div class="table-actions">
                                            @if ($account->status === 'active')
                                                <form method="post" action="/admin/users"
                                                      data-confirm="Deactivate this account?">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ (int) $account->id }}">
                                                    <input type="hidden" name="action" value="deactivate">
                                                    <input type="hidden" name="return" value="{{ $returnUrl }}">
                                                    <button class="btn btn-danger" type="submit">Deactivate</button>
                                                </form>
                                            @else
                                                <form method="post" action="/admin/users">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ (int) $account->id }}">
                                                    <input type="hidden" name="action" value="activate">
                                                    <input type="hidden" name="return" value="{{ $returnUrl }}">
                                                    <button class="btn btn-success" type="submit">Activate</button>
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
