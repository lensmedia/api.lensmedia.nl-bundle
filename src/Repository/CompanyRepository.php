<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use RuntimeException;
use Symfony\Component\Uid\BinaryUtil;

use function chr;

use const OPENSSL_RAW_DATA;
use const OPENSSL_ZERO_PADDING;

class CompanyRepository extends ServiceEntityRepository
{
    use CompanyRepositoryTrait;

    private const LINKING_CODE_CIPHER = 'aes-256-cfb';
    private const LINKING_CODE_KEY = '22HBy88OB8ROcRSM';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    /**
     * Create linking code for company using partial ID and affiliate id.
     */
    public function linkingCode(Company $company): string
    {
        if (!$company->affiliate) {
            throw new RuntimeException('Company has no affiliate id yet, make sure it has generated.');
        }

        $string = substr($company->id->toBinary(), -6) // 6 bytes
            .pack('n', $company->affiliate); // 2 bytes

        $encrypted = openssl_encrypt(
            $string,
            self::LINKING_CODE_CIPHER,
            self::LINKING_CODE_KEY,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            self::iv(),
        );

        return BinaryUtil::toBase($encrypted, BinaryUtil::BASE58);
    }

    public function fromLinkingCode(string $linkingCode): Company
    {
        $decoded = BinaryUtil::fromBase($linkingCode, BinaryUtil::BASE58);

        $decrypted = openssl_decrypt(
            $decoded,
            self::LINKING_CODE_CIPHER,
            self::LINKING_CODE_KEY,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            self::iv(),
        );

        if (false === $decrypted) {
            return throw new RuntimeException('Invalid linking code, decryption failed.');
        }

        $id = substr($decrypted, 0, -2);
        $affiliate = unpack('n', substr($decrypted, -2))[1];

        $company = $this->findOneByAffiliate($affiliate);
        if (!$company) {
            throw new RuntimeException('Invalid linking code, affiliate not found.');
        }

        if (substr($company->id->toBinary(), -6) !== $id) {
            throw new RuntimeException('Invalid linking code, affiliate and id do not match.');
        }

        return $company;
    }

    private static function iv(): string
    {
        return str_repeat(chr(0), openssl_cipher_iv_length(self::LINKING_CODE_CIPHER));
    }
}
