<?php

namespace App\Service\Marc21;

class ControlField extends Field
{

    /**
     * @var null|string
     */
    protected $value;

    function getType(): string
    {
        return "control";
    }

    function getAllowedTags(): array
    {
        return [
            'FMT',
            'LDR',
            '001',
            '003',
            '005',
            '006',
            '007',
            '008',
        ];
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
     * @return ControlField
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

}