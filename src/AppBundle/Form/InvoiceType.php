<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Repository\QuoteRepository;

class InvoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quote', EntityType::class, [
                'class' => 'AppBundle:Quote',
                'query_builder' => function (QuoteRepository $ir) {
                    return $ir->createAvailableQuotesQueryBuilder();
                },
            ])
            ->add('percentage')
            ->add('replacementText', TextareaType::class, [
            	'attr' => [
            		'rows' => 6,
					'class' => 'markdown form-control'
				],
				'required' => false,
			])
			->add('selectedSections', ChoiceType::class, [
				'mapped' => false,
				'required' => false,
				'expanded' => true,
				'multiple' => true,
				'choices' => $options['availableSections']
			]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Invoice',
			'availableSections' => [],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_invoice';
    }


}
