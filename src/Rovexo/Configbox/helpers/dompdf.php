<?php
class ConfigboxDomPdfHelper {

	static $initialized = false;
	static $dpi = 300;

	static function getLocation() {
		return KPATH_DIR_CB.DS.'external'.DS.'dompdf';
	}

	static function init() {
		die('This method is no longer in use. Use ConfigboxDomPdfHelper::getDomPdfObject instead.');
	}

	/**
	 * @return Dompdf\Dompdf $domPdf
	 */
	static function getDomPdfObject() {

		require_once(KenedoPlatform::p()->getComponentDir('com_configbox').'/external/dompdf/autoload.inc.php');

		$domPdf = new Dompdf\Dompdf();

		$context = stream_context_create([
			'ssl' => [
				'verify_peer' => FALSE,
				'verify_peer_name' => FALSE,
				'allow_self_signed'=> TRUE
			]
		]);
		$domPdf->setHttpContext($context);

		$customFontDir = KenedoPlatform::p()->getDirDataStore().'/private/dompdf';

		if (!is_dir($customFontDir)) {
			mkdir($customFontDir, 0777, true);
		}

		$options = new DomPdf\Options();
		$options->setTempDir(KenedoPlatform::p()->getTmpPath());
		$options->setLogOutputFile(KenedoPlatform::p()->getLogPath().'/configbox/dompdf.log');
		$options->setDefaultPaperSize('a4');
		$options->setDefaultPaperOrientation('portrait');
		$options->setFontCache($customFontDir);
		$options->setFontDir($customFontDir);
		$options->setIsRemoteEnabled(true);
		$options->setIsPhpEnabled(true);

		$domPdf->setOptions($options);

		return $domPdf;

	}

}