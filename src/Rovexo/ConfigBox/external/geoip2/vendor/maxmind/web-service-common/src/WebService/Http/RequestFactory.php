<?php

namespace MaxMind\WebService\Http;






class RequestFactory
{
public function __construct()
{
}







public function request($url, $options)
{
return new CurlRequest($url, $options);
}
}
