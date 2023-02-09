<?php

test('doRequest replace XOP by base64 encoded text', function () {
    /** @var \KeepItSimple\Http\Soap\MTOMSoapClient $mtom_soap_client */
    $mtom_soap_client = $this->mtom_soap_client;

    $response = $mtom_soap_client->dryRun($this->response);
    expect($response)->toBeString();

    $xml = simplexml_load_string($response, null, LIBXML_NOCDATA);
    expect($xml)->toBeInstanceOf(\SimpleXMLElement::class);

    $elements = $xml
        ->children('soap', true)
        ->Body
        ->children('http://tempuri.org/')
        ->GetProductResponse
        ->GetProductResult;

    expect($elements)->toBeInstanceOf(\SimpleXMLElement::class)->toBeIterable()
        ->and((string)$elements[0]->Product->ProductName ?? '')->toBe('Product 1');

    $label = base64_decode((string)$elements[0]->Product->ProductImage ?? '');
    expect($label)->toBe('[Binary Data of a JPEG Image Here]');
});
