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
            throw new \InvalidArgumentException('The given city is not yet supported', 400);
        }

        if ($type === BookingTypeEnum::ArrivalDeparture && !static::validateAvailabilityDate($data['date'])) {
            $date = $data['date'];
            throw new \InvalidArgumentException("The Date $date is unavailable", 400);
        }


        if ($type === BookingTypeEnum::Connection) {
            $arrivalDate = $data['arrival']['date'];
            $departureDate = $data['departure']['date'];
            if (!static::validateAvailabilityDate($data['arrival']['date'])) {
                throw new \InvalidArgumentException("The Arrival Date $arrivalDate is unavailable", 400);
            }

            if (!static::validateAvailabilityDate($data['departure']['date'])) {
                throw new \InvalidArgumentException("The Departure Date $departureDate is unavailable", 400);
            }
        }


        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException(ucfirst($field) . ' is required', 400);
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
