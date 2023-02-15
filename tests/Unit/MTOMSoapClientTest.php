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

test('multiple XOP to replace by base64 encoded text', function () {
    /** @var \KeepItSimple\Http\Soap\MTOMSoapClient $mtom_soap_client */
    $mtom_soap_client = $this->mtom_soap_client;

    $response = $mtom_soap_client->dryRun($this->response_multiple_xop);
    expect($response)->toBeString();

    $xml = simplexml_load_string($response, null, LIBXML_NOCDATA);
    expect($xml)->toBeInstanceOf(\SimpleXMLElement::class);

    $elements = $xml
        ->children('soap', true)
        ->Body
        ->children('ns2', true)
        ->SoapMethodResponse
        ->response
        ->xpath('Attachments');

    expect($elements)->toBeArray()->toHaveCount(2)->toHaveKey(0)->toHaveKey(1)
        ->and($elements[0])->toBeInstanceOf(SimpleXMLElement::class)->toBeIterable()
        ->and((string)$elements[0]->DocumentId ?? '')->toBe('1381878')
        ->and($elements[1])->toBeInstanceOf(SimpleXMLElement::class)->toBeIterable()
        ->and((string)$elements[1]->DocumentId ?? '')->toBe('1381879')
        ->and(base64_decode((string)$elements[0]->Content ?? ''))->toBe('[Binary Data of a PNG Image Here]')
        ->and(base64_decode((string)$elements[1]->Content ?? ''))->toBe('[Binary Data of a JPEG Image Here]');

});
