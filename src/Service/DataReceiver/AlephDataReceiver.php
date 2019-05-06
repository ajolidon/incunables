<?php

namespace App\Service\DataReceiver;


use App\Exception\DataReceiverException;

class AlephDataReceiver implements DataReceiverInterface
{
    public function receive(array $args = []): string
    {
        set_error_handler(function(int $code, string $message) {
            if (0 === error_reporting()) {
                return false;
            }

            throw new DataReceiverException($message, $code);
        });

        if(!isset($args['systemNumber']))
        {
            throw new DataReceiverException("Argument `systemNumber` not provided.", 901);
        }

        restore_error_handler ();

        return file_get_contents("https://aleph.unibas.ch/OAI?verb=GetRecord&identifier=oai:aleph.unibas.ch:DSV01-" . $args['systemNumber'] . "&metadataPrefix=marc21");
    }

}