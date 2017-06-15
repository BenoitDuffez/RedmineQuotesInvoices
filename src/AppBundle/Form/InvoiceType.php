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
			$form->add('sections', EntityType::class, array(
                'class'       => 'AppBundle:Section',
				'multiple'    => true,
				'expanded' 	  => true,
				'choices'     => $sections,
                'choice_attr' => function($section, $key, $index) {
                    /* @var Section $section */
                    return [
                        'disabled' => $section->isOption() ? false : 'disabled',
                        'checked' => $section->isOption() ? false : 'checked',
                    ];
                },
                'choice_label' => function($section, $key, $index) {
                    /* @var Section $section */
                    return $section->getTitle();
                },
			));
            
            // If a quote was selected, compute the remaining amount billable for that
            if ($quote !== null) {
                // First look up all the invoices related to that quote
                $invoices = [];
                foreach ($quote->getSections() as $section) {
                    /* @var Section $section */
                    foreach ($section->getInvoices() as $invoice) {
                        /* @var Invoice $invoice */
                        $invoices[$invoice->getId()] = $invoice;
                    }
                }
                // Then for reach invoice remove the amount already invoiced
                $remaining = 100;
                foreach ($invoices as $invoice) {
                    /* @var Invoice $invoice */
                    $remaining -= $invoice->getPercentage();
                }

                // Apply the maximum remaining amount that can be invoiced
                $p = $form->get('percentage');
                $options = $p->getConfig()->getOptions();
                $options['attr'] = array_merge($options['attr'], [
                    'data-slider-max' => $remaining,
                ]);
                $form->add('percentage', null, $options);
            }
		};

		$builder->addEventListener(
			FormEvents::PRE_SET_DATA,
			function (FormEvent $event) use ($formModifier) {
				$invoice = $event->getData();
                /* @var Invoice $data */
				$formModifier($event->getForm(), $invoice->getQuote());
			}
		);

		$builder->get('quote')->addEventListener(
			FormEvents::POST_SUBMIT,
			function (FormEvent $event) use ($formModifier) {
				$quote = $event->getForm()->getData();
                /* var Quote $quote */
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
