<?php

class Rovexo_ConfigBox_KenedoLoader
{
	public function initKenedo() {
		require_once(__DIR__.'/external/kenedo/helpers/init.php');
		initKenedo();
	}
}