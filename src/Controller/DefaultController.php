<?php

namespace App\Controller;

use App\Entity\Task;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $taskRepository = $this->getDoctrine()->getRepository(Task::class);

        return $this->render(
            'default/index.html.twig',
            [
                'task_to_do' => $taskRepository->count(['isDone' => 0]),
                'task_done' => $taskRepository->count(['isDone' => 1])
            ]
        );
    }
}
