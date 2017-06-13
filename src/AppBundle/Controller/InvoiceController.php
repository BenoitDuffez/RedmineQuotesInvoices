<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Invoice;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

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

        $invoices = $em->getRepository('AppBundle:Invoice')->findAll();

        return $this->render('invoice/index.html.twig', array(
            'invoices' => $invoices,
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
            $em = $this->getDoctrine()->getManager();
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
     */
    public function showAction(Invoice $invoice)
    {
        $deleteForm = $this->createDeleteForm($invoice);

        return $this->render('invoice/show.html.twig', array(
            'invoice' => $invoice,
            'delete_form' => $deleteForm->createView(),
        ));
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
