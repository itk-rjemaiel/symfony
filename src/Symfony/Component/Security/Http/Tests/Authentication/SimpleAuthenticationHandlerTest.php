<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Http\Tests\Authentication;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\SimpleAuthenticatorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimpleAuthenticationHandler;
use Symfony\Component\Security\Http\Tests\Fixtures\TokenInterface;

/**
 * @group legacy
 */
class SimpleAuthenticationHandlerTest extends TestCase
{
    private $successHandler;

    private $failureHandler;

    private $request;

    private $token;

    private $authenticationException;

    private $response;

    protected function setUp(): void
    {
        $this->successHandler = $this->createMock(AuthenticationSuccessHandlerInterface::class);
        $this->failureHandler = $this->createMock(AuthenticationFailureHandlerInterface::class);

        $this->request = $this->createMock(Request::class);
        $this->token = $this->createMock(TokenInterface::class);
        // No methods are invoked on the exception; we just assert on its class
        $this->authenticationException = new AuthenticationException();

        $this->response = new Response();
    }

    public function testOnAuthenticationSuccessFallsBackToDefaultHandlerIfSimpleIsNotASuccessHandler()
    {
        $authenticator = $this->createMock(SimpleAuthenticatorInterface::class);

        $this->successHandler->expects($this->once())
            ->method('onAuthenticationSuccess')
            ->with($this->request, $this->token)
            ->willReturn($this->response);

        $handler = new SimpleAuthenticationHandler($authenticator, $this->successHandler, $this->failureHandler);
        $result = $handler->onAuthenticationSuccess($this->request, $this->token);

        $this->assertSame($this->response, $result);
    }

    public function testOnAuthenticationSuccessCallsSimpleAuthenticator()
    {
        $this->successHandler->expects($this->never())
            ->method('onAuthenticationSuccess');

        $authenticator = $this->getMockForAbstractClass(TestSuccessHandlerInterface::class);
        $authenticator->expects($this->once())
            ->method('onAuthenticationSuccess')
            ->with($this->request, $this->token)
            ->willReturn($this->response);

        $handler = new SimpleAuthenticationHandler($authenticator, $this->successHandler, $this->failureHandler);
        $result = $handler->onAuthenticationSuccess($this->request, $this->token);

        $this->assertSame($this->response, $result);
    }

    public function testOnAuthenticationSuccessThrowsAnExceptionIfNonResponseIsReturned()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('onAuthenticationSuccess()" method must return null to use the default success handler, or a Response object');
        $this->successHandler->expects($this->never())
            ->method('onAuthenticationSuccess');

        $authenticator = $this->getMockForAbstractClass(TestSuccessHandlerInterface::class);
        $authenticator->expects($this->once())
            ->method('onAuthenticationSuccess')
            ->with($this->request, $this->token)
            ->willReturn(new \stdClass());

        $handler = new SimpleAuthenticationHandler($authenticator, $this->successHandler, $this->failureHandler);
        $handler->onAuthenticationSuccess($this->request, $this->token);
    }

    public function testOnAuthenticationSuccessFallsBackToDefaultHandlerIfNullIsReturned()
    {
        $this->successHandler->expects($this->once())
            ->method('onAuthenticationSuccess')
            ->with($this->request, $this->token)
            ->willReturn($this->response);

        $authenticator = $this->getMockForAbstractClass(TestSuccessHandlerInterface::class);
        $authenticator->expects($this->once())
            ->method('onAuthenticationSuccess')
            ->with($this->request, $this->token)
            ->willReturn(null);

        $handler = new SimpleAuthenticationHandler($authenticator, $this->successHandler, $this->failureHandler);
        $result = $handler->onAuthenticationSuccess($this->request, $this->token);

        $this->assertSame($this->response, $result);
    }

    public function testOnAuthenticationFailureFallsBackToDefaultHandlerIfSimpleIsNotAFailureHandler()
    {
        $authenticator = $this->createMock(SimpleAuthenticatorInterface::class);

        $this->failureHandler->expects($this->once())
            ->method('onAuthenticationFailure')
            ->with($this->request, $this->authenticationException)
            ->willReturn($this->response);

        $handler = new SimpleAuthenticationHandler($authenticator, $this->successHandler, $this->failureHandler);
        $result = $handler->onAuthenticationFailure($this->request, $this->authenticationException);

        $this->assertSame($this->response, $result);
    }

    public function testOnAuthenticationFailureCallsSimpleAuthenticator()
    {
        $this->failureHandler->expects($this->never())
            ->method('onAuthenticationFailure');

        $authenticator = $this->getMockForAbstractClass(TestFailureHandlerInterface::class);
        $authenticator->expects($this->once())
            ->method('onAuthenticationFailure')
            ->with($this->request, $this->authenticationException)
            ->willReturn($this->response);

        $handler = new SimpleAuthenticationHandler($authenticator, $this->successHandler, $this->failureHandler);
        $result = $handler->onAuthenticationFailure($this->request, $this->authenticationException);

        $this->assertSame($this->response, $result);
    }

    public function testOnAuthenticationFailureThrowsAnExceptionIfNonResponseIsReturned()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('onAuthenticationFailure()" method must return null to use the default failure handler, or a Response object');
        $this->failureHandler->expects($this->never())
            ->method('onAuthenticationFailure');

        $authenticator = $this->getMockForAbstractClass(TestFailureHandlerInterface::class);
        $authenticator->expects($this->once())
            ->method('onAuthenticationFailure')
            ->with($this->request, $this->authenticationException)
            ->willReturn(new \stdClass());

        $handler = new SimpleAuthenticationHandler($authenticator, $this->successHandler, $this->failureHandler);
        $handler->onAuthenticationFailure($this->request, $this->authenticationException);
    }

    public function testOnAuthenticationFailureFallsBackToDefaultHandlerIfNullIsReturned()
    {
        $this->failureHandler->expects($this->once())
            ->method('onAuthenticationFailure')
            ->with($this->request, $this->authenticationException)
            ->willReturn($this->response);

        $authenticator = $this->getMockForAbstractClass(TestFailureHandlerInterface::class);
        $authenticator->expects($this->once())
            ->method('onAuthenticationFailure')
            ->with($this->request, $this->authenticationException)
            ->willReturn(null);

        $handler = new SimpleAuthenticationHandler($authenticator, $this->successHandler, $this->failureHandler);
        $result = $handler->onAuthenticationFailure($this->request, $this->authenticationException);

        $this->assertSame($this->response, $result);
    }
}

interface TestSuccessHandlerInterface extends AuthenticationSuccessHandlerInterface, SimpleAuthenticatorInterface
{
}

interface TestFailureHandlerInterface extends AuthenticationFailureHandlerInterface, SimpleAuthenticatorInterface
{
}
