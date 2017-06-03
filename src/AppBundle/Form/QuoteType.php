<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
    	// List of projects, if provided
		if (isset($options['projects_choices'])) {
			$choices = [];
			foreach ($options['projects_choices'] as $project) {
				$choices[$project['name']] = $project['id'];
			}
			$builder->add('projectId', ChoiceType::class, [
				'choices' => $options['projects_choices'],
				'label' => 'Target project',
			]);
		} else {
			$builder->add('projectId', HiddenType::class);
		}

    	// List of customers, if provided
		if (isset($options['customers_choices'])) {
			$choices = [];
			foreach ($options['customers_choices'] as $customer) {
				$choices[$customer['name']] = $customer['id'];
			}
			$builder->add('customerId', ChoiceType::class, ['choices' => $choices]);
		} else {
			$builder->add('customerId', HiddenType::class);
		}

		$showSections = isset($options['customers_choices']);

        $builder->add('description', TextareaType::class, [
				'label' => 'Global quote description',
				'required' => false,
			]);

		if ($showSections) {
			$builder->add('sections', CollectionType::class, [
				'entry_type' => SectionType::class,
				'entry_options'  => array(
					'entry_type'     => ItemType::class,
					'allow_add'      => true,
					'allow_delete'   => true,
					'prototype'      => true,
					'prototype_name' => '__children_name__',
					'attr'           => array(
						'class' => "items",
					),
				),
				'allow_add' => true,
				'allow_delete' => true,
				'prototype' => true,
				'attr' => array(
					'class' => 'sections'
				),
				'by_reference' => false,
			]);
    	} else {
			$builder->add('sections', CollectionType::class);
		}

		// Submit buttons
		if ($showSections) {
			$builder->add('next', SubmitType::class, ['label' => 'Create quote']);
		} else {
			$builder->add('submit', SubmitType::class, ['label' => 'Next']);
		}
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
