<?php


namespace Aero\Modules\Rating;

use Aero\Contracts\AeroControllerContract;
use Aero\Helpers\AeroRouter;
use Aero\Modules\Email\EmailBuilder;
use Aero\Modules\Email\EmailService;
use WP_REST_Request;
use WP_REST_Response;

class RatingController implements AeroControllerContract
{

    public function registerRoutes()
    {
        AeroRouter::post('rating', [$this, 'notifyReceivingRating']);
    }
    public function notifyReceivingRating(WP_REST_Request $request)
    {

        $data = $request->get_json_params();

        $rateCount = sanitize_text_field($data['rateCount']);
        $comment = sanitize_text_field($data['comment']);
        $name = sanitize_text_field($data['first_name']);
        $email = sanitize_email($data['email']);
        $phone = sanitize_text_field($data['phone']);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: ' . $email,
            "From: booking@fasttrackaero.com"
        ];

        $body = EmailBuilder::buildRatingNotification($name, $rateCount, $comment, $email, $phone);

        $result = EmailService::send('achchiraj@traveldesign.ma', 'Fast Track Aero Rating: ', $body, $headers);

        return new WP_REST_Response($result);
    }
}
