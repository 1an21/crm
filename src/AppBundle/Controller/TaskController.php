<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Repository\TaskRepository;
use AppBundle\Form\Type\TaskType;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
/**
 * Class TaskController
 * @package AppBundle\Controller
 *
 * @RouteResource("Task")
 */
class TaskController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Gets a single Task by ID
     *
     */
    public function getAction($id)
    {
        $task = $this->getTaskRepository()->findOneByIdQuery($id)->getOneOrNullResult();
        if ($task === null) {
            return new Response(sprintf('Dont exist task with id %s', $id),Response::HTTP_NOT_FOUND);
        }
        return $task;
    }
    /**
     * Gets Tasks by employee
     *
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @ApiDoc(
     *     output="AppBundle\Entity\Task",
     *     statusCodes={
     *         200 = "Returned when successful",
     *         404 = "Return when not found"
     *     }
     * )
     * @Route("/tasks/my")
     */
    public function myAction(Request $request)
    {

        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();

        $task= $this->getTaskRepository()->findOnlyOwnQuery($userId)->getResult();

        if ($task === null) {
            return new View("Dont exist Task or permission denied");
        }
      return $task;
    }

    /**
     * Gets all Tasks
     *
     * @return array
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @ApiDoc(
     *     output="AppBundle\Entity\Task",
     *     statusCodes={
     *         200 = "Returned when successful",
     *         404 = "Return when not found"
     *     }
     * )

     */
    public function cgetAction(Request $request)
    {
        return $this->getTaskRepository()->findAllQuery()->getResult();

    }



    /**
     * Add a new Task
     * @param Request $request
     * @return View|\Symfony\Component\Form\Form
     *
     * @ApiDoc(
     *     output="AppBundle\Entity\Task",
     *     statusCodes={
     *         201 = "Returned when a new Task has been successful created",
     *         404 = "Return when not found"
     *     }
     * )
     * @Route("/tasks/create")
     */
    public function postAction(Request $request)
    {
        $form = $this->createForm(TaskType::class, null, [
            'csrf_protection' => false,
        ]);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }

        $task = $form->getData();
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $task->setUser($user);


        $em->persist($task);
        $em->flush();

        $routeOptions = [
            'id' => $task->getId(),
            '_format' => $request->get('_format'),
        ];

        $id=$task->getId();

        $this->routeRedirectView('', $routeOptions, Response::HTTP_CREATED);
        return $this->getTaskRepository()->findOneByIdQuery($id)->getOneOrNullResult();
    }

    /**
     * Update a key
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

        $task = $this->getTaskRepository()->find($id);
        if ($task === null) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }
        $form = $this->createForm(TaskType::class, $task, [
            'csrf_protection' => false,
        ]);
        $form->submit($request->request->all(), false);
        if (!$form->isValid()) {
            return $form;
        }
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        $routeOptions = [
            'id' => $task->getId(),
            '_format' => $request->get('_format'),
        ];
        $this->routeRedirectView('', $routeOptions, Response::HTTP_NO_CONTENT);
        $id=$task->getId();
        return $this->getTaskRepository()->findOneByIdQuery($id)->getOneOrNullResult();
    }

    /**
     * Delete Task

     * @return View
     *
     * @ApiDoc(
     *     statusCodes={
     *         204 = "Returned when an existing Task has been successful deleted",
     *         404 = "Return when not found"
     *     }
     * )
     * @Route("/task/delete")
     */
    public function deleteAction(Request $request)
    {
        $form = $this->createForm(TaskType::class, null, [
            'csrf_protection' => false,
        ]);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }

        $taskId = $form->getData()->getId();
        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();

        $task = $this->getTaskRepository()->deleteQuery($taskId, $userId)->getOneOrNullResult();
        if ($task === null) {
            return new View("Not deleted. Doent exist $taskId or permission denied");
        }
        return new View("Deleted task $taskId");
    }

    /**
     * @return TaskRepository
     */
    private function getTaskRepository()
    {
        return $this->get('crv.doctrine_entity_repository.task');
    }
}
