<?php

namespace MaxMind\WebService;

use Composer\CaBundle\CaBundle;
use MaxMind\Exception\AuthenticationException;
use MaxMind\Exception\HttpException;
use MaxMind\Exception\InsufficientFundsException;
use MaxMind\Exception\InvalidInputException;
use MaxMind\Exception\InvalidRequestException;
use MaxMind\Exception\IpAddressNotFoundException;
use MaxMind\Exception\PermissionRequiredException;
use MaxMind\Exception\WebServiceException;
use MaxMind\WebService\Http\RequestFactory;








class Client
{
const VERSION = '0.2.0';

private $caBundle;
private $connectTimeout;
private $host = 'api.maxmind.com';
private $httpRequestFactory;
private $licenseKey;
private $proxy;
private $timeout;
private $userAgentPrefix;
private $userId;













public function __construct(
$userId,
$licenseKey,
$options = []
) {
$this->userId = $userId;
$this->licenseKey = $licenseKey;

$this->httpRequestFactory = isset($options['httpRequestFactory'])
? $options['httpRequestFactory']
: new RequestFactory();

if (isset($options['host'])) {
$this->host = $options['host'];
}
if (isset($options['userAgent'])) {
$this->userAgentPrefix = $options['userAgent'] . ' ';
}

$this->caBundle = isset($options['caBundle']) ?
$this->caBundle = $options['caBundle'] : $this->getCaBundle();

if (isset($options['connectTimeout'])) {
$this->connectTimeout = $options['connectTimeout'];
}
if (isset($options['timeout'])) {
$this->timeout = $options['timeout'];
}

if (isset($options['proxy'])) {
$this->proxy = $options['proxy'];
}
}



















public function post($service, $path, $input)
{
$body = json_encode($input);
if ($body === false) {
throw new InvalidInputException(
'Error encoding input as JSON: '
. $this->jsonErrorDescription()
);
}

$request = $this->createRequest(
$path,
['Content-Type: application/json']
);

list($statusCode, $contentType, $body) = $request->post($body);

return $this->handleResponse(
$statusCode,
$contentType,
$body,
$service,
$path
);
}

public function get($service, $path)
{
$request = $this->createRequest($path);

list($statusCode, $contentType, $body) = $request->get();

return $this->handleResponse(
$statusCode,
$contentType,
$body,
$service,
$path
);
}

private function userAgent()
{
$curlVersion = curl_version();

return $this->userAgentPrefix . 'MaxMind-WS-API/' . self::VERSION . ' PHP/' . PHP_VERSION .
' curl/' . $curlVersion['version'];
}

private function createRequest($path, $headers = [])
{
array_push(
$headers,
'Authorization: Basic '
. base64_encode($this->userId . ':' . $this->licenseKey),
'Accept: application/json'
);

return $this->httpRequestFactory->request(
$this->urlFor($path),
[
'caBundle' => $this->caBundle,
'connectTimeout' => $this->connectTimeout,
'headers' => $headers,
'proxy' => $this->proxy,
'timeout' => $this->timeout,
'userAgent' => $this->userAgent(),
]
);
}



















private function handleResponse(
$statusCode,
$contentType,
$body,
$service,
$path
) {
if ($statusCode >= 400 && $statusCode <= 499) {
$this->handle4xx($statusCode, $contentType, $body, $service, $path);
} elseif ($statusCode >= 500) {
$this->handle5xx($statusCode, $service, $path);
} elseif ($statusCode !== 200) {
$this->handleUnexpectedStatus($statusCode, $service, $path);
}

return $this->handleSuccess($body, $service);
}




private function jsonErrorDescription()
{
$errno = json_last_error();
switch ($errno) {
case JSON_ERROR_DEPTH:
return 'The maximum stack depth has been exceeded.';
case JSON_ERROR_STATE_MISMATCH:
return 'Invalid or malformed JSON.';
case JSON_ERROR_CTRL_CHAR:
return 'Control character error.';
case JSON_ERROR_SYNTAX:
return 'Syntax error.';
case JSON_ERROR_UTF8:
return 'Malformed UTF-8 characters.';
default:
return "Other JSON error ($errno).";
}
}






private function urlFor($path)
{
return 'https://' . $this->host . $path;
}













private function handle4xx(
$statusCode,
$contentType,
$body,
$service,
$path
) {
if (strlen($body) === 0) {
throw new HttpException(
"Received a $statusCode error for $service with no body",
$statusCode,
$this->urlFor($path)
);
}
if (!strstr($contentType, 'json')) {
throw new HttpException(
"Received a $statusCode error for $service with " .
'the following body: ' . $body,
$statusCode,
$this->urlFor($path)
);
}

$message = json_decode($body, true);
if ($message === null) {
throw new HttpException(
"Received a $statusCode error for $service but could " .
'not decode the response as JSON: '
. $this->jsonErrorDescription() . ' Body: ' . $body,
$statusCode,
$this->urlFor($path)
);
}

if (!isset($message['code']) || !isset($message['error'])) {
throw new HttpException(
'Error response contains JSON but it does not ' .
'specify code or error keys: ' . $body,
$statusCode,
$this->urlFor($path)
);
}

$this->handleWebServiceError(
$message['error'],
$message['code'],
$statusCode,
$path
);
}











private function handleWebServiceError(
$message,
$code,
$statusCode,
$path
) {
switch ($code) {
case 'IP_ADDRESS_NOT_FOUND':
case 'IP_ADDRESS_RESERVED':
throw new IpAddressNotFoundException(
$message,
$code,
$statusCode,
$this->urlFor($path)
);
case 'AUTHORIZATION_INVALID':
case 'LICENSE_KEY_REQUIRED':
case 'USER_ID_REQUIRED':
case 'USER_ID_UNKNOWN':
throw new AuthenticationException(
$message,
$code,
$statusCode,
$this->urlFor($path)
);
case 'OUT_OF_QUERIES':
case 'INSUFFICIENT_FUNDS':
throw new InsufficientFundsException(
$message,
$code,
$statusCode,
$this->urlFor($path)
);
case 'PERMISSION_REQUIRED':
throw new PermissionRequiredException(
$message,
$code,
$statusCode,
$this->urlFor($path)
);
default:
throw new InvalidRequestException(
$message,
$code,
$statusCode,
$this->urlFor($path)
);
}
}








private function handle5xx($statusCode, $service, $path)
{
throw new HttpException(
"Received a server error ($statusCode) for $service",
$statusCode,
$this->urlFor($path)
);
}








private function handleUnexpectedStatus($statusCode, $service, $path)
{
throw new HttpException(
'Received an unexpected HTTP status ' .
"($statusCode) for $service",
$statusCode,
$this->urlFor($path)
);
}










private function handleSuccess($body, $service)
{
if (strlen($body) === 0) {
throw new WebServiceException(
"Received a 200 response for $service but did not " .
'receive a HTTP body.'
);
}

$decodedContent = json_decode($body, true);
if ($decodedContent === null) {
throw new WebServiceException(
"Received a 200 response for $service but could " .
'not decode the response as JSON: '
. $this->jsonErrorDescription() . ' Body: ' . $body
);
}

return $decodedContent;
}

private function getCaBundle()
{
$curlVersion = curl_version();



if ($curlVersion['ssl_version'] === 'SecureTransport') {
return;
}
$cert = CaBundle::getSystemCaRootBundlePath();



if (substr($cert, 0, 7) === 'phar://') {
$tempDir = sys_get_temp_dir();
$newCert = tempnam($tempDir, 'geoip2-');
if ($newCert === false) {
throw new \RuntimeException(
"Unable to create temporary file in $tempDir"
);
}
if (!copy($cert, $newCert)) {
throw new \RuntimeException(
"Could not copy $cert to $newCert: "
. var_export(error_get_last(), true)
);
}




register_shutdown_function(
function () use ($newCert) {
unlink($newCert);
}
);
$cert = $newCert;
}
if (!file_exists($cert)) {
throw new \RuntimeException("CA cert does not exist at $cert");
}

return $cert;
}
}
