@include('partials.header')

<div class="page-head">
    <div>
        <p class="eyebrow">Guest feedback</p>
        <h1 class="page-title">Rate Your Cottage Experience</h1>
        <p class="page-sub">Share a rating after your stay, and tell the resort team what you think.</p>
    </div>
    @if ($role === 'admin')
        <a class="btn btn-ghost btn-sm" href="/admin/reviews">Moderate reviews</a>
    @endif
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
                <p class="eyebrow">Feedback</p>
                <h2>Tell us about your visit</h2>
            </div>
        </div>

        <p class="text-muted">Have a comment or suggestion about the resort? Send it to the resort team. Your feedback stays private.</p>

        <form class="form mt-2" method="post" action="/actions/submit-feedback">
            @csrf
            <div class="field">
                <label for="feedback">Your comment about the resort</label>
                <textarea id="feedback" name="comment" maxlength="2000" required
                          placeholder="What did you enjoy? What could the resort improve?"></textarea>
            </div>

            <button class="btn btn-primary" type="submit">Send Feedback</button>
            <p class="helper">Feedback is sent directly to the resort team.</p>
        </form>
    </aside>
</div>

@include('partials.footer')
