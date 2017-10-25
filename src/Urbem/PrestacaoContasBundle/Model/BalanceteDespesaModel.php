<?php

namespace Urbem\PrestacaoContasBundle\Model;

/**
 * Class BalanceteDespesaModel
 *
 * @package Urbem\PrestacaoContasBundle\Model\Orcamento
 */
class BalanceteDespesaModel extends AbstractTransparenciaModel
{
    /**
     * @param string    $exercicio
     * @param \DateTime $dataInicial
     * @param \DateTime $dataFinal
     * @param array     $entidades
     *
     * @return mixed
     */
    public function getDadosExportacao($exercicio, \DateTime $dataInicial, \DateTime $dataFinal, array $entidades)
    {
        $sql = <<<SQL
SELECT
  LPAD(cod_entidade :: VARCHAR, 2, '0')                                                             AS cod_entidade,
  cod_despesa,
  LPAD(num_orgao :: VARCHAR, 5, '0')                                                                AS num_orgao,
  LPAD(num_unidade :: VARCHAR, 5, '0')                                                              AS num_unidade,
  LPAD(cod_funcao :: VARCHAR, 2, '0')                                                               AS cod_funcao,
  LPAD(cod_subfuncao :: VARCHAR, 3, '0')                                                            AS cod_subfuncao,
  LPAD(cod_programa :: VARCHAR, 4, '0')                                                             AS cod_programa,
  LPAD(num_pao :: VARCHAR, 5, '0')                                                                  AS num_pao,
  LPAD(replace(tabela.classificacao, '.', ''), 14, '0')                                             AS cod_subelemento,
  LPAD(cod_recurso :: VARCHAR, 4, '0')                                                              AS cod_recurso,
  LPAD(REPLACE(saldo_inicial :: VARCHAR, '.', ''), 13, '0')                                         AS saldo_inicial,
  LPAD(0 :: VARCHAR, 13, '0')                                                                       AS atualizacao,
  LPAD(REPLACE(coalesce(tabela.credito_suplementar, 0.00) :: VARCHAR, '.', ''), 13,
       '0')                                                                                         AS credito_suplementar,
  LPAD(REPLACE(coalesce(tabela.credito_especial, 0.00) :: VARCHAR, '.', ''), 13, '0')               AS credito_especial,
  LPAD(REPLACE(coalesce(tabela.credito_extraordinario, 0.00) :: VARCHAR, '.', ''), 13,
       '0')                                                                                         AS credito_extraordinario,
  LPAD(REPLACE(coalesce(tabela.reducoes, 0.00) :: VARCHAR, '.', ''), 13, '0')                       AS reducoes,
  LPAD(REPLACE(tabela.suplementacoes :: VARCHAR, '.', ''), 13, '0')                                 AS suplementacoes,
  LPAD('0', 13, '0')                                                                                AS reducao,
  SUM(tabela.empenhado_per)                                                                         AS empenhado_per,
  SUM(tabela.anulado_per)                                                                           AS anulado_per,
  LPAD(REPLACE(SUM(tabela.liquidado_per) :: VARCHAR, '.', ''), 13, '0')                             AS liquidado_per,
  LPAD(REPLACE(SUM(tabela.pago_per) :: VARCHAR, '.', ''), 13, '0')                                  AS pago_per,
  tabela.total_creditos                                                                             AS total_creditos,
  LPAD(REPLACE((SUM(tabela.empenhado_per) - SUM(tabela.anulado_per)) :: VARCHAR, '.', ''), 13, '0') AS vl_empenho
FROM orcamento.fn_balancete_despesa(:exercicio, :in_query, :dataInicial, :dataFinal, '',
                                    '', '', '', '', '', '', '') AS tabela(
     exercicio CHAR(4),
     cod_despesa INTEGER,
     cod_entidade INTEGER,
     cod_programa INTEGER,
     cod_conta INTEGER,
     num_pao INTEGER,
     num_orgao INTEGER,
     num_unidade INTEGER,
     cod_recurso INTEGER,
     cod_funcao INTEGER,
     cod_subfuncao INTEGER,
     tipo_conta VARCHAR,
     vl_original NUMERIC,
     dt_criacao DATE,
     classificacao VARCHAR,
     descricao VARCHAR,
     num_recurso VARCHAR,
     nom_recurso VARCHAR,
     nom_orgao VARCHAR,
     nom_unidade VARCHAR,
     nom_funcao VARCHAR,
     nom_subfuncao VARCHAR,
     nom_programa VARCHAR,
     nom_pao VARCHAR,
     empenhado_ano NUMERIC,
     empenhado_per NUMERIC,
     anulado_ano NUMERIC,
     anulado_per NUMERIC,
     pago_ano NUMERIC,
     pago_per NUMERIC,
     liquidado_ano NUMERIC,
     liquidado_per NUMERIC,
     saldo_inicial NUMERIC,
     suplementacoes NUMERIC,
     reducoes NUMERIC,
     total_creditos NUMERIC,
     credito_suplementar NUMERIC,
     credito_especial NUMERIC,
     credito_extraordinario NUMERIC,
     num_programa VARCHAR,
     num_acao VARCHAR
     )
GROUP BY
  tabela.cod_entidade,
  tabela.num_orgao,
  tabela.num_unidade,
  tabela.cod_funcao,
  tabela.cod_subfuncao,
  tabela.cod_programa,
  tabela.num_pao,
  tabela.cod_recurso,
  replace(tabela.classificacao, '.', ''),
  tabela.cod_despesa,
  tabela.saldo_inicial,
  tabela.total_creditos,
  tabela.reducoes,
  tabela.credito_suplementar,
  tabela.credito_especial,
  tabela.credito_extraordinario,
  tabela.suplementacoes;
SQL;

        return $this->getQueryResults($sql, [
            'exercicio'   => $exercicio,
            'in_query'    => 'AND OD.cod_entidade IN (' . implode(',', $entidades) . ')',
            'dataInicial' => $dataInicial->format('d/m/Y'),
            'dataFinal'   => $dataFinal->format('d/m/Y'),
        ]);
    }
}
