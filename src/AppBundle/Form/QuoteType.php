<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuoteType extends AbstractType
{
	const BUTTON_ADD_SECTION = 'addSection';

	/**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		if (isset($options['customers_choices'])) {
			$choices = [];
			foreach ($options['customers_choices'] as $customer) {
				$choices[$customer['name']] = $customer['id'];
			}
			$builder->add('customerId', ChoiceType::class, ['choices' => $choices]);
		}
		if (isset($options['projects_choices'])) {
			$choices = [];
			foreach ($options['projects_choices'] as $project) {
				$choices[$project['name']] = $project['id'];
			}
			$builder->add('projectId', ChoiceType::class, ['choices' => $options['projects_choices']]);
		}
        $builder
			//->add('title')
			//->add('dateCreation')
			//->add('dateEdition')
			//->add('pdfPath')
			->add('description')
			->add('sections', CollectionType::class, [
				'entry_type' => SectionType::class,
				'allow_add' => true,
				'allow_delete' => true,
				'prototype' => true,
				'attr' => array(
					'class' => 'my-selector',
				),
				'by_reference' => false,
			]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Quote',
			'customers_choices' => null,
			'projects_choices' => null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_quote';
    }


}
