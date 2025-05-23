<?php

namespace Aero\Modules\Contact;

use Aero\Modules\Email\EmailService;
use Aero\Modules\Email\EmailBuilder;

class ContactService
{
    public function contact_form(array $data)
    {
        $name = sanitize_text_field($data['name']);
        $email = sanitize_email($data['email']);
        $message = sanitize_textarea_field($data['message']);

        $to = defined("CONTACT_EMAIL") ? CONTACT_EMAIL : 'booking@fasttrackaero.com';
        $subject = "Fast Track Aero Contact: From $name";
        $body = EmailBuilder::buildContactEmail($email, $name, $message);

        $headers = EmailService::buildEmailHeader($email, "Fast Track Aero <$to>");

        $email_result = EmailService::send($to, $subject, $body, $headers);

        if ($email_result) {
            return true;
        } else {
            return false;
        }
    }


    public function notifyNewOrder($orderStatus, $orderId)
    {
        if (is_development() || !defined('DEVELOPER_CONTACT')) {
            return;
        }
        $from = defined("CONTACT_EMAIL") ? CONTACT_EMAIL : 'booking@fasttrackaero.com';
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            "From: $from"
        ];
        $body = EmailBuilder::buildNewOrderNotification($orderStatus, $orderId);


        EmailService::send(DEVELOPER_CONTACT, 'New order Received', $body, $headers);
    }
}
