<?php

namespace App\Tests\Unit\Controller;

use App\Entity\User;
use App\Controller\SecurityController;
use App\Tests\Unit\Controller\AuthenticationUtil;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private $client = null;
    private $auth;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->auth = new AuthenticationUtil($this->client);
    }

    /**
     * Test accessing to restricted area without login
     *
     * @return void
     */
    public function testUnauthenticated() {
        $this->client->request('GET', '/tasks');
        $this->assertEquals('302', $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test accessing to restricted area with login
     *
     * @return void
     */
    public function testAuthenticated() {
        $this->auth->logIn();
        $this->client->request('GET', '/tasks');
        $this->assertEquals('200', $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test redirection after login
     *
     * @return void
     */
    public function testLoggedUserRedirectedToHomepage()
    {
        $this->auth->logIn();
        $this->client->request('GET', '/login');
        $this->assertEquals(true, $this->client->getResponse()->isRedirect('/'));
    }

    /**
     * Test error message with invalid username
     *
     * @return void
     */
    public function testUsernameErrorMessage()
    {
        // get the csrf token
        $crawler = $this->client->request('GET', '/login');
        $extract = $crawler->filter('input[name="_csrf_token"]')->extract(array('value'));
        $csrf_token = $extract[0];

        // post login form and follow the redirection
        $this->client->request('POST', '/login', ['_csrf_token' => $csrf_token, 'username' => 'xxx', 'password' => 'xxx']);
        $crawler = $this->client->followRedirect();
        $this->assertContains('Username could not be found.', $crawler->text());
    }

    /**
     * Test error message with invalid password
     *
     * @return void
     */
    public function testPasswordErrorMessage()
    {
        // get the csrf token
        $crawler = $this->client->request('GET', '/login');
        $extract = $crawler->filter('input[name="_csrf_token"]')->extract(array('value'));
        $csrf_token = $extract[0];

        // post login form and follow the redirection
        $this->client->request('POST', '/login', ['_csrf_token' => $csrf_token, 'username' => 'admin', 'password' => 'xxx']);
        $crawler = $this->client->followRedirect();

        // test if response contains the error message
        $this->assertContains('Invalid credentials.', $crawler->text());
    }

    /**
     * Test error message with invalid Csrf token
     *
     * @return void
     */
    public function testCsrfErrorMessage()
    {
        // post login form and follow the redirection
        $this->client->request('POST', '/login', ['_csrf_token' => 'xxx', 'username' => 'admin', 'password' => 'admin']);
        $crawler = $this->client->followRedirect();
        $this->assertContains('Invalid CSRF token.', $crawler->text());
    }

    /**
     * Test accessing to user management with admin role
     *
     * @return void
     */
    public function testAdminToAccessToUserManagement()
    {
        $this->auth->logIn('ROLE_ADMIN');
        $this->client->request('GET', '/users');
        $this->assertEquals('200', $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test accessing to user management with user role
     *
     * @return void
     */
    public function testUserToAccessToUserManagement()
    {
        $this->auth->logIn('ROLE_USER');
        $this->client->request('GET', '/users');
        $this->assertEquals('403', $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test logout
     *
     * @return void
     */
    public function testLogout()
    {
        // test that accessing to an unauthorized route gets a 302 error
        $this->auth->logIn();
        $crawler = $this->client->request('GET', '/logout');
        $this->client->request('GET', '/tasks');
        $this->assertEquals('302', $this->client->getResponse()->getStatusCode());

        // test that the logout method returns nothing
        $controller = new SecurityController();
        $this->expectException("Exception");
        $controller->logout();
    }

    /**
     * Test redirection to the last requested route after login success
     *
     * @return void
     */
    public function testLoginSuccessLastRouteRedirection()
    {
        // get the csrf token
        $crawler = $this->client->request('GET', '/login');
        $extract = $crawler->filter('input[name="_csrf_token"]')->extract(array('value'));
        $csrf_token = $extract[0];

        // post login form and follow the redirection
        $this->client->request('GET', '/users');
        $crawler = $this->client->request('POST', '/login', ['_csrf_token' => $csrf_token, 'username' => 'admin', 'password' => 'admin']);
        $this->assertRegexp('#Redirecting to(.+)/users#', $crawler->text());
    }

    /**
     * Test redirection to tasks after login success with no last requested route
     *
     * @return void
     */
    public function testLoginSuccessTasksRedirection()
    {
        // get the csrf token
        $crawler = $this->client->request('GET', '/login');
        $extract = $crawler->filter('input[name="_csrf_token"]')->extract(array('value'));
        $csrf_token = $extract[0];

        // post login form and follow the redirection
        $crawler = $this->client->request('POST', '/login', ['_csrf_token' => $csrf_token, 'username' => 'admin', 'password' => 'admin']);
        $this->assertRegexp('#Redirecting to(.+)/tasks#', $crawler->text());
    }

}