@include('partials.header', ['pageTitle' => 'Access denied'])
<div class="auth-wrap">
    <section class="auth-card" aria-labelledby="error-title">
        <p class="eyebrow">403 · Access denied</p>
        <h1 class="auth-title" id="error-title">You do not have permission to view this page.</h1>
        <p class="auth-sub">Sign in with an account that has access, or return to the home page.</p>
        <a class="btn btn-primary btn-block" href="/">Go to Home</a>
    </section>
</div>
@include('partials.footer')
