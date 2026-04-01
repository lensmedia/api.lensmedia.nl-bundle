<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\MeiliSearch;

use Doctrine\Common\Collections\Collection;

trait MapAddressesTrait
{
    private function mapAddresses(Collection $collection): array
    {
        $output = [];
        /** @var \Lens\Bundle\LensApiBundle\Entity\Address $address */
        foreach ($collection as $address) {
            $output[$address->type] = $address->streetName.' '.trim($address->streetNumber.' '.$address->addition).', '.$address->zipCode.' '.$address->city;
        }

        return $output;
    }
}
