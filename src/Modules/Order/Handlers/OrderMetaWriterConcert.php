<?php

namespace Aero\Modules\Order\Handlers;

interface OrderMetaWriterConcert {
    public function writeMetaItem(int $orderItem, array $data);
}