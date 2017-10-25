<?php

namespace Urbem\CoreBundle\Repository\Orcamento;

use Doctrine\ORM;

class OrgaoRepository extends ORM\EntityRepository
{
    public function getByTermAsQueryBuilder($term)
    {
        $qb = $this->createQueryBuilder('Orgao');

        $orx = $qb->expr()->orX();

        $like = $qb->expr()->like('string(Orgao.numOrgao)', ':term');
        $orx->add($like);

        $like = $qb->expr()->like('Orgao.nomOrgao', ':term');
        $orx->add($like);

        $qb->andWhere($orx);

        $qb->setParameter('term', sprintf('%%%s%%', $term));

        $qb->orderBy('Orgao.numOrgao');
        $qb->setMaxResults(10);

        return $qb;
    }
}
