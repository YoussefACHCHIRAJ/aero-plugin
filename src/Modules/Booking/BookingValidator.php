<?php

namespace Aero\Modules\Booking;

use Aero\Modules\City\CityRepository;
use BookingTypeEnum;
use WP_Error;

class BookingValidator
{
    public static function validate(array $data, ?array $required = null): ?WP_Error
    {
        $required = $required ?? [
            'productId',
            'date',
            'persons',
            'time',
            'airline',
            'mobileNumber',
            'amount',
            'flight',
            'passengerName',
        ];

        $type = $data['serviceType'];

        if (!static::validateCity($data['city'])) {
            return new WP_Error('invalid_city', 'The given city is not yet supported', ['status' => 400]);
        }

        if ($type === BookingTypeEnum::ArrivalDeparture && !static::validateAvailabilityDate($data['date'])) {
            $date = $data['date'];
            return new WP_Error('invalid_date', "The Date $date is unavailable", ['status' => 400]);
        }


        if ($type === BookingTypeEnum::Connection) {
            $arrivalDate = $data['arrival']['date'];
            $departureDate = $data['departure']['date'];
            if (!static::validateAvailabilityDate($data['arrival']['date'])) {
                return new WP_Error('invalid_date', "The Arrival Date $arrivalDate is unavailable", ['status' => 400]);
            }

            if (!static::validateAvailabilityDate($data['departure']['date'])) {
                return new WP_Error('invalid_date', "The Departure Date $departureDate is unavailable", ['status' => 400]);
            }
        }


        foreach ($required as $field) {
            if (empty($data[$field])) {
                return new WP_Error('missing_field', ucfirst($field) . ' is required', ['status' => 400, 'field' => $field]);
            }
        }

        return null;
    }

    public static function validateCity(string | null $city)
    {

        $availableCities = CityRepository::fetchCitiesName();

        $availableCities = array_map('strtolower', $availableCities);

        if (!in_array(strtolower($city), $availableCities)) {
            return false;
        }

        return true;
    }

    public static function validateAvailabilityDate($date)
    {
        $start_date_timestamp = strtotime(date('Y-m-d', strtotime($date)));
        $now = strtotime(date('Y-m-d')); // Today at 00:00:00

        // Compare full days
        if ($start_date_timestamp < strtotime('+2 days', $now)) {
            return false;
        }

        return true;
    }
}
