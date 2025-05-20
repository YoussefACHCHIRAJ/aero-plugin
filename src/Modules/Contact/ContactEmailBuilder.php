<?php

namespace Aero\Modules\Contact;

class ContactEmailBuilder
{
  public static function build(string $email, string $name, string $message): string
  {
    return <<<HTML
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
        HTML;
  }

  public static function buildNewOrderNotification(string $orderStatus, int $orderId)
  {
    return <<<HTML
      <html>
        <body>
            <h3>Fast track has been received new order: </h3>
            <p>
                Order status: $orderStatus
            </p>
            <p>
                Order Id: $orderId
            </p>
        </body>
      </html>
      HTML;
  }

  public static function buildRatingNotification(string $name, int $rateCount, string $comment, string $email, string $phone)
  {
    return <<<HTML
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
      HTML;
  }
}
