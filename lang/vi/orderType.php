<?php

use App\Enums\OrderType;

return [
    OrderType::DELIVERY     => 'Giao hàng',
    OrderType::TAKEAWAY     => 'Mang đi',
    OrderType::POS          => 'Thanh toán tại quầy',
    OrderType::DINING_TABLE => 'Bàn ăn tại chỗ',
];
