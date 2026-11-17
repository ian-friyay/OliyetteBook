@include('partials.header')
<div class="app-shell">
    @include('partials.admin_nav')

    <section class="content">
        <div class="page-head">
            <div>
                <p class="eyebrow">Moderation</p>
                <h1 class="page-title">Guest Reviews</h1>
                <p class="page-sub">Approve genuine reviews before they appear on the Home page, or remove inappropriate ones.</p>
            </div>
        </div>

        <div class="tabs">
            <a class="tab{{ $filter === 'pending' ? ' is-active' : '' }}" href="/admin/reviews?status=pending">Pending</a>
            <a class="tab{{ $filter === 'approved' ? ' is-active' : '' }}" href="/admin/reviews?status=approved">Approved</a>
            <a class="tab{{ $filter === 'removed' ? ' is-active' : '' }}" href="/admin/reviews?status=removed">Removed</a>
            <a class="tab{{ $filter === 'all' ? ' is-active' : '' }}" href="/admin/reviews?status=all">All</a>
        </div>

        <div class="panel reveal">
            <div class="section-heading">
                <h2>{{ $filter === 'all' ? 'All reviews' : ucfirst($filter) . ' reviews' }}</h2>
                <span class="helper">{{ $reviews->count() }} shown</span>
            </div>

            @if ($reviews->isEmpty())
                <div class="empty-state">
                    <h3>No reviews here</h3>
                    <p>Try a different filter.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Guest</th>
                                <th>Cottage</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reviews as $review)
                                <tr>
                                    <td>
                                        {{ $review->first_name }} {{ $review->last_name }}<br>
                                        <small class="text-muted">{{ $review->email }}</small>
                                    </td>
                                    <td>{{ $review->cottage_name }}</td>
                                    <td>{!! star_html((float) $review->rating) !!}</td>
                                    <td style="max-width:340px;">{{ $review->comment ?: 'No written comment.' }}</td>
                                    <td>{{ format_datetime($review->created_at) }}</td>
                                    <td>{!! status_badge($review->status) !!}</td>
                                    <td>
                                        <div class="table-actions">
                                            @if ($review->status === 'pending')
                                                <form method="post" action="/admin/reviews">
                                                    @csrf
                                                    <input type="hidden" name="review_id" value="{{ (int) $review->id }}">
                                                    <input type="hidden" name="action" value="approve">
                                                    <input type="hidden" name="return" value="{{ $returnUrl }}">
                                                    <button class="btn btn-success" type="submit">Approve</button>
                                                </form>
                                            @endif
                                            @if ($review->status !== 'removed')
                                                <form method="post" action="/admin/reviews"
                                                      data-confirm="Remove this review?">
                                                    @csrf
                                                    <input type="hidden" name="review_id" value="{{ (int) $review->id }}">
                                                    <input type="hidden" name="action" value="remove">
                                                    <input type="hidden" name="return" value="{{ $returnUrl }}">
                                                    <button class="btn btn-danger" type="submit">Remove</button>
                                                </form>
                                            @else
                                                <form method="post" action="/admin/reviews">
                                                    @csrf
                                                    <input type="hidden" name="review_id" value="{{ (int) $review->id }}">
                                                    <input type="hidden" name="action" value="pending">
                                                    <input type="hidden" name="return" value="{{ $returnUrl }}">
                                                    <button class="btn btn-ghost" type="submit">Restore</button>
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
