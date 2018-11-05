<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Repository\PauseRepository;
use AppBundle\Form\Type\PauseType;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
/**
 * Class PauseController
 * @package AppBundle\Controller
 *
 * @RouteResource("Pause")
 */
class PauseController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Gets a single Pause by ID
     *
     */
    public function getAction($id)
    {
        $pause = $this->getPauseRepository()->findOneByIdQuery($id)->getOneOrNullResult();
        if ($pause === null) {
            return new Response(sprintf('Dont exist pause with id %s', $id),Response::HTTP_NOT_FOUND);
        }
        return $pause;
    }
    /**
     * Gets Pauses by employee
     *
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @ApiDoc(
     *     output="AppBundle\Entity\Pause",
     *     statusCodes={
     *         200 = "Returned when successful",
     *         404 = "Return when not found"
     *     }
     * )
     * @Route("/pauses/my")
     */
    public function myAction(Request $request)
    {

        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();

        $pause= $this->getPauseRepository()->findOnlyOwnQuery($userId)->getResult();

        if ($pause === null) {
            return new View("Dont exist Pause or permission denied");
        }
        return $pause;
    }

    /**
     * Gets all Pauses
     *
     * @return array
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @ApiDoc(
     *     output="AppBundle\Entity\Pause",
     *     statusCodes={
     *         200 = "Returned when successful",
     *         404 = "Return when not found"
     *     }
     * )

     */
    public function cgetAction(Request $request)
    {
        return $this->getPauseRepository()->findAllQuery()->getResult();

    }



    /**
     * Add a new Pause
     * @param Request $request
     * @return View|\Symfony\Component\Form\Form
     *
     * @ApiDoc(
     *     output="AppBundle\Entity\Pause",
     *     statusCodes={
     *         201 = "Returned when a new Pause has been successful created",
     *         404 = "Return when not found"
     *     }
     * )
     * @Route("/pauses/create/{tasks}")
     */
    public function postAction(Request $request, $tasks)
    {
        $task_id= $this->getTaskRepository()->find($tasks);
        $form = $this->createForm(PauseType::class, null, [
            'csrf_protection' => false,
        ]);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }

        $pause = $form->getData();
        $em = $this->getDoctrine()->getManager();

        $pause->setTask($task_id);
        $pause->setDateStarted(new \DateTime('now'));

        $em->persist($pause);
        $em->flush();

        $routeOptions = [
            'id' => $pause->getId(),
            '_format' => $request->get('_format'),
        ];

        $id=$pause->getId();

        $this->routeRedirectView('', $routeOptions, Response::HTTP_CREATED);
        return $this->getPauseRepository()->findOneByIdQuery($id)->getOneOrNullResult();
    }

    /**
     * End of pause
     * @param Request $request
     * @param int     $id
     * @return View|\Symfony\Component\Form\Form
     *
     * @ApiDoc(
     *     input="AppBundle\Form\Type\KeyType",
     *     output="AppBundle\Entity\Key",
     *     statusCodes={
     *         204 = "Returned when an existing key has been successful updated",
     *         400 = "Return when errors",
     *         404 = "Return when not found"
     *     }
     * )
     */
    public function patchAction(Request $request, $id)
    {

        $pause = $this->getPauseRepository()->find($id);
        if ($pause === null) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }
        $form = $this->createForm(PauseType::class, $pause, [
            'csrf_protection' => false,
        ]);
        $form->submit($request->request->all(), false);
        if (!$form->isValid()) {
            return $form;
        }
        $em = $this->getDoctrine()->getManager();


        $pause->setDateFinished(new \DateTime('now'));
        $em->flush();
        $routeOptions = [
            'id' => $pause->getId(),
            '_format' => $request->get('_format'),
        ];
        $this->routeRedirectView('', $routeOptions, Response::HTTP_NO_CONTENT);
        $id=$pause->getId();
        return $this->getPauseRepository()->findOneByIdQuery($id)->getOneOrNullResult();
    }

    /**
     * Delete Pause

     * @return View
     *
     * @ApiDoc(
     *     statusCodes={
     *         204 = "Returned when an existing Pause has been successful deleted",
     *         404 = "Return when not found"
     *     }
     * )
     * @Route("/pause/delete")
     */
    public function deleteAction(Request $request)
    {
        $form = $this->createForm(PauseType::class, null, [
            'csrf_protection' => false,
        ]);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }

        $pauseId = $form->getData()->getId();
        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();

        $pause = $this->getPauseRepository()->deleteQuery($pauseId, $userId)->getOneOrNullResult();
        if ($pause === null) {
            return new View("Not deleted. Doent exist $pauseId or permission denied");
        }
        return new View("Deleted pause $pauseId");
    }

    /**
     * @return PauseRepository
     */
    private function getPauseRepository()
    {
        return $this->get('crv.doctrine_entity_repository.pause');
    }
    private function getTaskRepository()
    {
        return $this->get('crv.doctrine_entity_repository.task');
    }
}
