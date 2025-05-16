<?php

namespace Aero\Modules\Auth;

use WP_Error;

class AuthService
{
    public function login(array $data)
    {

        if (!isset($data['email']) || !isset($data['password'])) {
            return new WP_Error(400, "Email and password are required");
        }

        $email = sanitize_email($data['email']);
        $password = sanitize_text_field($data['password']);

        $user = get_user_by('email', $email);

        if (!$user || !wp_check_password($password, $user->user_pass, $user->ID)) {
            return new WP_Error(400, 'Email or password incorrect');
        }

        $token = bin2hex(random_bytes(32));

        update_user_meta($user->ID, 'auth_token', $token);

        return [
            'token' => $token,
            'customer_id' => $user->ID
        ];
    }

    public function register(array $data)
    {
        $email = sanitize_email($data['email']);
        $username = isset($data['username']) ? sanitize_text_field($data['username']) : $email;
        $password = sanitize_text_field($data['password']);
        $first_name = sanitize_text_field($data['first_name']);
        $last_name = sanitize_text_field($data['last_name']);

        if (email_exists($email)) return new WP_Error(400, "The Email already exists");

        $user_id = wc_create_new_customer($email, $username, $password);

        if (is_wp_error($user_id)) return new WP_Error(500, 'Failed to save the customer info');

        wp_update_user([
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name
        ]);

        return [
            'customer_id' => $user_id
        ];
    }
}
