<?php
class ConfigboxVersionHelper {

	/**
	 * @param string $part (major, minor, patchLevel, betaString), empty to get the whole version string
	 * @return string
	 * @deprecated Use KenedoPlatform::p()->getApplicationVersion() instead. For getting parts of version, split yourself.
	 */
	static public function getConfigBoxVersion($part = NULL) {

		$version = KenedoPlatform::p()->getApplicationVersion();

		if ($part == NULL) {
			return $version;
		}
		else {

			$x = explode('.', $version,3);

			$parts['major'] = $x[0];
			$parts['minor'] = $x[1];

			if (is_int($x[2])) {
				$parts['patchLevel'] = $x[2];
				$parts['betaString'] = '';
			}
			else {
				$l = explode('-',$x[2],2);
				$parts['patchLevel'] = $l[0];
				$parts['betaString'] = !empty($l[1]) ? $l[1] : '';
			}

			return $parts[$part];

		}

	}

}