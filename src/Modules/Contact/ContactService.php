<?php

namespace Aero\Modules\Contact;

class ContactService
{
    public function contact_form(array $data)
    {
        $name = sanitize_text_field($data['name']);
        $email = sanitize_email($data['email']);
        $message = sanitize_textarea_field($data['message']);

        $to = defined("CONTACT_EMAIL") ? CONTACT_EMAIL : 'booking@fasttrackaero.com';
        $subject = "Fast Track Aero Contact: From $name";
        $body = ContactEmailBuilder::build($email, $name, $message);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: ' . $email,
            "From: Fast Track Aero <$to>"
        ];

        $email_result = wp_mail($to, $subject, $body, $headers);

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
        $body = ContactEmailBuilder::buildNewOrderNotification($orderStatus, $orderId);


        wp_mail(DEVELOPER_CONTACT, 'New order Received', $body, $headers);
    }
}
