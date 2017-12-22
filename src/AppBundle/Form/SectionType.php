<?php

namespace AppBundle\Form;

use AppBundle\Entity\Section;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectionType extends AbstractType {
	const BUTTON_ADD_ITEM = 'addItem';

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('title')
				->add('rate')//->add('quote')
				->add('items', CollectionType::class, [
				'entry_type' => ItemType::class,
				'allow_add' => true,
				'allow_delete' => true,
				'prototype' => true,
				'attr' => ['class' => 'items'],
				'by_reference' => false,
			])
				->add('position', HiddenType::class, [
					'attr' => [
						'class' => 'section-position',
					],
				])
				->add('option', CheckboxType::class, [
					'label' => 'Optional',
					'required' => false,
				]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => Section::class,
			'allow_add' => false,
			'allow_delete' => false,
			'prototype' => true,
			'prototype_data' => null,
			'prototype_name' => '__item_name__',
			'entry_type' => __NAMESPACE__ . '\ItemType',
			'entry_options' => array(),
			'delete_empty' => false,
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix() {
		return 'appbundle_section';
	}


}
