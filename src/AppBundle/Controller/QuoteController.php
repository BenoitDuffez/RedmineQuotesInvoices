<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Quote;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
		foreach ($quotes as $quote) {
			$quote->initRedmine($this->getParameter('redmine_url'), $this->getParameter('redmine_api_key'));
		}

        return $this->render('quote/index.html.twig', array(
            'quotes' => $quotes,
        ));
    }

	/**
	 * Creates a new quote entity.
	 *
	 * @Route("/new", name="quote_new")
	 * @Method({"GET", "POST"})
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
    public function newAction(Request $request)
    {
    	$options = [];
    	$project = null;

        $redmine = new Client($this->getParameter('redmine_url'), $this->getParameter('redmine_api_key'));
        $projectsList = $redmine->project->all(['limit' => 1000]);
		if (isset($projectsList['projects'])) {
			$projects = [];
			foreach ($projectsList['projects'] as $p) {
				$projects[$p['name']] = $p['id'];
			}
			$options['projects_choices'] = $projects;
		}

        $quote = new Quote();
        $form = $this->createForm('AppBundle\Form\QuoteType', $quote, $options);
        $form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$quote->setTitle(sprintf("%d%03d%02d%02d", date('Y'), $quote->getCustomerId(), $quote->getProjectId(), $quote->getId()));
			$quote->setDescription(trim($quote->getDescription()));
			$quote->setDateCreation(new \DateTime());

			// TODO:
			$quote->setPdfPath("TODO");
			$quote->setDateEdition(new \DateTime());

			$em = $this->getDoctrine()->getManager();
			$em->persist($quote);
			$em->flush();

			$quote->setTitle(sprintf("%d%03d%03d%03d", date('Y'), $quote->getCustomerId(), $quote->getProjectId(), $quote->getId()));
			$em->persist($quote);
			$em->flush();

			return $this->redirectToRoute('quote_show', array('id' => $quote->getId()));
		}

        return $this->render('quote/new.html.twig', array(
            'quote' => $quote,
            'form' => $form->createView(),
			'redmine_url' => $this->getParameter('redmine_url'),
        ));
    }

	/**
	 * Finds and displays a quote entity.
	 *
	 * @Route("/{id}", name="quote_show")
	 * @Method("GET")
	 * @param Quote $quote Quote to display
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function showAction(Quote $quote)
	{
		$quote->initRedmine($this->getParameter('redmine_url'), $this->getParameter('redmine_api_key'));
		$deleteForm = $this->createDeleteForm($quote);

		return $this->render('quote/show.html.twig', array(
			'quote' => $quote,
			'delete_form' => $deleteForm->createView(),
		));
	}

	/**
	 * Duplicate a quote entity.
	 *
	 * @Route("/{id}/duplicate", name="quote_duplicate")
	 * @Method("GET")
	 * @param Quote $quote Quote to display
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function duplicateAction(Quote $quote)
	{
		$dupe = clone $quote;

		$em = $this->getDoctrine()->getManager();
		$em->persist($dupe);
		$em->flush();

		return $this->redirectToRoute('quote_show', array('id' => $dupe->getId()));
	}

	/**
	 * Finds and displays a quote entity.
	 *
	 * @Route("/{id}/pdf", name="quote_show_pdf")
	 * @Method("GET")
	 * @param Quote $quote Quote to display
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
    public function showPdfAction(Quote $quote)
    {
        $quote->initRedmine($this->getParameter('redmine_url'), $this->getParameter('redmine_api_key'));

        $html = $this->renderView('quote/show_pdf.html.twig', ['quote' => $quote]);
        $header = $this->renderView('quote/pdf_header.html.twig', ['quote' => $quote]);
        $footer = $this->renderView('quote/pdf_footer.html.twig', ['quote' => $quote]);

        $filename = sprintf("devis_%s.pdf", $quote->getTitle());

        $snappy = $this->get('knp_snappy.pdf');
        $snappy->setOption('header-html', $header);
        $snappy->setOption('footer-html', $footer);
        $snappy->setOption('margin-top', 10);
        $snappy->setOption('margin-bottom', 10);
        $snappy->setOption('margin-left', 10);
        $snappy->setOption('margin-right', 10);
        $snappy->setOption('print-media-type', true);
        return new Response(
            $snappy->getOutputFromHtml($html),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            ]
        );
    }

	/**
	 * Finds and displays a quote entity.
	 *
	 * @Route("/{id}/pdf/footer", name="pdf_footer")
	 * @Method("GET")
	 * @param Quote $quote Quote to display
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
    public function pdfFooterAction(Quote $quote)
    {
        $quote->initRedmine($this->getParameter('redmine_url'), $this->getParameter('redmine_api_key'));
        return $this->render('quote/pdf_footer.html.twig', ['quote' => $quote]);
    }

	/**
	 * Finds and displays a quote entity.
	 *
	 * @Route("/{id}/pdf/header", name="pdf_header")
	 * @Method("GET")
	 * @param Quote $quote Quote to display
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
    public function pdfHeaderAction(Quote $quote)
    {
        $quote->initRedmine($this->getParameter('redmine_url'), $this->getParameter('redmine_api_key'));
        return $this->render('quote/pdf_header.html.twig', ['quote' => $quote]);
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

		return $this->redirectToRoute('quote_show', array('id' => $quote->getId()));
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
