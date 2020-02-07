<?php

declare(strict_types=1);

namespace SymfonyCasts\Bundle\ResetPassword\tests\UnitTests\Generator;

use SymfonyCasts\Bundle\ResetPassword\Exception\TokenException;
use SymfonyCasts\Bundle\ResetPassword\Generator\ResetPasswordTokenGenerator;
use PHPUnit\Framework\TestCase;
use SymfonyCasts\Bundle\ResetPassword\tests\Fixtures\ResetPasswordTokenGeneratorTestFixture;

class TokenGeneratorTest extends TestCase
{
    /** @var ResetPasswordTokenGeneratorTestFixture */
    public $fixture;

    protected function setUp()
    {
        $this->fixture = new ResetPasswordTokenGeneratorTestFixture();
    }

    /** @test */
    public function randomStrReturned(): void
    {
        //@todo remove me
        $this->markTestSkipped('method removed');
        $generator = new ResetPasswordTokenGenerator();

        $resultA = $generator->getRandomAlphaNumStr(20);
        $resultB = $generator->getRandomAlphaNumStr(20);

        self::assertNotSame($resultA, $resultB);
    }

    /** @test */
    public function randomStrReturnsCorrectLength(): void
    {
        //@TODO remove me
        $this->markTestSkipped('Method to be removed');
        $generator = new ResetPasswordTokenGenerator();
        $result = $generator->getRandomAlphaNumStr(100);

        self::assertSame(100, strlen($result));
    }

    /** @test */
    public function randomBytesThrowsExceptionWithBadSize(): void
    {
        //@todo Remove after refactoring token generator
        $this->markTestSkipped('Not catching random_bytes error. Prob safe to remove test.');
        $this->expectException(TokenException::class);
        $this->fixture->getRandomBytesFromProtected(0);
    }

    /** @test */
    public function getRandomBytesUsesLength(): void
    {
        //@todo method removed, do better
        $this->markTestSkipped('Method removed, make me better..');
        $result = $this->fixture->getRandomBytesFromProtected(100);

        $this->assertSame(200, strlen(bin2hex($result)));
    }

    /** @test */
    public function hashDataEncodesToJson(): void
    {
        $mockDateTime = $this->createMock(\DateTimeImmutable::class);
        $mockDateTime
            ->expects($this->once())
            ->method('format')
            ->willReturn('2020')
        ;

        $result = $this->fixture->getEncodeHashedDataProtected($mockDateTime, 'verify', '1234');
        self::assertJson($result);
    }

    /** @test */
    public function hashDataEncodesWithProvidedParams(): void
    {
        $mockDateTime = $this->createMock(\DateTimeImmutable::class);
        $mockDateTime
            ->method('format')
            ->willReturn('2020')
        ;

        $result = $this->fixture->getEncodeHashedDataProtected($mockDateTime, 'verify', '1234');
        self::assertJsonStringEqualsJsonString(
        '["verify", "1234", "2020"]',
            $result
        );
    }

    /** @test */
    public function returnsHashedHmac(): void
    {
        //@todo consolidate test with getToken test
        $this->markTestSkipped('method removed, getToken returns \hash_hmac directly');
        $key = 'abc';

        $mockTime = $this->createMock(\DateTimeImmutable::class);
        $mockTime
            ->method('format')
            ->willReturn('2020')
        ;

        $verifier = 'verify';
        $userId = '1234';

        $hashed = $this->fixture->getGenerateHashProtected(
            $key,
            $mockTime,
            $verifier,
            $userId
        );

        $expected = \hash_hmac(
            'sha256',
            \json_encode([$verifier, $userId, '2020']),
            $key
        );

        self::assertSame($expected, $hashed);
    }

    public function emptyParamDataProvider(): \Generator
    {
        yield ['', 'verify', 'user'];
        yield ['key', '', 'user'];
        yield ['key', 'verify', ''];
    }

    /**
     * @test
     * @dataProvider emptyParamDataProvider
     */
    public function throwsExceptionWithEmptyParams($key, $verifier, $userId): void
    {
        //@todo remove me
        $this->markTestSkipped('getToken doesnt check for mt strings. Safe to remove after refactor.');
        $this->expectException(TokenException::class);

        $mockDate = $this->createMock(\DateTimeImmutable::class);

        $generator = new ResetPasswordTokenGenerator();
        $generator->getToken($key, $mockDate, $verifier, $userId);
    }

    /** @test */
    public function throwsExceptionIfExpiresInThePast(): void
    {
        //@todo remove me after refactoring
        $this->markTestSkipped('getToken doesnt check if xpird. safe to remove..');
        $mockDate = $this->createMock(\DateTimeImmutable::class);
        $mockDate
            ->expects($this->once())
            ->method('getTimestamp')
            ->willReturn(1580685011)
        ;

        $this->expectException(TokenException::class);

        $generator = new ResetPasswordTokenGenerator();
        $generator->getToken('x', $mockDate, 'x', 'x');
    }

    /** @test */
    public function returnsHmacHashedToken(): void
    {
        $mockExpectedAt = $this->createMock(\DateTimeImmutable::class);
        $mockExpectedAt
            ->method('format')
            ->willReturn('2020')
        ;

        $mockExpectedAt
            ->method('getTimestamp')
            ->willReturn(9999999999999)
        ;

        $signingKey = 'abcd';
        $verifier = 'verify';
        $userId = '1234';

        $generator = new ResetPasswordTokenGenerator();
        $result = $generator->getToken($signingKey, $mockExpectedAt, $verifier, $userId);

        $expected = \hash_hmac(
            'sha256',
            \json_encode([$verifier, $userId, '2020']),
            $signingKey
        );

        self::assertSame($expected, $result);
    }
}
