<?php
class ConfigboxDataHelper {

	static function getClassDeclaration($className, $object) {

		$dec = "<?php\n";
		$dec .= "class $className {\n";
		foreach ($object as $key=>$value) {

			// Leave out illegal member names
			if (strstr($key, '-')) {
				continue;
			}

			$dec .= "\tpublic \$$key;\n";
		}
		$dec .= "}";

		return $dec;

	}

	static function writeClassDeclaration($className, $object, $dir = null) {

		if ($dir === null) {
			$dir = KenedoPlatform::p()->getComponentDir('com_configbox').'/classes/datahelpers';
		}
		$filename = $dir.'/'.$className.'.php';

		file_put_contents($filename, self::getClassDeclaration($className, $object));
	}

}