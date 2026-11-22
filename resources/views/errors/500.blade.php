@include('partials.header', ['pageTitle' => 'Something went wrong'])
<div class="auth-wrap">
    <section class="auth-card" aria-labelledby="error-title">
        <p class="eyebrow">500 · Server error</p>
        <h1 class="auth-title" id="error-title">We could not complete that request.</h1>
        <p class="auth-sub">The resort team has been notified. Please try again in a little while.</p>
        <a class="btn btn-primary btn-block" href="/">Go to Home</a>
    </section>
</div>
@include('partials.footer')
