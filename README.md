<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling, easing common tasks in many web projects.

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for session and cache storage.
- Expressive, intuitive ORM.
- Database agnostic migrations.
- Real-time event broadcasting.

## Learning Laravel

See the [Laravel documentation](https://laravel.com/docs), [Laravel Bootcamp](https://bootcamp.laravel.com), or [Laracasts](https://laracasts.com).

## Laravel Sponsors

Thanks to the Laravel sponsors, including [Vehikl](https://vehikl.com), [Tighten](https://tighten.co), [WebReinvent](https://webreinvent.com), [64 Robots](https://64robots.com), [Curotec](https://curotec.com), [Cyber-Duck](https://cyber-duck.co.uk), [DevSquad](https://devsquad.com), [Jump24](https://jump24.com), [Redberry](https://redberry.international/laravel), [Active Logic](https://activelogic.com), [byte5](https://byte5.de), and [OP.GG](https://op.gg).

## Contributing

Thank you for considering contributing to Laravel. See the [contribution guide](https://laravel.com/docs/contributions).

## Code of Conduct

Please review and follow the [Laravel Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

For Laravel security vulnerabilities, contact Taylor Otwell at [taylor@laravel.com](mailto:taylor@laravel.com).

## Deploying to Render

### SMTP for password resets

The Render Blueprint configures SendGrid SMTP over STARTTLS on port 2525. Render's [Free web services block outbound ports 25, 465, and 587](https://render.com/docs/free#other-limitations), so port 2525 allows SMTP without changing the service to a paid plan. After syncing the Blueprint, set `MAIL_PASSWORD` to a SendGrid API key with Mail Send permission and `MAIL_FROM_ADDRESS` to a sender verified with SendGrid in the Render service's Environment settings. The username is `apikey`. Do not put a Gmail password or Google App Password in Render's `MAIL_PASSWORD`; Render is configured for SendGrid. The Blueprint keeps the key and sender address out of source control. For another SMTP provider, update `MAIL_HOST`, `MAIL_PORT`, `MAIL_SCHEME`, and credentials to match its settings and ensure its port is allowed by your Render plan. For local testing, use the Laravel mail log instead.

For local Gmail SMTP, set `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`, `MAIL_USERNAME` to the full Gmail address, and `MAIL_FROM_ADDRESS` to that same address (or a verified Gmail alias). Enable 2-Step Verification and generate an App Password in your Google Account; use that 16-character App Password as `MAIL_PASSWORD`, not your normal Google password. If Laravel logs `535 5.7.8 Username and Password not accepted`, Google rejected the credentials. Replace the App Password, then restart the app or clear/rebuild Laravel's config cache so it reads the updated environment.

The Render entrypoint creates or updates the demo administrator after every migration:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@oliyettebook.test` | `Admin@123` |

These values are configurable in `render.yaml` with `DEMO_ADMIN_EMAIL` and `DEMO_ADMIN_PASSWORD`.

This repository includes a Render Blueprint and Dockerfile. Create a Blueprint from `render.yaml`; Render will build the app, run migrations, and safely insert the sample cottage and room listings when the service starts. The listings seeders only add records with missing codes and do not truncate production data. The Blueprint references the existing `palaycheck-db` PostgreSQL instance and creates a separate `oliyettebook` schema for this app.

The included Blueprint uses Render's free web-service plan for preview/testing only. Free web services can spin down while idle, and the shared free PostgreSQL database expires after 30 days and does not include backups. Local uploads are also not persistent across redeploys. Do not use this configuration for real bookings or customer information; configure paid persistent storage and a durable database before production use.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
