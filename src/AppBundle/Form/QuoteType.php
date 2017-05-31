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
        $builder
			//->add('title')
			//->add('dateCreation')
			//->add('dateEdition')
			//->add('pdfPath')
			->add('projectId')
			->add('description')
			->add('sections', CollectionType::class, [
				'entry_type' => SectionType::class
			])
			->add('save', SubmitType::class, [])
			->add(self::BUTTON_ADD_SECTION, SubmitType::class, [
				'label' => 'Add section'
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
