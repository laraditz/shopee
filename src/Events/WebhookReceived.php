<?php

namespace Laraditz\Shopee\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class WebhookReceived
{
    use Dispatchable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        logger()->info('WebhookReceived', $data);
        $this->data = $data;
    }
}
