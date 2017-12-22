<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


/**
 * User controller.
 *
 * @Route("user")
 */
class UserController extends Controller {
	/**
	 * Log in
	 *
	 * @Route("/login", name="user_login")
	 * @param AuthenticationUtils $authUtils
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function loginAction(AuthenticationUtils $authUtils) {
		$error = $authUtils->getLastAuthenticationError();
		$lastUsername = $authUtils->getLastUsername();

		return $this->render('user/login.html.twig', [
			'last_username' => $lastUsername,
			'error' => $error,
		]);
	}

	/**
	 * Log out
	 *
	 * @Route("/logout", name="user_logout")
	 * @param AuthenticationUtils $authUtils
	 * @return Response
	 */
	public function logoutAction(AuthenticationUtils $authUtils) {
		$error = $authUtils->getLastAuthenticationError();
		$lastUsername = $authUtils->getLastUsername();

		return $this->render('user/login.html.twig', [
			'last_username' => $lastUsername,
			'error' => $error,
		]);
	}

	/**
	 * Register
	 *
	 * @Route("/register", name="user_register")
	 * @param Request $request
	 * @param UserPasswordEncoderInterface $passwordEncoder
	 * @param EntityManagerInterface $em
	 * @return RedirectResponse|Response
	 */
	public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em) {
		// 1) build the form
		$user = new User();
		$form = $this->createForm(UserType::class, $user);

		// 2) handle the submit (will only happen on POST)
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

			// 3) Encode the password (you could also do this via Doctrine listener)
			$password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
			$user->setPassword($password);

			// 4) save the User!
			$em->persist($user);
			$em->flush();

			// ... do any other work - like sending them an email, etc
			// maybe set a "flash" success message for the user

			return $this->redirectToRoute('homepage');
		}

		return $this->render('user/register.html.twig', array('form' => $form->createView()));
	}

	/**
	 * Lists all user entities.
	 *
	 * @Route("/", name="user_index")
	 * @Security("has_role('ROLE_ADMIN')")
	 * @Method("GET")
	 */
	public function indexAction() {
		$em = $this->getDoctrine()
				   ->getManager();

		$users = $em->getRepository(User::class)
					->findAll();

		return $this->render('user/index.html.twig', array(
			'users' => $users,
		));
	}

	/**
	 * Creates a new user entity.
	 *
	 * @Route("/new", name="user_new")
	 * @Security("has_role('ROLE_ADMIN')")
	 * @Method({"GET", "POST"})
	 * @param Request $request
	 * @return RedirectResponse|Response
	 */
	public function newAction(Request $request) {
		$user = new User();
		$form = $this->createForm(UserType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()
					   ->getManager();
			$em->persist($user);
			$em->flush();

			return $this->redirectToRoute('user_show', array('id' => $user->getId()));
		}

		return $this->render('user/new.html.twig', array(
			'user' => $user,
			'form' => $form->createView(),
		));
	}

	/**
	 * Finds and displays a user entity.
	 *
	 * @Route("/{id}", name="user_show")
	 * @Security("has_role('ROLE_ADMIN')")
	 * @Method("GET")
	 * @param User $user
	 * @return Response
	 */
	public function showAction(User $user) {
		$deleteForm = $this->createDeleteForm($user);

		return $this->render('user/show.html.twig', array(
			'user' => $user,
			'delete_form' => $deleteForm->createView(),
		));
	}

	/**
	 * Creates a form to delete a user entity.
	 *
	 * @param User $user The user entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm(User $user) {
		return $this->createFormBuilder()
					->setAction($this->generateUrl('user_delete', array('id' => $user->getId())))
					->setMethod('DELETE')
					->getForm();
	}

	/**
	 * Displays a form to edit an existing user entity.
	 *
	 * @Route("/{id}/edit", name="user_edit")
	 * @Security("has_role('ROLE_ADMIN')")
	 * @Method({"GET", "POST"})
	 * @param Request $request
	 * @param User $user
	 * @return RedirectResponse|Response
	 */
	public function editAction(Request $request, User $user) {
		$deleteForm = $this->createDeleteForm($user);
		$editForm = $this->createForm(UserType::class, $user);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->getDoctrine()
				 ->getManager()
				 ->flush();

			return $this->redirectToRoute('user_edit', array('id' => $user->getId()));
		}

		return $this->render('user/edit.html.twig', array(
			'user' => $user,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		));
	}

	/**
	 * Deletes a user entity.
	 *
	 * @Route("/{id}", name="user_delete")
	 * @Security("has_role('ROLE_ADMIN')")
	 * @Method("DELETE")
	 * @param Request $request
	 * @param User $user
	 * @return RedirectResponse
	 */
	public function deleteAction(Request $request, User $user) {
		$form = $this->createDeleteForm($user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()
					   ->getManager();
			$em->remove($user);
			$em->flush();
		}

		return $this->redirectToRoute('user_index');
	}
}
