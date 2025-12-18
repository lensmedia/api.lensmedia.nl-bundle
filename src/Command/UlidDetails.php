<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Command;

use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Ulid;

#[AsCommand(name: 'lens-api:ulid-details', description: 'Converts input ulid(s) to various formats.')]
class UlidDetails extends Command
{
    protected function configure(): void
    {
        $this->addArgument('codes', InputArgument::IS_ARRAY);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $data = [];
        foreach ($input->getArgument('codes') as $code) {
            try {
                if (preg_match('~^(0x)?[\da-f]{32}$~i', $code)) {
                    $ulid = Ulid::fromBinary(hex2bin(preg_replace('~^0x~', '', $code)));
                } else {
                    $ulid = Ulid::fromString($code);
                }

                $data[] = [
                    'v',
                    $ulid->toBase32(),
                    $ulid->toBase58(),
                    $ulid->toRfc4122(),
                    '0x'.mb_strtolower(bin2hex($ulid->toBinary())),
                    $ulid->getDateTime()->format('Y-m-d'),
                    '-',
                ];
            } catch (Exception $exception) {
                $data[] = ['x', null, null, null, null, null, $exception->getMessage()];
            }
        }

        $io->table(['', 'base_32', 'base_58', 'rfc_4122', 'rfc_4122 (sql formatted)', 'timestamp', 'error'], $data);

        return 0;
    }
}
