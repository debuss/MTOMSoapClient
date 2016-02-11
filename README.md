# MTOMSoapClient
Small PHP Soap class to deal with MTOM technology, fetching binaries as base64 string. 

This class overrides some SoapClient methods to implement MTOM for PHP.  
It decodes XML and integrate attchment in the XML response.

It replaces the

```
<xop:Include href="cid:d08bab58-dfea-43f0-8520-477d4c5e0677-103@cxf.apache.org" xmlns:xop="http://www.w3.org/2004/08/xop/include"/>
```

By the binary code contained in attachment

```
Content-ID: <d08bab58-dfea-43f0-8520-477d4c5e0677-103@cxf.apache.org>  
```

# Personal Note

The class is not perfect and not so optimized but it works for most cases and you can modify it as you wish to make it better

**Enjoy !**

# Example

It will turn this answer (normal Soap Response) :


```xml
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">  
   <soap:Body>  
      <ns2:generateLabelResponse xmlns:ns2="http://sls.ws.coliposte.fr">  
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

To this (MTOMSoap Response) :

```xml
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <ns2:generateLabelResponse xmlns:ns2="http://sls.ws.coliposte.fr">
      <return>
        <messages>
          <id>0</id>
          <messageContent>La requete a ete traitee avec succes</messageContent>
          <type>INFOS</type>
        </messages>
        <labelResponse>
          <label>EENUfn5DRCx+Q0NefkNUfg0KXlhBDQpeUFc3OTkNCl5GTzAsMF5HRkEsMTEyNjQsMTEyNjQsMDAwODgsOlo2NDoKZUp6dDJVRnYyell[... ZPL code shortened for the sake of this Readme.md ...]</label>
          <parcelNumber>6A12097564594</parcelNumber>
        </labelResponse>
      </return>
    </ns2:generateLabelResponse>
  </soap:Body>
</soap:Envelope>
```

A _var_dump()_ of _$result = $client->\_\_call($url, $params) will look like this (SoapClient auto base64_decode()) :

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
eJzt2UFv2zYUAGByKqIW8MKrD4bUnnbVsEsGuGH+wf4CjQLZJRg87JJDECnLoZcB+QND90cGlEEO2aGA/0DRydihlwFTkENZTBD3HknRdixnTi0NO/gBrR1H[... ZPL code shortened for the sake of this Readme.md ...]' (length=5856)
          public 'parcelNumber' => string '6A12097564600' (length=13)
```