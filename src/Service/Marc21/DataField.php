<?php

namespace App\Service\Marc21;


use App\Exception\Marc21Exception;
use Doctrine\Common\Collections\ArrayCollection;

class DataField extends Field
{
    /**
     * @var null|string
     */
    protected $indicator1;

    /**
     * @var null|string
     */
    protected $indicator2;

    /**
     * @var SubField[]
     */
    protected $subFields;

    /**
     * @var array
     */
    protected $allowedIndicators = [];

    function getType(): string
    {
        return "data";
    }

    function getAllowedTags(): array
    {
        $allowedTags = [];
        for($i = 1; $i < 1000; $i++){
            $allowedTags[] = str_pad($i, 3, '0', STR_PAD_LEFT);
        }

        $allowedTags[] = "OWN";

        return $allowedTags;
    }


    public function __construct()
    {
        $this->subFields = new ArrayCollection();
        $this->allowedIndicators = str_split("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ");
    }

    /**
     * @return string|null
     */
    public function getIndicator1(): ?string
    {
        return $this->indicator1;
    }

    /**
     * @param string|null $indicator1
     * @return DataField
     * @throws Marc21Exception
     */
    public function setIndicator1(?string $indicator1): self
    {
        $this->indicator1 = $this->checkIndicator($indicator1);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIndicator2(): ?string
    {
        return $this->indicator2;
    }

    /**
     * @param string|null $indicator2
     * @return DataField
     * @throws Marc21Exception
     */
    public function setIndicator2(?string $indicator2): self
    {
        $this->indicator2 = $this->checkIndicator($indicator2);

        return $this;
    }

    /**
     * @param SubField $subField
     * @return DataField
     */
    public function addSubField(SubField $subField): self
    {
        $subField->setDataField($this);
        $this->subFields->add($subField);

        return $this;
    }

    /**
     * @param string|null $indicator
     * @return string|null
     * @throws Marc21Exception
     */
    protected function checkIndicator(?string $indicator)
    {
        $indicator = trim($indicator);
        if($indicator === ""){
            $indicator = null;
        }

        if(!is_null($indicator) && !in_array($indicator, $this->allowedIndicators))
        {
            throw new Marc21Exception('The indicator `' . $indicator . '` is not allowed.');
        }

        return $indicator;
    }

    /**
     * @return SubField[]
     */
    public function getSubFields($code = false): ArrayCollection
    {
        if($code === false)
        {
            return $this->subFields;
        }

        $result = new ArrayCollection();
        foreach($this->subFields as $subField)
        {
            if($subField->getCode() === $code)
            {
                $result->add($subField);
            }
        }

        return $result;
    }
}