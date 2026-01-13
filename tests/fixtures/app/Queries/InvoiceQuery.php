<?php

namespace App\Queries;

readonly class InvoiceQuery
{
    public function __construct(public int $userId, public string $customer)
    {
    }
}
