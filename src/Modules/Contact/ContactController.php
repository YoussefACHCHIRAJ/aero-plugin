<?php

namespace Aero\Modules\Contact;

use Aero\Config\ApiConfig;
use Aero\Helpers\AeroRouter;
use WP_REST_Request;
use WP_REST_Response;

class ContactController
{

    protected $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    public function register_routes() {
        AeroRouter::post('contact', [$this, 'handle_contact_form']);
    }

    public function handle_contact_form(WP_REST_Request $request)
    {
        $data = $request->get_json_params();


        $email_result = $this->contactService->contact_form($data);

        if ($email_result) {
            return new WP_REST_Response(['success' => true, 'message' => 'Message sent successfully. Thanks for reaching us']);
        } else {
            return new WP_REST_Response(['success' => false, 'message' => 'Message Failed. Please try again']);
        }
    }
}
