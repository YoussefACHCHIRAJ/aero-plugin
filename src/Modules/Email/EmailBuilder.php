<?php

namespace Aero\Modules\Email;

class EmailBuilder
{
  public static function buildContactEmail(string $email, string $name, string $message): string
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

  public static function buildRequestReview(string $customerName, string $airportName, string $serviceDate): string
  {
    return <<<HTML
        <html>
          <body style='margin:0;padding:4em 1em;background-color:#f5f5f5;'>
            <div style="margin-bottom: 2em">
              
              <p style="font-size:16px;">Hi $customerName,</p>
              
              <p style="font-size:16px;line-height: 25px;">We hope your recent <strong>Fast Track experience at $airportName</strong> on <strong>$serviceDate</strong> made your travel smoother and more enjoyable.</p>
              
              <p style="font-size:16px;line-height: 25px;">Would you take a moment to share your experience with others?</p>
              <p style="font-size:16px;line-height: 25px;"><strong>It only takes one minute</strong> and your feedback helps other travelers make better decisions, and helps us keep improving.</p>
              
              <a style="font-size:16px;" href="https://g.page/r/CaVSlLp-5kJVEBM/review" style="text-decoration: underline"><strong>Leave a Google Review</strong></a>

              <p style="font-size:16px;line-height: 25px;">To help us improve and help future travelers choose the right service, here are two quick questions:</p>

              <ul style="font-size:16px;line-height: 25px;">
                <li style="line-height: 25px;">Did Fast Track Aero help you skip long lines and save time at the airport?</li>
                <li style="line-height: 25px;">Did our team make your arrival or departure experience more comfortable?</li>
              </ul>
              
              <p style="font-size:15px;line-height: 25px;">
                Thanks again for choosing Fast Track Aero. Your journey matters, and so does your voice.
              </p>

              <p style="font-size:15px;">Best regards,</p>
              <p style="font-size:15px;">The Fast Track Aero Team</p>
            </div>

            <hr style="margin-top:2em;">
            <p style="text-align:center;">
              <a href="https://fasttrackaero.com" style="color:#0066cc;">Visit Our Official Website</a>
            </p>
          </body>
        </html>
      HTML;
  }

  public static function buildRequestReviewSummary(int $emailsCount, string $receiverName): string
  {
    $today = current_time('Y-m-d H:i:s');

    return <<<HTML
        <html>
          <body style='margin:0;padding:4em 1em;background-color:#f5f5f5;'>
            <div style="margin-bottom: 2em">
              <p>Hi $receiverName,</p>
              <p>This is the summary of request reviews for $today</p>
              <div>
                
              </div>
              <p>
                $emailsCount has been sent.
              </p>

              <p>Best regards,</p>
              <p>The Fast Track Aero Team</p>
            </div>

            <div >
              <a href="https://fasttrackaero.com">Fast Track Aero Official Website</a>
            </div>
          </body>
        </html>
      HTML;
  }
}
