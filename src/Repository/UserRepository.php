<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Doctrine\LensServiceEntityRepository;
use Lens\Bundle\LensApiBundle\Entity\User;

use function sprintf;

use const PREG_SPLIT_NO_EMPTY;

class UserRepository extends LensServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    // used in itheorie for now (needs decorator).
    public function hasUserByCompanyChamberOfCommerce(string $chamberOfCommerce): bool
    {
        return $this->createQueryBuilder('user')
            ->select('count(user.id)')
            ->leftJoin('user.personal', 'personal')
            ->leftJoin('personal.companies', 'employee')
            ->leftJoin('employee.company', 'company')

            ->andWhere('company.chamberOfCommerce = :chamberOfCommerce')
            ->setParameter('chamberOfCommerce', $chamberOfCommerce)

            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    public function search(string $terms): array
    {
        $weights = $this->calculateUserSearchWeights($terms);

        return $this->getUserSearchResultsUsingWeights($weights);
    }

    private function calculateUserSearchWeights(string $terms): array
    {
        $terms = preg_split('~\s+~', trim($terms), flags: PREG_SPLIT_NO_EMPTY);

        $parameters = [];

        $cases = [];
        foreach ($terms as $index => $term) {
            $cases[] = '(CASE WHEN user.username LIKE :term_'.$index.' THEN '.(20 * mb_strlen($term)).' ELSE 0 END)';
            $cases[] = "(CASE WHEN CONCAT_WS(' ', personal.initials, personal.nickname, personal.surname_affix, personal.surname) LIKE :term_".$index.' THEN '.(6 * mb_strlen($term)).' ELSE 0 END)';
            $cases[] = '(CASE WHEN personal_contact_method.value LIKE :term_'.$index.' THEN '.(1 * mb_strlen($term)).' ELSE 0 END)';
            $cases[] = '(CASE WHEN company.name LIKE :term_'.$index.' THEN '.(1 * mb_strlen($term)).' ELSE 0 END)';

            $parameters['term_'.$index] = '%'.$term.'%';
        }

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('weight', 'weight');

        $query = $this->getEntityManager()->createNativeQuery('
            SELECT user.id, SUM('.implode(' + ', $cases).') AS weight
            FROM user
            LEFT JOIN personal ON (user.id = personal.user_id)
            LEFT JOIN contact_method personal_contact_method ON (personal.id = personal_contact_method.personal_id)
            LEFT JOIN company_employee ON (personal.id = company_employee.personal_id)
            LEFT JOIN company ON (company_employee.company_id = company.id)
            GROUP BY user.id
            HAVING weight > 0
            ORDER BY weight DESC, user.username ASC
            LIMIT :limit
        ', $rsm);

        $parameters['limit'] = 25;

        $query->setParameters($parameters);

        // die((new SqlFormatter())->format($query->getSQL()));

        return $query->getResult();
    }

    private function getUserSearchResultsUsingWeights(array $weights): array
    {
        if (empty($weights)) {
            return [];
        }

        $qb = $this->createQueryBuilder('user');

        $qb->andWhere('user.id IN (:ids)');
        $qb->setParameter('ids', array_column($weights, 'id'));

        $qb->orderBy(sprintf(
            'FIELD(HEX(user.id), %s)',
            implode(', ', array_map(static fn ($entry) => sprintf("'%s'", bin2hex($entry['id'])), $weights)),
        ));

        $qb->leftJoin('user.personal', 'personal');
        $qb->addSelect('personal');
        $qb->leftJoin('personal.contactMethods', 'contactMethod');
        $qb->addSelect('contactMethod');
        $qb->leftJoin('personal.advertisements', 'advertisements');
        $qb->addSelect('advertisements');
        $qb->leftJoin('personal.companies', 'employee');
        $qb->addSelect('employee');
        $qb->leftJoin('employee.company', 'company');
        $qb->addSelect('company');

        // die((new SqlFormatter())->format($qb->getQuery()->getDQL()));

        $results = $qb->getQuery()->getResult();
        foreach ($results as $index => $result) {
            $result->weight = $weights[$index]['weight'];
        }

        return $results;
    }
}
