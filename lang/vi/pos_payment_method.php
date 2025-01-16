<?php

use App\Enums\PosPaymentMethod;

return [
    PosPaymentMethod::CARD => 'Thẻ',
    PosPaymentMethod::CASH => 'Tiền mặt',
    PosPaymentMethod::OTHER => 'Khách',
    PosPaymentMethod::MOBILE_BANKING => 'Chuyển khoản'
];
