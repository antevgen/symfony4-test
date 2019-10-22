<?php

namespace App\DataFixtures;

use App\Entity\MicroPost;
use App\Entity\User;
use App\Entity\UserPreferences;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private const USERS = [
        [
            'username' => 'john_doe',
            'email' => 'john_doe@doe.com',
            'password' => 'john123',
            'fullName' => 'John Doe',
            'roles' => [User::ROLE_USER],
        ],
        [
            'username' => 'rob_smith',
            'email' => 'rob_smith@smith.com',
            'password' => 'rob12345',
            'fullName' => 'Rob Smith',
            'roles' => [User::ROLE_USER],
        ],
        [
            'username' => 'marry_gold',
            'email' => 'marry_gold@gold.com',
            'password' => 'marry12345',
            'fullName' => 'Marry Gold',
            'roles' => [User::ROLE_USER],
        ],
        [
            'username' => 'super_admin',
            'email' => 'super_admin@gold.com',
            'password' => 'admin12345',
            'fullName' => 'Micro Admin',
            'roles' => [User::ROLE_ADMIN],
        ],
    ];

    private const POST_TEXT = [
        'Hello, how are you?',
        'It\'s nice sunny weather today',
        'I need to buy some ice cream!',
        'I wanna buy a new car',
        'There\'s a problem with my phone',
        'I need to go to the doctor',
        'What are you up to today?',
        'Did you watch the game yesterday?',
        'How was your day?'
    ];

    private const LANGUAGES = [
        'en',
        'fr',
    ];

    /**
     * @var \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadMicroPost($manager);
    }

    private function loadMicroPost(ObjectManager $manager)
    {
        for ($i= 0; $i < 30; $i++) {
            $micro_post = new MicroPost();
            $micro_post->setText(self::POST_TEXT[rand(0, count(self::POST_TEXT) - 1)]);
            $date = new \DateTime();
            $date->modify('-' . rand(1, 20) . ' day');
            $micro_post->setTime($date);
            $micro_post->setUser($this->getReference(
                self::USERS[rand(0, count(self::USERS) - 1)]['username']
            ));
            $manager->persist($micro_post);
        }

        $manager->flush();
    }

    private function loadUsers(ObjectManager $manager)
    {
        foreach (self::USERS as $user_data) {
            $user = new User();
            $user->setUsername($user_data['username']);
            $user->setFullName($user_data['fullName']);
            $user->setEmail($user_data['email']);
            $user->setPassword(
                $this->userPasswordEncoder->encodePassword(
                    $user,
                    $user_data['password']
                )
            );
            $user->setRoles($user_data['roles']);
            $user->setEnabled(true);

            $this->addReference(
                $user_data['username'],
                $user
            );

            $preferences = new UserPreferences();
            $preferences->setLocale(self::LANGUAGES[rand(0, count(self::LANGUAGES) - 1)]);
            $user->setPreferences($preferences);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
