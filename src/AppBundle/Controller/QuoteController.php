<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Quote;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Redmine\Client;

/**
 * Quote controller.
 *
 * @Route("quote")
 */
class QuoteController extends Controller
{
	const REDMINE_URL = 'https://projects.upactivity.com';

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
	 * Get detailed information about one customer (plus custom fields)
	 *
	 * @Route("/customer-info/{userId}", name="customer_info", requirements={"userId": "\d+"}, defaults={"userId": 0}))
	 * @Method("GET")
	 * @param $userId integer Redmine user ID
	 * @return JsonResponse
	 */
	public function customerInfoAction($userId) {
		$redmine = new Client(self::REDMINE_URL, '0f3be55b17af11b80c7331db4b6aea3f68a5f4ba');
		$response = new JsonResponse($redmine->user->show($userId, ['include' => ['custom_fields']]));
		return $response;
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

        $redmine = new Client(self::REDMINE_URL, '0f3be55b17af11b80c7331db4b6aea3f68a5f4ba');
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

		$customers = null;
        if ($quote->getProjectId() > 0) {
			$project = $redmine->project->show($quote->getProjectId())['project'];
            $memberships = $redmine->membership->all($quote->getProjectId(), ['limit' => 1000]);
			if ($memberships != null && isset($memberships['memberships'])) {
            	$customers = [];
				foreach ($memberships['memberships'] as $membership) {
					$customers[] = $membership['user'];
				}
            }
        }

		if ($customers != null) {
			$form = $this->createForm('AppBundle\Form\QuoteType', $quote, ['customers_choices' => $customers]);
		}

		if ($form->isSubmitted() && $form->isValid() && $form->get('save')->isClicked()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($quote);
			$em->flush();

			return $this->redirectToRoute('quote_show', array('id' => $quote->getId()));
		}

        return $this->render('quote/new.html.twig', array(
            'quote' => $quote,
            'form' => $form->createView(),
			'project' => $project,
			'redmineUrl' => self::REDMINE_URL,
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
