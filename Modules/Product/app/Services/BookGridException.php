<?php

namespace Modules\Product\Services;

/** Thrown by BookGridService::apply() when the plan has errors / confirmations are missing. Carries the public plan. */
class BookGridException extends \RuntimeException
{
    public function __construct(public readonly array $plan)
    {
        parent::__construct($plan['fatal'] ?? 'Books grid validation failed');
    }
}
