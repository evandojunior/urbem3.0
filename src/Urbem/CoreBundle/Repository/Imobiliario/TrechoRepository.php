<?php

namespace Urbem\CoreBundle\Repository\Imobiliario;

use Doctrine\ORM;

/**
 * Class TrechoRepository
 * @package Urbem\CoreBundle\Repository\Imobiliario
 */
class TrechoRepository extends ORM\EntityRepository
{
    /**
     * @param $params
     * @return array
     */
    public function filtraTrecho($params)
    {
        $andWhere = $this::isEmptyCodLogradouroAndSequencia($params);

        $sqlVariaveis = $this::getSqlVariaveis($params);

        $stSql = $sqlVariaveis[0];
        $stSql2 = $sqlVariaveis[1];

        $ordenacao = $params['ordenacao'];

        if ($ordenacao == 'nomLogradouro') {
            $orderBy = " ORDER BY NL.nom_logradouro, T.cod_trecho ";
        } else {
            $orderBy = " ORDER BY T.cod_logradouro, T.cod_trecho ";
        }

        $sql = "
                SELECT
                    T.*,
                    T.cod_logradouro as trecho,
                    TL.nom_tipo||' '||NL.nom_logradouro as logradouro "

            . $stSql . "
                
                FROM
                    imobiliario.trecho AS T "

            . $stSql2 . "

                   INNER JOIN ( SELECT tmp.* FROM sw_nome_logradouro AS tmp INNER JOIN ( SELECT max(timestamp) AS timestamp
                                    , cod_logradouro
                                FROM sw_nome_logradouro
                                GROUP BY cod_logradouro
                   ) AS tmp2 ON tmp.cod_logradouro = tmp2.cod_logradouro AND tmp.timestamp = tmp2.timestamp )AS NL
                   ON NL.cod_logradouro = T.cod_logradouro,
                   sw_tipo_logradouro AS TL,
                   sw_logradouro      AS L
                WHERE
                    T.cod_logradouro = L.cod_logradouro AND
                    L.cod_logradouro = NL.cod_logradouro AND
                    NL.cod_tipo      = TL.cod_tipo 
                "
            . $andWhere
            . $orderBy;

        $query = $this->_em->getConnection()->prepare($sql);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @return string
     */
    private function isEmptyCodLogradouroAndSequencia($params)
    {
        $logradouro_inicio = $params['codLogradouroDe'];
        $logradouro_fim = $params['codLogradouroAte'];
        $sequencia_inicio = $params['sequenciaDe'];
        $sequencia_fim = $params['sequenciaAte'];

        $andWhere = "";
        if ($logradouro_inicio and !$logradouro_fim) {
            $andWhere .= " AND T.cod_logradouro > " . $logradouro_inicio;
        } elseif (!$logradouro_inicio and $logradouro_fim) {
            $andWhere .= " AND T.cod_logradouro < " . $logradouro_fim;
        } elseif ($logradouro_inicio and $logradouro_fim) {
            $andWhere .= " AND T.cod_logradouro between " . $logradouro_inicio . " AND " . $logradouro_fim;
        }

        if ($sequencia_inicio and !$sequencia_fim) {
            $andWhere .= " AND T.sequencia > " . $sequencia_inicio;
        } elseif (!$sequencia_inicio and $sequencia_fim) {
            $andWhere .= " AND T.sequencia < " . $sequencia_fim;
        } elseif ($sequencia_inicio and $sequencia_fim) {
            $andWhere .= " AND T.sequencia between " . $sequencia_inicio . " AND " . $sequencia_fim;
        }

        return $andWhere;
    }

    /**
     * @return array
     */
    private function getSqlVariaveis($params)
    {
        $rsmd = $params['rsmd'];
        $aliquota = $params['aliquota'];

        $stSql = "";
        if ($rsmd == true) {
            $stSql .= " , m2.valor_m2_territorial, m2.valor_m2_predial, to_char(m2.dt_vigencia, 'dd/mm/YY') as dt_vigencia";
        }

        if ($aliquota == true) {
            $stSql .= " ,aliquota.aliquota_territorial, aliquota.aliquota_predial";
        }

        $stSql2 = "";
        if ($rsmd == true) {
            $stSql2 .= " LEFT JOIN (SELECT *
                      FROM imobiliario.trecho_valor_m2
                         INNER JOIN( SELECT max(timestamp) as timestamp
                          , cod_trecho
                                , cod_logradouro
                            FROM imobiliario.trecho_valor_m2
                          GROUP BY cod_trecho, cod_logradouro) as valor_m2_predial
                      USING( cod_trecho, cod_logradouro) ";
        }

        if ($aliquota == true) {
            $stSql2 .= " LEFT JOIN (SELECT * 
                        FROM imobiliario.trecho_aliquota 
                        INNER JOIN( SELECT max(timestamp) as timestamp 
                            , cod_trecho 
                            , cod_logradouro 
                        FROM imobiliario.trecho_aliquota 
                        GROUP BY cod_trecho, cod_logradouro) as aliquota_temp 
                       USING(cod_trecho, cod_logradouro, timestamp) ) AS aliquota 
                     USING( cod_trecho, cod_logradouro) ";
        }

        return array($stSql, $stSql2);
    }
}
