<?php

namespace Urbem\CoreBundle\Repository\Tributario\DividaAtiva;

use Urbem\CoreBundle\Repository\AbstractRepository;

class DividaAtivaRepository extends AbstractRepository
{

    /**
     * @param $params
     * @return array
     */
    public function filtraInscricaoDividaAtiva($params)
    {
        $andWhere = "";

        if (isset($params['periodoDe']) && $params['periodoDe'] != "") {
            $andWhere .= sprintf(" AND divida_ativa.dt_inscricao BETWEEN TO_DATE('%s','dd/mm/yyyy') AND TO_DATE('%s','dd/mm/yyyy')", $params['periodoDe'], $params['periodoAte']);
        }

        if (isset($params['cod_credito']) && $params['cod_credito'] != "") {
            $andWhere .= sprintf(" AND parcela_origem.cod_credito  = %s", $params['cod_credito']);
            $andWhere .= sprintf(" AND parcela_origem.cod_especie  = %s", $params['cod_especie']);
            $andWhere .= sprintf(" AND parcela_origem.cod_genero  = %s", $params['cod_genero']);
            $andWhere .= sprintf(" AND parcela_origem.cod_natureza  = %s", $params['cod_natureza']);
        }

        if (isset($params['cod_grupo']) && $params['cod_grupo'] != "") {
            $andWhere .= sprintf(" AND grupo_credito.cod_grupo  = %s", $params['cod_grupo']);
            $andWhere .= sprintf(" AND grupo_credito.ano_exercicio  = '%s'", $params['grupo_ano_exercicio']);
        }

        if (isset($params['contribuinte']) && $params['contribuinte'] != "") {
            $andWhere .= sprintf(" AND divida_cgm.numcgm IN (%s)", $params['contribuinte']);
        }

        if (isset($params['inscricaoEconomicaDe']) && $params['inscricaoEconomicaDe'] != "") {
            $andWhere .= sprintf(" AND divida_empresa.inscricao_economica >= %s", $params['inscricaoEconomicaDe']);
        }

        if (isset($params['inscricaoEconomicaAte']) && $params['inscricaoEconomicaAte'] != "") {
            $andWhere .= sprintf(" AND divida_empresa.inscricao_economica <= %s", $params['inscricaoEconomicaAte']);
        }

        if (isset($params['inscricaoImobiliariaDe']) && $params['inscricaoImobiliariaDe'] != "") {
            $andWhere .= sprintf(" AND divida_imovel.inscricao_municipal >= %s", $params['inscricaoImobiliariaDe']);
        }

        if (isset($params['inscricaoImobiliariaAte']) && $params['inscricaoImobiliariaAte'] != "") {
            $andWhere .= sprintf(" AND divida_imovel.inscricao_municipal <= %s", $params['inscricaoImobiliariaAte']);
        }

        if (isset($params['inscricaoImobiliariaDe']) && $params['inscricaoImobiliariaDe'] != "" || isset($params['inscricaoImobiliariaAte']) && $params['inscricaoImobiliariaAte'] != "") {
            $select = "CASE WHEN divida_imovel.inscricao_municipal > 0 THEN
                            divida_imovel.inscricao_municipal
                        ELSE
                            divida_empresa.inscricao_economica
                        END ";
        } else {
            $select = "divida_cgm.numcgm ";
        }

        $sql = sprintf("
            SELECT DISTINCT
                    $select AS inscricao_origem
                    , divida_ativa.exercicio
                    , credito.descricao_credito || ' / ' || COALESCE(grupo_credito.descricao, '') AS imposto
                    , divida_ativa.num_livro    AS livro
                    , divida_ativa.num_folha    AS folha
                    , divida_ativa.cod_inscricao || '/' || divida_ativa.exercicio AS ida
                    , SUM(parcela_origem.valor) AS valor_origem
                     FROM divida.divida_ativa
               JOIN (SELECT MIN(divida_parcelamento.num_parcelamento) as num_parcelamento
                               , divida_parcelamento.exercicio
                               , divida_parcelamento.cod_inscricao
                            FROM divida.divida_parcelamento
                        GROUP BY divida_parcelamento.exercicio
                               , divida_parcelamento.cod_inscricao
                         ) AS divida_parcelamento
                   ON divida_parcelamento.exercicio     = divida_ativa.exercicio
                 AND divida_parcelamento.cod_inscricao = divida_ativa.cod_inscricao
                 JOIN divida.divida_cgm
                   ON divida_cgm.exercicio     = divida_ativa.exercicio
                 AND divida_cgm.cod_inscricao = divida_ativa.cod_inscricao
                    JOIN divida.parcelamento
                      ON parcelamento.num_parcelamento = divida_parcelamento.num_parcelamento
                    JOIN divida.parcela_origem
                      ON parcela_origem.num_parcelamento = parcelamento.num_parcelamento
                LEFT JOIN monetario.credito
                       ON credito.cod_credito  = parcela_origem.cod_credito
                      AND credito.cod_natureza = parcela_origem.cod_natureza
                      AND credito.cod_genero   = parcela_origem.cod_genero
                      AND credito.cod_especie  = parcela_origem.cod_especie
                    JOIN arrecadacao.parcela
                      ON parcela.cod_parcela = parcela_origem.cod_parcela
                    JOIN arrecadacao.lancamento
                      ON lancamento.cod_lancamento = parcela.cod_lancamento
               INNER JOIN arrecadacao.lancamento_calculo
                       ON lancamento_calculo.cod_lancamento = lancamento.cod_lancamento
               INNER JOIN arrecadacao.calculo
                       ON calculo.cod_calculo  = lancamento_calculo.cod_calculo
           LEFT JOIN arrecadacao.calculo_grupo_credito
                       ON calculo.cod_calculo = calculo_grupo_credito.cod_calculo
            LEFT JOIN arrecadacao.grupo_credito
                       ON calculo_grupo_credito.cod_grupo     = grupo_credito.cod_grupo
                      AND calculo_grupo_credito.ano_exercicio = grupo_credito.ano_exercicio
                LEFT JOIN divida.divida_empresa
                       ON divida_empresa.exercicio     = divida_ativa.exercicio
                      AND divida_empresa.cod_inscricao = divida_ativa.cod_inscricao

                LEFT JOIN divida.divida_imovel
                       ON divida_imovel.exercicio     = divida_ativa.exercicio
                      AND divida_imovel.cod_inscricao = divida_ativa.cod_inscricao

               INNER JOIN divida.modalidade_vigencia
                       ON modalidade_vigencia.cod_modalidade  = parcelamento.cod_modalidade
                      AND modalidade_vigencia.timestamp       = parcelamento.timestamp_modalidade

               INNER JOIN divida.modalidade
                       ON modalidade.cod_modalidade = modalidade_vigencia.cod_modalidade
         WHERE 1=1
        %s
        GROUP BY inscricao_origem
	         , divida_ativa.exercicio
	         , imposto
	         , livro
	         , folha
	         , ida
        ORDER BY inscricao_origem, ida
        ", $andWhere);

        $query = $this->_em->getConnection()->prepare($sql);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * @param $numParcelamento
     * @param $diasAtraso
     * @return array
     */
    public function getEstornarCobrancaList($numParcelamento = null, $diasAtraso = null)
    {
        $sql = '
        SELECT DISTINCT
                  dp.num_parcelamento,
                  dparc.numero_parcelamento,
                  dparc.exercicio AS exercicio_cobranca,
                  ddc.numcgm,
                  (
                      SELECT
                          nom_cgm
                      FROM
                          sw_cgm
                      WHERE
                          sw_cgm.numcgm = ddc.numcgm
                  )AS nom_cgm,
                  ded.num_documento,
                  vlr.valor AS valor_parcelamento,
                  tot.parcela AS qtd_parcelas,
                  CASE WHEN tot_vencida.parcela IS NULL THEN
                      0
                  ELSE
                      tot_vencida.parcela
                  END AS qtd_parcelas_vencidas,
                  lista_inscricao_por_num_parcelamento( dp.num_parcelamento ) AS inscricao,
                  CASE WHEN ( max_vencida.dt_vencimento_parcela IS NOT NULL ) THEN
                      to_char(now() - max_vencida.dt_vencimento_parcela, \'dd\')::integer
                  ELSE
                      0
                  END AS dias_atraso
              FROM
                  divida.documento AS dd

              LEFT JOIN
                  divida.emissao_documento AS ded
              ON
                  ded.num_parcelamento = dd.num_parcelamento
                  AND ded.cod_documento = dd.cod_documento
                  AND ded.cod_tipo_documento = dd.cod_tipo_documento

              INNER JOIN
                  divida.divida_parcelamento AS ddp
              ON
                  ddp.num_parcelamento = dd.num_parcelamento

              INNER JOIN
                  divida.parcelamento AS dparc
              ON
                  dparc.num_parcelamento = dd.num_parcelamento

              INNER JOIN
                  divida.divida_cgm AS ddc
              ON
                  ddc.cod_inscricao = ddp.cod_inscricao
                  AND ddc.exercicio = ddp.exercicio

              LEFT JOIN
                  divida.divida_cancelada AS ddcanc
              ON
                  ddcanc.cod_inscricao = ddp.cod_inscricao
                  AND ddcanc.exercicio = ddp.exercicio

              INNER JOIN
                  (
                      SELECT
                          SUM(vlr_parcela) AS valor,
                          num_parcelamento
                      FROM
                          divida.parcela
                      WHERE
                          paga = false
                          AND cancelada = false
                      GROUP BY
                          num_parcelamento
                  ) AS vlr
              ON
                  vlr.num_parcelamento = dd.num_parcelamento

              INNER JOIN
                  (
                      SELECT
                          COUNT(num_parcela) AS parcela,
                          num_parcelamento
                      FROM
                          divida.parcela
                      WHERE
                          paga = false
                          AND cancelada = false
                      GROUP BY
                          num_parcelamento
                  ) AS tot
              ON
                  tot.num_parcelamento = dd.num_parcelamento

              LEFT JOIN
                  (
                      SELECT
                          COUNT(num_parcela) AS parcela,
                          num_parcelamento
                      FROM
                          divida.parcela
                      WHERE
                          paga = false
                          AND cancelada = false
                          AND dt_vencimento_parcela < now()
                      GROUP BY
                          num_parcelamento
                  ) AS tot_vencida
              ON
                  tot_vencida.num_parcelamento = dd.num_parcelamento

              LEFT JOIN
                  (
                      SELECT
                          min(dt_vencimento_parcela) AS dt_vencimento_parcela,
                          num_parcelamento
                      FROM
                          divida.parcela
                      WHERE
                          paga = false
                          AND cancelada = false
                          AND now() > dt_vencimento_parcela
                      GROUP BY
                          num_parcelamento
                  )AS max_vencida
              ON
                  max_vencida.num_parcelamento = dd.num_parcelamento

              INNER JOIN
                  divida.parcela AS dp
              ON
                  dp.num_parcelamento = dd.num_parcelamento

             WHERE
             ddcanc.cod_inscricao IS NULL
             %s
        ';

        $where[] = ' AND 1=1';

        if ($numParcelamento) {
            $where[] = 'dp.num_parcelamento = :numParcelamento ';
        }

        if ($diasAtraso) {
            $where[] = '(to_char(now() - max_vencida.dt_vencimento_parcela, \'dd\')::integer) = :diasAtraso';
        }

        $sql = sprintf($sql, implode(' AND ', $where));
        $q = $this->_em->getConnection()->prepare($sql);

        if ($numParcelamento) {
            $q->bindValue('numParcelamento', $numParcelamento, \PDO::PARAM_STR);
        }

        if ($diasAtraso) {
            $q->bindValue('diasAtraso', $diasAtraso, \PDO::PARAM_INT);
        }

        $q->execute();

        return $q->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * @param $numParcelamento
     * @return false|array
     */
    public function getListaInscricaoByNumParcelamento($numParcelamento)
    {
        $sql = 'select lista_inscricao_por_num_parcelamento(:numParcelamento)';

        if (!$numParcelamento) {
            return false;
        }

        $q = $this->_em->getConnection()->prepare($sql);
        $q->bindValue('numParcelamento', $numParcelamento, \PDO::PARAM_STR);
        $q->execute();

        return $q->fetchAll();
    }
}
