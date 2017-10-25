<?php

namespace Urbem\CoreBundle\Entity\Orcamento;

/**
 * VwClassificacaoReceitaView
 */
class VwClassificacaoReceitaView
{
    /**
     * PK
     * @var string
     */
    private $exercicio;

    /**
     * @var integer
     */
    private $codConta;

    /**
     * @var string
     */
    private $descricao;

    /**
     * @var string
     */
    private $mascaraClassificacao;


    /**
     * Set exercicio
     *
     * @param string $exercicio
     * @return VwClassificacaoReceita
     */
    public function setExercicio($exercicio)
    {
        $this->exercicio = $exercicio;
        return $this;
    }

    /**
     * Get exercicio
     *
     * @return string
     */
    public function getExercicio()
    {
        return $this->exercicio;
    }

    /**
     * Set codConta
     *
     * @param integer $codConta
     * @return VwClassificacaoReceita
     */
    public function setCodConta($codConta = null)
    {
        $this->codConta = $codConta;
        return $this;
    }

    /**
     * Get codConta
     *
     * @return integer
     */
    public function getCodConta()
    {
        return $this->codConta;
    }

    /**
     * Set descricao
     *
     * @param string $descricao
     * @return VwClassificacaoReceita
     */
    public function setDescricao($descricao = null)
    {
        $this->descricao = $descricao;
        return $this;
    }

    /**
     * Get descricao
     *
     * @return string
     */
    public function getDescricao()
    {
        return $this->descricao;
    }

    /**
     * Set mascaraClassificacao
     *
     * @param string $mascaraClassificacao
     * @return VwClassificacaoReceita
     */
    public function setMascaraClassificacao($mascaraClassificacao = null)
    {
        $this->mascaraClassificacao = $mascaraClassificacao;
        return $this;
    }

    /**
     * Get mascaraClassificacao
     *
     * @return string
     */
    public function getMascaraClassificacao()
    {
        return $this->mascaraClassificacao;
    }
}
