<?php

namespace Urbem\CoreBundle\Repository\Arrecadacao;

use Urbem\CoreBundle\Entity\Arrecadacao\DocumentoEmissao;
use Urbem\CoreBundle\Repository\AbstractRepository;

/**
 * Class DocumentoEmissaoRepository
 * @package Urbem\CoreBundle\Repository\Arrecadacao
 */
class DocumentoEmissaoRepository extends AbstractRepository
{

    /**
     * @param $params
     * @return int
     */
    public function getNextVal($params)
    {
        return $this->nextVal(
            "num_documento",
            [
                'cod_documento' => $params['cod_documento'],
                'exercicio' => $params['exercicio']
            ]
        );
    }

    /**
     * @param $numDocumento
     * @param $exercicio
     * @return array
     */
    public function findDocumentoEmissao($numDocumento, $exercicio)
    {
        $sql =
            '
              SELECT DISTINCT
	                        (
	                            SELECT
	                                sw_cgm_pessoa_fisica.cpf
	                            FROM
	                                sw_cgm_pessoa_fisica
	                            WHERE
	                                sw_cgm_pessoa_fisica.numcgm = COALESCE( prop_imovel.numcgm, eco.numcgm, documento_cgm.numcgm )
	                        )AS cpf,
	                        (
	                            SELECT
	                                sw_cgm_pessoa_juridica.cnpj
	                            FROM
	                                sw_cgm_pessoa_juridica
	                            WHERE
	                                sw_cgm_pessoa_juridica.numcgm = COALESCE( prop_imovel.numcgm, eco.numcgm, documento_cgm.numcgm )
	                        )AS cnpj,
	                        arrecadacao.fn_consulta_endereco_todos(
	                            COALESCE( prop_imovel.inscricao_municipal, eco.inscricao_economica, documento_cgm.numcgm ),
	                            CASE WHEN prop_imovel.inscricao_municipal is not null THEN
	                                1
	                            ELSE
	                                CASE WHEN eco.inscricao_economica IS NOT NULL THEN
	                                    2
	                                ELSE
	                                    3
	                                END
	                            END,
	                            1
	                        )AS endereco,
	                        arrecadacao.fn_consulta_endereco_todos(
	                            COALESCE( prop_imovel.inscricao_municipal, eco.inscricao_economica, documento_cgm.numcgm ),
	                            CASE WHEN prop_imovel.inscricao_municipal is not null THEN
	                                1
	                            ELSE
	                                CASE WHEN eco.inscricao_economica IS NOT NULL THEN
	                                    2
	                                ELSE
	                                    3
	                                END
	                            END,
	                            2
	                        )AS bairro,
	                        arrecadacao.fn_consulta_endereco_todos(
	                            COALESCE( prop_imovel.inscricao_municipal, eco.inscricao_economica, documento_cgm.numcgm ),
	                            CASE WHEN prop_imovel.inscricao_municipal is not null THEN
	                                1
	                            ELSE
	                                CASE WHEN eco.inscricao_economica IS NOT NULL THEN
	                                    2
	                                ELSE
	                                    3
	                                END
	                            END,
	                            3
	                        )AS cep,
	                        arrecadacao.fn_consulta_endereco_todos(
	                            COALESCE( prop_imovel.inscricao_municipal, eco.inscricao_economica, documento_cgm.numcgm ),
	                            CASE WHEN prop_imovel.inscricao_municipal is not null THEN
	                                1
	                            ELSE
	                                CASE WHEN eco.inscricao_economica IS NOT NULL THEN
	                                    2
	                                ELSE
	                                    3
	                                END
	                            END,
	                            4
	                        )AS municipio,
	                        COALESCE( documento_cgm.numcgm, prop_imovel.numcgm, eco.numcgm ) AS numcgm,
	                        (
	                            SELECT
	                                sw_cgm.nom_cgm
	                            FROM
	                                sw_cgm
	                            WHERE
	                                sw_cgm.numcgm = COALESCE( documento_cgm.numcgm, prop_imovel.numcgm, eco.numcgm )
	                        )AS contribuinte,
	                        documento_imovel.inscricao_municipal,
	                        documento_empresa.inscricao_economica,
	                        lpad(documento_emissao.num_documento::varchar,4,\'0\') as num_documento,
	                        documento_emissao.exercicio,
	                        documento.cod_documento,
	                        documento.cod_tipo_documento,
	                        documento.descricao,
	                        to_char( documento_emissao.timestamp, \'dd/mm/YYYY\' ) AS dt_emissao
	
	                    FROM
	                        arrecadacao.documento
	
	                    INNER JOIN
	                        arrecadacao.documento_emissao
	                    ON
	                        documento_emissao.cod_documento = documento.cod_documento
	
	                    LEFT JOIN
	                        arrecadacao.documento_imovel
	                    ON
	                        documento_imovel.num_documento = documento_emissao.num_documento
	                        AND documento_imovel.cod_documento = documento_emissao.cod_documento
	                        AND documento_imovel.exercicio = documento_emissao.exercicio
	
	                    LEFT JOIN
	                        arrecadacao.documento_empresa
	                    ON
	                        documento_empresa.num_documento = documento_emissao.num_documento
	                        AND documento_empresa.cod_documento = documento_emissao.cod_documento
	                        AND documento_empresa.exercicio = documento_emissao.exercicio
	
	                    LEFT JOIN
	                        arrecadacao.documento_cgm
	                    ON
	                        documento_cgm.num_documento = documento_emissao.num_documento
	                        AND documento_cgm.cod_documento = documento_emissao.cod_documento
	                        AND documento_cgm.exercicio = documento_emissao.exercicio
	
	
	                    LEFT JOIN
	                        (
	                            SELECT
	                                prop.*
	                            FROM
	                                imobiliario.proprietario AS prop
	
	                            INNER JOIN
	                                (
	                                    SELECT
	                                        inscricao_municipal,
	                                        MAX( timestamp) AS timestamp
	                                    FROM
	                                        imobiliario.proprietario
	                                    GROUP BY
	                                        inscricao_municipal
	                                )AS temp
	                            ON
	                                temp.inscricao_municipal = prop.inscricao_municipal
	                                AND temp.timestamp = prop.timestamp
	                        ) AS prop_imovel
	                    ON
	                        prop_imovel.inscricao_municipal = documento_imovel.inscricao_municipal
	
	                    LEFT JOIN
	                        (
	                            SELECT DISTINCT
	                                COALESCE( cadastro_economico_autonomo.numcgm, cadastro_economico_empresa_fato.numcgm, cadastro_economico_empresa_direito.numcgm ) AS numcgm,
	                                cadastro_economico.inscricao_economica
	
	                            FROM
	                                economico.cadastro_economico
	
	                            LEFT JOIN
	                                economico.cadastro_economico_autonomo
	                            ON
	                                cadastro_economico_autonomo.inscricao_economica = cadastro_economico.inscricao_economica
	
	                            LEFT JOIN
	                                economico.cadastro_economico_empresa_fato
	                            ON
	                                cadastro_economico_empresa_fato.inscricao_economica = cadastro_economico.inscricao_economica
	
	                            LEFT JOIN
	                                economico.cadastro_economico_empresa_direito
	                            ON
	                                cadastro_economico_empresa_direito.inscricao_economica = cadastro_economico.inscricao_economica
	                        ) AS eco
	                    ON
	                        eco.inscricao_economica = documento_empresa.inscricao_economica
	
	                    WHERE
	                        documento_emissao.num_documento = :numDocumento
	                        AND documento_emissao.exercicio = :exercicio
	
	                    GROUP BY
	                        prop_imovel.numcgm,
	                        eco.numcgm,
	                        documento_cgm.numcgm,
	                        prop_imovel.inscricao_municipal,
	                        eco.inscricao_economica,
	                        documento_imovel.inscricao_municipal,
	                        documento_empresa.inscricao_economica,
	                        documento_emissao.num_documento,
	                        documento_emissao.exercicio,
	                        documento.cod_documento,
	                        documento.cod_tipo_documento,
	                        documento.descricao,
	                        documento_emissao.timestamp ;
	                        ';


        $query = $this->_em->getConnection()->prepare($sql);

        $query->bindValue(':numDocumento', $numDocumento, \PDO::PARAM_INT);
        $query->bindValue(':exercicio', $exercicio, \PDO::PARAM_STR);

        $query->execute();

        return $query->fetch();
    }
}
