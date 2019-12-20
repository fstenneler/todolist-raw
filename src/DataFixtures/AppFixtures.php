<?php

namespace App\DataFixtures;
use Faker;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Load fixtures
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadTasks($manager);
    }

    /**
     * Load users and store results into database
     *
     * @param ObjectManager $manager
     * @return void
     */
    private function loadUsers(ObjectManager $manager)
    {

        // initiate the faker bundle
        $faker = Faker\Factory::create('fr_FR');

        // create 1 admin
        $user = new User();
        $user->setUsername('admin');
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'admin')); 
        $user->setEmail('admin@orlinstreet.rocks');
        $user->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);

        // create 1 user for tests
        $user = new User();
        $user->setUsername('user');
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'user')); 
        $user->setEmail('user@orlinstreet.rocks');
        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);

        // create 15 fake users
        for ($i = 0; $i < 15; $i++) {

            $user = new User();
            $user->setUsername($faker->userName);
            $user->setPassword($this->passwordEncoder->encodePassword($user, 'user')); 
            $user->setEmail($faker->email);
            $user->setRoles(['ROLE_USER']);
            $manager->persist($user);
            $this->addReference('[user] ' . $i, $user);

        }

        $manager->flush();

    }

    /**
     * Load tasks and store results into database
     *
     * @param ObjectManager $manager
     * @return void
     */
    private function loadTasks(ObjectManager $manager)
    {

        // initiate the faker bundle
        $faker = Faker\Factory::create('fr_FR');

        // create 20 tasks without user (old tasks)
        for ($i = 0; $i < 50; $i++) {

            // random values
            $randDone = false;
            if(1 === rand(0,1)) {
                $randDone = true;
            }
            $randUserNumber = rand(0,9);

            $task = new Task();
            $task->setCreatedAt(
                $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now', $timezone = 'Europe/Paris')
            );
            $task->setTitle(
                preg_replace("#.$#", "", $faker->sentence($nbWords = 3, $variableNbWords = true))
            );
            $task->setContent(
                $faker->sentence($nbWords = 10, $variableNbWords = true)
            );
            $task->toggle($randDone);
            $manager->persist($task);

        }

        // create 50 tasks with a user
        for ($i = 0; $i < 30; $i++) {

            // random values
            $randDone = false;
            if(1 === rand(0,1)) {
                $randDone = true;
            }
            $randUserNumber = rand(0,9);

            $task = new Task();
            $task->setCreatedAt(
                $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now', $timezone = 'Europe/Paris')
            );
            $task->setTitle(
                preg_replace("#.$#", "", $faker->sentence($nbWords = 3, $variableNbWords = true))
            );
            $task->setContent(
                $faker->sentence($nbWords = 10, $variableNbWords = true)
            );
            $task->toggle($randDone);
            $task->setUser(
                $this->getReference('[user] ' . $randUserNumber)
            );
            $manager->persist($task);

        }

        $manager->flush();

    }

}
