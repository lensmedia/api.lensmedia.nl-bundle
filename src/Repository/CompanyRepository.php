<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Doctrine\LensServiceEntityRepository;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use RuntimeException;
use Symfony\Component\Uid\BinaryUtil;

use function chr;

use const OPENSSL_RAW_DATA;
use const OPENSSL_ZERO_PADDING;

class CompanyRepository extends LensServiceEntityRepository
{
    use CompanyRepositoryTrait;

    private const LINKING_CODE_CIPHER = 'aes-256-cfb';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    /**
     * @param Company $company
     * @param string $passphrase allows us to create different variants
     *
     * @return string
     */
    public function linkingCode(Company $company, string $passphrase): string
    {
        if (!$company->affiliate) {
            throw new RuntimeException('Company has no affiliate id yet, make sure it has generated/persisted (auto incr column).');
        }

        $string = substr($company->id->toBinary(), -6) // 6 bytes
            .pack('n', $company->affiliate); // 2 bytes

        $encrypted = openssl_encrypt(
            $string,
            self::LINKING_CODE_CIPHER,
            $passphrase,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            self::iv(),
        );

        return BinaryUtil::toBase($encrypted, BinaryUtil::BASE58);
    }

    public function fromLinkingCode(string $linkingCode, string $passphrase): Company
    {
        $decoded = @BinaryUtil::fromBase($linkingCode, BinaryUtil::BASE58);

        $decrypted = openssl_decrypt(
            $decoded,
            self::LINKING_CODE_CIPHER,
            $passphrase,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            self::iv(),
        );

        if (false === $decrypted) {
            return throw new RuntimeException('Invalid code, decryption failed.');
        }

        $id = substr($decrypted, 0, -2);
        $affiliate = unpack('n', substr($decrypted, -2))[1];

        $company = $this->findOneByAffiliate($affiliate);
        if (!$company) {
            throw new RuntimeException('Invalid code, affiliate not found.');
        }

        if (substr($company->id->toBinary(), -6) !== $id) {
            throw new RuntimeException('Invalid code, affiliate and id do not match.');
        }

        return $company;
    }

    private static function iv(): string
    {
        return str_repeat(chr(0), openssl_cipher_iv_length(self::LINKING_CODE_CIPHER));
    }
}
