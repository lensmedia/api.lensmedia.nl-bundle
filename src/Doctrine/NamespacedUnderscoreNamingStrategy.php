<?php

namespace Lens\Bundle\LensApiBundle\Doctrine;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\Inflector\Language;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;

use function count;

class NamespacedUnderscoreNamingStrategy extends UnderscoreNamingStrategy
{
    private Inflector $inflector;

    public function __construct()
    {
        parent::__construct();

        $this->inflector = InflectorFactory::createForLanguage(Language::ENGLISH)->build();
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
        // If the class namespace contains Entity, we strip everything else up to it.
        // App\Entity\Namespace\Class => Namespace\Class
        // Vendor\Bundle\VendorBundle\Entity\Class => Class
        $stripEntity = mb_strstr($className, 'Entity\\');
        if (false === $stripEntity) {
            $stripEntity = $className;
        }

        // Removes the first part from the namespace.
        // Usually `Entity` (if split before) or `App`.
        $parts = explode('\\', $stripEntity);
        array_shift($parts);

        // Removes the last part if the last namespace part and the entity class name are the same.
        $lastIndex = count($parts) - 1;
        if ((count($parts) > 1) && ($parts[$lastIndex] === $parts[$lastIndex - 1])) {
            array_pop($parts);
        }

        return $parts;
    }
}
