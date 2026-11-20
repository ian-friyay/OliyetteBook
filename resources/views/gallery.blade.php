@php $user = auth()->user(); @endphp
@include('partials.header')

<div class="page-head">
    <div>
        <p class="eyebrow">Photo gallery</p>
        <h1 class="page-title">A Look Around the Resort</h1>
        <p class="page-sub">Cottages, gardens, and quiet corners around Oliyette Resort.</p>
    </div>
    @if ($user && $user->role === 'owner')
        <a class="btn btn-primary" href="/profile?tab=cottages">Manage my cottages</a>
    @elseif (! $user || $user->role === 'guest')
        <a class="btn btn-primary" href="/booking">Book a cottage</a>
    @endif
</div>

<div class="gallery-grid">
    @foreach ($photos as $index => $photo)
        <figure class="gallery-item reveal stagger-{{ ($index % 3) + 1 }}">
            <img src="{{ $photo->path }}" alt="{{ $photo->name }}" loading="lazy">
            <figcaption>
                {{ $photo->name }}@if (! empty($photo->location)) · {{ $photo->location }}@endif
            </figcaption>
        </figure>
    @endforeach
</div>

@if ($user && $user->role === 'owner')
    <section class="cta-banner reveal">
        <h2>Keep your listings looking their best.</h2>
        <p>Update cottage details, photos, and rates from your owner dashboard.</p>
        <a class="btn btn-accent btn-lg" href="/profile?tab=cottages">Manage my cottages</a>
    </section>
@elseif (! $user || $user->role === 'guest')
    <section class="cta-banner reveal">
        <h2>Ready to see it in person?</h2>
        <p>Choose a date and reserve the cottage that fits your group.</p>
        <a class="btn btn-accent btn-lg" href="/booking">Check availability</a>
    </section>
@endif

@include('partials.footer')
