<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;

class MailConfigurator
{
    /**
     * Returns the mailer name to send through: a freshly-configured
     * 'dynamic' SMTP mailer sourced from the admin's Settings, or the app's
     * .env default (typically 'log') if no SMTP host has been configured
     * yet — a safe no-op rather than a crash.
     *
     * Defining the mailer at runtime (rather than reading .env at boot)
     * works because every web request is a fresh PHP process here — there's
     * no persistent state to worry about going stale between requests.
     */
    public function mailer(): string
    {
        $settings = Setting::current();

        if (blank($settings->smtp_host)) {
            return config('mail.default');
        }

        Config::set('mail.mailers.dynamic', [
            'transport' => 'smtp',
            'host' => $settings->smtp_host,
            'port' => $settings->smtp_port ?: 587,
            'username' => $settings->smtp_username,
            'password' => $settings->smtp_password,
            'encryption' => $settings->smtp_encryption ?: null,
        ]);

        Config::set('mail.from.address', $settings->mail_from_address ?: $settings->contact_email ?: 'hello@example.com');
        Config::set('mail.from.name', $settings->mail_from_name ?: config('app.name'));

        return 'dynamic';
    }

    /**
     * Where the admin's copy of each notification goes — the dedicated
     * notification inbox if set, otherwise the publicly-displayed contact
     * email as a reasonable fallback. Null if neither is configured, in
     * which case callers should skip sending the admin copy entirely.
     */
    public function notificationEmail(): ?string
    {
        $settings = Setting::current();

        return $settings->notification_email ?: $settings->contact_email;
    }
}
