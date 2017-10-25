<?php

namespace Urbem\PrestacaoContasBundle\Service\Tribunal\MG\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Urbem\CoreBundle\Entity\Orcamento\Orgao;
use Urbem\CoreBundle\Entity\Orcamento\Unidade;
use Urbem\CoreBundle\Entity\SwCgm;
use Urbem\CoreBundle\Entity\Tcemg\Uniorcam;
use Urbem\CoreBundle\Form\Type\AutoCompleteType;

class ConfiguracaoUnidadeOrcamentariaConversaoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('exercicio', TextType::class, [
            'label' => 'Exercicio',
            'attr' => [
                'readonly' => true,
            ],
            'disabled' => true
        ]);

        $builder->add('num_orgao', TextType::class, [
            'label' => 'Orgão',
            'attr' => [
                'readonly' => true,
            ],
            'disabled' => true
        ]);

        $builder->add('num_unidade', TextType::class, [
            'label' => 'Unidade',
            'attr' => [
                'readonly' => true,
            ],
            'disabled' => true
        ]);

        $builder->add('identificador', ChoiceType::class, [
            'label' => 'Identificador',
            'placeholder' => 'Selecione',
            'attr' => ['class' => 'select2-parameters select2-unidade-orcamentarial-conversao '],
            'choices' => [
                'FUNDEB' => 1,
                'FMS - Fundo Municipal de Saúde' => 2,
                'Controle FMAS - Fundo Municipal de Assitência Social' => 3,
                'FMCA - Fundo Municipal da Criança e do Adolescente' => 4,
                'Outros Fundos' => 99
            ],
            'required' => false,
        ]);

        /* @see gestaoPrestacaoContas/fontes/PHP/TCEMG/instancias/configuracao/FMManterConfiguracaoUnidadeOrcamentaria.php:158 */
        $builder->add('fkSwCgm', AutoCompleteType::class, [
            'label' => 'Ordenador de Despesa',
            'class' => SwCgm::class,
            'json_from_admin_code' => 'core.admin.filter.sw_cgm',
           # 'attr' => ['class' => 'select2-parameters '],
            'route' => [
                'name' => AutoCompleteType::ROUTE_AUTOCOMPLETE_DEFAULT,
                'parameters' => [
                    'json_from_admin_field' => 'autocomplete_field'
                ]
            ],
            'required' => true,
            'constraints' => [new NotNull()]
        ]);

        /* @see gestaoPrestacaoContas/fontes/PHP/TCEMG/instancias/configuracao/FMManterConfiguracaoUnidadeOrcamentaria.php:273 */
        $builder->add('fkOrcamentoOrgaoAtual', AutoCompleteType::class, [
            'label' => 'Orgão Atual',
            'class' => Orgao::class,
            'json_from_admin_code' => 'core.admin.filter.orcamento_orgao',
            'attr' => ['class' => 'select2-parameters select2-unidade-orcamentarial-conversao '],
            'route' => [
                'name' => AutoCompleteType::ROUTE_AUTOCOMPLETE_DEFAULT,
                'parameters' => [
                    'json_from_admin_field' => 'autocomplete_field'
                ]
            ],
        ]);

        /* @see gestaoPrestacaoContas/fontes/PHP/TCEMG/instancias/configuracao/FMManterConfiguracaoUnidadeOrcamentaria.php:273 */
        $builder->add('fkOrcamentoUnidadeAtual', AutoCompleteType::class, [
            'label' => 'Unidade Atual',
            'class' => Unidade::class,
            'json_from_admin_code' => 'core.admin.filter.orcamento_unidade',
            'attr' => ['class' => 'select2-parameters select2-unidade-orcamentarial-conversao '],
            'route' => [
                'name' => AutoCompleteType::ROUTE_AUTOCOMPLETE_DEFAULT,
                'parameters' => [
                    'json_from_admin_field' => 'autocomplete_field'
                ]
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Uniorcam::class);
    }
}