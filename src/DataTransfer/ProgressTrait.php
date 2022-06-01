<?php

namespace App\DataTransfer;

trait ProgressTrait {

    /**
     * @var int
     */
    protected $valuenow = 0;

    /**
     * @var int
     */
    protected $valuemax = 0;

    /**
     * @return int
     */
    public function getProgress(): int {
        return !$this->valuemax ? 0 : round(100 * $this->valuenow / $this->valuemax);
    }

    /**
     * @return int
     */
    public function getValuenow(): int {
        return $this->valuenow;
    }

    /**
     * @return int
     */
    public function getValuemax(): int {
        return $this->valuemax;
    }
}
