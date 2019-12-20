<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{

    /**
     * Unit test for id
     *
     * @return void
     */
    public function testGetId()
    {
        $task = new Task();
        $testValue = null;
        $this->assertSame($testValue, $task->getId());
    }

    /**
     * Unit test for createdAt
     *
     * @return void
     */
     public function testCreatedAt()
    {
        $task = new Task();
        $testValue = new \DateTime();
        $task->setCreatedAt($testValue);
        $this->assertSame($testValue, $task->getCreatedAt());
    }

    /**
     * Unit test for title
     *
     * @return void
     */
    public function testGetTitle()
    {
        $task = new Task();
        $testValue = 'Et cupiditate corporis';
        $task->setTitle($testValue);
        $this->assertSame($testValue, $task->getTitle());
    }

    /**
     * Unit test for content
     *
     * @return void
     */
    public function testGetContent()
    {
        $task = new Task();
        $testValue = 'Magnam dolore incidunt qui autem ipsum voluptates iure.';
        $task->setContent($testValue);
        $this->assertSame($testValue, $task->getContent());
    }

    /**
     * Unit test for isDone
     *
     * @return void
     */
    public function testIsDone()
    {
        $task = new Task();
        $testValue = true;
        $task->toggle($testValue);
        $this->assertSame($testValue, $task->isDone());
    }

    /**
     * Unit test for user
     *
     * @return void
     */
    public function testGetUser()
    {
        $task = new Task();
        $testValue = new User();
        $task->setUser($testValue);
        $this->assertSame($testValue, $task->getUser());
    }


}
