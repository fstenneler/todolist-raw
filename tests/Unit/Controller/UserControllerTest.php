<?php

namespace App\Tests\Unit\Controller;

use App\Entity\User;
use App\Tests\Unit\Controller\AuthenticationUtil;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
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
     * Test list users
     *
     * @return void
     */
    public function testlistAction()
    {
        $this->auth->logIn('ROLE_ADMIN');
        $crawler = $this->client->request('GET', '/users');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Créer un utilisateur', $crawler->text());
    }

    /**
     * Test create a new user
     *
     * @return void
     */
    public function testCreateAction()
    {
        $this->auth->logIn('ROLE_ADMIN');

        // get the csrf token
        $crawler = $this->client->request('GET', '/users/create');
        $extract = $crawler->filter('input[name="user[_token]"]')->extract(array('value'));
        $csrf_token = $extract[0];

        // set a random number to identify resource
        $randomNumber = rand(1, 1000);

        $this->client->request('POST', '/users/create', [
            'user' => [
                'username' => 'test' . $randomNumber,
                'password' => [
                    'first' => 'password' . $randomNumber,
                    'second' => 'password' . $randomNumber,
                ],
                'email' => 'test@test.test' . $randomNumber,
                'roles' => ['ROLE_USER'],
                '_token' => $csrf_token,
            ]
        ]);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->followRedirect();
        $item = $crawler->filter('.table tr')->last()->text();
        $this->assertContains('test' . $randomNumber, $item);
        $this->assertContains('test@test.test' . $randomNumber, $item);
        $this->assertContains('Utilisateur', $item);
    }

    /**
     * Test edit an existing user
     *
     * @return void
     */
    public function testEditAction()
    {
        $this->auth->logIn('ROLE_ADMIN');

        // get the last user
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy([], ['id' => 'DESC']);

        // create the uri
        $uri = '/users/' . $user->getId() . '/edit';

        // get the csrf token
        $crawler = $this->client->request('GET', $uri);
        $extract = $crawler->filter('input[name="user[_token]"]')->extract(array('value'));
        $csrf_token = $extract[0];

        // set a random number to identify resource
        $randomNumber = rand(1, 1000);

        $this->client->request('POST', $uri, [
            'user' => [
                'username' => 'test' . $randomNumber,
                'password' => [
                    'first' => 'password' . $randomNumber,
                    'second' => 'password' . $randomNumber,
                ],
                'email' => 'test@test.test' . $randomNumber,
                'roles' => ['ROLE_USER'],
                '_token' => $csrf_token,
            ]
        ]);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->followRedirect();
        $item = $crawler->filter('.table tr')->last()->text();
        $this->assertContains('test' . $randomNumber, $item);
        $this->assertContains('test@test.test' . $randomNumber, $item);
        $this->assertContains('Utilisateur', $item);
    }

}
