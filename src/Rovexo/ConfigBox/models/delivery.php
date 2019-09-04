<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelDelivery extends KenedoModel {

	/**
	 * @return ConfigboxDeliveryMethodData[]
	 */
	function getDeliveryOptionsData() {
		$deliveryOptions = ConfigboxCacheHelper::getFromCache('deliveryOptions');
		if ($deliveryOptions === NULL) {
			$db = KenedoPlatform::getDb();
			$query = "SELECT * FROM `#__configbox_shipping_methods` WHERE `published` = '1'";
			$db->setQuery($query);
			$deliveryOptions = $db->loadObjectList('id');
			ConfigboxCacheHelper::writeToCache('deliveryOptions', $deliveryOptions);
			$deliveryOptions = ConfigboxCacheHelper::getFromCache('deliveryOptions');
		}

		// Add translatable texts
		foreach($deliveryOptions as $option) {
			$option->shipperTitle 	= ConfigboxCacheHelper::getTranslation('#__configbox_strings', 44, $option->shipper_id);
			$option->rateTitle 		= ConfigboxCacheHelper::getTranslation('#__configbox_strings', 45, $option->id);
			$option->title =& $option->rateTitle;
		}

		return $deliveryOptions;
	}

	/**
	 * @param int $cartId
	 * @param ConfigboxUserData $orderAddress
	 * @param float $weight
	 * @param array $maxDimensions
	 * @param bool $cheapestOnly
	 * @return ConfigboxDeliveryMethodData[]
	 */
	function getDeliveryOptions($cartId, $orderAddress, $weight, $maxDimensions, $cheapestOnly = false) {
		
		if (!$orderAddress) {
			$return = array();
			return $return;
		}
		
		if (!$orderAddress->country) {
			$return = array();
			return $return;
		}

		// Get delivery option IDs that match the address' zone
		$ass = ConfigboxCacheHelper::getAssignments();
		$zoneIds = isset($ass['country_to_zone'][$orderAddress->country]) ? $ass['country_to_zone'][$orderAddress->country] : array();
		$possibleOptionIds = array();
		foreach ($zoneIds as $zoneId) {
			if (isset($ass['zone_to_shippingmethod'][$zoneId])) {
				foreach ($ass['zone_to_shippingmethod'][$zoneId] as $possibleOptionId) {
					$possibleOptionIds[] = $possibleOptionId;
				}
			}
		}

		// Get the data of all methods
		$allOptions = $this->getDeliveryOptionsData();

		// Collect options with matching weight
		$possibleOptions = array();
		foreach ($possibleOptionIds as $possibleOptionId) {
			
			if ($allOptions[$possibleOptionId]->minweight && $weight < $allOptions[$possibleOptionId]->minweight) {
				continue;
			}

			if ($allOptions[$possibleOptionId]->maxweight && $weight > $allOptions[$possibleOptionId]->maxweight) {
				continue;
			}

			$possibleOptions[] = $allOptions[$possibleOptionId];

		}

		// Add tax rate and pricing
		foreach ($possibleOptions as $possibleOption) {

			$taxRate = ConfigboxUserHelper::getTaxRate($possibleOption->taxclass_id, $orderAddress);

			$possibleOption->taxRate = $taxRate;

			$possibleOption->basePriceNet = $possibleOption->price;
			$possibleOption->basePriceTax = round($possibleOption->basePriceNet * floatval($taxRate) / 100, 2);
			$possibleOption->basePriceGross = $possibleOption->basePriceNet + $possibleOption->basePriceTax;
			unset($possibleOption->price);

		}
		unset($possibleOption);


		// Run each option through the manipulation function
		foreach ($possibleOptions as $key=>$possibleOption) {

			if (function_exists('manipulateDeliveryOption')) {
				manipulateDeliveryOption($possibleOption, $cartId, $orderAddress, $weight, $maxDimensions);
			}

			// Remove deactivated options (can be deactivated in override function)
			if (!empty($possibleOption->deactivated)) {
				unset($possibleOptions[$key]);
			}
		}
		unset($possibleOption);


		// Append currency prices and add the sortHelper
		foreach ($possibleOptions as $possibleOption) {
			ConfigboxCurrencyHelper::appendCurrencyPrices($possibleOption);
		}
		unset($possibleOption);

		$sortFunction = function($a, $b) {
			$sortA = round($a->basePriceGross, 3).$a->ordering;
			$sortB = round($b->basePriceGross, 3).$b->ordering;
			return $sortA > $sortB;
		};
		
		// Sort by price descending and ordering
		usort($possibleOptions, $sortFunction);

		// Return the cheapest option if requested
		if ($cheapestOnly && count($possibleOptions)) {
			return array($possibleOptions[0]);
		}
		
		return $possibleOptions;
		
	}
	
}
