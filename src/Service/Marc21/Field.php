<?php
/**
 * Created by PhpStorm.
 * User: mathias
 * Date: 01.02.19
 * Time: 18:21
 */

namespace App\Service\Marc21;


use App\Exception\Marc21Exception;

abstract class Field
{
    /**
     * @var Record
     */
    protected $record;

    /**
     * @var null|string
     */
    protected $tag;

    protected $tagMessage = "Tag `%s` not allowed in %s fields.";

    abstract function getType(): string;

    abstract function getAllowedTags(): array;

    /**
     * @return Record
     */
    public function getRecord(): Record
    {
        return $this->record;
    }

    /**
     * @param Record $record
     * @return DataField
     */
    public function setRecord(Record $record): self
    {
        $this->record = $record;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTag(): ?string
    {
        return $this->tag;
    }

    /**
     * @param string|null $tag
     * @return ControlField
     * @throws Marc21Exception
     */
    public function setTag(?string $tag): self
    {
        if(!in_array($tag, $this->getAllowedTags()))
        {
            throw new Marc21Exception(sprintf($this->tagMessage, $tag, $this->getType()));
        }

        $this->tag = $tag;

        return $this;
    }
}