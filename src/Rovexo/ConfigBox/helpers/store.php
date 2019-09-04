<?php
class ConfigboxStoreHelper {

	/**
	 * @param int|NULL $storeId
	 * @return ConfigboxShopData $storeData
	 * @see ConfigboxModelAdminshopdata::getRecord
	 * @throws Exception
	 *
	 */
	static function getStoreRecord($storeId = NULL) {

		if (!$storeId) {
			$storeId = self::getStoreId();
		}

		$shopModel = KenedoModel::getModel('ConfigboxModelAdminshopdata');
		$shopData = $shopModel->getRecord($storeId);
		return $shopData;
	}

	static function forgetStoreRecords() {
		$shopModel = KenedoModel::getModel('ConfigboxModelAdminshopdata');
		$shopModel->forgetRecords();
	}

	static function getStoreId() {

		if (function_exists('getStoreId')) {
			$storeId = getStoreId();
		}
		else {
			$storeId = 1;
		}

		return $storeId;

	}
}