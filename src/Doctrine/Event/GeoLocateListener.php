<?php

namespace Lens\Bundle\LensApiBundle\Doctrine\Event;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Lens\Bundle\LensApiBundle\Entity\Address;
use Lens\Bundle\LensApiBundle\GeoLocate\GeoLocate;
use Lens\Bundle\LensApiBundle\GeoLocate\GeoLocateException;
use Psr\Log\LoggerInterface;

#[AsDoctrineListener(event: Events::prePersist, connection: 'lens_api')]
#[AsDoctrineListener(event: Events::preUpdate, connection: 'lens_api')]
class GeoLocateListener
{
    public function __construct(
        private readonly GeoLocate $geoLocate,
        private readonly LoggerInterface $logger,
        private readonly bool $isDebug,
    ) {
    }

    public function onPrePersist(PrePersistEventArgs $event): void
    {
        $this->handleEvent($event);
    }

    public function onPreUpdate(PreUpdateEventArgs $event): void
    {
        $this->handleEvent($event);
    }

    private function handleEvent(PrePersistEventArgs|PreUpdateEventArgs $event): void
    {
        $address = $event->getObject();
        if (!($address instanceof Address)) {
            return;
        }

        try {
            [$address->latitude, $address->longitude] = ($this->geoLocate)($address);
        } catch (GeoLocateException $e) {
            if ($this->isDebug) {
                throw $e;
            }

            $this->logger->error($e->getMessage());
        }
    }
}
