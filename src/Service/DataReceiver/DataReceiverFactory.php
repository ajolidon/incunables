<?php

namespace App\Service\DataReceiver;


use App\Exception\DataReceiverException;

class DataReceiverFactory
{
    /**
     * @param string $dataReceiver
     * @return DataReceiverInterface
     * @throws DataReceiverException
     */
    public function get(string $dataReceiver): DataReceiverInterface
    {
        if($dataReceiver == 'swissbib')
        {
            return new SwissBibDataReceiver();
        }

        if($dataReceiver == 'aleph')
        {
            return new AlephDataReceiver();
        }

        throw new DataReceiverException('No data receiver available for tag `' . $dataReceiver . '`.');
    }
}