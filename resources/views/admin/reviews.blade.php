@include('partials.header')
<div class="app-shell">
    @include('partials.admin_nav')

    <section class="content">
        <div class="page-head">
            <div>
                <p class="eyebrow">Guest feedback</p>
                <h1 class="page-title">Reviews</h1>
                <p class="page-sub">Review guest feedback and manage what appears publicly.</p>
            </div>
        </div>

        <div class="tabs">
            @foreach (['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'removed' => 'Removed'] as $status => $label)
                <a class="tab{{ $filter === $status ? ' is-active' : '' }}"
                   href="{{ $status === 'all' ? '/admin/reviews' : '/admin/reviews?status=' . $status }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="panel reveal">
            <div class="section-heading">
                <h2>{{ ucfirst($filter) }} reviews</h2>
                <span class="helper">{{ $reviews->count() }} review{{ $reviews->count() === 1 ? '' : 's' }}</span>
            </div>

            @if ($reviews->isEmpty())
                <div class="empty-state">
                    <h3>No reviews found</h3>
                    <p>Reviews that need attention will appear here.</p>
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
                                    <td>{{ $review->first_name }} {{ $review->last_name }}</td>
                                    <td>{{ $review->cottage_name }}</td>
                                    <td>{{ (int) $review->rating }} / 5</td>
                                    <td>{{ $review->comment ?: '—' }}</td>
                                    <td>{{ format_date($review->created_at) }}</td>
                                    <td>{!! status_badge($review->status) !!}</td>
                                    <td>
                                        <div class="table-actions">
                                            @if ($review->status === 'pending')
                                                <form method="post" action="/admin/reviews/action">
                                                    @csrf
                                                    <input type="hidden" name="review_id" value="{{ (int) $review->id }}">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button class="btn btn-success" type="submit">Approve</button>
                                                </form>
                                            @endif
                                            @if (in_array($review->status, ['pending', 'approved'], true))
                                                <form method="post" action="/admin/reviews/action"
                                                      data-confirm="Remove this review from public view?">
                                                    @csrf
                                                    <input type="hidden" name="review_id" value="{{ (int) $review->id }}">
                                                    <input type="hidden" name="action" value="remove">
                                                    <button class="btn btn-danger" type="submit">Remove</button>
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

        <div class="panel reveal">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Resort feedback</p>
                    <h2>Guest comments</h2>
                </div>
                <span class="helper">{{ $feedback->count() }} shown</span>
            </div>

            @if ($feedback->isEmpty())
                <div class="empty-state">
                    <h3>No comments yet</h3>
                    <p>Guest suggestions about the resort will appear here.</p>
                </div>
            @else
                <div class="review-list">
                    @foreach ($feedback as $comment)
                        <article class="review-card">
                            <div class="review-card__head">
                                <div>
                                    <strong>{{ $comment->first_name }} {{ $comment->last_name }}</strong>
                                    <small>{{ format_datetime($comment->created_at) }}</small>
                                </div>
                            </div>
                            <p>{{ $comment->comment }}</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@include('partials.footer')
