<?php

namespace Lens\Bundle\LensApiBundle\Doctrine;

use const CASE_LOWER;

use function count;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\Inflector\Language;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;

class NamespacedUnderscoreNamingStrategy extends UnderscoreNamingStrategy
{
    private Inflector $inflector;

    public function __construct($case = CASE_LOWER, bool $numberAware = false)
    {
        parent::__construct($case, $numberAware);

        $this->inflector = InflectorFactory::createForLanguage(Language::ENGLISH)
            ->build();
    }

    public function classToTableName($className): string
    {
        $parts = implode('', $this->classToParts($className));

        return parent::classToTableName($parts);
    }

    public function joinTableName($sourceEntity, $targetEntity, $propertyName = null): string
    {
        $sourceEntity = $this->classToParts($sourceEntity);
        $targetEntity = $this->classToParts($targetEntity);

        // shift common indexes
        for ($i = 0; $i < min(count($sourceEntity), count($targetEntity)); ++$i) {
            if ($sourceEntity[$i] === $targetEntity[0]) {
                array_shift($targetEntity);
            }
        }

        if (count($targetEntity)) {
            $last = count($targetEntity) - 1;
            $targetEntity[$last] = $this->inflector->pluralize($targetEntity[count($targetEntity) - 1]);
        } else {
            $last = count($sourceEntity) - 1;
            $sourceEntity[$last] = $this->inflector->pluralize($sourceEntity[count($sourceEntity) - 1]);
        }

        $joinTableName = implode('', $sourceEntity).implode('', $targetEntity);

        return parent::classToTableName($joinTableName);
    }

    /**
     * Converts classes to a split array.
     *
     * @param class-string $className
     *
     * @return string[]
     */
    protected function classToParts(string $className): array
    {
        $parts = explode('\\', mb_strstr($className, 'Entity\\'));
        array_shift($parts); // Removes 'Entity'

        $lastIndex = count($parts) - 1;
        if ((count($parts) > 1) && ($parts[$lastIndex] === $parts[$lastIndex - 1])) {
            array_pop($parts);
        }

        return $parts;
    }
}
