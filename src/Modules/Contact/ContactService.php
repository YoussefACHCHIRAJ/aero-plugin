<?php

namespace Aero\Modules\Contact;

class ContactService
{
    public function contact_form(array $data)
    {
        $name = sanitize_text_field($data['name']);
        $email = sanitize_email($data['email']);
        $message = sanitize_textarea_field($data['message']);
        $platform = sanitize_textarea_field($data['platform'] ?? "Web site");

        $to = defined("CONTACT_EMAIL") ? CONTACT_EMAIL : 'booking@fasttrackaero.com';
        $subject = "Fast Track Aero Contact: From $name";
        $body = generate_message_body_email($email, $name, $message, $platform);

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


    public function notify_receiving_order($to, $subject, $order_status, $customer_email, $order_id)
    {
        $from = defined("CONTACT_EMAIL") ? CONTACT_EMAIL : 'booking@fasttrackaero.com';
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            "From: $from"
        ];
        $env = "prod";

        if (is_development()) {
           return;
        }

        $body = "
    <html>
        <body>
            <h3>Fast track has been received new order: </h3>
            <p>
                Order status: $order_status
            </p>
            <p>
                Environment: $env
            </p>
            <p>
                Customer email: $customer_email
            </p>
            <p>
                Order Id: $order_id
            </p>
        </body>
    </html>
    ";


        wp_mail($to, $subject, $body, $headers);
    }
}
