@include('partials.header')

<div class="page-head">
    <div>
        <p class="eyebrow">Reservations</p>
        <h1 class="page-title">Choose a Room</h1>
        <p class="page-sub">Pick the room you want, then set your dates and guests.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/booking">Choose a Cottage</a>
</div>

@if ($rooms->isEmpty())
    <div class="card empty-state">
        <h3>No rooms available</h3>
        <p>Please check back after the resort publishes its rooms.</p>
    </div>
@else
    <div class="cottage-grid">
        @foreach ($rooms as $index => $room)
            @php
                $photo = $room->photo ?: '/assets/img/fallback.svg';
                $amenities = array_filter(array_map('trim', explode(',', (string) ($room->amenities ?? ''))));
            @endphp
            <article class="cottage-card reveal stagger-{{ ($index % 3) + 1 }}">
                <div class="cottage-card__media">
                    <img src="{{ $photo }}" alt="{{ $room->name }}" loading="lazy">
                </div>
                <div class="cottage-card__body">
                    <h3>{{ $room->name }}</h3>
                    <p class="cottage-meta">{!! icon('pin') !!}<span>{{ $room->location ?: 'Oliyette Resort' }}</span></p>
                    <div class="rating-line">
                        <span>Up to {{ (int) $room->capacity }} guests</span>
                    </div>
                    <p class="price">{{ peso($room->rate_per_day) }}<small> / day</small></p>
                </div>
                <div class="cottage-card__foot">
                    <a class="btn btn-primary" href="/booking?room={{ (int) $room->id }}">Select Room</a>
                </div>
            </article>
        @endforeach
    </div>
@endif

@include('partials.footer')
