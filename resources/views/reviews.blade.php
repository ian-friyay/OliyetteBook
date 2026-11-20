@include('partials.header')

@if ($role === 'owner')
    <div class="page-head">
        <div>
            <p class="eyebrow">Resort owner</p>
            <h1 class="page-title">User Reviews</h1>
            <p class="page-sub">Read approved guest reviews and comments about the resort.</p>
        </div>
        <a class="btn btn-ghost btn-sm" href="/profile?tab=cottages">My cottages</a>
    </div>

    <div class="panel reveal">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Approved</p>
                <h2>What guests are saying</h2>
            </div>
            <span class="helper">{{ $ownerReviews->count() }} shown</span>
        </div>

        @if ($ownerReviews->isEmpty())
            <div class="empty-state">
                <h3>No approved reviews yet</h3>
                <p>Guest reviews will appear here after the admin approves them.</p>
            </div>
        @else
            <div class="review-list">
                @foreach ($ownerReviews as $review)
                    <article class="review-card">
                        <div class="review-card__head">
                            <div>
                                <strong>{{ $review->first_name }} {{ $review->last_name }}</strong>
                                <small>{{ $review->cottage_name }} · {{ format_datetime($review->created_at) }}</small>
                            </div>
                            {!! star_html((float) $review->rating) !!}
                        </div>
                        <p>{{ $review->comment ?: 'No written comment.' }}</p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    <div class="panel reveal">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Comments</p>
                <h2>About the resort</h2>
            </div>
            <span class="helper">{{ $feedback->count() }} shown</span>
        </div>

        @if ($feedback->isEmpty())
            <div class="empty-state">
                <h3>No comments yet</h3>
                <p>Guest comments about the resort will appear here.</p>
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
@else
    <div class="page-head">
        <div>
            <p class="eyebrow">User reviews</p>
            <h1 class="page-title">Rate Your Cottage Experience</h1>
            <p class="page-sub">Share a rating after your stay, and tell the resort team what you think.</p>
        </div>
    </div>

    <div class="booking-layout">
        <div class="panel reveal">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Write a review</p>
                    <h2>How was your stay?</h2>
                </div>
            </div>

            @if (count($eligible) === 0)
                <div class="empty-state">
                    <h3>No stays waiting for a review</h3>
                    <p>Reviews can be written after a booking is marked completed by the resort admin.</p>
                    <a class="btn btn-primary btn-sm" href="/profile?tab=bookings">View my bookings</a>
                </div>
            @else
                <form class="form" method="post" action="/actions/submit-review">
                    @csrf

                    @if ($selectedBooking)
                        <input type="hidden" name="booking_id" value="{{ (int) $selectedBooking->id }}">
                        <div class="price-breakdown">
                            <div class="summary-list__row"><span>Cottage</span><span>{{ $selectedBooking->cottage_name }}</span></div>
                            <div class="summary-list__row"><span>Receipt</span><span>{{ $selectedBooking->receipt_no }}</span></div>
                            <div class="summary-list__row"><span>Stay</span><span>{{ format_date($selectedBooking->check_in) }} – {{ format_date($selectedBooking->check_out) }}</span></div>
                        </div>
                    @else
                        <div class="field">
                            <label for="booking_id">Which stay are you reviewing?</label>
                            <select id="booking_id" name="booking_id" required>
                                <option value="">Choose a completed booking</option>
                                @foreach ($eligible as $booking)
                                    <option value="{{ (int) $booking->id }}">
                                        {{ $booking->cottage_name }} · {{ format_date($booking->check_in) }} ({{ $booking->receipt_no }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="field">
                        <span class="field-label">Rating</span>
                        <div class="rating-input" role="radiogroup" aria-label="Star rating">
                            @for ($i = 5; $i >= 1; $i--)
                                <input type="radio" id="rating-{{ $i }}" name="rating" value="{{ $i }}" required>
                                <label for="rating-{{ $i }}" title="{{ $i }} star{{ $i === 1 ? '' : 's' }}">
                                    <span class="sr-only">{{ $i }} star{{ $i === 1 ? '' : 's' }}</span>
                                    {!! icon('star') !!}
                                </label>
                            @endfor
                        </div>
                        <p class="helper">Choose 1 to 5 stars.</p>
                    </div>

                    <div class="field">
                        <label for="comment">Review (optional)</label>
                        <textarea id="comment" name="comment" maxlength="2000"
                                  placeholder="What did you enjoy? What could be better?"></textarea>
                    </div>

                    <div class="booking-actions">
                        <button class="btn btn-primary" type="submit">Submit Rating</button>
                        <a class="btn btn-secondary" href="/profile?tab=bookings">Cancel</a>
                    </div>
                    <p class="helper">Reviews are checked by the admin before they appear publicly.</p>
                </form>
            @endif
        </div>

        <aside class="panel reveal stagger-2">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Comments</p>
                    <h2>Tell us about your visit</h2>
                </div>
            </div>

            <p class="text-muted">Have a comment or suggestion about the resort? Send it to the resort owner and team.</p>

            <form class="form mt-2" method="post" action="/actions/submit-feedback">
                @csrf
                <div class="field">
                    <label for="feedback">Your comment about the resort</label>
                    <textarea id="feedback" name="comment" maxlength="2000" required
                              placeholder="What did you enjoy? What could the resort improve?"></textarea>
                </div>

                <button class="btn btn-primary" type="submit">Send Comment</button>
                <p class="helper">Comments are shared with the resort owner and team.</p>
            </form>
        </aside>
    </div>
@endif

@include('partials.footer')
