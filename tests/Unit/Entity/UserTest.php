<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{

    /**
     * Unit test for id
     *
     * @return void
     */
    public function testGetId()
    {
        $user = new user();
        $testValue = null;
        $this->assertSame($testValue, $user->getId());
    }

    /**
     * Unit test for username
     *
     * @return void
     */
     public function testGetUsername()
    {
        $user = new User();
        $testValue = 'nicole.launay';
        $user->setUsername($testValue);
        $this->assertSame($testValue, $user->getUsername());
    }

    /**
     * Unit test for salt
     *
     * @return void
     */
    public function testGetSalt()
    {
        $user = new User();
        $testValue = null;
        $this->assertSame($testValue, $user->getSalt());
    }

    /**
     * Unit test for password
     *
     * @return void
     */
    public function testGetPassword()
    {
        $user = new User();
        $testValue = 'azerty';
        $user->setPassword($testValue);
        $this->assertSame($testValue, $user->getPassword());
    }

    /**
     * Unit test for email
     *
     * @return void
     */
    public function testGetEmail()
    {
        $user = new User();
        $testValue = 'rguerin@yahoo.fr';
        $user->setEmail($testValue);
        $this->assertSame($testValue, $user->getEmail());
    }

    /**
     * Unit test for roles
     *
     * @return void
     */
    public function testGetRoles()
    {
        $user = new User();

        // test with ROLE_ADMIN
        $testValue = ['ROLE_ADMIN'];
        $user->setRoles($testValue);
        $this->assertSame($testValue, $user->getRoles());

        // test with empty roles
        $testValue = [];
        $user->setRoles($testValue);
        $this->assertSame(['ROLE_USER'], $user->getRoles());        
    }

    /**
     * Unit test for roles method getRoleName()
     *
     * @return void
     */
    public function testGetRoleName()
    {
        $user = new User();

        // test with ROLE_ADMIN
        $testValue = ['ROLE_ADMIN'];
        $user->setRoles($testValue);
        $this->assertSame('Administrateur', $user->getRoleName());

        // test with ROLE_USER
        $testValue = ['ROLE_USER'];
        $user->setRoles($testValue);
        $this->assertSame('Utilisateur', $user->getRoleName());

        // test with empty roles
        $testValue = [];
        $user->setRoles($testValue);
        $this->assertSame('Utilisateur', $user->getRoleName());
    }

    /**
     * Unit test for eraseCredentials
     *
     * @return void
     */
    public function testEraseCredentials()
    {
        $user = new User();
        $this->assertEmpty($user->eraseCredentials());
    }

    /**
     * Unit test for tasks
     *
     * @return void
     */
    public function testGetTasks()
    {
        $user = new User();
        $task = new Task();

        // test with addTask
        $user->addTask($task);
        $this->assertSame($task, $user->getTasks()[0]);

        // test with removeTask
        $user->removeTask($task);
        $this->assertSame(array(), $user->getTasks()->toArray());

    }


}
