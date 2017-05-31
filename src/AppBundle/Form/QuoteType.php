<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
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
        $builder
			//->add('title')
			//->add('dateCreation')
			//->add('dateEdition')
			//->add('pdfPath')
			->add('customerId')
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
            'data_class' => 'AppBundle\Entity\Quote'
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
