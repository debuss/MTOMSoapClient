<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses()
    ->beforeEach(function () {
        $this->mtom_soap_client = new \KeepItSimpleTest\Http\Soap\Assets\MTOMSoapClientTestDecorator(
            __DIR__.'/Assets/mock.wsdl',
            [
                'trace' => true,
                'exceptions' => true,
                'soap_version' => SOAP_1_1,
                'encoding' => 'utf-8'
            ]
        );

        $this->response = file_get_contents(__DIR__.'/Assets/mock.response');
        $this->response_multiple_xop = file_get_contents(__DIR__.'/Assets/mock2.response');
    })
    ->in('Unit/MTOMSoapClientTest.php');
