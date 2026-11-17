@include('partials.header')
<div class="app-shell">
    @include('partials.admin_nav')

    <section class="content">
        <div class="page-head">
            <div>
                <p class="eyebrow">Accommodations</p>
                <h1 class="page-title">Cottage Management</h1>
                <p class="page-sub">Add cottages, assign owners, change rates, and publish or hide listings.</p>
            </div>
        </div>

        @if (!empty($errors['form']))
            <p class="form-error" role="alert">{{ $errors['form'] }}</p>
        @endif

        <div class="panel reveal">
            <div class="section-heading">
                <h2>{{ $formValues['id'] > 0 ? 'Edit cottage #' . (int) $formValues['id'] : 'Add a cottage' }}</h2>
                @if ($formValues['id'] > 0)
                    <a class="btn btn-ghost btn-sm" href="/admin/cottages">Cancel edit</a>
                @endif
            </div>

            <form class="form" method="post" action="/admin/cottages" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="form" value="cottage">
                <input type="hidden" name="cottage_id" value="{{ (int) $formValues['id'] }}">

                <div class="form-grid">
                    <div class="field">
                        <label for="name">Cottage Name</label>
                        <input type="text" id="name" name="name" value="{{ $formValues['name'] }}"
                               maxlength="100" required{{ isset($errors['name']) ? ' aria-invalid="true"' : '' }}>
                        @if (isset($errors['name']))
                            <p class="field-error">{{ $errors['name'] }}</p>
                        @endif
                    </div>
                    <div class="field">
                        <label for="owner_id">Owner</label>
                        <select id="owner_id" name="owner_id"{{ isset($errors['owner_id']) ? ' aria-invalid="true"' : '' }}>
                            <option value="">Unassigned</option>
                            @foreach ($owners as $owner)
                                <option value="{{ (int) $owner->id }}"{{ (string) $formValues['owner_id'] === (string) $owner->id ? ' selected' : '' }}>
                                    {{ $owner->first_name }} {{ $owner->last_name }} ({{ $owner->email }})
                                </option>
                            @endforeach
                        </select>
                        @if (isset($errors['owner_id']))
                            <p class="field-error">{{ $errors['owner_id'] }}</p>
                        @endif
                    </div>
                </div>

                <div class="form-grid">
                    <div class="field">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" value="{{ $formValues['location'] }}"
                               maxlength="100"{{ isset($errors['location']) ? ' aria-invalid="true"' : '' }}>
                        @if (isset($errors['location']))
                            <p class="field-error">{{ $errors['location'] }}</p>
                        @endif
                    </div>
                    <div class="field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="pending"{{ $formValues['status'] === 'pending' ? ' selected' : '' }}>Pending</option>
                            <option value="active"{{ $formValues['status'] === 'active' ? ' selected' : '' }}>Active</option>
                            <option value="hidden"{{ $formValues['status'] === 'hidden' ? ' selected' : '' }}>Hidden</option>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label for="description">Description</label>
                    <textarea id="description" name="description">{{ $formValues['description'] }}</textarea>
                </div>

                <div class="form-grid">
                    <div class="field">
                        <label for="capacity">Capacity</label>
                        <input type="number" id="capacity" name="capacity" min="1" max="50"
                               value="{{ (int) $formValues['capacity'] }}" required{{ isset($errors['capacity']) ? ' aria-invalid="true"' : '' }}>
                        @if (isset($errors['capacity']))
                            <p class="field-error">{{ $errors['capacity'] }}</p>
                        @endif
                    </div>
                    <div class="field">
                        <label for="rate_per_day">Rate per Day (₱)</label>
                        <input type="number" id="rate_per_day" name="rate_per_day" min="1" step="0.01"
                               value="{{ (string) $formValues['rate_per_day'] }}" required{{ isset($errors['rate_per_day']) ? ' aria-invalid="true"' : '' }}>
                        @if (isset($errors['rate_per_day']))
                            <p class="field-error">{{ $errors['rate_per_day'] }}</p>
                        @endif
                    </div>
                </div>

                <div class="field">
                    <label for="amenities">Amenities</label>
                    <input type="text" id="amenities" name="amenities" value="{{ $formValues['amenities'] }}"
                           placeholder="Air conditioning, Wi-Fi, Beach access">
                </div>

                <div class="field">
                    <label for="photo">Cottage Photo</label>
                    <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
                    <p class="helper">JPG, PNG, WEBP, or GIF. Maximum 3 MB.</p>
                    @if (isset($errors['photo']))
                        <p class="field-error">{{ $errors['photo'] }}</p>
                    @endif
                    @if ($currentPhoto)
                        <img class="mt-1" src="{{ $currentPhoto }}" alt="Current cottage photo"
                             style="max-width:220px;border-radius:12px;">
                    @endif
                </div>

                <button class="btn btn-primary" type="submit">
                    {{ $formValues['id'] > 0 ? 'Save Changes' : 'Create Cottage' }}
                </button>
            </form>
        </div>

        <div class="tabs">
            <a class="tab{{ $filter === 'all' ? ' is-active' : '' }}" href="/admin/cottages">All</a>
            <a class="tab{{ $filter === 'pending' ? ' is-active' : '' }}" href="/admin/cottages?status=pending">Pending</a>
            <a class="tab{{ $filter === 'active' ? ' is-active' : '' }}" href="/admin/cottages?status=active">Active</a>
            <a class="tab{{ $filter === 'hidden' ? ' is-active' : '' }}" href="/admin/cottages?status=hidden">Hidden</a>
        </div>

        <div class="panel reveal">
            <div class="section-heading">
                <h2>{{ $filter === 'all' ? 'All cottages' : ucfirst($filter) . ' cottages' }}</h2>
                <span class="helper">{{ $cottages->count() }} listing{{ $cottages->count() === 1 ? '' : 's' }}</span>
            </div>

            @if ($cottages->isEmpty())
                <div class="empty-state">
                    <h3>No cottages found</h3>
                    <p>Try a different filter or create a cottage above.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Cottage</th>
                                <th>Owner</th>
                                <th>Rate</th>
                                <th>Bookings</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cottages as $cottage)
                                <tr>
                                    <td>{{ $cottage->code }}</td>
                                    <td>
                                        {{ $cottage->name }}<br>
                                        <small class="text-muted">{{ $cottage->location ?: '—' }}</small>
                                    </td>
                                    <td>
                                        @if (!empty($cottage->first_name))
                                            {{ $cottage->first_name }} {{ $cottage->last_name }}
                                        @else
                                            <span class="text-muted">Unassigned</span>
                                        @endif
                                    </td>
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
                                            <a class="btn btn-ghost" href="/admin/cottages?edit={{ (int) $cottage->id }}">Edit</a>
                                            @if ($cottage->status !== 'active')
                                                <form method="post" action="/admin/cottages?status={{ $filter }}">
                                                    @csrf
                                                    <input type="hidden" name="form" value="status">
                                                    <input type="hidden" name="cottage_id" value="{{ (int) $cottage->id }}">
                                                    <input type="hidden" name="status" value="active">
                                                    <button class="btn btn-success" type="submit">Activate</button>
                                                </form>
                                            @endif
                                            @if ($cottage->status !== 'hidden')
                                                <form method="post" action="/admin/cottages?status={{ $filter }}"
                                                      data-confirm="Hide this cottage from the Home page?">
                                                    @csrf
                                                    <input type="hidden" name="form" value="status">
                                                    <input type="hidden" name="cottage_id" value="{{ (int) $cottage->id }}">
                                                    <input type="hidden" name="status" value="hidden">
                                                    <button class="btn btn-danger" type="submit">Hide</button>
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
