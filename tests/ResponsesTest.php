<?php

declare(strict_types=1);

namespace EchoFusion\ResponseManager\Tests;

use EchoFusion\ResponseManager\Exceptions\JsonEncodingException;
use EchoFusion\ResponseManager\HtmlResponse;
use EchoFusion\ResponseManager\JsonResponse;
use EchoFusion\ResponseManager\RedirectResponse;
use EchoFusion\ResponseManager\XmlResponse;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use const JSON_PRETTY_PRINT;
use const NAN;

final class ResponsesTest extends TestCase
{
    public function testHtmlResponse(): void
    {
        $response = new HtmlResponse('<h1>Hi</h1>');

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/html; charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertSame('<h1>Hi</h1>', (string) $response->getBody());
    }

    public function testHtmlResponseKeepsCallerContentTypeRegardlessOfCase(): void
    {
        $response = new HtmlResponse('x', 201, ['content-type' => 'text/plain']);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame(['text/plain'], $response->getHeader('Content-Type'));
    }

    public function testXmlResponseDefaultsToStatus200(): void
    {
        $response = new XmlResponse('<a/>');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/xml; charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertSame('<a/>', (string) $response->getBody());
    }

    public function testJsonResponseEncodesData(): void
    {
        $response = new JsonResponse(['a' => 1, 'url' => 'http://x/y', 'tag' => '<b>'], 201, ['X-Test' => 'yes']);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame('yes', $response->getHeaderLine('X-Test'));
        // JSON_HEX_TAG escapes "<" and ">"; concatenated so style fixers cannot rewrite the escape.
        $expected = '{"a":1,"url":"http://x/y","tag":"' . '\\u003C' . 'b' . '\\u003E' . '"}';
        $this->assertSame($expected, (string) $response->getBody());
    }

    public function testJsonResponseKeepsPayloadAndClonesObjects(): void
    {
        $object = new stdClass();
        $object->name = 'echo';

        $response = new JsonResponse($object);
        $object->name = 'changed';

        $this->assertSame('echo', $response->getPayload()->name);
        $this->assertSame('{"name":"echo"}', (string) $response->getBody());
        $this->assertSame([1], (new JsonResponse([1]))->getPayload());
    }

    public function testJsonResponseHonoursEncodingOptions(): void
    {
        $response = new JsonResponse(['a' => 1], 200, [], JSON_PRETTY_PRINT);

        $this->assertStringContainsString("\n", (string) $response->getBody());
    }

    public function testJsonResponseThrowsForUnencodableData(): void
    {
        $this->expectException(JsonEncodingException::class);
        $this->expectExceptionMessage('Unable to encode data to JSON');

        new JsonResponse(NAN);
    }

    public function testJsonResponseThrowsForResources(): void
    {
        $this->expectException(JsonEncodingException::class);

        new JsonResponse(fopen('php://memory', 'rb'));
    }

    public function testRedirectResponse(): void
    {
        $response = new RedirectResponse('/login?next=/a&b=1');

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/login?next=/a&b=1', $response->getHeaderLine('Location'));
        $this->assertStringContainsString('Redirecting to <a href="/login?next=/a&amp;b=1">', (string) $response->getBody());
    }

    public function testRedirectResponseEscapesTheUrlInTheBody(): void
    {
        $response = new RedirectResponse('/x"><script>alert(1)</script>', 301);

        $this->assertSame(301, $response->getStatusCode());
        $this->assertStringNotContainsString('<script>', (string) $response->getBody());
    }

    public function testRedirectResponseKeepsCallerLocation(): void
    {
        $response = new RedirectResponse('/a', 303, ['location' => '/b']);

        $this->assertSame(['/b'], $response->getHeader('Location'));
    }

    /**
     * @dataProvider invalidStatuses
     */
    public function testRedirectResponseRejectsNonRedirectStatus(int $status): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RedirectResponse('/', $status);
    }

    /**
     * @return array<string, array{int}>
     */
    public function invalidStatuses(): array
    {
        return ['200' => [200], '299' => [299], '400' => [400]];
    }
}
