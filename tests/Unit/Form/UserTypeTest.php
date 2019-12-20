<?php

namespace App\Tests\Unit\Form;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;

class UserTypeTest extends TypeTestCase
{

    protected function getExtensions()
    {
        $validator = Validation::createValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }
    
    /**
     * Unit test for UserType
     *
     * @return void
     */
    public function testSubmitValidData()
    {
        $formData = [
            'username' => 'test',
            'password' => [
                'first' => 'password',
                'second' => 'password',
            ],
            'email' => 'test@test.test',
            'roles' => ['ROLE_ADMIN'],
        ];
        $testDateTime = new \DateTime();

        $objectToCompare = new User();
        // $objectToCompare will retrieve data from the form submission; pass it as the second argument
        $form = $this->factory->create(UserType::class, $objectToCompare);

        $object = new User();
        $object->setUsername('test');
        $object->setPassword('password');
        $object->setEmail('test@test.test');
        $object->setRoles(['ROLE_ADMIN']);

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
