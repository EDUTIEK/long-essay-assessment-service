<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class CorrectionMarkPoint
{
    private int $x;
    private int $y;

    public function __construct(int $x = 0, int $y = 0) 
    {
        $this->x = $x;
        $this->y = $y;
    }
    
    public static function fromArray($data): self
    {
        $x = 0;
        $y = 0;
        if (is_array($data)) {
            $x = (int) ($data['x'] ?? 0);
            $y = (int) ($data['y'] ?? 0);
        }
        return new self($x, $y);
    }

    /**
     * @return array
     */
    public function toArray() :array 
    {
        return [
            'x' => $this->getX(),
            'y' => $this->getY()
        ];
    }
    
    
    /**
     * @return int
     */
    public function getX(): int
    {
        return $this->x;
    }

    /**
     * @return int
     */
    public function getY(): int
    {
        return $this->y;
    }

}