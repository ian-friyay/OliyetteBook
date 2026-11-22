@include('partials.header')
<div class="auth-wrap">
    <div class="auth-card auth-card--wide">
        <a class="auth-home" href="/">&larr; Home</a>
        <div class="auth-logo">
            <img src="/assets/img/logo.svg" alt="{{ config('app.name') }} logo">
        </div>
        <h1 class="auth-title">Create Your Account</h1>
        <p class="auth-sub">Join as a guest to book cottages, or as a resort owner to list your cottages.</p>

        @if (! empty($errors['form']))
            <p class="form-error mb-2" role="alert">{{ $errors['form'] }}</p>
        @endif

        <form class="form" method="post" action="/signup" novalidate>
            @csrf

            <div class="field">
                <span class="field-label">I am signing up as</span>
                <div class="role-toggle" role="radiogroup" aria-label="Account type">
                    <label>
                        <input type="radio" name="role" value="guest" @checked($values['role'] === 'guest')>
                        <span>Guest</span>
                    </label>
                    <label>
                        <input type="radio" name="role" value="owner" @checked($values['role'] === 'owner')>
                        <span>Owner</span>
                    </label>
                </div>
                <p class="helper">Owner accounts are reviewed by the resort admin before they become active.</p>
            </div>

            <div class="form-grid">
                <div class="field">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="{{ $values['first_name'] }}"
                           autocomplete="given-name" maxlength="60" required @if (isset($errors['first_name'])) aria-invalid="true" @endif>
                    @if (isset($errors['first_name']))
                        <p class="field-error">{{ $errors['first_name'] }}</p>
                    @endif
                </div>
                <div class="field">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="{{ $values['last_name'] }}"
                           autocomplete="family-name" maxlength="60" required @if (isset($errors['last_name'])) aria-invalid="true" @endif>
                    @if (isset($errors['last_name']))
                        <p class="field-error">{{ $errors['last_name'] }}</p>
                    @endif
                </div>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ $values['email'] }}"
                       autocomplete="email" required @if (isset($errors['email'])) aria-invalid="true" @endif>
                @if (isset($errors['email']))
                    <p class="field-error">{{ $errors['email'] }}</p>
                @endif
            </div>

            <div class="field">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="{{ $values['phone'] }}"
                       placeholder="09XX XXX XXXX" autocomplete="tel" data-phone required @if (isset($errors['phone'])) aria-invalid="true" @endif>
                @if (isset($errors['phone']))
                    <p class="field-error">{{ $errors['phone'] }}</p>
                @endif
            </div>

            <div class="form-grid">
                <div class="field">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" autocomplete="new-password"
                               minlength="8" required @if (isset($errors['password'])) aria-invalid="true" @endif>
                        {!! password_toggle() !!}
                    </div>
                    @if (isset($errors['password']))
                        <p class="field-error">{{ $errors['password'] }}</p>
                    @else
                        <p class="helper">At least 8 characters.</p>
                    @endif
                </div>
                <div class="field">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-field">
                        <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password"
                               minlength="8" required @if (isset($errors['confirm_password'])) aria-invalid="true" @endif>
                        {!! password_toggle() !!}
                    </div>
                    @if (isset($errors['confirm_password']))
                        <p class="field-error">{{ $errors['confirm_password'] }}</p>
                    @endif
                </div>
            </div>

            <div class="field">
                <label class="checkbox{{ isset($errors['consent']) ? ' is-invalid' : '' }}">
                    <input type="checkbox" name="consent" value="1" required @checked(request()->has('consent'))>
                    <span>I agree to the <a href="/contact#terms">Terms of Service</a> and <a href="/contact#privacy">Privacy Policy</a>.</span>
                </label>
                @if (isset($errors['consent']))
                    <p class="field-error">{{ $errors['consent'] }}</p>
                @endif
            </div>

            <button class="btn btn-primary btn-block btn-lg" type="submit">Create Account</button>
        </form>

    </div>
</div>
@include('partials.footer')
