<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Section;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Section controller.
 *
 * @Route("section")
 */
class SectionController extends Controller
{
    /**
     * Lists all section entities.
     *
     * @Route("/", name="section_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $sections = $em->getRepository('AppBundle:Section')->findAll();

        return $this->render('section/index.html.twig', array(
            'sections' => $sections,
        ));
    }

    /**
     * Creates a new section entity.
     *
     * @Route("/new", name="section_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $section = new Section();
        $form = $this->createForm('AppBundle\Form\SectionType', $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($section);
            $em->flush();

            return $this->redirectToRoute('section_show', array('id' => $section->getId()));
        }

        return $this->render('section/new.html.twig', array(
            'section' => $section,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a section entity.
     *
     * @Route("/{id}", name="section_show")
     * @Method("GET")
     */
    public function showAction(Section $section)
    {
        $deleteForm = $this->createDeleteForm($section);

        return $this->render('section/show.html.twig', array(
            'section' => $section,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing section entity.
     *
     * @Route("/{id}/edit", name="section_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Section $section)
    {
        $deleteForm = $this->createDeleteForm($section);
        $editForm = $this->createForm('AppBundle\Form\SectionType', $section);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('section_edit', array('id' => $section->getId()));
        }

        return $this->render('section/edit.html.twig', array(
            'section' => $section,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a section entity.
     *
     * @Route("/{id}", name="section_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Section $section)
    {
        $form = $this->createDeleteForm($section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($section);
            $em->flush();
        }

        return $this->redirectToRoute('section_index');
    }

    /**
     * Creates a form to delete a section entity.
     *
     * @param Section $section The section entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Section $section)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('section_delete', array('id' => $section->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
