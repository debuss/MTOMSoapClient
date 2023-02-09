<?php
/**
 * This file is part of the KeepItSimple package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package   KeepItSimple\Http\Soap
 * @author    Alexandre Debusschere (debuss-a)
 * @copyright Copyright (c) Alexandre Debusschere <alexandre@debuss-a.me>
 * @licence   MIT
 */

namespace KeepItSimple\Http\Soap;

use DOMDocument;
use SoapClient;
use Exception;

/**
 * Class MTOMSoapClient
 *
 * This class overrides SoapClient::__doRequest() method to implement MTOM for PHP.
 * It decodes XML and integrate attachments in the XML response.
 *
 * @author Alexandre D. <debuss-a>
 * @version 1.0.0
 */
class MTOMSoapClient extends SoapClient
{

    /**
     * It replaces the :
     *      <xop:Include href="cid:d08bab58-dfea-43f0-8520-477d4c5e0677-103@cxf.apache.org" xmlns:xop="http://www.w3.org/2004/08/xop/include"/>
     * By the binary code contained in attachment
     *      Content-ID: <d08bab58-dfea-43f0-8520-477d4c5e0677-103@cxf.apache.org>
     *
     * Note that the binary is converted to base64 with base64_encode().
     *
     * @param string|null $response
     * @return string|null The XML SOAP response with <xop> tag replaced by base64 corresponding attachment
     * @throws Exception
     */
    protected function process(?string $response): ?string
    {
        if (!$response) {
            return null;
        }

        // Catch XML response
        $xml_response = null;
        preg_match('/<soap[\s\S]*nvelope>/i', $response, $xml_response);

        if (!is_array($xml_response) || !count($xml_response)) {
            throw new Exception('No XML has been found.');
        }

        $xml_response = reset($xml_response);

        try {
            $dom = new DOMDocument();
            $dom->loadXML($xml_response);

            $xop_elements = $dom->getElementsByTagNameNS('http://www.w3.org/2004/08/xop/include', 'Include');
            foreach ($xop_elements as $xop_element) {
                $cid = $xop_element->getAttribute('href');
                $cid = str_replace('cid:', '', $cid);

                // Find binary
                $content_id_tag = 'Content-ID: <'.$cid.'>';
                $start = strpos($response, $content_id_tag) + strlen($content_id_tag);
                $end = strpos($response, '--uuid:', $start);

                $binary = substr($response, $start, $end - $start);
                $binary = trim($binary);
                $binary = base64_encode($binary);

                $xop_element->parentNode->nodeValue = $binary;
            }

            // Save modified XML string
            $xml_response = $dom->saveXML();
        } catch (Exception $exception) {
            throw new Exception(sprintf(
                'An error occurred while processing the XML response: %s.',
                $exception->getMessage()
            ));
        }

        return $xml_response;
    }

    /**
     * Override SoapClient to add MTOM decoding on responses.
     *
     * @link http://php.net/manual/en/soapclient.dorequest.php
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param int $one_way
     * @return string|null
     * @throws Exception
     */
    public function __doRequest($request, $location, $action, $version, $one_way = 0): ?string
    {
        $response = parent::__doRequest($request, $location, $action, $version, $one_way);

        return $this->process($response);
    }
}
