<?php

namespace App\Tests\Unit\Controller;

use App\Entity\User;
use App\Tests\Unit\Controller\AuthenticationUtil;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    private $client = null;
    private $auth;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->auth = new AuthenticationUtil($this->client);
    }

    public function testIndexRedirection()
    {

        // test redirection to login page
        $this->client->request('GET', '/');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(true, $this->client->getResponse()->isRedirect('/login'));

        // test response after redirection
        $this->client->request('GET', '/');
        $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testIndexContent()
    {
        $this->auth->logIn();
        $crawler = $this->client->request('GET', '/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

}
