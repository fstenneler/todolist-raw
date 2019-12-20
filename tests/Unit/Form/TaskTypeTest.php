<?php

namespace App\Tests\Unit\Form;

use App\Entity\Task;
use App\Form\TaskType;
use Symfony\Component\Form\Test\TypeTestCase;

class TaskTypeTest extends TypeTestCase
{

    /**
     * Unit test for TaskType
     *
     * @return void
     */
    public function testSubmitValidData()
    {
        $formData = [
            'title' => 'Et cupiditate corporis',
            'content' => 'Magnam dolore incidunt qui autem ipsum voluptates iure.',
        ];
        $testDateTime = new \DateTime();

        $objectToCompare = new Task();
        $objectToCompare->setCreatedAt($testDateTime);
        // $objectToCompare will retrieve data from the form submission; pass it as the second argument
        $form = $this->factory->create(TaskType::class, $objectToCompare);

        $object = new Task();
        $object->setCreatedAt($testDateTime);
        $object->setTitle('Et cupiditate corporis');
        $object->setContent('Magnam dolore incidunt qui autem ipsum voluptates iure.');

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        // check that $objectToCompare was modified as expected when the form was submitted
        $this->assertEquals($object, $objectToCompare);

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }

    }


}
