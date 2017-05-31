<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Quote;
use AppBundle\Entity\Section;
use AppBundle\Form\QuoteType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;
use Redmine\Client;

/**
 * Quote controller.
 *
 * @Route("quote")
 */
class QuoteController extends Controller
{
    /**
     * Lists all quote entities.
     *
     * @Route("/", name="quote_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $quotes = $em->getRepository('AppBundle:Quote')->findAll();

        return $this->render('quote/index.html.twig', array(
            'quotes' => $quotes,
        ));
    }

    /**
     * Creates a new quote entity.
     *
     * @Route("/new", name="quote_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $quote = new Quote();
        $form = $this->createForm('AppBundle\Form\QuoteType', $quote);
        $form->handleRequest($request);

        if ($form->get(QuoteType::BUTTON_ADD_SECTION)->isClicked()) {
			$quote->addSection(new Section());
			$form = $this->createForm('AppBundle\Form\QuoteType', $quote);
			$form->handleRequest($request);
		}

        if ($form->isSubmitted() && $form->isValid() && $form->get('save')->isClicked()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($quote);
            $em->flush();

            return $this->redirectToRoute('quote_show', array('id' => $quote->getId()));
        }

        $redmine = new Client('https://projects.upactivity.com', '0f3be55b17af11b80c7331db4b6aea3f68a5f4ba');
        $projects = $redmine->project->all(['limit' => 1000]);
        if ($quote->getProjectId() > 0) {
            $customers = [];
            $memberships = $redmine->membership->all($quote->getProjectId(), ['limit' => 1000]);
            foreach ($memberships['memberships'] as $membership) {
                $customers[] = $membership['user'];
            }
        } else {
            $customers = null;
        }

        return $this->render('quote/new.html.twig', array(
            'quote' => $quote,
            'form' => $form->createView(),
            'projects' => $projects,
            'customers' => $customers,
        ));
    }

    /**
     * Finds and displays a quote entity.
     *
     * @Route("/{id}", name="quote_show")
     * @Method("GET")
     */
    public function showAction(Quote $quote)
    {
        $deleteForm = $this->createDeleteForm($quote);

        return $this->render('quote/show.html.twig', array(
            'quote' => $quote,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing quote entity.
     *
     * @Route("/{id}/edit", name="quote_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Quote $quote)
    {
        $deleteForm = $this->createDeleteForm($quote);
        $editForm = $this->createForm('AppBundle\Form\QuoteType', $quote);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('quote_edit', array('id' => $quote->getId()));
        }

        return $this->render('quote/edit.html.twig', array(
            'quote' => $quote,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a quote entity.
     *
     * @Route("/{id}", name="quote_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Quote $quote)
    {
        $form = $this->createDeleteForm($quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($quote);
            $em->flush();
        }

        return $this->redirectToRoute('quote_index');
    }

    /**
     * Creates a form to delete a quote entity.
     *
     * @param Quote $quote The quote entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Quote $quote)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('quote_delete', array('id' => $quote->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
