<?php

namespace Urbem\CoreBundle\Model\Divida;

use Doctrine\ORM\EntityManager;
use Urbem\CoreBundle\AbstractModel;
use Urbem\CoreBundle\Entity\Divida\DividaAtiva;

/**
 * Class ConsultaInscricaoDividaModel
 * @package Urbem\CoreBundle\Model\Divida
 */
class DividaAtivaModel extends AbstractModel
{
    protected $entityManager = null;
    protected $repository;

    /**
     * ConsultaInscricaoDividaModel constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(DividaAtiva::class);
    }

    /**
     * @param $params
     * @return array
     */
    public function filtraInscricaoDividaAtiva($params)
    {
        return $this->repository->filtraInscricaoDividaAtiva($params);
    }
}
