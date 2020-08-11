<?php

namespace KeepItSimpleTest\Http\Soap;

use KeepItSimple\Http\Soap\MTOMSoapClient;
use PHPUnit\Framework\TestCase;

class MTOMSoapClientTest extends TestCase
{

    /** @var MTOMSoapClient */
    protected $mtom;

    /** @var string */
    protected $response_to_process;

    /**
     * Initialize MTOMSoapClient instance.
     *
     * @throws \SoapFault
     */
    public function setUp(): void
    {
        $this->mtom = new MTOMSoapClient(dirname(__FILE__).'/ShopCustomerService.mock.wsdl', array(
            'trace' => true,
            'exceptions' => true,
            'soap_version' => SOAP_1_1,
            'encoding' => 'utf-8'
        ));

        $this->response_to_process = file_get_contents(
            dirname(__FILE__).'/response_to_process'
        );
    }

    /**
     * Let's simplify the XML.
     *
     * @param string $xml
     * @return string
     */
    protected function cleanXML(string $xml): string
    {
        // Remove namespaces
        $xml = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xml);
        $xml = preg_replace('/[a-zA-Z0-9]+:([a-zA-Z0-9]+[ =>])/', '$1', $xml);
        // Filters
        $xml = str_replace(array("\n", "\r", "\t"), '', $xml);
        $xml = trim(str_replace('"', "'", $xml));

        return $xml;
    }

    public function testDoRequest()
    {
        $response = $this->mtom->dryRun($this->response_to_process);
        $this->assertIsString($response);

        $response = $this->cleanXML($response);
        $xml = simplexml_load_string($response, null, LIBXML_NOCDATA);
        $this->assertInstanceOf(\SimpleXMLElement::class, $xml);

        $elements = $xml->xpath('//Envelope/Body/generateLabelResponse/*');
        $this->assertArrayHasKey(0, $elements);
        $this->assertEquals(
            'Success',
            (string)$elements[0]->messages->messageContent ?? ''
        );

        $label = base64_decode((string)$elements[0]->labelResponse->label ?? '');
        $this->assertEquals(
            'MTOMSoapClient for PHP rocks !',
            $label
        );
    }
}
