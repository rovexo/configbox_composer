<?php

class Rovexo_Configbox_KenedoLoader
{
	public function initKenedo() {
		require_once(__DIR__.'/external/kenedo/helpers/init.php');
		initKenedo();
	}
}