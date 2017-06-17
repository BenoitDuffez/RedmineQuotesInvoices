<?php

namespace AppBundle\Controller;

use AppBundle\DBAL\Types\InvoiceStateType;
use AppBundle\Entity\Invoice;
use AppBundle\Repository\InvoiceRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Invoice controller.
 *
 * @Route("invoice")
 */
class InvoiceController extends Controller
{
    /**
     * Lists all invoice entities.
     *
     * @Route("/", name="invoice_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

		$total = $em->getRepository(Invoice::class)->amountByState();
		$totalPaidOption = $total[InvoiceStateType::PAID][InvoiceRepository::OPTIONAL];
		$totalPaid = $totalPaidOption + $total[InvoiceStateType::PAID][InvoiceRepository::BASE];
		$totalPendingOption = $total[InvoiceStateType::SENT][InvoiceRepository::OPTIONAL];
		$totalPending = $totalPendingOption + $total[InvoiceStateType::SENT][InvoiceRepository::BASE];
		$totalInvoiced = $totalPaid + $totalPending;
		$totalInvoicedOption = $totalPaidOption + $totalPendingOption;

		$invoices = $em->getRepository('AppBundle:Invoice')->findAll();

        return $this->render('invoice/index.html.twig', array(
            'invoices' => $invoices,
			'totalPaidOption' => $totalPaidOption,
			'totalPaid' => $totalPaid,
			'totalPendingOption' => $totalPendingOption,
			'totalPending' => $totalPending,
			'totalInvoicedOption' => $totalInvoicedOption,
			'totalInvoiced' => $totalInvoiced,
        ));
    }

    /**
     * Creates a new invoice entity.
     *
     * @Route("/new", name="invoice_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $invoice = new Invoice();
        $form = $this->createForm('AppBundle\Form\InvoiceType', $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invoice->setBillingDate(new \DateTime());
            $invoice->setTitle("");

            $em = $this->getDoctrine()->getManager();
            $em->persist($invoice);
            $em->flush();

            $invoice->updateTitle();
            $em->persist($invoice);
            $em->flush();

            return $this->redirectToRoute('invoice_show', array('id' => $invoice->getId()));
        }

        return $this->render('invoice/new.html.twig', array(
            'invoice' => $invoice,
            'form' => $form->createView(),
        ));
    }

	/**
	 * Finds and displays a invoice entity.
	 *
	 * @Route("/{id}", name="invoice_show")
	 * @Method("GET")
	 * @param Invoice $invoice
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
    public function showAction(Invoice $invoice) {
    	$invoice->getQuote()->initRedmine($this->getParameter('redmine_url'), $this->getParameter('redmine_api_key'));
		$deleteForm = $this->createDeleteForm($invoice);

        return $this->render('invoice/show.html.twig', array(
            'invoice' => $invoice,
            'delete_form' => $deleteForm->createView(),
        ));
    }

	/**
	 * Finds and displays a invoice entity.
	 *
	 * @Route("/{id}/pdf", name="invoice_show_pdf")
	 * @Method("GET")
	 * @param Invoice $invoice Invoice to display
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function showPdfAction(Invoice $invoice)
	{
		$invoice->getQuote()->initRedmine($this->getParameter('redmine_url'), $this->getParameter('redmine_api_key'));

		$html = $this->renderView('invoice/show_pdf.html.twig', ['invoice' => $invoice]);
		$header = $this->renderView('pdf/header_footer.html.twig', ['title' => $invoice->getTitle()]);
		$footer = $this->renderView('pdf/header_footer.html.twig', ['title' => $invoice->getTitle(), 'type' => 'footer']);

		$filename = sprintf("F%s.pdf", $invoice->getTitle());

		$snappy = $this->get('knp_snappy.pdf');
		$snappy->setOption('footer-html', $footer);
		$snappy->setOption('margin-top', 10);
		$snappy->setOption('margin-bottom', 10);
		$snappy->setOption('margin-left', 10);
		$snappy->setOption('margin-right', 10);
		$snappy->setOption('print-media-type', true);

		return new \Symfony\Component\HttpFoundation\Response(
			$snappy->getOutputFromHtml($html),
			200,
			[
				'Content-Type'        => 'application/pdf',
				'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
			]
		);
	}

	/**
	 * @Route("/{id}/mark/{state}", name="invoice_change_state")
	 * @Method("GET")
	 *
	 * @param Request $request
	 * @param Invoice $invoice
	 * @param string $state
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function changeStateAction(Request $request, Invoice $invoice, $state) {
		if (!InvoiceStateType::isValueExist($state)) {
			throw $this->createNotFoundException('The target invoice state does not exist');
		}

		$invoice->setState($state);
		$em = $this->getDoctrine()->getManager();
		$em->persist($invoice);
		$em->flush();

		return $this->redirectToRoute('invoice_show', array('id' => $invoice->getId()));
	}

    /**
     * Displays a form to edit an existing invoice entity.
     *
     * @Route("/{id}/edit", name="invoice_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Invoice $invoice)
    {
        $deleteForm = $this->createDeleteForm($invoice);
        $editForm = $this->createForm('AppBundle\Form\InvoiceType', $invoice);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('invoice_edit', array('id' => $invoice->getId()));
        }

        return $this->render('invoice/edit.html.twig', array(
            'invoice' => $invoice,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a invoice entity.
     *
     * @Route("/{id}", name="invoice_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Invoice $invoice)
    {
        $form = $this->createDeleteForm($invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($invoice);
            $em->flush();
        }

        return $this->redirectToRoute('invoice_index');
    }

    /**
     * Creates a form to delete a invoice entity.
     *
     * @param Invoice $invoice The invoice entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Invoice $invoice)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('invoice_delete', array('id' => $invoice->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
