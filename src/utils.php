<?php


//* Shared 
function create_error_response($message, $status = 400)
{
  return new WP_REST_Response([
    'message' => $message,
    'status' => $status
  ], $status);
}

function create_response($response, string $message, int $statusCode = 200)
{
  if (is_wp_error($response)) {
    return $response;
  }
  return new WP_REST_Response([
    'message' => $message,
    'data' => $response
  ], $statusCode);
}

//* Bookings 
function validate_required_fields($data, $required_fields = null)
{
  if (!$required_fields) {
    $required_fields = [
      'productId',
      'date',
      'persons',
      'time',
      'airline',
      'mobileNumber',
      'amount',
      'flight',
      'passengerName'
    ];
  }
  $restricted_start = strtotime('2025-01-01');
  $restricted_end = strtotime('2025-01-07');

  foreach ($required_fields as $field) {
    if (empty($data[$field])) {
      return new WP_Error('missing_field', ucfirst($field) . ' is required', ['status' => 400, 'field' => $field]);
    }
  }

  if (isset($data['date'])) {
    $submitted_date = strtotime($data['date']);
    if ($submitted_date >= $restricted_start && $submitted_date <= $restricted_end) {
      return new WP_Error('Rejected', 'The selected date is not available', ['status' => 400]);
    }
  }

  return null;
}

//* Contacts 
function generate_message_body_email($email, $name, $message)
{
  return "
    <html>
      <body style='margin:0;padding:4em 1em;background-color:#f5f5f5;'>
        <table width='100%' cellpadding='0' cellspacing='0' border='0'>
          <tr>
            <td align='center'>
              <table width='600' cellpadding='0' cellspacing='0' border='0' style='border-radius:5px;background-color:#ffffff;font-family:Segoe UI, Tahoma, Geneva, Verdana, sans-serif;'>
                <tr>
                  <td style='background-color:#c28e53;color:#ffffff;padding:15px;border-top-left-radius:5px;border-top-right-radius:5px;font-size:20px;'>
                    New Contact Form Submission from Fast Track Aero
                  </td>
                </tr>
                <tr>
                  <td style='padding:10px 15px;color:#4e1874;font-size:16px;'>
                    A new message received via the contact form.
                  </td>
                </tr>
                <tr>
                  <td style='padding:10px 15px;'>
                    <strong style='color:#c28e53;'>Message:</strong>
                    <p style='line-height:1.5;margin-top:5px;'>$message</p>
                  </td>
                </tr>
                <tr>
                  <td style='padding:10px 15px;border-top:1px solid #c28e53;color:#4e1874;'>
                    <p style='margin:0;'>Name: <strong style='color:#c28e53;'>$name</strong></p>
                    <p style='margin:0;'>Email: <strong style='color:#c28e53;'>$email</strong></p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </body>
    </html>

    ";
}
