<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\UserRegisterEvent;
use App\Form\UserType;
use App\Security\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Twig\Environment;

class RegisterController
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        Environment $twig
    ) {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->twig = $twig;
    }

    /**
     * @Route("/register", name="user_register")
     */
    public function register(
        UserPasswordEncoderInterface $userPasswordEncoder,
        Request $request,
        EventDispatcherInterface $eventDispatcher,
        TokenGenerator $tokenGenerator
    ) {
        $user = new User();
        $form = $this->formFactory->create(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $userPasswordEncoder->encodePassword(
                $user,
                $user->getPlainPassword()
            );
            $user->setPassword($password);
            $user->setConfirmationToken($tokenGenerator->getRandomSecureToken(30));

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $userRegisteredEvent = new UserRegisterEvent($user);
            $eventDispatcher->dispatch($userRegisteredEvent, UserRegisterEvent::NAME);

            return new RedirectResponse(
                $this->router->generate('micro_post_index')
            );
        }

        return new Response(
            $this->twig->render(
                'register/register.html.twig',
                ['form' => $form->createView()]
            )
        );
    }
}
