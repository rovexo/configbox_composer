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

			$memberDefault = var_export($value, true);

			if (is_numeric($value)) {
				$memberDefault = var_export(floatval($value), true);
			}
			elseif(is_object($value)) {
				$memberDefault = 'NULL';
			}
			elseif(is_resource($value)) {
				$memberDefault = 'NULL';
			}
			elseif(is_array($value)) {
				$memberDefault = 'array()';
			}

			$dec .= "\tvar \$$key = $memberDefault;\n";
		}
		$dec .= "}";

		return $dec;

	}

	static function writeClassDeclaration($className, $object) {

		$filename = KPATH_DIR_CB.'/classes/datahelpers/'.$className.'.php';

		file_put_contents($filename, self::getClassDeclaration($className, $object));
	}

}