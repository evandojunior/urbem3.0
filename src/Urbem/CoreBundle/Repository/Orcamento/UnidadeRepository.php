<?php

namespace Urbem\CoreBundle\Repository\Orcamento;

use Doctrine\ORM;

class UnidadeRepository extends ORM\EntityRepository
{
    public function getUnidadeNumOrgao($exercicio, $numOrgao)
    {
        $sql = "
        SELECT
            unidade.*,
            unidade.nom_unidade,
            orgao.nom_orgao,
            sw_cgm.nom_cgm AS nome_usuario
        FROM
            orcamento.unidade
            INNER JOIN orcamento.orgao ON unidade.exercicio = orgao.exercicio
                AND unidade.num_orgao = orgao.num_orgao
            INNER JOIN sw_cgm ON sw_cgm.numcgm = unidade.usuario_responsavel
            WHERE
                unidade.exercicio = :exercicio
                AND unidade.num_orgao = :num_orgao
        ";
        
        $query = $this->_em->getConnection()->prepare($sql);
        $query->bindValue('exercicio', $exercicio, \PDO::PARAM_STR);
        $query->bindValue('num_orgao', $numOrgao, \PDO::PARAM_INT);
        
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getByTermAsQueryBuilder($term)
    {
        $qb = $this->createQueryBuilder('Unidade');

        $orx = $qb->expr()->orX();

        $like = $qb->expr()->like('string(Unidade.numUnidade)', ':term');
        $orx->add($like);

        $like = $qb->expr()->like('Unidade.nomUnidade', ':term');
        $orx->add($like);

        $qb->andWhere($orx);

        $qb->setParameter('term', sprintf('%%%s%%', $term));

        $qb->orderBy('Unidade.numUnidade');
        $qb->setMaxResults(10);

        return $qb;
    }
}
