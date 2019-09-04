<?php



namespace Composer\Autoload;

class ComposerStaticInitd85538a5d6ebadae98c6043cfad17930
{
public static $prefixLengthsPsr4 = array (
'M' => 
array (
'MaxMind\\WebService\\' => 19,
'MaxMind\\Exception\\' => 18,
'MaxMind\\Db\\' => 11,
),
'G' => 
array (
'GeoIp2\\' => 7,
),
'C' => 
array (
'Composer\\CaBundle\\' => 18,
),
);

public static $prefixDirsPsr4 = array (
'MaxMind\\WebService\\' => 
array (
0 => __DIR__ . '/..' . '/maxmind/web-service-common/src/WebService',
),
'MaxMind\\Exception\\' => 
array (
0 => __DIR__ . '/..' . '/maxmind/web-service-common/src/Exception',
),
'MaxMind\\Db\\' => 
array (
0 => __DIR__ . '/..' . '/maxmind-db/reader/src/MaxMind/Db',
),
'GeoIp2\\' => 
array (
0 => __DIR__ . '/../..' . '/src',
),
'Composer\\CaBundle\\' => 
array (
0 => __DIR__ . '/..' . '/composer/ca-bundle/src',
),
);

public static function getInitializer(ClassLoader $loader)
{
return \Closure::bind(function () use ($loader) {
$loader->prefixLengthsPsr4 = ComposerStaticInitd85538a5d6ebadae98c6043cfad17930::$prefixLengthsPsr4;
$loader->prefixDirsPsr4 = ComposerStaticInitd85538a5d6ebadae98c6043cfad17930::$prefixDirsPsr4;

}, null, ClassLoader::class);
}
}
