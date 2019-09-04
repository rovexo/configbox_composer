<?php

namespace MaxMind\Exception;




class HttpException extends WebServiceException
{



private $uri;







public function __construct(
$message,
$httpStatus,
$uri,
\Exception $previous = null
) {
$this->uri = $uri;
parent::__construct($message, $httpStatus, $previous);
}

public function getUri()
{
return $this->uri;
}

public function getStatusCode()
{
return $this->getCode();
}
}
