<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\Events;
use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Brevo\Brevo;
use Lens\Bundle\LensApiBundle\Command\UlidDetails;
use Lens\Bundle\LensApiBundle\Doctrine\Event\GeoLocateListener;
use Lens\Bundle\LensApiBundle\Doctrine\Event\UpdateBrevoListener;
use Lens\Bundle\LensApiBundle\Doctrine\Event\UpdateMeiliSearchListener;
use Lens\Bundle\LensApiBundle\Doctrine\NamespacedUnderscoreNamingStrategy;
use Lens\Bundle\LensApiBundle\Form\Type\AdvertisementChoiceType;
use Lens\Bundle\LensApiBundle\Form\Type\DealerChoiceType;
use Lens\Bundle\LensApiBundle\Form\Type\DriversLicenceChoiceType;
use Lens\Bundle\LensApiBundle\GeoLocate\GeoLocate;
use Lens\Bundle\LensApiBundle\LensApi;
use Lens\Bundle\LensApiBundle\MeiliSearch\CompanySearch;
use Lens\Bundle\LensApiBundle\Repository;
use Lens\Bundle\LensApiBundle\Validator\UniqueAdvertisementValidator;
use Lens\Bundle\LensApiBundle\Validator\UniqueDealerValidator;
use Lens\Bundle\LensApiBundle\Validator\UniqueUserValidator;
use Lens\Bundle\MeiliSearchBundle\LensMeiliSearch;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set(LensApi::class)
        ->args([
            service(ManagerRegistry::class),
            service(Repository\AddressRepository::class),
            service(Repository\AdvertisementRepository::class),
            service(Repository\CompanyRepository::class),
            service(Repository\ContactMethodRepository::class),
            service(Repository\DealerRepository::class),
            service(Repository\DebitRepository::class),
            service(Repository\DriversLicenceRepository::class),
            service(Repository\DrivingSchoolRepository::class),
            service(Repository\EmployeeRepository::class),
            service(Repository\PaymentMethodRepository::class),
            service(Repository\PersonalRepository::class),
            service(Repository\RemarkRepository::class),
            service(Repository\ResultRepository::class),
            service(Repository\UserRepository::class),
        ])

        ->set(DealerChoiceType::class)
        ->tag('form.type')
        ->args([
            service(LensApi::class),
        ])

        ->set(DriversLicenceChoiceType::class)
        ->tag('form.type')
        ->args([
            service(LensApi::class),
        ])

        ->set(AdvertisementChoiceType::class)
        ->tag('form.type')
        ->args([
            service(LensApi::class),
        ])

        ->set(UniqueAdvertisementValidator::class)
        ->tag('validator.constraint_validator')
        ->args([
            service(LensApi::class),
        ])

        ->set(UniqueDealerValidator::class)
        ->tag('validator.constraint_validator')
        ->args([
            service(LensApi::class),
        ])

        ->set(UniqueUserValidator::class)
        ->tag('validator.constraint_validator')
        ->args([
            service(LensApi::class),
        ])

        ->set(NamespacedUnderscoreNamingStrategy::class)

        ->set(GeoLocate::class)
        ->args([
            service(HttpClientInterface::class),
        ])

        ->set(GeoLocateListener::class)
        ->args([
            service(GeoLocate::class),
            service(LoggerInterface::class),
            param('kernel.debug'),
        ])->autoconfigure()

        ->set(UlidDetails::class)
        ->tag('console.command')

        ->set(Repository\AddressRepository::class)->autoWire()->autoConfigure()
        ->set(Repository\AdvertisementRepository::class)->autoWire()->autoConfigure()
        ->set(Repository\CompanyRepository::class)->autoWire()->autoConfigure()
        ->set(Repository\ContactMethodRepository::class)->autoWire()->autoConfigure()
        ->set(Repository\DealerRepository::class)->autoWire()->autoConfigure()
        ->set(Repository\DebitRepository::class)->autoWire()->autoConfigure()
        ->set(Repository\DriversLicenceRepository::class)->autoWire()->autoConfigure()
        ->set(Repository\DrivingSchoolRepository::class)->autoWire()->autoConfigure()
        ->set(Repository\EmployeeRepository::class)->autoWire()->autoConfigure()
        ->set(Repository\PaymentMethodRepository::class)->autoWire()->autoConfigure()
        ->set(Repository\PersonalRepository::class)->autoWire()->autoConfigure()
        ->set(Repository\RemarkRepository::class)->autoWire()->autoConfigure()
        ->set(Repository\ResultRepository::class)->autoWire()->autoConfigure()
        ->set(Repository\UserRepository::class)->autoWire()->autoConfigure()

        // Brevo
        ->set(Brevo::class)
        ->args([
            service(LensApi::class),
            param('lens_lens_api.brevo.api_key'),
            param('lens_lens_api.brevo.subscriber_list'),
            param('lens_lens_api.brevo.dealer_lists'),
        ])

        ->set(UpdateBrevoListener::class)
        ->args([
            service(Brevo::class),
            service(LoggerInterface::class),
        ])
        ->tag('doctrine.event_listener', ['event' => Events::onFlush, 'connection' => 'lens_api'])

        // MeiliSearch
        ->set(UpdateMeiliSearchListener::class)
        ->args([
            service(LensMeiliSearch::class),
        ])
        ->tag('doctrine.event_listener', ['event' => Events::onFlush, 'connection' => 'lens_api'])

        ->set(CompanySearch::class)->autoConfigure()
    ;
};
