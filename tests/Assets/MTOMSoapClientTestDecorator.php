<?php

namespace KeepItSimpleTest\Http\Soap\Assets;

use KeepItSimple\Http\Soap\MTOMSoapClient;

class MTOMSoapClientTestDecorator extends MTOMSoapClient
{

    public function dryRun(?string $response): ?string
    {
        return $this->process($response);
    }
}
