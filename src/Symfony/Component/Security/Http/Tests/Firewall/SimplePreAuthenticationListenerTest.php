<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Http\Tests\Firewall;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Firewall\SimplePreAuthenticationListener;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Tests\Fixtures\TokenInterface;

/**
 * @group legacy
 */
class SimplePreAuthenticationListenerTest extends TestCase
{
    private $authenticationManager;
    private $dispatcher;
    private $event;
    private $logger;
    private $request;
    private $tokenStorage;
    private $token;

    public function testHandle()
    {
        $this->tokenStorage
            ->expects($this->once())
            ->method('setToken')
            ->with($this->equalTo($this->token))
        ;

        $this->authenticationManager
            ->expects($this->once())
            ->method('authenticate')
            ->with($this->equalTo($this->token))
            ->willReturn($this->token)
        ;

        $simpleAuthenticator = $this->createMock(SimplePreAuthenticatorInterface::class);
        $simpleAuthenticator
            ->expects($this->once())
            ->method('createToken')
            ->with($this->equalTo($this->request), $this->equalTo('secured_area'))
            ->willReturn($this->token)
        ;

        $loginEvent = new InteractiveLoginEvent($this->request, $this->token);

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo($loginEvent), $this->equalTo(SecurityEvents::INTERACTIVE_LOGIN))
        ;

        $listener = new SimplePreAuthenticationListener($this->tokenStorage, $this->authenticationManager, 'secured_area', $simpleAuthenticator, $this->logger, $this->dispatcher);

        $listener($this->event);
    }

    public function testHandlecatchAuthenticationException()
    {
        $exception = new AuthenticationException('Authentication failed.');

        $this->authenticationManager
            ->expects($this->once())
            ->method('authenticate')
            ->with($this->equalTo($this->token))
            ->willThrowException($exception)
        ;

        $this->tokenStorage->expects($this->once())
            ->method('setToken')
            ->with($this->equalTo(null))
        ;

        $simpleAuthenticator = $this->createMock(SimplePreAuthenticatorInterface::class);
        $simpleAuthenticator
            ->expects($this->once())
            ->method('createToken')
            ->with($this->equalTo($this->request), $this->equalTo('secured_area'))
            ->willReturn($this->token)
        ;

        $listener = new SimplePreAuthenticationListener($this->tokenStorage, $this->authenticationManager, 'secured_area', $simpleAuthenticator, $this->logger, $this->dispatcher);

        $listener($this->event);
    }

    protected function setUp(): void
    {
        $this->authenticationManager = $this->createMock(AuthenticationProviderManager::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->request = new Request([], [], [], [], [], []);

        $this->event = $this->createMock(RequestEvent::class);
        $this->event
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request)
        ;

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->token = $this->createMock(TokenInterface::class);
    }

    protected function tearDown(): void
    {
        $this->authenticationManager = null;
        $this->dispatcher = null;
        $this->event = null;
        $this->logger = null;
        $this->request = null;
        $this->tokenStorage = null;
        $this->token = null;
    }
}
