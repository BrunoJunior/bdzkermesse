<?php

namespace App\DataTransfer;

class MovedActivityDto implements \JsonSerializable
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var int
     */
    private $oldPosition;
    /**
     * @var int
     */
    private $newPosition;

    /**
     * @param int $id
     * @param int $oldPosition
     * @param int $newPosition
     */
    public function __construct(int $id, int $oldPosition, int $newPosition)
    {
        $this->id= $id;
        $this->oldPosition= $oldPosition;
        $this->newPosition= $newPosition;
    }

    /**
     * For json serialization
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'oldPosition' => $this->oldPosition,
            'newPosition' => $this->newPosition,
        ];
    }
}