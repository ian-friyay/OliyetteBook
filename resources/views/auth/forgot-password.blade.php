@include('partials.header')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="/assets/img/logo.svg" alt="{{ config('app.name') }} logo">
        </div>
        <h1 class="auth-title">Forgot Password</h1>
        <p class="auth-sub">Enter the email on your account and we will send a reset link.</p>

        @if ($done)
            <p class="form-success mb-2" role="status">
                If an active account uses that email, a reset link has been sent. The link expires in one hour.
            </p>
            <a class="btn btn-primary btn-block" href="/login">Back to Log In</a>
        @else
            @if (! empty($errors['email']))
                <p class="form-error mb-2" role="alert">{{ $errors['email'] }}</p>
            @endif

            <form class="form" method="post" action="/forgot-password" novalidate>
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ $email }}"
                           autocomplete="email" required @if (isset($errors['email'])) aria-invalid="true" @endif>
                </div>
                <button class="btn btn-primary btn-block" type="submit">Send Reset Link</button>
            </form>

            <div class="auth-footer">
                <a href="/login">Back to Log In</a>
            </div>
        @endif
    </div>
</div>
@include('partials.footer')
