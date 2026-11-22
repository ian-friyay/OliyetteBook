@include('partials.header', ['pageTitle' => 'Page not found'])
<div class="auth-wrap">
    <section class="auth-card" aria-labelledby="error-title">
        <p class="eyebrow">404 · Page not found</p>
        <h1 class="auth-title" id="error-title">We could not find that page.</h1>
        <p class="auth-sub">The address may have changed, or the page may no longer be available.</p>
        <a class="btn btn-primary btn-block" href="/">Go to Home</a>
    </section>
</div>
@include('partials.footer')
