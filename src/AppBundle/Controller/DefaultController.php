<?php

namespace AppBundle\Controller;

use Redmine\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller {
	/**
	 * @Route("/", name="homepage")
	 */
	public function indexAction(Request $request) {
		// replace this example code with whatever you need
		$redmine = new Client($this->getParameter('redmine_url'), $this->getParameter('redmine_api_key'));

		return $this->render('default/index.html.twig', [
			'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
			'time' => $redmine->time_entry->all([
				'project_id' => 46,
				'spent_on' => '><2017-12-01|2017-12-03'
			]),
		]);
	}
}
