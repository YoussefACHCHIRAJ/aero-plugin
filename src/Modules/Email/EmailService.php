<?php

namespace Aero\Modules\Email;

class EmailService
{

    public static function send(string $receiver, string $subject, string $body, array $headers)
    {
        return wp_mail($receiver, $subject, $body, $headers);
    }

    public static function buildEmailHeader(string  $replier, ?string $from = "Fast Track Aero") {
        
        if(!$from) $from = "Fast Track Aero <$replier>";
        return [
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: ' . $replier,
            "From: $from"
        ];
    }
}
