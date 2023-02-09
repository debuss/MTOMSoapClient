# MTOMSoapClient
[![Build Status](https://travis-ci.com/debuss/mtomsoapclient.svg?branch=master)](https://travis-ci.com/debuss/mtomsoapclient)
[![Latest Stable Version](https://poser.pugx.org/debuss-a/mtomsoapclient/v)](//packagist.org/packages/debuss-a/mtomsoapclient)
[![Total Downloads](https://poser.pugx.org/debuss-a/mtomsoapclient/downloads)](//packagist.org/packages/debuss-a/mtomsoapclient)
[![License](https://poser.pugx.org/debuss-a/mtomsoapclient/license)](//packagist.org/packages/debuss-a/mtomsoapclient)

Small PHP Soap class to deal with MTOM technology, fetching binaries as base64 string. 

This class overrides SoapClient::__doRequest() method to implement MTOM for PHP.  
It decodes XML and integrate attachments in the XML response.

It replaces the

```
<xop:Include href="cid:d08bab58-dfea-43f0-8520-477d4c5e0677-103@cxf.apache.org" xmlns:xop="http://www.w3.org/2004/08/xop/include"/>
```

By the binary code contained in attachment

```
Content-ID: <d08bab58-dfea-43f0-8520-477d4c5e0677-103@cxf.apache.org>  
```

# Personal Note

The class is not perfect and not so optimized but it works for most cases and you can modify it as you wish to make it better.

**Enjoy !**

# Installation

Via composer :

```
$ composer require debuss-a/mtomsoapclient
```

# Example

Use it the same as a normal Soap call :

```php
$client = new MTOMSoapClient($webservice, array(
    'trace' => true,
    'exceptions' => true,
    'soap_version' => SOAP_1_1,
    'encoding' => 'utf-8'
));

$result = $client->__call(
    $method,
    $parameters
);

if (!$result instanceof stdClass) {
    throw new Exception('Soap call response is not a valid stdClass instance.');
}

var_dump($result->path->to->my->data);
```

### Explanation

It will turn this answer (normal Soap Response, MTOM is not parsed by Soap) :

```xml
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">  
   <soap:Body>  
      <ns2:generateLabelResponse xmlns:ns2="http://sls.ws.webservice.fr">  
         <return>  
            <messages>  
               <id>0</id>  
               <messageContent>La requete a ete traitee avec succes</messageContent>  
               <type>INFOS</type>  
            </messages>  
            <labelResponse>  
               <label>  
                  <xop:Include href="cid:983c41d7-d699-4373-b8da-4815099ef250-3880@cxf.apache.org" xmlns:xop="http://www.w3.org/2004/08/xop/include"/>  
               </label>  
               <parcelNumber>6A11353659111</parcelNumber>  
            </labelResponse>  
         </return>  
      </ns2:generateLabelResponse>  
   </soap:Body>  
</soap:Envelope>  
```

To this (MTOMSoap Response, <xop> tags are replaced by there base64 values) :

```xml
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <ns2:generateLabelResponse xmlns:ns2="http://sls.ws.webservice.fr">
      <return>
        <messages>
          <id>0</id>
          <messageContent>La requete a ete traitee avec succes</messageContent>
          <type>INFOS</type>
        </messages>
        <labelResponse>
          <label>EENUfn5DRCx+Q0NefkNUfg0KXlhBDQpeUF[... ZPL code shortened for the sake of this Readme.md ...]</label>
          <parcelNumber>6A12097564594</parcelNumber>
        </labelResponse>
      </return>
    </ns2:generateLabelResponse>
  </soap:Body>
</soap:Envelope>
```

A _var_dump()_ of _$result = $client->__call($url, $params) will look like this (**Note :** SoapClient auto base64_decode()) :

```php
object(stdClass)[2]
  public 'return' => 
    object(stdClass)[3]
      public 'messages' => 
        object(stdClass)[4]
          public 'id' => string '0' (length=1)
          public 'messageContent' => string 'La requete a ete traitee avec succes' (length=41)
          public 'type' => string 'INFOS' (length=5)
      public 'labelResponse' => 
        object(stdClass)[5]
          public 'label' => string 'CT~~CD,~CC^~CT~
^XA
^PW799
^FO0,0^GFA,11264,11264,00088,:Z64:
eJzt2UFv2zYUAGByKqIW8MKrD4bUnnbVsEsGuGH+wf[... ZPL code shortened for the sake of this Readme.md ...]' (length=5856)
          public 'parcelNumber' => string '6A12097564600' (length=13)
```
