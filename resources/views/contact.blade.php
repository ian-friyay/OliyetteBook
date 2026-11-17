@include('partials.header')

<div class="page-head">
    <div>
        <p class="eyebrow">Get in touch</p>
        <h1 class="page-title">Contact Oliyette Resort</h1>
        <p class="page-sub">Ask about availability, group stays, or anything else about the resort.</p>
    </div>
</div>

<div class="contact-grid">
    <div class="panel reveal">
        <div class="section-heading">
            <h2>Send a message</h2>
        </div>

        <form class="form" method="post" action="/contact" novalidate>
            @csrf

            <div class="form-grid">
                <div class="field">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="{{ $values['name'] }}"
                           maxlength="100" required @if (isset($errors['name'])) aria-invalid="true" @endif>
                    @if (isset($errors['name']))
                        <p class="field-error">{{ $errors['name'] }}</p>
                    @endif
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ $values['email'] }}"
                           autocomplete="email" required @if (isset($errors['email'])) aria-invalid="true" @endif>
                    @if (isset($errors['email']))
                        <p class="field-error">{{ $errors['email'] }}</p>
                    @endif
                </div>
            </div>

            <div class="field">
                <label for="message">Message</label>
                <textarea id="message" name="message" required @if (isset($errors['message'])) aria-invalid="true" @endif>{{ $values['message'] }}</textarea>
                @if (isset($errors['message']))
                    <p class="field-error">{{ $errors['message'] }}</p>
                @endif
            </div>

            <button class="btn btn-primary" type="submit">Send Message</button>
        </form>
    </div>

    <aside class="panel reveal stagger-2">
        <div class="section-heading">
            <h2>Resort details</h2>
        </div>

        <div class="contact-info">
            <div class="contact-item">
                <span class="nav-icon">{!! icon('pin') !!}</span>
                <div>
                    <strong>Address</strong>
                    <p>Oliyette Resort, Victoria, Oriental Mindoro, Philippines</p>
                </div>
            </div>
            <div class="contact-item">
                <span class="nav-icon">{!! icon('phone') !!}</span>
                <div>
                    <strong>Phone</strong>
                    <p><a href="tel:+639170000000">0917 000 0000</a></p>
                </div>
            </div>
            <div class="contact-item">
                <span class="nav-icon">{!! icon('mail') !!}</span>
                <div>
                    <strong>Email</strong>
                    <p><a href="mailto:hello@oliyettebook.test">hello@oliyettebook.test</a></p>
                </div>
            </div>
            <div class="contact-item">
                <span class="nav-icon">{!! icon('clock') !!}</span>
                <div>
                    <strong>Front desk</strong>
                    <p>Daily, 7:00 AM – 7:00 PM</p>
                </div>
            </div>
        </div>
    </aside>
</div>

<section class="panel reveal privacy-note" id="privacy">
    <h2>Data Privacy Notice</h2>
    <p class="text-muted">OliyetteBook collects your name, email address, phone number, and reservation details only to manage your account and cottage booking. Passwords are stored as one-way hashes. We do not sell or share your information with third parties.</p>
    <p class="text-muted">You may ask the resort administrator to correct or delete your personal information by contacting us through this page. Inquiries are kept only as long as needed to respond to your message. This notice follows the Data Privacy Act of 2012 (RA 10173).</p>
    <p class="text-muted">By creating an account you consent to this collection and use of your information for reservation and resort management purposes.</p>
</section>

@include('partials.footer')
