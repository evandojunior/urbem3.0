<?php

namespace Urbem\CoreBundle\Resources\config\Sonata\Filter;

use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\HttpFoundation\Request;
use Urbem\CoreBundle\Entity\SwCgm;
use Urbem\CoreBundle\Resources\config\Sonata\AbstractSonataAdmin as AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * Class SwCgmAdmin
 * @package Urbem\CoreBundle\Resources\config\Sonata
 */
class SwCgmAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'urbem_core_filter_swcgm';
    protected $baseRoutePattern = 'core/filter/swcgm';

    /**
     * @param RouteCollection $routeCollection
     */
    protected function configureRoutes(RouteCollection $routeCollection)
    {
        $routeCollection->clearExcept([]);
        $routeCollection->add('autocomplete', 'autocomplete');
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('numcgm')
            ->add('codResponsavel')
            ->add('nomCgm')
            ->add('logradouro')
            ->add('numero')
            ->add('complemento')
            ->add('bairro')
            ->add('cep')
            ->add('logradouroCorresp')
            ->add('numeroCorresp')
            ->add('complementoCorresp')
            ->add('bairroCorresp')
            ->add('cepCorresp')
            ->add('foneResidencial')
            ->add('ramalResidencial')
            ->add('foneComercial')
            ->add('ramalComercial')
            ->add('foneCelular')
            ->add('eMail')
            ->add('eMailAdcional')
            ->add('dtCadastro')
            ->add('tipoLogradouroCorresp')
            ->add('tipoLogradouro')
            ->add('timestampInclusao')
            ->add('timestampAlteracao')
            ->add('site')
        ;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form->add('autocomplete_field', 'autocomplete', [
            'class' => SwCgm::class,
            'json_from_admin_code' => $this->code,
            'json_query_builder' => function (EntityRepository $repo, $term, Request $request) {
                $term = strtolower($term);

                return $repo->createQueryBuilder('o')
                    ->where('LOWER(o.nomCgm) LIKE :nomCgm')
                    ->setParameter('nomCgm', "%{$term}%")
                    ->setMaxResults(10);
            },
        ]);
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('numcgm')
            ->add('codResponsavel')
            ->add('nomCgm')
            ->add('logradouro')
            ->add('numero')
            ->add('complemento')
            ->add('bairro')
            ->add('cep')
            ->add('logradouroCorresp')
            ->add('numeroCorresp')
            ->add('complementoCorresp')
            ->add('bairroCorresp')
            ->add('cepCorresp')
            ->add('foneResidencial')
            ->add('ramalResidencial')
            ->add('foneComercial')
            ->add('ramalComercial')
            ->add('foneCelular')
            ->add('eMail')
            ->add('eMailAdcional')
            ->add('dtCadastro')
            ->add('tipoLogradouroCorresp')
            ->add('tipoLogradouro')
            ->add('timestampInclusao')
            ->add('timestampAlteracao')
            ->add('site')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }
}
