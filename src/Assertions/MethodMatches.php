<?php

namespace Muzzle\Assertions;

use Muzzle\Messages\AssertableRequest;

class MethodMatches implements Assertion
{

    /**
     * @var string
     */
    private $methods;

    public function __construct(string ...$methods)
    {

        $this->methods = array_map('strtoupper', $methods);
    }

    public function __invoke(AssertableRequest $actual) : void
    {

        Assert::assertArrayHasKey(
            $actual->getMethod(),
            array_flip(array_map('strtoupper', $this->methods)),
            sprintf(
                'Expected HTTP method [%s]. Got [%s] for request to %s.',
                implode(', ', array_map('strtoupper', $this->methods)),
                $actual->getMethod(),
                urldecode($actual->getUri())
            )
        );
    }
}
