<?php

namespace App\Service\Marc21;


use Doctrine\Common\Collections\ArrayCollection;

class Record
{
    /**
     * @var null|string
     */
    protected $leader;

    /**
     * @var ControlField[]
     */
    protected $controlFields;

    /**
     * @var DataField[];
     */
    protected $dataFields;

    /**
     * @var \DateTime
     */
    protected $dateStamp;

    public function __construct()
    {
        $this->controlFields = new ArrayCollection();
        $this->dataFields = new ArrayCollection();
    }

    public function setLeader(string $leader): self
    {
        $this->leader = $leader;

        return $this;
    }

    public function getLeader(): ?string
    {
        return $this->leader;
    }

    /**
     * @param ControlField $controlField
     * @return Record
     */
    public function addControlField(ControlField $controlField): self
    {
        $controlField->setRecord($this);
        $this->controlFields->add($controlField);

        return $this;
    }

    /**
     * @param DataField $dataField
     * @return Record
     */
    public function addDataField(DataField $dataField): self
    {
        $dataField->setRecord($this);
        $this->dataFields->add($dataField);

        return $this;
    }

    public function getControlField(string $tag)
    {
        foreach($this->controlFields as $controlField)
        {
            if($controlField->getTag() == $tag)
            {
                return $controlField;
            }
        }

        return null;
    }

    /**
     * @param bool $tag
     * @param bool $indicator1
     * @param bool $indicator2
     * @return DataField[]
     */
    public function getDataFields($tag = false, $indicator1 = false, $indicator2 = false): ArrayCollection
    {
        if($tag === false && $indicator1 === false && $indicator2 === false)
        {
            return $this->dataFields;
        }

        $result = new ArrayCollection();
        foreach($this->dataFields as $dataField){
            if(($tag === false || $dataField->getTag() === $tag) && ($indicator1 === false || $dataField->getIndicator1() === $indicator1) && ($indicator2 === false || $dataField->getIndicator2() === $indicator2))
            {
                $result->add($dataField);
            }
        }

        return $result;
    }

    /**
     * @return \DateTime
     */
    public function getDateStamp(): \DateTime
    {
        return $this->dateStamp;
    }

    /**
     * @param \DateTime $dateStamp
     * @return Record
     */
    public function setDateStamp(\DateTime $dateStamp): self
    {
        $this->dateStamp = $dateStamp;

        return $this;
    }
}