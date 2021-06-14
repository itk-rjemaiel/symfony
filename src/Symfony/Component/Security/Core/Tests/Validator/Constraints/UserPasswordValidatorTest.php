<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\Tests\Validator\Constraints;

use Foo\Bar\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Tests\Fixtures\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class UserPasswordValidatorTest extends ConstraintValidatorTestCase
{
    private const PASSWORD = 's3Cr3t';
    private const SALT = '^S4lt$';

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var PasswordEncoderInterface
     */
    protected $encoder;

    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    protected function createValidator()
    {
        return new UserPasswordValidator($this->tokenStorage, $this->encoderFactory);
    }

    protected function setUp(): void
    {
        $user = $this->createUser();
        $this->tokenStorage = $this->createTokenStorage($user);
        $this->encoder = $this->createMock(PasswordEncoderInterface::class);
        $this->encoderFactory = $this->createEncoderFactory($this->encoder);

        parent::setUp();
    }

    public function testPasswordIsValid()
    {
        $constraint = new UserPassword([
            'message' => 'myMessage',
        ]);

        $this->encoder->expects($this->once())
            ->method('isPasswordValid')
            ->with(static::PASSWORD, 'secret', static::SALT)
            ->willReturn(true);

        $this->validator->validate('secret', $constraint);

        $this->assertNoViolation();
    }

    public function testPasswordIsNotValid()
    {
        $constraint = new UserPassword([
            'message' => 'myMessage',
        ]);

        $this->encoder->expects($this->once())
            ->method('isPasswordValid')
            ->with(static::PASSWORD, 'secret', static::SALT)
            ->willReturn(false);

        $this->validator->validate('secret', $constraint);

        $this->buildViolation('myMessage')
            ->assertRaised();
    }

    /**
     * @dataProvider emptyPasswordData
     */
    public function testEmptyPasswordsAreNotValid($password)
    {
        $constraint = new UserPassword([
            'message' => 'myMessage',
        ]);

        $this->validator->validate($password, $constraint);

        $this->buildViolation('myMessage')
            ->assertRaised();
    }

    public function emptyPasswordData()
    {
        return [
            [null],
            [''],
        ];
    }

    public function testUserIsNotValid()
    {
        $this->expectException(ConstraintDefinitionException::class);
        $user = $this->createMock(User::class);

        $this->tokenStorage = $this->createTokenStorage($user);
        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);

        $this->validator->validate('secret', new UserPassword());
    }

    protected function createUser()
    {
        $mock = $this->createMock(UserInterface::class);

        $mock
            ->expects($this->any())
            ->method('getPassword')
            ->willReturn(static::PASSWORD)
        ;

        $mock
            ->expects($this->any())
            ->method('getSalt')
            ->willReturn(static::SALT)
        ;

        return $mock;
    }

    protected function createEncoderFactory($encoder = null)
    {
        $mock = $this->createMock(EncoderFactoryInterface::class);

        $mock
            ->expects($this->any())
            ->method('getEncoder')
            ->willReturn($encoder)
        ;

        return $mock;
    }

    protected function createTokenStorage($user = null)
    {
        $token = $this->createAuthenticationToken($user);

        $mock = $this->createMock(TokenStorageInterface::class);
        $mock
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token)
        ;

        return $mock;
    }

    protected function createAuthenticationToken($user = null)
    {
        $mock = $this->createMock(TokenInterface::class);
        $mock
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($user)
        ;

        return $mock;
    }
}
