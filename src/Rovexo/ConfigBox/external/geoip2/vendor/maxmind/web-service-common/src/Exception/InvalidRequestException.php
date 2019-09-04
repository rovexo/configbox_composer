<?php

namespace MaxMind\Exception;




class InvalidRequestException extends HttpException
{



private $error;








public function __construct(
$message,
$error,
$httpStatus,
$uri,
\Exception $previous = null
) {
$this->error = $error;
parent::__construct($message, $httpStatus, $uri, $previous);
}

public function getErrorCode()
{
return $this->error;
}
}
