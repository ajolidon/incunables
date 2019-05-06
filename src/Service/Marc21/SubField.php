<?php

namespace App\Service\Marc21;


use App\Exception\Marc21Exception;

class SubField
{
    /**
     * @var null|string
     */
    protected $code;

    /**
     * @var null|string
     */
    protected $value;

    /**
     * @var DataField
     */
    protected $dataField;

    protected $allowedCodes = [];

    public function __construct()
    {
        $this->allowedCodes = str_split("abcdefghijklmnopqrstuvwxyz0123456789");
    }

    /**
     * @return null|string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return SubField
     * @throws Marc21Exception
     */
    public function setCode(string $code): self
    {
        if(!in_array($code, $this->allowedCodes))
        {
            throw new Marc21Exception('Code `' . $code . '` not allowed in sub fields.');
        }
        $this->code = $code;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return SubField
     */
    public function setValue(string $value): self
    {
        $this->value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5);

        return $this;
    }

    /**
     * @return null|DataField
     */
    public function getDataField(): ?DataField
    {
        return $this->dataField;
    }

    public function setDataField(DataField $dataField): self
    {
        $this->dataField = $dataField;

        return $this;
    }
}