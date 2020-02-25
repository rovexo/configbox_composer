<?php

class Rovexo_Configbox_KenedoLoader
{
	public function initKenedo() {
		require_once(__DIR__.'/external/kenedo/helpers/init.php');
		initKenedo();
	}

	/**
	 * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
	 */
	public function changeDbConnection($connection) {
		$db = KenedoPlatform::getDb();
		$db->changeConnection($connection);
	}

	public function applyUpdates() {
		ConfigboxUpdateHelper::applyUpdates();
	}
}