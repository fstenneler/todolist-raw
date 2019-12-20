<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class TaskController extends AbstractController
{
    /**
     * @Route(
     *   "/tasks/{filter}",
     *   name="task_list",
     *   defaults={"filter" = null},
     *   requirements={"filter"="^(to-do|done)$"}
     * )
     */
    public function listAction($filter)
    {
        $parameters = [];
        if($filter === 'to-do') {
            $parameters = ['isDone' => 0];
        }
        if($filter === 'done') {
            $parameters = ['isDone' => 1];
        }
        $tasks = $this->getDoctrine()->getRepository(Task::class)->findBy($parameters);

        return $this->render(
            'task/list.html.twig',
            [
                'tasks' => $tasks
            ]
        );
    }

    /**
     * @Route("/tasks/create", name="task_create")
     */
    public function createAction(Request $request)
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $task->setUser($this->getUser());
            $em->persist($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     */
    public function editAction(Task $task, Request $request)
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     */
    public function toggleTaskAction(Task $task)
    {
        $task->toggle(!$task->isDone());
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     */
    public function deleteTaskAction(Task $task)
    {
        // users can delete only their own tasks
        // anonymous tasks can be deleted only by admin
        if(
            $task->getUser() === $this->getUser()
            || ($task->getUser() === null && $this->isGranted('ROLE_ADMIN'))
        ) {
    
            $em = $this->getDoctrine()->getManager();
            $em->remove($task);
            $em->flush();
    
            $this->addFlash('success', 'La tâche a bien été supprimée.');

            return $this->redirectToRoute('task_list');

        }

        throw new UnauthorizedHttpException('Vous n\'êtes pas autorisé à supprimer cette tâche.');
       
    }
}
