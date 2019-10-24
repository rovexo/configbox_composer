<?php

namespace MaxMind\Db\Reader;

class Decoder
{
private $fileStream;
private $pointerBase;

private $pointerTestHack;
private $switchByteOrder;

private $types = [
0 => 'extended',
1 => 'pointer',
2 => 'utf8_string',
3 => 'double',
4 => 'bytes',
5 => 'uint16',
6 => 'uint32',
7 => 'map',
8 => 'int32',
9 => 'uint64',
10 => 'uint128',
11 => 'array',
12 => 'container',
13 => 'end_marker',
14 => 'boolean',
15 => 'float',
];

public function __construct(
$fileStream,
$pointerBase = 0,
$pointerTestHack = false
) {
$this->fileStream = $fileStream;
$this->pointerBase = $pointerBase;
$this->pointerTestHack = $pointerTestHack;

$this->switchByteOrder = $this->isPlatformLittleEndian();
}

public function decode($offset)
{
list(, $ctrlByte) = unpack(
'C',
Util::read($this->fileStream, $offset, 1)
);
$offset++;

$type = $this->types[$ctrlByte >> 5];




if ($type === 'pointer') {
list($pointer, $offset) = $this->decodePointer($ctrlByte, $offset);


if ($this->pointerTestHack) {
return [$pointer];
}

list($result) = $this->decode($pointer);

return [$result, $offset];
}

if ($type === 'extended') {
list(, $nextByte) = unpack(
'C',
Util::read($this->fileStream, $offset, 1)
);

$typeNum = $nextByte + 7;

if ($typeNum < 8) {
throw new InvalidDatabaseException(
'Something went horribly wrong in the decoder. An extended type '
. 'resolved to a type number < 8 ('
. $this->types[$typeNum]
. ')'
);
}

$type = $this->types[$typeNum];
$offset++;
}

list($size, $offset) = $this->sizeFromCtrlByte($ctrlByte, $offset);

return $this->decodeByType($type, $offset, $size);
}

private function decodeByType($type, $offset, $size)
{
switch ($type) {
case 'map':
return $this->decodeMap($size, $offset);
case 'array':
return $this->decodeArray($size, $offset);
case 'boolean':
return [$this->decodeBoolean($size), $offset];
}

$newOffset = $offset + $size;
$bytes = Util::read($this->fileStream, $offset, $size);
switch ($type) {
case 'utf8_string':
return [$this->decodeString($bytes), $newOffset];
case 'double':
$this->verifySize(8, $size);

return [$this->decodeDouble($bytes), $newOffset];
case 'float':
$this->verifySize(4, $size);

return [$this->decodeFloat($bytes), $newOffset];
case 'bytes':
return [$bytes, $newOffset];
case 'uint16':
case 'uint32':
return [$this->decodeUint($bytes), $newOffset];
case 'int32':
return [$this->decodeInt32($bytes), $newOffset];
case 'uint64':
case 'uint128':
return [$this->decodeBigUint($bytes, $size), $newOffset];
default:
throw new InvalidDatabaseException(
'Unknown or unexpected type: ' . $type
);
}
}

private function verifySize($expected, $actual)
{
if ($expected !== $actual) {
throw new InvalidDatabaseException(
"The MaxMind DB file's data section contains bad data (unknown data type or corrupt data)"
);
}
}

private function decodeArray($size, $offset)
{
$array = [];

for ($i = 0; $i < $size; $i++) {
list($value, $offset) = $this->decode($offset);
array_push($array, $value);
}

return [$array, $offset];
}

private function decodeBoolean($size)
{
return $size === 0 ? false : true;
}

private function decodeDouble($bits)
{

list(, $double) = unpack('d', $this->maybeSwitchByteOrder($bits));

return $double;
}

private function decodeFloat($bits)
{

list(, $float) = unpack('f', $this->maybeSwitchByteOrder($bits));

return $float;
}

private function decodeInt32($bytes)
{
$bytes = $this->zeroPadLeft($bytes, 4);
list(, $int) = unpack('l', $this->maybeSwitchByteOrder($bytes));

return $int;
}

private function decodeMap($size, $offset)
{
$map = [];

for ($i = 0; $i < $size; $i++) {
list($key, $offset) = $this->decode($offset);
list($value, $offset) = $this->decode($offset);
$map[$key] = $value;
}

return [$map, $offset];
}

private $pointerValueOffset = [
1 => 0,
2 => 2048,
3 => 526336,
4 => 0,
];

private function decodePointer($ctrlByte, $offset)
{
$pointerSize = (($ctrlByte >> 3) & 0x3) + 1;

$buffer = Util::read($this->fileStream, $offset, $pointerSize);
$offset = $offset + $pointerSize;

$packed = $pointerSize === 4
? $buffer
: (pack('C', $ctrlByte & 0x7)) . $buffer;

$unpacked = $this->decodeUint($packed);
$pointer = $unpacked + $this->pointerBase
+ $this->pointerValueOffset[$pointerSize];

return [$pointer, $offset];
}

private function decodeUint($bytes)
{
list(, $int) = unpack('N', $this->zeroPadLeft($bytes, 4));

return $int;
}

private function decodeBigUint($bytes, $byteLength)
{
$maxUintBytes = log(PHP_INT_MAX, 2) / 8;

if ($byteLength === 0) {
return 0;
}

$numberOfLongs = ceil($byteLength / 4);
$paddedLength = $numberOfLongs * 4;
$paddedBytes = $this->zeroPadLeft($bytes, $paddedLength);
$unpacked = array_merge(unpack("N$numberOfLongs", $paddedBytes));

$integer = 0;


$twoTo32 = '4294967296';

foreach ($unpacked as $part) {

if ($byteLength <= $maxUintBytes) {
$integer = ($integer << 32) + $part;
} elseif (extension_loaded('gmp')) {
$integer = gmp_strval(gmp_add(gmp_mul($integer, $twoTo32), $part));
} elseif (extension_loaded('bcmath')) {
$integer = bcadd(bcmul($integer, $twoTo32), $part);
} else {
throw new \RuntimeException(
'The gmp or bcmath extension must be installed to read this database.'
);
}
}

return $integer;
}

private function decodeString($bytes)
{


return $bytes;
}

private function sizeFromCtrlByte($ctrlByte, $offset)
{
$size = $ctrlByte & 0x1f;
$bytesToRead = $size < 29 ? 0 : $size - 28;
$bytes = Util::read($this->fileStream, $offset, $bytesToRead);
$decoded = $this->decodeUint($bytes);

if ($size === 29) {
$size = 29 + $decoded;
} elseif ($size === 30) {
$size = 285 + $decoded;
} elseif ($size > 30) {
$size = ($decoded & (0x0FFFFFFF >> (32 - (8 * $bytesToRead))))
+ 65821;
}

return [$size, $offset + $bytesToRead];
}

private function zeroPadLeft($content, $desiredLength)
{
return str_pad($content, $desiredLength, "\x00", STR_PAD_LEFT);
}

private function maybeSwitchByteOrder($bytes)
{
return $this->switchByteOrder ? strrev($bytes) : $bytes;
}

private function isPlatformLittleEndian()
{
$testint = 0x00FF;
$packed = pack('S', $testint);

return $testint === current(unpack('v', $packed));
}
}
