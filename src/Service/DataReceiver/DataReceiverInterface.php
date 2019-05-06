<?php

namespace App\Service\DataReceiver;


use App\Exception\DataReceiverException;

interface DataReceiverInterface
{
    /**
     * @param array $args
     * @return string
     * @throws DataReceiverException
     */
    public function receive(array $args = []);
}