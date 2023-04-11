<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\ORM\Query\ResultSetMapping;

use const PREG_SPLIT_NO_EMPTY;

/**
 * Repository functions for company. This is in a trait so the
 * inherited DrivingSchool entity can also reuse this in the
 * entity repository without inheritance on the repository itself.
 */
trait CompanyRepositoryTrait
{
    public function search(string $terms, int $limit = 25): array
    {
        $weights = $this->calculateCompanySearchWeights($terms, $limit);

        return $this->getCompanySearchResultsUsingWeights($weights);
    }

    private function calculateCompanySearchWeights(string $searchQueryString, int $limit): array
    {
        $terms = preg_split('~\s+~', trim($searchQueryString), flags: PREG_SPLIT_NO_EMPTY);

        $parameters = [];

        $cases = [];
        foreach ($terms as $index => $term) {
            $cases[] = '(CASE WHEN company.name LIKE :term_'.$index.' THEN '.(10 * mb_strlen($term)).' ELSE 0 END)';
            $cases[] = '(CASE WHEN company.chamber_of_commerce LIKE :term_'.$index.' THEN '.(10 * mb_strlen($term)).' ELSE 0 END)';
            $cases[] = '(CASE WHEN company_contact_method.value LIKE :term_'.$index.' THEN '.(1 * mb_strlen($term)).' ELSE 0 END)';
            $cases[] = '(CASE WHEN company_driving_school.cbr LIKE :term_'.$index.' THEN '.(10 * mb_strlen($term)).' ELSE 0 END)';
            $cases[] = '(CASE WHEN personal.nickname LIKE :term_'.$index.' THEN '.(3 * mb_strlen($term)).' ELSE 0 END)';
            $cases[] = '(CASE WHEN personal.surname LIKE :term_'.$index.' THEN '.(3 * mb_strlen($term)).' ELSE 0 END)';
            $cases[] = '(CASE WHEN user.username LIKE :term_'.$index.' THEN '.(10 * mb_strlen($term)).' ELSE 0 END)';
            $cases[] = '(CASE WHEN personal_contact_method.value LIKE :term_'.$index.' THEN '.(1 * mb_strlen($term)).' ELSE 0 END)';
            $cases[] = '(CASE WHEN address.street_name LIKE :term_'.$index.' THEN '.(1 * mb_strlen($term)).' ELSE 0 END)';
            $cases[] = '(CASE WHEN address.street_number LIKE :term_'.$index.' THEN '.(1 * mb_strlen($term)).' ELSE 0 END)';
            $cases[] = '(CASE WHEN address.city LIKE :term_'.$index.' THEN '.(1 * mb_strlen($term)).' ELSE 0 END)';
            $cases[] = '(CASE WHEN address.zip_code LIKE :term_'.$index.' THEN '.(4 * mb_strlen($term)).' ELSE 0 END)';

            $parameters['term_'.$index] = '%'.$term.'%';
        }

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('weight', 'weight');

        $query = $this->getEntityManager()->createNativeQuery('
            SELECT company.id, SUM('.implode(' + ', $cases).') / COUNT(company.id) AS weight
            FROM company
            LEFT JOIN company_driving_school ON (company.id = company_driving_school.id)
            LEFT JOIN contact_method company_contact_method ON (company.id = company_contact_method.company_id)
            LEFT JOIN address ON (company.id = address.company_id)
            LEFT JOIN company_employee ON (company.id = company_employee.company_id)
            LEFT JOIN personal ON (company_employee.personal_id = personal.id)
            LEFT JOIN contact_method personal_contact_method ON (personal.id = personal_contact_method.personal_id)
            LEFT JOIN user ON (personal.user_id = user.id)
            GROUP BY company.id
            HAVING weight > 0
            ORDER BY weight DESC, company.name ASC
            LIMIT :limit
        ', $rsm);

        $parameters['limit'] = $limit;

        $query->setParameters($parameters);

        // die((new SqlFormatter())->format($query->getSQL()));

        return $query->getResult();
    }

    private function getCompanySearchResultsUsingWeights(array $weights)
    {
        if (empty($weights)) {
            return [];
        }

        $qb = $this->createQueryBuilder('company');
        $qb->andWhere('company.id IN (:ids)');
        $qb->setParameter('ids', array_column($weights, 'id'));

        $qb->orderBy(sprintf(
            'FIELD(HEX(company.id), %s)',
            implode(', ', array_map(static fn ($entry) => sprintf("'%s'", bin2hex($entry['id'])), $weights)),
        ));

        $qb->leftJoin('company.addresses', 'address');
        $qb->addSelect('address');
        $qb->leftJoin('company.contactMethods', 'contactMethod');
        $qb->addSelect('contactMethod');
        $qb->leftJoin('company.employees', 'employee');
        $qb->addSelect('employee');
        $qb->leftJoin('company.dealers', 'dealer');
        $qb->addSelect('dealer');
        $qb->leftJoin('company.paymentMethods', 'paymentMethod');
        $qb->addSelect('paymentMethod');
        $qb->leftJoin('company.remarks', 'remark');
        $qb->addSelect('remark');
        $qb->leftJoin('employee.personal', 'personal');
        $qb->addSelect('personal');
        $qb->leftJoin('personal.user', 'user');
        $qb->addSelect('user');

        // die((new SqlFormatter())->format($qb->getQuery()->getDQL()));

        $results = $qb->getQuery()->getResult();
        foreach ($results as $index => $result) {
            $result->weight = $weights[$index]['weight'];
        }

        return $results;
    }
}
