@php $user = auth()->user(); @endphp
@include('partials.header')

<section class="home-hero">
    <div class="reveal">
        <p class="eyebrow">Oliyette Resort · Victoria, Oriental Mindoro</p>
        <h1>A quieter kind of getaway by the sea.</h1>
        <p>{{ $user && $user->role === 'owner' ? 'Manage your cottages, keep track of reservations, and read what guests are saying about the resort.' : 'Choose a cottage, pick your dates, and let the resort team confirm your stay. Clear prices, real availability, and a simple booking flow from start to finish.' }}</p>
        <div class="hero-actions">
            @if ($user && $user->role === 'owner')
                <a class="btn btn-primary btn-lg" href="/profile?tab=cottages">Manage my cottages</a>
                <a class="btn btn-ghost btn-lg" href="/reviews">User reviews</a>
            @elseif ($user)
                <a class="btn btn-primary btn-lg" href="/booking">Book a cottage</a>
                <a class="btn btn-ghost btn-lg" href="/profile">My bookings</a>
            @else
                <a class="btn btn-ghost btn-lg" href="#cottages">Explore cottages</a>
            @endif
        </div>
    </div>
    <div class="hero-art reveal stagger-2">
        <img src="/assets/img/hero.jpg" alt="Resort pool with colorful pinwheel decorations">
    </div>
</section>

<section id="cottages">
    <div class="section-heading reveal">
        <div>
            <p class="eyebrow">Stay with us</p>
            <h2>Cottages ready for your dates</h2>
        </div>
    </div>

    @if ($cottages->isEmpty())
        <div class="card empty-state reveal">
            <h3>No cottages available yet</h3>
            <p>The resort is preparing its listings. Please check back soon.</p>
        </div>
    @else
        <div class="cottage-grid">
            @foreach ($cottages as $index => $cottage)
                @php
                    $photo = $cottage->photo ?: '/assets/img/fallback.svg';
                    $rating = (float) $cottage->avg_rating;
                    $reviewCount = (int) $cottage->review_count;
                @endphp
                <article class="cottage-card reveal stagger-{{ ($index % 3) + 1 }}"
                         @if ($index >= 3) data-cottage-extra hidden @endif>
                    <div class="cottage-card__media">
                        <img src="{{ $photo }}" alt="{{ $cottage->name }}" loading="lazy">
                    </div>
                    <div class="cottage-card__body">
                        <h3>{{ $cottage->name }}</h3>
                        <p class="cottage-meta">
                            {!! icon('pin') !!}
                            <span>{{ $cottage->location ?: 'Oliyette Resort' }}</span>
                        </p>
                        <div class="rating-line">
                            <span>Up to {{ (int) $cottage->capacity }} guests</span>
                            @if ($reviewCount > 0)
                                {!! star_html($rating, $reviewCount) !!}
                                <span>{{ number_format($rating, 1) }} ({{ $reviewCount }})</span>
                            @else
                                <span>No reviews yet</span>
                            @endif
                        </div>
                        <p class="price">{{ peso($cottage->rate_per_day) }}<small> / day</small></p>
                    </div>
                    <div class="cottage-card__foot">
                        @if ($user && $user->role === 'owner')
                            @if ((int) ($cottage->owner_id ?? 0) === (int) $user->id)
                                <a class="btn btn-ghost" href="/profile?tab=cottages&edit={{ (int) $cottage->id }}">Manage cottage</a>
                            @endif
                        @else
                            <a class="btn btn-primary" href="/booking?cottage={{ (int) $cottage->id }}">Select Cottage</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        @if ($cottages->count() > 3)
            <div class="cottage-more">
                <button class="btn btn-ghost" type="button" data-cottage-more aria-expanded="false">
                    View more cottages
                </button>
            </div>
        @endif
    @endif
</section>

@if (! $user)
    <section class="cta-banner reveal">
        <h2>Get Ready to Start Your Journey!</h2>
        <p>Create a free guest account to reserve a cottage, track your booking status, and share a review after your stay.</p>
        <a class="btn btn-accent btn-lg" href="/login">Get Started</a>
    </section>
@endif

@include('partials.footer')
