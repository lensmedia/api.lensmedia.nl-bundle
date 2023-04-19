<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\SqlFormatter\SqlFormatter;

use const PREG_SPLIT_NO_EMPTY;

use function count;

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
        [$cases, $parameters] = $this->buildTermsList($searchQueryString);

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

    private function buildTermsList(string $searchQueryString): array
    {
        $terms = preg_split('~\s+~', trim($searchQueryString), flags: PREG_SPLIT_NO_EMPTY);
        $terms = array_unique($terms);

        $parameters = [];
        $cases = [];

        // Add a special first priority case for the term as a whole
        $this->buildTermsListForFields(0, $searchQueryString, $cases, $parameters);

        if (count($terms) > 1) {
            foreach ($terms as $index => $term) {
                $this->buildTermsListForFields($index + 1, $term, $cases, $parameters);
            }
        }

        return [$cases, $parameters];
    }

    private function buildTermsListForFields(int $index, string $term, array &$cases, array &$parameters): void
    {
        array_push($cases, ...$this->buildTermsListForField('company.name', $index, 20));
        array_push($cases, ...$this->buildTermsListForField('company.chamber_of_commerce', $index, 20));
        array_push($cases, ...$this->buildTermsListForField('company_contact_method.value', $index, 5));
        array_push($cases, ...$this->buildTermsListForField('company_driving_school.cbr', $index, 20));
        array_push($cases, ...$this->buildTermsListForField('personal.nickname', $index, 1));
        array_push($cases, ...$this->buildTermsListForField('personal.surname', $index, 1));
        array_push($cases, ...$this->buildTermsListForField('user.username', $index, 10));
        array_push($cases, ...$this->buildTermsListForField('personal_contact_method.value', $index, 5));
        array_push($cases, ...$this->buildTermsListForField('address.street_name', $index, 1));
        array_push($cases, ...$this->buildTermsListForField('address.street_number', $index, 1));
        array_push($cases, ...$this->buildTermsListForField('address.city', $index, 1));
        array_push($cases, ...$this->buildTermsListForField('address.zip_code', $index, 15));

        $parameters['term_exact_'.$index] = $term;
        $parameters['term_start_'.$index] = $term.'%';
        $parameters['term_end_'.$index] = '%'.$term;
        $parameters['term_'.$index] = '%'.$term.'%';
    }

    private function buildTermsListForField(string $field, int $index, int $weight): array
    {
        $weight /= .9 ** ($index + 1);

        $cases = [];
        // Using like - https://stackoverflow.com/questions/22080382/mysql-why-comparing-a-string-to-0-gives-true
        $cases[] = sprintf('(CASE WHEN %s LIKE :term_exact_%d THEN %d ELSE 0 END)', $field, $index, $weight * 10);
        $cases[] = sprintf('(CASE WHEN %s LIKE :term_start_%d THEN %d ELSE 0 END)', $field, $index, $weight * 3);
        $cases[] = sprintf('(CASE WHEN %s LIKE :term_%d THEN %d ELSE 0 END)', $field, $index, $weight * .5);
        $cases[] = sprintf('(CASE WHEN %s LIKE :term_end_%d THEN %d ELSE 0 END)', $field, $index, $weight);

        return $cases;
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
