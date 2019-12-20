<?php

namespace App\Tests\Unit\Controller;

use App\Entity\User;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AuthenticationUtil
{
    private $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Login and set token
     *
     * @param string $role
     * @param string $username
     * @return void
     */
    public function logIn($role = 'ROLE_USER', $username = null)
    {   

        // get credentials
        $credentials = $this->getCredentials($role, $username);

        // get doctrine
        $entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();

        // get a user from database
        $user = $entityManager
            ->getRepository(User::class)
            ->findOneBy([
                'username' => $credentials['username']
            ]);

        $session = $this->client->getContainer()->get('session');

        $firewall = 'main';
        $token = new UsernamePasswordToken($user, $credentials['password'], $firewall, $user->getRoles());
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();
      
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

    }

    /**
     * Return credentials
     *
     * @return array credentials[]
     */
    private function getCredentials($role, $username)
    {

        $credentials = [
            'username' => 'user',
            'password' => 'user',
        ];
        if($role === 'ROLE_ADMIN') {
            $credentials = [
                'username' => 'admin',
                'password' => 'admin',
            ];
        }
        if($username !== null) {
            $credentials = [
                'username' => $username,
                'password' => 'user',
            ];
        }

        return $credentials;

    }
    
}
