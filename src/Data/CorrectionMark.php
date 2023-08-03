<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class CorrectionMark
{
    const SHAPE_NONE = '';
    const SHAPE_CIRCLE = 'circle';
    const SHAPE_RECTANGLE = 'rectangle';
    const SHAPE_POLYGON = 'polygon';
    const SHAPE_LINE = 'line';
    const SHAPE_WAVE = 'wave';

    
    
    private string $key;
    private string $shape;
    private CorrectionMarkPoint $pos;
    private CorrectionMarkPoint $end;
    private int $height;
    private int $width;
    private array $polygon;

    /**
     * @param string              $key
     * @param string              $shape
     * @param CorrectionMarkPoint $pos
     * @param CorrectionMarkPoint $end
     * @param int                 $height
     * @param int                 $width
     * @param CorrectionMarkPoint[] $polygon
     */
    public function __construct(
        string $key,
        string $shape,
        CorrectionMarkPoint $pos,
        CorrectionMarkPoint $end,
        int $height = 0,
        int $width = 0,
        array $polygon = []
    ) {
        $this->key = $key;
        $this->shape = $shape;
        $this->pos = $pos;
        $this->end = $end;
        $this->height = $height;
        $this->width = $width;
        $this->polygon = $polygon;
    }
    
    public static function fromJson($json) : self
    {
        try {
            $array = (array) json_decode($json);
        }
        catch (\Exception $e)  {
            $array = [];
        }
        
        return self::fromArray($array);
    }
    
    
    public static function fromArray($data = [])  : self
    {
        $key = '';
        $shape = self::SHAPE_NONE;
        $pos = new CorrectionMarkPoint(0,0);
        $end = new CorrectionMarkPoint(0,0);
        $width = 0;
        $height = 0;
        $polygon = [];
        
        
        if (isset($data['key'])) {
            $key = (string) $data['key'];
        }
        
        if (isset($data['shape'])) {
            if (in_array($data['shape'], [self::SHAPE_CIRCLE, self::SHAPE_LINE, self::SHAPE_WAVE, self::SHAPE_RECTANGLE, self::SHAPE_POLYGON])) {
                $shape = (string) $data['shape'];
            }
        }
        if (isset($data['pos']) && isset($data['pos']['x']) && isset($data['pos']['y'])) {
            $pos = new CorrectionMarkPoint((int) $data['pos']['x'], (int)$data['pos']['y']);
        }
        if (isset($data['end']) && isset($data['end']['x']) && isset($data['end']['y'])) {
            $end = new CorrectionMarkPoint((int) $data['end']['x'], (int)$data['end']['y']);
        }
        if (isset($data['width'])) {
            $width = (int) $data['width'];
        }
        if (isset($data['height'])) {
            $height = (int) $data['height'];
        }
        if (isset($data['polygon']) && is_array($data['polygon'])) {
            foreach ($data['polygon'] as $point) {
                if (isset($point['x']) && isset($point['y'])) {
                    $polygon[] = new CorrectionMarkPoint((int) $point['x'], (int) $point['y']);
                }
            }
        }

        return new self($key, $shape, $pos, $end, $width, $height, $polygon);
    }
    
    
    public function toJson() : string
    {
        return json_encode($this->toArray());   
    }
    
    public function toArray() : array
    {
        $polygon = [];
        foreach ($this->getPolygon() as $point) {
            $polygon[] = $point->toArray();
        }
        
        return [
            'key' => $this->getKey(),
            'shape' => $this->getShape(),
            'pos' => $this->getPos()->toArray(),
            'end' => $this->getEnd()->toArray(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'polygon' => $polygon
        ];
    }
    
    
    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getShape(): string
    {
        return $this->shape;
    }

    /**
     * @return CorrectionMarkPoint
     */
    public function getPos(): CorrectionMarkPoint
    {
        return $this->pos;
    }

    /**
     * @return CorrectionMarkPoint
     */
    public function getEnd(): CorrectionMarkPoint
    {
        return $this->end;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return CorrectionMarkPoint[]
     */
    public function getPolygon(): array
    {
        return $this->polygon;
    }
}