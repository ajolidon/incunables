<?php

namespace App\Service\Marc21;


use App\Exception\Marc21Exception;

class Marc21Parser
{
    /**
     * @param string $xml
     * @return Record
     * @throws Marc21Exception
     */
    public function parse(string $xml): Record
    {

        $record = new Record();

        $re = '/<datestamp>(.*?)<\/datestamp>/m';
        preg_match_all($re, $xml, $matches, PREG_SET_ORDER, 0);

        if(count($matches) == 0){
            throw new Marc21Exception("No datestamp found:\n" . $xml);
        }

        $record->setDateStamp(new \DateTime($matches[0][1]));

        $re = '/<marc:leader>(.*?)<\/marc:leader>/m';
        preg_match_all($re, $xml, $matches, PREG_SET_ORDER, 0);

        if(count($matches) == 0){
            throw new Marc21Exception("No leader found:\n" . $xml);
        }

        if(count($matches) > 1){
            throw new Marc21Exception("Multiple leaders found:\n" . $xml);
        }

        $record->setLeader($matches[0][1]);

        $re = '/<marc:controlfield tag="(.*?)">(.*?)<\/marc:controlfield>/m';
        preg_match_all($re, $xml, $matches, PREG_SET_ORDER, 0);
        foreach($matches as $match){
            $controlField = new ControlField();
            $controlField->setTag($match[1]);
            $controlField->setValue($match[2]);
            $record->addControlField($controlField);
        }

        $re = '/<marc:datafield tag="(.*?)" ind1="(.*?)" ind2="(.*?)">(.*?)<\/marc:datafield>/m';
        preg_match_all($re, $xml, $matches, PREG_SET_ORDER, 0);
        foreach($matches as $match){
            $dataField = new DataField();
            $dataField->setTag($match[1]);
            $dataField->setIndicator1($match[2]);
            $dataField->setIndicator2($match[3]);

            $subFieldData = $match[4];

            $re = '/<marc:subfield code="(.*?)">(.*?)<\/marc:subfield>/m';
            preg_match_all($re, $subFieldData, $matches, PREG_SET_ORDER, 0);
            foreach($matches as $match)
            {
                $subField = new SubField();
                $subField->setCode($match[1]);
                $subField->setValue($match[2]);
                $dataField->addSubField($subField);
            }

            $record->addDataField($dataField);
        }

        return $record;
    }
}