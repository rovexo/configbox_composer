<?php



$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
'MaxMind\\WebService\\' => array($vendorDir . '/maxmind/web-service-common/src/WebService'),
'MaxMind\\Exception\\' => array($vendorDir . '/maxmind/web-service-common/src/Exception'),
'MaxMind\\Db\\' => array($vendorDir . '/maxmind-db/reader/src/MaxMind/Db'),
'GeoIp2\\' => array($baseDir . '/src'),
'Composer\\CaBundle\\' => array($vendorDir . '/composer/ca-bundle/src'),
);
