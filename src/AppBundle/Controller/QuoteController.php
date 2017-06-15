<?php

namespace AppBundle\Controller;

use AppBundle\DBAL\Types\QuoteStateType;
use AppBundle\Entity\Item;
use AppBundle\Entity\Quote;
use AppBundle\Entity\Section;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Redmine\Client;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Quote controller.
 *
 * @Route("quote")
 * @Security("has_role('ROLE_USER')")
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

			$em = $this->getDoctrine()->getManager();
			$em->persist($quote);
			$em->flush();

			$quote->updateTitle();
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
	 * List the sections
	 *
	 * @Route("/{id}/sections.{_format}", name="quote_list_sections", requirements={"_format"="json"}, defaults={"id"=0, "_format"="json"})
	 * @Method("GET")
	 * @param Quote $quote Quote to display
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function showSectionsAction(Quote $quote) {
		$data = [];
		foreach ($quote->getSections() as $section) {
			/* @var Section $section */
			$data[] = [
				'id' => $section->getId(),
				'title' => $section->getTitle(),
				'option' => $section->isOption(),
			];
		}
		return new Response(json_encode($data));
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
		$dupe->updateTitle();
		$dupe->setDateCreation(new \DateTime());

		$quote->setState(QuoteStateType::REPLACED);
		$quote->addChild($dupe);
		$dupe->setParent($quote);

		$em = $this->getDoctrine()->getManager();
		$em->persist($dupe);
		$em->persist($quote);
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

		$footerText = "SIRET : 538 795 659 00035 — "
			. "TVA non applicable, art. 293 B du CGI<br />"
            . "Dispensé d’immatriculation au registre du commerce et des sociétés (RCS) en application de l'article L.123-1-1 du Code du Commerce";

        $html = $this->renderView('quote/show_pdf.html.twig', ['quote' => $quote]);
        $header = $this->renderView('pdf/header_footer.html.twig', ['title' => $quote->getTitle(), 'text' => '']);
        $footer = $this->renderView('pdf/header_footer.html.twig', ['quote' => $quote->getTitle(), 'text' => $footerText]);

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
        return $this->render('quote/header_footer.html.twig', ['quote' => $quote]);
    }

	/**
	 * Displays a form to edit an existing quote entity.
	 *
	 * @Route("/{id}/edit", name="quote_edit")
	 * @Method({"GET", "POST"})
	 * @param Request $request
	 * @param Quote $quote
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
	 */
    public function editAction(Request $request, Quote $quote)
    {
        $deleteForm = $this->createDeleteForm($quote);
        $editForm = $this->createForm('AppBundle\Form\QuoteType', $quote);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

			return $this->redirectToRoute('quote_show', array('id' => $quote->getId()));
        }

		return $this->render('quote/edit.html.twig', array(
			'quote' => $quote,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
			'redmine_url' => $this->getParameter('redmine_url'),
		));
    }

	/**
	 * Deletes a quote entity.
	 *
	 * @Route("/{id}", name="quote_delete")
	 * @Method("DELETE")
	 * @param Request $request
	 * @param Quote $quote
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
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
	 * @Route("/{id}/mark/{state}", name="quote_change_state")
	 * @Method("GET")
	 *
	 * @param Request $request
	 * @param Quote $quote
	 * @param string $state
	 * @return Response
	 */
	public function changeStateAction(Request $request, Quote $quote, $state) {
		if (!QuoteStateType::isValueExist($state)) {
			throw $this->createNotFoundException('The target quote state does not exist');
		}

		$quote->setState($state);
		$em = $this->getDoctrine()->getManager();
		$em->persist($quote);
		$em->flush();

		return $this->redirectToRoute('quote_show', array('id' => $quote->getId()));
	}

	/**
	 * Upload the quote to redmine
	 *
	 * @Route("/{id}/upload", name="quote_upload_redmine")
	 * @param Request $request
	 * @param Quote $quote
	 * @return Response
	 */
	public function uploadToRedmineAction(Request $request, Quote $quote) {
		$redmine = new Client($this->getParameter('redmine_url'), $this->getParameter('redmine_api_key'));
		$issues = [];
		$categories = [];

		foreach ($redmine->issue_category->all($quote->getProjectId())['issue_categories'] as $cat) {
			$categories[$cat['name']] = $cat['id'];
		}

		$suffix = "\n\n---\nCreated from " . $this->generateUrl('quote_show', ['id' => $quote->getId()]);

		foreach ($quote->getSections() as $section) {
			foreach ($section->getItems() as $item) {
				/* @var Item $item */
				if (!isset($categories[$section->getTitle()])) {
					$categories[$section->getTitle()]
						= (int) $redmine->issue_category->create(
							$quote->getProjectId(),
							[ 'name' => $section->getTitle() ]
					)->id;
				}
				$issues[] = $redmine->issue->create([
					'project_id' => $quote->getProjectId(),
					'category_id' => $categories[$section->getTitle()],
					'tracker_id' => 12,
					'subject' => explode("\n", $item->getDescription())[0],
					'description' => $item->getDescription() . $suffix,
					'is_private' => true,
					'estimated_hours' => $item->getHours(),
				]);
			}
		}

		$url = sprintf("%s/projects/%s/issues",
			$this->getParameter('redmine_url'),
			$quote->getProjectId()
		);
		return $this->redirect($url);
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
