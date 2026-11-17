</main>

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <a class="brand brand--footer" href="/">
                <img src="/assets/img/logo.svg" alt="" width="32" height="32">
                <span class="brand-text">{{ config('app.name') }}</span>
            </a>
            <p>A web-based reservation and management system for Oliyette Resort, Victoria, Oriental Mindoro.</p>
        </div>

        <div class="footer-col">
            <h2>Visit us</h2>
            <p>Oliyette Resort<br>Victoria, Oriental Mindoro<br>Philippines</p>
            <p>
                <a href="tel:+639170000000">0917 000 0000</a><br>
                <a href="mailto:hello@oliyettebook.test">hello@oliyettebook.test</a>
            </p>
        </div>

        <div class="footer-col">
            <h2>Explore</h2>
            <ul class="footer-links">
                <li><a href="/gallery">Gallery</a></li>
                <li><a href="/contact">Contact</a></li>
                <li><a href="/reviews">Guest reviews</a></li>
                <li><a href="/contact#privacy">Data Privacy notice</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>Built as a PHP + MySQL reservation system.</p>
    </div>
</footer>

<script src="/assets/js/app.js" defer></script>
</body>
</html>
