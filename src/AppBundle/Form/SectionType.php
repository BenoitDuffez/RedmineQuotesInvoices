<?php

namespace AppBundle\Form;

use function Sodium\add;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectionType extends AbstractType
{
	const BUTTON_ADD_ITEM = 'addItem';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('title')
			->add('position')
			->add('rate')
			//->add('quote')
        	->add('items', CollectionType::class, [
        		'entry_type' => ItemType::class
			])
			->add('save', SubmitType::class, [])
			->add(self::BUTTON_ADD_ITEM, SubmitType::class, [
				'label' => 'Add item in section'
			]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Section'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_section';
    }


}
