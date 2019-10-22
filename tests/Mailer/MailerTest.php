<?php

namespace App\Tests\Mailer;

use App\Entity\User;
use App\Mailer\Mailer;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class MailerTest extends TestCase
{
    public function testConfirmationEmail() {
        $user = new User();
        $user->setEmail('john@doe.com');

        $swiftMailerMock = $this->getMockBuilder(\Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $swiftMailerMock->expects($this->once())->method('send')
            ->with($this->callback(function ($subject) {
                $messageString = (string) $subject;
                return strpos($messageString, "From: me@example.com") !== false
                    && strpos($messageString, "Content-Type: content/html; charset=utf-8") !== false
                    && strpos($messageString, "Subject: Welcome to micro-post app!") !== false
                    && strpos($messageString, "To: john@doe.com") !== false
                    && strpos($messageString, "This is message body") !== false;
            }));

        $twigMock = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $twigMock->expects($this->once())->method('render')
            ->with('email/registration.html.twig', [
                'user' => $user,
            ])->willReturn('This is message body');

        $mailer = new Mailer($swiftMailerMock, $twigMock, 'me@example.com');
        $mailer->sendConfirmationEmail($user);
    }

}
