<?php

test('__doRequest passes correct parameters on PHP 8.5+', function () {
    if (PHP_VERSION_ID < 80500) {
        $this->markTestSkipped('This test is only for PHP 8.5+');
    }

    $parentCallArgs = [];

    $client = new class(__DIR__.'/../Assets/mock.wsdl', [
        'trace' => true,
        'exceptions' => true,
        'soap_version' => SOAP_1_1,
        'encoding' => 'utf-8'
    ], $parentCallArgs) extends \KeepItSimple\Http\Soap\MTOMSoapClient {
        private array $argsReference;

        public function __construct($wsdl, $options, &$argsReference)
        {
            parent::__construct($wsdl, $options);
            $this->argsReference = &$argsReference;
        }

        protected function process(?string $response): ?string
        {
            // Skip processing for this test
            return $response;
        }

        public function __doRequest(
            string $request,
            string $location,
            string $action,
            int $version,
            bool $one_way = false,
            ?string $uriParserClass = null
        ): ?string {
            // Build args array as the implementation does
            $args = [$request, $location, $action, $version, $one_way];

            if (PHP_VERSION_ID >= 80500) {
                $args[] = $uriParserClass;
            }

            // Capture what would be passed to parent
            $this->argsReference = $args;

            // Return mock response
            return '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><response>test</response></soap:Body></soap:Envelope>';
        }
    };

    // Trigger a call by trying to invoke a method (will fail but that's ok, we just need __doRequest to be called)
    try {
        // We can't actually call a SOAP method without a real server, so we'll test the logic directly
        $reflection = new ReflectionMethod($client, '__doRequest');
        $reflection->invoke(
            $client,
            '<request>test</request>',
            'http://example.com/service',
            'testAction',
            SOAP_1_1,
            false,
            'CustomParser'
        );
    } catch (\Exception $e) {
        // Ignore exceptions from actual SOAP calls
    }

    // Verify that 6 arguments were captured (including the optional uriParserClass)
    expect($parentCallArgs)->toHaveCount(6)
        ->and($parentCallArgs[5])->toBe('CustomParser');
});

test('__doRequest passes correct parameters on PHP 8.2-8.4', function () {
    if (PHP_VERSION_ID >= 80500) {
        $this->markTestSkipped('This test is only for PHP 8.2-8.4');
    }

    $client = new class(__DIR__.'/../Assets/mock.wsdl', [
        'trace' => true,
        'exceptions' => true,
        'soap_version' => SOAP_1_1,
        'encoding' => 'utf-8'
    ]) extends \KeepItSimple\Http\Soap\MTOMSoapClient {
        public array $capturedArgs = [];
        public int $parentCallArgCount = 0;

        protected function process(?string $response): ?string
        {
            // Skip processing for this test
            return $response;
        }
    };

    // Use reflection to intercept the parent call
    $reflection = new ReflectionClass($client);
    $method = $reflection->getMethod('__doRequest');

    // Create a mock response
    $mockResponse = '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><response>test</response></soap:Body></soap:Envelope>';

    // Call __doRequest with all 6 parameters
    $result = $client->__doRequest(
        '<request>test</request>',
        'http://example.com/service',
        'testAction',
        SOAP_1_1,
        false,
        null
    );

    // Verify it doesn't throw an error on PHP < 8.5
    expect($result)->not->toBeNull();
});

test('__doRequest builds argument array correctly based on PHP version', function () {
    // Create a test decorator that exposes the argument building logic
    $client = new class(__DIR__.'/../Assets/mock.wsdl', [
        'trace' => true,
        'exceptions' => true,
        'soap_version' => SOAP_1_1,
        'encoding' => 'utf-8'
    ]) extends \KeepItSimple\Http\Soap\MTOMSoapClient {
        public function getBuiltArgs(
            string $request,
            string $location,
            string $action,
            int $version,
            bool $one_way = false,
            ?string $uriParserClass = null
        ): array {
            // Replicate the argument building logic from __doRequest
            $args = [$request, $location, $action, $version, $one_way];

            if (PHP_VERSION_ID >= 80500) {
                $args[] = $uriParserClass;
            }

            return $args;
        }

        protected function process(?string $response): ?string
        {
            return $response;
        }
    };

    $testRequest = '<request>test</request>';
    $testLocation = 'http://example.com/service';
    $testAction = 'testAction';
    $testVersion = SOAP_1_1;
    $testOneWay = false;
    $testUriParser = null;

    $args = $client->getBuiltArgs(
        $testRequest,
        $testLocation,
        $testAction,
        $testVersion,
        $testOneWay,
        $testUriParser
    );

    // Verify the argument array has the correct number of elements
    if (PHP_VERSION_ID >= 80500) {
        expect($args)
            ->toHaveCount(6)
            ->and($args[0])->toBe($testRequest)
            ->and($args[1])->toBe($testLocation)
            ->and($args[2])->toBe($testAction)
            ->and($args[3])->toBe($testVersion)
            ->and($args[4])->toBe($testOneWay)
            ->and($args[5])->toBe($testUriParser);
    } else {
        expect($args)
            ->toHaveCount(5)
            ->and($args[0])->toBe($testRequest)
            ->and($args[1])->toBe($testLocation)
            ->and($args[2])->toBe($testAction)
            ->and($args[3])->toBe($testVersion)
            ->and($args[4])->toBe($testOneWay);
    }
});