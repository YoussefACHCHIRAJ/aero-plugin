<?php


namespace Aero\Modules\Rating;

use Aero\Config\ApiConfig;
use WP_REST_Request;
use WP_REST_Response;

class RatingController
{

    public function register_routes()
    {
        register_rest_route(ApiConfig::AERO_NAMESPACE, 'rating', array(
            'methods' => 'POST',
            'callback' => [$this, 'notify_receiving_rating'],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
        ));
    }
    public function notify_receiving_rating(WP_REST_Request $request)
    {

        $data = $request->get_json_params();



        $rateCount = $data['rateCount'];
        $comment = $data['comment'];
        $name = $data['first_name'];
        $email = $data['email'];
        $phone = $data['phone'];

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: ' . $email,
            "From: booking@fasttrackaero.com"
        ];

        $body = "
    <html>
        <body>
            <h3>Fast Track has received a rating from: $name</h3>
            <p>
                Rating Score: $rateCount/5
            </p>
            <div style=' padding: .5em .2em; margin: 0'>
                <strong style='font-size: 1rem; color: #4e1874'>Customer Comment: </strong>
                <p style='line-height: 1.5;'>
                $comment
                </p>
            </div>
            <hr style='border: 1px solid #bd1a83'/>
            <div style='padding: 0.5em; color: #4e1874'>
                <span style='display: block; margin-bottom: 5px'>
                Name: <strong style='color: #bd1a83'>$name</strong>
                </span>
                <span style='display: block; margin-bottom: 5px'> Email: <strong style='color: #bd1a83'>$email</strong> </span>
                <span style='display: block; margin-bottom: 5px'> Phone: <strong style='color: #bd1a83'>$phone</strong> </span>
            </div>
        </body>
    </html>
    ";


        $result = wp_mail('achchiraj@traveldesign.ma', 'Fast Track Aero Rating: ', $body, $headers);

        return new WP_REST_Response($result);
    }
}
