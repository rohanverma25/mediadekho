<?php

namespace App\Services;

use App\Mail\AwardNominationAdminNotification;
use App\Mail\AwardNominationCustomerConfirmation;
use App\Mail\ContactLeadAdminNotification;
use App\Mail\ContactLeadCustomerConfirmation;
use App\Mail\JobApplicationAdminNotification;
use App\Mail\JobApplicationCustomerConfirmation;
use App\Mail\NewCustomerAdminNotification;
use App\Mail\OrderAdminNotification;
use App\Mail\OrderCustomerConfirmation;
use App\Mail\WelcomeCustomerEmail;
use App\Models\AwardNomination;
use App\Models\ContactLead;
use App\Models\JobApplication;
use App\Models\Order;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Single place every form-triggered email goes through. Failures are
 * logged, never thrown — the underlying record (lead, order, etc.) is
 * already saved by the time these run, so a broken mail server should
 * degrade to "no email sent" rather than turning a successful form
 * submission into an HTTP error for the customer.
 */
class NotificationMailer
{
    public function __construct(private readonly MailConfigurator $mail)
    {
    }

    public function contactLead(ContactLead $lead): void
    {
        $this->sendAdminCopy(new ContactLeadAdminNotification($lead));
        $this->send($lead->email, new ContactLeadCustomerConfirmation($lead));
    }

    public function awardNomination(AwardNomination $nomination): void
    {
        $this->sendAdminCopy(new AwardNominationAdminNotification($nomination));
        $this->send($nomination->email, new AwardNominationCustomerConfirmation($nomination));
    }

    public function jobApplication(JobApplication $application): void
    {
        $this->sendAdminCopy(new JobApplicationAdminNotification($application));
        $this->send($application->email, new JobApplicationCustomerConfirmation($application));
    }

    public function orderConfirmed(Order $order): void
    {
        $this->sendAdminCopy(new OrderAdminNotification($order));

        if ($order->user?->email) {
            $this->send($order->user->email, new OrderCustomerConfirmation($order));
        }
    }

    public function welcome(User $user): void
    {
        $this->sendAdminCopy(new NewCustomerAdminNotification($user));
        $this->send($user->email, new WelcomeCustomerEmail($user));
    }

    /**
     * B2B self-registrations need an admin to review and approve them
     * before they can log in — no "welcome, you're all set" copy goes to
     * the customer yet, only the internal notification so admin knows a
     * new account is waiting on them.
     */
    public function pendingApproval(User $user): void
    {
        $this->sendAdminCopy(new NewCustomerAdminNotification($user));
    }

    private function sendAdminCopy(Mailable $mailable): void
    {
        $to = $this->mail->notificationEmail();

        if (blank($to)) {
            return;
        }

        $this->send($to, $mailable);
    }

    private function send(string $to, Mailable $mailable): void
    {
        try {
            Mail::mailer($this->mail->mailer())->to($to)->send($mailable);
        } catch (\Throwable $e) {
            Log::error('Notification email failed: '.$e->getMessage(), [
                'to' => $to,
                'mailable' => $mailable::class,
            ]);
        }
    }
}
