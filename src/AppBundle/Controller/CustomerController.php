<?php

namespace AppBundle\Controller;

use Redmine\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CustomerController
 * @package AppBundle\Controller
 * @Route("customers")
 * @Security("has_role('ROLE_USER')")
 */
class CustomerController extends Controller {
	/**
	 * Get detailed information about one customer (plus custom fields)
	 *
	 * @Route("/{userId}", name="customer_info", requirements={"userId": "\d+"}, defaults={"userId": 0}))
	 * @Method("GET")
	 * @param $userId integer Redmine user ID
	 * @return JsonResponse
	 */
	public function customerInfoAction($userId) {
		$redmine = new Client($this->getParameter('redmine_url'), $this->getParameter('redmine_api_key'));
		$response = new JsonResponse($redmine->user->show($userId, ['include' => ['custom_fields']]));
		return $response;
	}

	/**
	 * Get the list of customers in a project
	 *
	 * @Route("/list/{projectId}", name="customer_list", defaults={"projectId": 0}))
	 * @Method("GET")
	 * @param $projectId string Redmine project identifier (ID or string)
	 * @return JsonResponse
	 */
	public function customerInProjectAction($projectId) {
		$redmine = new Client($this->getParameter('redmine_url'), $this->getParameter('redmine_api_key'));
		$data = array_merge($redmine->membership->all($projectId, ['limit' => 1000]), $redmine->project->show($projectId));
		$response = new JsonResponse($data);
		return $response;
	}
}
