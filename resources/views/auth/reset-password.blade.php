@include('partials.header')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="/assets/img/logo.svg" alt="{{ config('app.name') }} logo">
        </div>

        @if ($showPasswordForm)
            <h1 class="auth-title">Choose a New Password</h1>
            <p class="auth-sub">Use at least 8 characters. Your verified code can only be used once.</p>

            @if (! empty($errors['form']))
                <p class="form-error mb-2" role="alert">{{ $errors['form'] }}</p>
            @endif

            <form class="form" method="post" action="/reset-password" novalidate>
                @csrf
                <div class="field">
                    <label for="password">New Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" autocomplete="new-password"
                               minlength="8" required @if (isset($errors['password'])) aria-invalid="true" @endif>
                        {!! password_toggle() !!}
                    </div>
                    @if (isset($errors['password']))
                        <p class="field-error">{{ $errors['password'] }}</p>
                    @endif
                </div>

                <div class="field">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="password-field">
                        <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password"
                               minlength="8" required @if (isset($errors['confirm_password'])) aria-invalid="true" @endif>
                        {!! password_toggle() !!}
                    </div>
                    @if (isset($errors['confirm_password']))
                        <p class="field-error">{{ $errors['confirm_password'] }}</p>
                    @endif
                </div>

                <button class="btn btn-primary btn-block" type="submit">Save New Password</button>
            </form>
        @else
            <h1 class="auth-title">Verify Reset Code</h1>
            <p class="auth-sub">Enter the email address that received your six-digit code.</p>

            @if (! empty($errors['form']))
                <p class="form-error mb-2" role="alert">{{ $errors['form'] }}</p>
            @endif

            <form class="form" method="post" action="/reset-password" novalidate>
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ $email }}"
                           autocomplete="email" required @if (isset($errors['email'])) aria-invalid="true" @endif>
                </div>
                <div class="field">
                    <label for="code">Reset Code</label>
                    <input type="text" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}"
                           minlength="6" maxlength="6" autocomplete="one-time-code" required>
                </div>
                <button class="btn btn-primary btn-block" type="submit">Verify Code</button>
            </form>
        @endif
    </div>
</div>
@include('partials.footer')
