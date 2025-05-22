<?php

namespace Aero\Modules\Booking;

use Aero\Modules\Email\EmailBuilder;
use Aero\Modules\Email\EmailService;
use WC_Booking;
use WP_Error;
use WP_Query;

class BookingReview
{

    private const REVIEW_ELIGIBILITY_WINDOW = 4 * DAY_IN_SECONDS;


    public static function sendRequestReview()
    {
        try {
            $bookings = self::getEligibleBookings();
            $now = current_time('timestamp');

            foreach ($bookings->posts as $b) {
                $booking = get_wc_booking($b->ID);

                if (self::shouldSendReviewRequest($booking, $now)) {
                    self::sendReviewRequest($booking);
                }
            }

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Scheduling triggered at " . date('Y/m/d H:i:s', $now));
            }

            return "Scheduling triggered";
        } catch (\Throwable $th) {
            return new WP_Error('error', "Failed send request review. Error details: " . $th->getMessage());
        }
    }

    private static function shouldSendReviewRequest(WC_Booking $booking, string $now)
    {
        $requestReviewSentBefore = $booking->get_meta('_request_review_sent');

        if ($requestReviewSentBefore) return false;

        $start = $booking->get_start();

        return ($now - $start) <= self::REVIEW_ELIGIBILITY_WINDOW && ($now > $start);
    }

    private static function sendReviewRequest(WC_Booking $booking)
    {
        $order = $booking->get_order();

        if (!$order) return;

        $bookingStartDate = $booking->get_start();

        $customerName =  $order->get_billing_first_name();

        $customerEmail = $order->get_billing_email();

        $airportName = $order->get_meta('_booking_city');

        $emailBody = EmailBuilder::buildRequestReview($customerName, $airportName, date('Y/m/d', $bookingStartDate));
        $emailHeader = EmailService::buildEmailHeader(CONTACT_EMAIL);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("********Customer Email " . $customerEmail);
        }

        if (!empty($customerEmail))
            $sent = EmailService::send($customerEmail, "How was your Fast Track Aero experience on " . date('Y/m/d', $bookingStartDate) . "?", $emailBody, $emailHeader);

        if ($sent) {
            $booking->add_meta_data('_request_review_sent', true);
            $booking->save_meta_data();
        }
    }

    private static function getEligibleBookings()
    {
        $args = [
            'post_type' => 'wc_booking',
            'post_status' => ['complete', 'paid', 'confirm'],
            'posts_per_page' => -1,
        ];

        return new WP_Query($args);
    }
}
