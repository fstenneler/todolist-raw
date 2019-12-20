<?php

namespace App\Tests\Unit\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Tests\Unit\Controller\AuthenticationUtil;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private $client = null;
    private $entityManager;
    private $auth;

    public function setUp()
    {
        $this->client = static::createClient();

        // get doctrine
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
            
        $this->auth = new AuthenticationUtil($this->client);
    }

    /**
     * Test list tasks
     *
     * @return void
     */
    public function testlistAction()
    {
        $this->auth->logIn();
        $crawler = $this->client->request('GET', '/tasks');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Créer une tâche', $crawler->text());
        $this->assertContains('', $crawler->filter('.thumbnail h4')->text());
    }

    /**
     * Test list tasks to do
     *
     * @return void
     */
    public function testlistToDoAction()
    {
        $this->auth->logIn();
        $crawler = $this->client->request('GET', '/tasks/to-do');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test list tasks done
     *
     * @return void
     */
    public function testlistDoneAction()
    {
        $this->auth->logIn();
        $crawler = $this->client->request('GET', '/tasks/done');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test create a new task
     *
     * @return void
     */
    public function testCreateAction()
    {
        $this->auth->logIn();

        // get the csrf token
        $crawler = $this->client->request('GET', '/tasks/create');
        $extract = $crawler->filter('input[name="task[_token]"]')->extract(array('value'));
        $csrf_token = $extract[0];

        // set a random number to identify resource
        $randomNumber = rand(1, 1000);

        $this->client->request('POST', '/tasks/create', [
            'task' => [
                'title' => 'test création titre' . $randomNumber,
                'content' => 'test création contenu' . $randomNumber,
                '_token' => $csrf_token
            ]
        ]);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->followRedirect();
        $item = $crawler->filter('.caption')->last()->text();
        $this->assertContains('test création titre' . $randomNumber, $item);
        $this->assertContains('test création contenu' . $randomNumber, $item);
    }

    /**
     * Test edit an existing task
     *
     * @return void
     */
    public function testEditAction()
    {

        $this->auth->logIn();

        // get the last task
        $task = $this->entityManager
            ->getRepository(Task::class)
            ->findOneBy([], ['id' => 'DESC']);

        // create the uri
        $uri = '/tasks/' . $task->getId() . '/edit';

        // get the csrf token
        $crawler = $this->client->request('GET', $uri);
        $extract = $crawler->filter('input[name="task[_token]"]')->extract(array('value'));
        $csrf_token = $extract[0];

        // set a random number to identify resource
        $randomNumber = rand(1, 1000);

        $this->client->request('POST', $uri, [
            'task' => [
                'title' => 'test édition titre' . $randomNumber,
                'content' => 'test édition contenu' . $randomNumber,
                '_token' => $csrf_token
            ]
        ]);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->followRedirect();
        $item = $crawler->filter('.caption')->last()->text();
        $this->assertContains('test édition titre' . $randomNumber, $item);
        $this->assertContains('test édition contenu' . $randomNumber, $item);
    }

    /**
     * Test toggle an existing task
     *
     * @return void
     */
    public function testToogleTaskAction()
    {

        $this->auth->logIn();

        // get the last task
        $task = $this->entityManager
            ->getRepository(Task::class)
            ->findOneBy([], ['id' => 'DESC']);

        // create the uri
        $uri = '/tasks/' . $task->getId() . '/toggle';

        // get the toggle value
        $taskIsDone = $task->isDone();

        $this->client->request('POST', $uri);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        // get the last task
        $task = $this->entityManager
            ->getRepository(Task::class)
            ->findOneBy([], ['id' => 'DESC']);

        if($taskIsDone === 0) {
            $this->assertEquals(1, $task->isDone());
        }
        if($taskIsDone === 1) {
            $this->assertEquals(0, $task->isDone());
        }

    }

    /**
     * Test delete an existing task
     *
     * @return void
     */
    public function testAuthorizedDeleteTaskAction()
    {

        // get the last task
        $task = $this->entityManager
            ->getRepository(Task::class)
            ->findOneBy([], ['id' => 'DESC']);

        // get the task username
        $username = $task->getUser()->getUsername();

        $this->auth->logIn('ROLE_USER', $username);

        // create the uri
        $uri = '/tasks/' . $task->getId() . '/delete';

        // get the task id
        $taskId = $task->getId();

        $this->client->request('POST', $uri);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        // get the last task
        $task = $this->entityManager
            ->getRepository(Task::class)
            ->findOneBy(['id' => $taskId]);

        $this->assertEquals(null, $task);

    }

    /**
     * Test unauthorized task deletion
     *
     * @return void
     */
    public function testUnauthorizedDeleteTaskAction()
    {

        // get the generic user object
        $genericUser = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'user']);

        // get a task with a different user
        $task = $this->entityManager
            ->getRepository(Task::class)
            ->findTaskWithDifferentUser($genericUser);

        // log in with the generic user
        $this->auth->logIn('ROLE_USER', 'user');

        // create the uri
        $uri = '/tasks/' . $task->getId() . '/delete';

        $this->client->request('POST', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

    }

    /**
     * Test that admin can delete anonymous tasks
     *
     * @return void
     */
    public function testAnonymousDeleteTaskAction()
    {

        // get a task with anonymous user
        $task = $this->entityManager
            ->getRepository(Task::class)
            ->findOneBy(['user' => null], ['id' => 'DESC']);

        // log in with the admin user
        $this->auth->logIn('ROLE_ADMIN');

        // create the uri
        $uri = '/tasks/' . $task->getId() . '/delete';

        // get the task id
        $taskId = $task->getId();

        $this->client->request('POST', $uri);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        // get the last task
        $task = $this->entityManager
            ->getRepository(Task::class)
            ->findOneBy(['id' => $taskId]);

        $this->assertEquals(null, $task);

    }

}
