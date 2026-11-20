@include('partials.header')
<div class="auth-wrap">
    <div class="auth-card">
        <a class="auth-home" href="/">&larr; Home</a>
        <div class="auth-logo">
            <img src="/assets/img/logo.svg" alt="{{ config('app.name') }} logo">
        </div>
        <h1 class="auth-title">Welcome Back</h1>
        <p class="auth-sub">Log in to manage your cottage reservations.</p>

        @if (! empty($errors['form']))
            <p class="form-error mb-2" role="alert">{{ $errors['form'] }}</p>
        @endif

        <form class="form" method="post" action="/login" novalidate>
            @csrf
            <input type="hidden" name="next" value="{{ $next }}">

            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ $email }}" autocomplete="email"
                       required @if (isset($errors['email'])) aria-invalid="true" @endif>
                @if (isset($errors['email']))
                    <p class="field-error">{{ $errors['email'] }}</p>
                @endif
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="password-field">
                    <input type="password" id="password" name="password" autocomplete="current-password"
                           required @if (isset($errors['password'])) aria-invalid="true" @endif>
                    {!! password_toggle() !!}
                </div>
                @if (isset($errors['password']))
                    <p class="field-error">{{ $errors['password'] }}</p>
                @endif
            </div>

            <div class="remember-row">
                <label class="checkbox">
                    <input type="checkbox" name="remember" value="1">
                    <span>Remember me</span>
                </label>
                <a href="/forgot-password">Forgot password?</a>
            </div>

            <button class="btn btn-primary btn-block" type="submit">Log In</button>
        </form>

    </div>
</div>
@include('partials.footer')
