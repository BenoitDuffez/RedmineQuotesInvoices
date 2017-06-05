<?php

namespace AppBundle\Controller;

use Redmine\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CustomerController
 * @package AppBundle\Controller
 * @Route("customers")
 */
class CustomerController extends Controller
{


	/**
	 * Get detailed information about one customer (plus custom fields)
	 *
	 * @Route("/{userId}", name="customer_info", requirements={"userId": "\d+"}, defaults={"userId": 0}))
	 * @Method("GET")
	 * @param $userId integer Redmine user ID
	 * @return JsonResponse
	 */
	public function customerInfoAction($userId) {
		$redmine = new Client(QuoteController::REDMINE_URL, '0f3be55b17af11b80c7331db4b6aea3f68a5f4ba');
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
		$redmine = new Client(QuoteController::REDMINE_URL, '0f3be55b17af11b80c7331db4b6aea3f68a5f4ba');
		$data = array_merge(
			$redmine->membership->all($projectId, ['limit' => 1000]),
			$redmine->project->show($projectId)
		);
		$response = new JsonResponse($data);
		return $response;
	}
}
