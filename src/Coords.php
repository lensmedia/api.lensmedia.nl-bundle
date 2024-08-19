<?php

namespace Lens\Bundle\LensApiBundle;

use Brick\Math\BigDecimal;
use Brick\Math\BigNumber;
use OutOfRangeException;

use function sprintf;

class Coords
{
    // Values are rounded to 10ths just for DX readability.
    // http://wiki.gis.com/wiki/index.php/Decimal_degrees

    public const PRECISION_100KM = 0;
    public const PRECISION_10KM = 1;
    public const PRECISION_1KM = 2;

    public const PRECISION_100M = 3;
    public const PRECISION_10M = 4;
    public const PRECISION_1M = 5;

    public const PRECISION_100CM = 5;
    public const PRECISION_10CM = 6;
    public const PRECISION_1CM = 7;

    public const PRECISION_100MM = 6;
    public const PRECISION_10MM = 7;
    public const PRECISION_1MM = 8;

    public const LATITUDE_MIN = -90;
    public const LATITUDE_MAX = 90;

    public const LONGITUDE_MIN = -180;
    public const LONGITUDE_MAX = 180;

    private string $latitude;
    private string $longitude;

    public function __construct(
        BigNumber|int|float|string $latitude = 0,
        BigNumber|int|float|string $longitude = 0,
    ) {
        $this->setLatitude($latitude);
        $this->setLongitude($longitude);
    }

    public function setLatitude(BigNumber|int|float|string $latitude): void
    {
        $latitude = BigDecimal::of($latitude);
        if ($latitude->isLessThan(self::LATITUDE_MIN) || $latitude->isGreaterThan(self::LATITUDE_MAX)) {
            throw new OutOfRangeException('Latitude must be between -90 and 90.');
        }

        $this->latitude = (string)BigDecimal::of($latitude);
    }

    public function getLatitude(): BigDecimal
    {
        return BigDecimal::of($this->latitude);
    }

    public function setLongitude(BigNumber|int|float|string $longitude): void
    {
        $longitude = BigDecimal::of($longitude);
        if ($longitude->isLessThan(self::LONGITUDE_MIN) || $longitude->isGreaterThan(self::LONGITUDE_MAX)) {
            throw new OutOfRangeException('Longitude must be between -180 and 180.');
        }

        $this->longitude = (string)BigDecimal::of($longitude);
    }

    public function getLongitude(): BigDecimal
    {
        return BigDecimal::of($this->longitude);
    }

    public function toArray(): array
    {
        return [$this->latitude, $this->longitude];
    }

    public function toAssociativeArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    public function toString(): string
    {
        return (string)$this;
    }

    public function __toString(): string
    {
        return sprintf('%d°, %d°', $this->latitude, $this->longitude);
    }
}
