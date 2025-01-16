<?php

use App\Enums\OrderStatus;

return [
    OrderStatus::PENDING          => 'Đang chờ xử lý',
    OrderStatus::ACCEPT           => 'Đã chấp nhận',
    OrderStatus::PROCESSING       => 'Đang xử lý',
    OrderStatus::OUT_FOR_DELIVERY => 'Đang giao hàng',
    OrderStatus::DELIVERED        => 'Đã giao hàng',
    OrderStatus::CANCELED         => 'Đã hủy',
    OrderStatus::REJECTED         => 'Đã từ chối',
    OrderStatus::RETURNED         => 'Đã trả lại',
];
