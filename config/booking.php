<?php

return [
    'entrance_fee_per_adult' => 100.00,
    'max_stay_nights' => 30,
    'max_photo_bytes' => 3 * 1024 * 1024,
    'login_max_attempts' => 5,
    'login_lock_minutes' => 15,
    'upload_url' => '/uploads',
    'upload_dir' => public_path('uploads'),
    'mail_from' => env('MAIL_FROM_ADDRESS', env('MAIL_FROM', 'no-reply@oliyettebook.local')),
    'mail_driver' => env('MAIL_DRIVER', env('MAIL_MAILER', 'log')),
    'mail_log' => storage_path('logs/mail.log'),
];
