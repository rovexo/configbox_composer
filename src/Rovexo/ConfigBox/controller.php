<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxController extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultView() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewList() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewForm() {
		return NULL;
	}

	/**
	 * @deprecated 2.7 Use ConfigboxControllerCart::copyCartPosition instead
	 */
	function copyorder() {
		KRequest::setVar('cart_position_id', KRequest::getInt('order_id'));
		KLog::logLegacyCall('You got copyorder as task in some link in your templates or JS (typically in the cart template), replace the task name with copyCartPosition, add parameter controller=cart and change the param order_id in cart_position_id.');
		KenedoController::getController('ConfigboxControllerCart')->copyCartPosition();
	}
	
	/**
	 * @deprecated 2.7 Use ConfigboxControllerCart::editCartPosition instead
	 */
	function editorder() {
		KRequest::setVar('cart_position_id', KRequest::getInt('order_id'));
		KLog::logLegacyCall('You got editorder as task in some link in your templates or JS (typically in the cart template), replace the task name with editCartPosition, add parameter controller=cart and change the param order_id in cart_position_id.');
		KenedoController::getController('ConfigboxControllerCart')->editCartPosition();
	}
	
	/**
	 * @deprecated 2.7 Use ConfigboxControllerCart::removeCartPosition instead
	 */
	function removeOrder() {
		KRequest::setVar('cart_position_id', KRequest::getInt('order_id'));
		KLog::logLegacyCall('You got removeOrder as task in some link in your templates or JS (typically in the cart template), replace the task name with removeCartPosition, add parameter controller=cart and change the param order_id in cart_position_id.');
		KenedoController::getController('ConfigboxControllerCart')->removeCartPosition();
	}
	
	/**
	 * @deprecated 2.7 Use ConfigboxControllerCart::setCartPositionQuantity instead
	 */
	function update_quantity() {
		KRequest::setVar('cart_position_id', KRequest::getInt('order_id'));
		KLog::logLegacyCall('You got update_quantity as task in some link or hidden form field in your templates or JS (typically in the cart template), replace the task name with setCartPositionQuantity, add parameter controller=cart and change the param order_id in cart_position_id.');
		KenedoController::getController('ConfigboxControllerCart')->setCartPositionQuantity();
	}
	
	/**
	 * @deprecated 2.7 Use ConfigboxControllerCart::finishConfiguration instead
	 */
	function finish_order() {
		KRequest::setVar('cart_position_id', KRequest::getInt('order_id'));
		KLog::logLegacyCall('You got finish_order as task in some link or hidden form field in your templates or JS (typically in the configurator page template), replace the task name with finishConfiguration, add parameter controller=cart and change the param order_id in cart_position_id.');
		KenedoController::getController('ConfigboxControllerCart')->finishConfiguration();
	}

	/**
	 * @deprecated 2.7 Use ConfigboxControllerCart::addProductToCart instead
	 */
	function addtocart() {
		KLog::logLegacyCall('You got addtocart as task in some link or hidden form field in your templates or JS (typically in the product listing or product detail page), add parameter controller=cart and replace the task name with addProductToCart.');
		KenedoController::getController('ConfigboxControllerCart')->addProductToCart();
	}
	
}
