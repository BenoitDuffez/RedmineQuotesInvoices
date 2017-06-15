<?php

namespace AppBundle\Form;

use AppBundle\Entity\Invoice;
use AppBundle\Entity\Quote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
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
			]);

		$formModifier = function (FormInterface $form, Quote $quote = null) {
			$sections = null === $quote ? array() : $quote->getSections();

			$form->add('section', ChoiceType::class, array(
				'class'       => 'AppBundle:Quote',
				'placeholder' => '',
				'multiple'    => true,
				'expanded' 	  => true,
				'choices'     => $sections,
			));
		};

		$builder->addEventListener(
			FormEvents::PRE_SET_DATA,
			function (FormEvent $event) use ($formModifier) {
				$data = $event->getData();

				$formModifier($event->getForm(), $data->getQuote());
			}
		);

		$builder->get('quote')->addEventListener(
			FormEvents::POST_SUBMIT,
			function (FormEvent $event) use ($formModifier) {
				// It's important here to fetch $event->getForm()->getData(), as
				// $event->getData() will get you the client data (that is, the ID)
				$quote = $event->getForm()->getData();

				// since we've added the listener to the child, we'll have to pass on
				// the parent to the callback functions!
				$formModifier($event->getForm()->getParent(), $quote);
			}
		);
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
