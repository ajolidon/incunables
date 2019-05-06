<?php

namespace App\Service\DataReceiver;


use App\Exception\DataReceiverException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SwissBibDataReceiver implements DataReceiverInterface
{
    /**
     * @var array
     */
    private $collections;

    public function __construct(ContainerInterface $container)
    {
        $this->collections = explode(",", $container->getParameter('collection_codes'));
    }

    public function receive(array $args = []): array
    {
        set_error_handler(function(int $code, string $message) {
            if (0 === error_reporting()) {
                return false;
            }

            throw new DataReceiverException($message, $code);
        });

        $systemNumbers = [];

        foreach($this->collections as $collection){
            $url = "https://solrgreendev.swissbib.ch/solr/green/select?q=localcode%3A" . urlencode($collection) . "&wt=json&indent=true&rows=10000";

            $data = json_decode(file_get_contents($url));

            foreach ($data->response->docs as $record) {
                $xml = $record->fullrecord;

                $re = '/<controlfield tag="001">(.*)<\/controlfield>/m';
                preg_match_all($re, $xml, $matches, PREG_SET_ORDER, 0);
                if(count($matches) == 0){
                    throw new DataReceiverException("No swissbib system number found:\n" . $xml);
                }

                $systemNumber['swissbib'] = $matches[0][1];


                $re = '/<subfield code="a">\(IDSBB\)(\d+)<\/subfield>/m';
                preg_match_all($re, $xml, $matches, PREG_SET_ORDER, 0);
                if (count($matches) == 0) {
                    throw new DataReceiverException("No idsbb system number found:\n" . $xml);
                }

                if (count($matches) > 1) {
                    throw new DataReceiverException("Multiple system number found:\n" . $xml);
                }

                $systemNumber['idsbb'] = $matches[0][1];

                if (!in_array($systemNumber, $systemNumbers)) {
                    $systemNumbers[] = $systemNumber;
                }
            }
        }

        restore_error_handler ();

        return $systemNumbers;
    }

}