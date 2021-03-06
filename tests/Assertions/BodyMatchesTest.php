<?php

namespace Muzzle\Assertions;

use Muzzle\HttpMethod;
use Muzzle\Messages\AssertableRequest;
use Muzzle\Muzzle;
use Muzzle\RequestBuilder;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;

class BodyMatchesTest extends TestCase
{

    /** @test */
    public function itFailsIfTheExpectedBodyDoesNotMatchTheActualBody()
    {

        $expectation = new BodyMatches('test body');
        $actual = new AssertableRequest(
            (new RequestBuilder(HttpMethod::GET()))
                ->setBody('foo')
                ->build()
        );

        $this->expectException(ExpectationFailedException::class);
        $expectation($actual, new Muzzle);
    }

    /** @test */
    public function itPassesIfTheBodyContainsAProvidedString()
    {

        $expectation = new BodyMatches('test body');
        $assertable = new AssertableRequest(
            (new RequestBuilder(HttpMethod::GET()))
                ->setBody('test body')
                ->build()
        );

        $expectation($assertable, new Muzzle);
        $this->expectException(ExpectationFailedException::class);
        $expectation(
            $assertable->withBody(stream_for('test not matching')),
            new Muzzle
        );
    }

    /** @test */
    public function itAcceptsAStreamAsTheBody()
    {

        $body = 'test body';

        $expectation = new BodyMatches(stream_for($body));
        $assertable = new AssertableRequest(
            (new RequestBuilder(HttpMethod::GET()))
                ->setBody($body)
                ->build()
        );

        $expectation($assertable, new Muzzle);
        $this->expectException(ExpectationFailedException::class);
        $expectation(
            $assertable->withBody(stream_for('test not matching')),
            new Muzzle
        );
    }

    /** @test */
    public function itCanMatchARegexExpression()
    {

        $body = '#test b.*#';

        $expectation = new BodyMatches($body);
        $assertable = new AssertableRequest(
            (new RequestBuilder(HttpMethod::GET()))
                ->setBody('test body')
                ->build()
        );
        $expectation($assertable, new Muzzle);
        $expectation($assertable->withBody(stream_for('test box')), new Muzzle);
        $this->expectException(ExpectationFailedException::class);
        $expectation(
            $assertable->withBody(stream_for('test not matching')),
            new Muzzle
        );
    }

    /** @test */
    public function itCanMatchAJsonString()
    {

        $body = '{"data" : [{"foo": {"bar": "baz"}}]}';

        $expectation = new BodyMatches($body);
        $assertable = new AssertableRequest(
            (new RequestBuilder(HttpMethod::GET()))
                ->setBody($body)
                ->build()
        );
        $expectation($assertable, new Muzzle);
        $this->expectException(ExpectationFailedException::class);
        $expectation(
            $assertable->withBody(stream_for('{"data" : [{"foo": {"bar": "qux"}}]}')),
            new Muzzle
        );
    }

    /** @test */
    public function itCanMatchAnArrayToAJsonBody()
    {

        $body = ['data' => [['foo' => ['bar' => 'baz']]]];

        $expectation = new BodyMatches($body);
        $assertable = new AssertableRequest(
            (new RequestBuilder(HttpMethod::GET()))
                ->setBody('{"data" : [{"foo": {"bar": "baz"}}]}')
                ->build()
        );
        $expectation($assertable, new Muzzle);
        $this->expectException(ExpectationFailedException::class);
        $expectation(
            $assertable->withBody(stream_for('{"data" : [{"foo": {"bar": "qux"}}]}')),
            new Muzzle
        );
    }

    /** @test */
    public function itCanMatchAnArrayWithRegexValuesToAJsonBody()
    {

        $body = ['data' => [['foo' => ['bar' => '#b.*z#']]]];

        $expectation = new BodyMatches($body);
        $assertable = new AssertableRequest(
            (new RequestBuilder(HttpMethod::GET()))
                ->setBody('{"data" : [{"foo": {"bar": "baz"}}]}')
                ->build()
        );
        $expectation($assertable, new Muzzle);
        $expectation(
            $assertable->withBody(stream_for('{"data" : [{"foo": {"bar": "buzz"}}]}')),
            new Muzzle
        );
        $this->expectException(ExpectationFailedException::class);
        $expectation(
            $assertable->withBody(stream_for('{"data" : [{"foo": {"bar": "qux"}}]}')),
            new Muzzle
        );
    }
}
