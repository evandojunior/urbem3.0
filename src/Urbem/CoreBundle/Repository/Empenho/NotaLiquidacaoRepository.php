<?php

namespace Urbem\CoreBundle\Repository\Empenho;

use Doctrine\ORM;

class NotaLiquidacaoRepository extends ORM\EntityRepository
{
    public function getProximoCodNota($codEntidade, $exercicio)
    {
        $sql = sprintf(
            "
            SELECT COALESCE(MAX(cod_nota), 0) + 1 AS CODIGO 
            FROM empenho.nota_liquidacao
            WHERE cod_entidade = %d AND exercicio = '%s'",
            $codEntidade,
            $exercicio
        );

        $query = $this->_em->getConnection()->prepare($sql);
        $query->execute();

        $result = $query->fetch(\PDO::FETCH_OBJ);

        return $result->codigo;
    }
}
