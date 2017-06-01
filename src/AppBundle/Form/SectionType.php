<?php

namespace AppBundle\Form;

use function Sodium\add;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
			->add('rate')
			//->add('quote')
        	->add('items', CollectionType::class, [
        		'entry_type' => ItemType::class
			])
			->add('position', HiddenType::class, [
				'attr' => [
					'class' => 'my-position',
				],
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
