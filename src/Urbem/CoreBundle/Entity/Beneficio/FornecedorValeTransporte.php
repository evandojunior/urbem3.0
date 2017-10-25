<?php
 
namespace Urbem\CoreBundle\Entity\Beneficio;

/**
 * FornecedorValeTransporte
 */
class FornecedorValeTransporte
{
    /**
     * PK
     * @var integer
     */
    private $fornecedorNumcgm;

    /**
     * OneToOne (owning side)
     * @var \Urbem\CoreBundle\Entity\Compras\Fornecedor
     */
    private $fkComprasFornecedor;


    /**
     * Set fornecedorNumcgm
     *
     * @param integer $fornecedorNumcgm
     * @return FornecedorValeTransporte
     */
    public function setFornecedorNumcgm($fornecedorNumcgm)
    {
        $this->fornecedorNumcgm = $fornecedorNumcgm;
        return $this;
    }

    /**
     * Get fornecedorNumcgm
     *
     * @return integer
     */
    public function getFornecedorNumcgm()
    {
        return $this->fornecedorNumcgm;
    }

    /**
     * OneToOne (owning side)
     * Set ComprasFornecedor
     *
     * @param \Urbem\CoreBundle\Entity\Compras\Fornecedor $fkComprasFornecedor
     * @return FornecedorValeTransporte
     */
    public function setFkComprasFornecedor(\Urbem\CoreBundle\Entity\Compras\Fornecedor $fkComprasFornecedor)
    {
        $this->fornecedorNumcgm = $fkComprasFornecedor->getCgmFornecedor();
        $this->fkComprasFornecedor = $fkComprasFornecedor;
        return $this;
    }

    /**
     * OneToOne (owning side)
     * Get fkComprasFornecedor
     *
     * @return \Urbem\CoreBundle\Entity\Compras\Fornecedor
     */
    public function getFkComprasFornecedor()
    {
        return $this->fkComprasFornecedor;
    }
}
