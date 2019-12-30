<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckoutpspbridge */

ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . dirname(__FILE__).DS.'Billsafe');

require (dirname(__FILE__).DS.'Billsafe'.DS.'Sdk.php');
require (dirname(__FILE__).DS.'Billsafe'.DS.'LoggerFile.php');

$bs = new Billsafe_Sdk();

$credentials = array(
	'merchantId'             => $this->orderRecord->payment->params->get('merchant_id',''),
	'merchantLicenseSandbox' => $this->orderRecord->payment->params->get('merchant_license_test',''),
	'merchantLicenseLive'    => $this->orderRecord->payment->params->get('merchant_license_live',''),
	'applicationSignature'   => $this->orderRecord->payment->params->get('application_signature',''),
	'applicationVersion'     => KenedoPlatform::p()->getApplicationVersion(),
);

$bs->setCredentials($credentials);

$logPath = KenedoPlatform::p()->getLogPath().DS.'configbox'.DS.'billsafe.log';

$bs->setLogger(new Billsafe_LoggerFile($logPath));

// Get the tax
$tax = $this->orderRecord->baseTotalTax;
if ($this->orderRecord->delivery) {
	$tax += $this->orderRecord->delivery->priceTax;
}
if ($this->orderRecord->payment) {
	$tax += $this->orderRecord->payment->priceTax;
}

$params = array(
		'order_number'        => intval($this->orderRecord->id),
		'order_amount'        => round($this->orderRecord->payableAmount, 2),
		'order_taxAmount'     => round($tax,2),
		'order_currencyCode'  => $this->orderRecord->currency->code,
		'customer'            => array(
				'id'              => $this->orderRecord->user_id,
				'gender'          => ($this->orderRecord->orderAddress->gender == 1) ? 'm':'f',
				'company'      	  => $this->orderRecord->orderAddress->billingcompanyname,
				'firstname'       => htmlentities($this->orderRecord->orderAddress->billingfirstname, ENT_SUBSTITUTE),
				'lastname'        => htmlentities($this->orderRecord->orderAddress->billinglastname, ENT_SUBSTITUTE),
				'street'          => htmlentities($this->orderRecord->orderAddress->billingaddress1, ENT_SUBSTITUTE),
				'postcode'        => htmlentities($this->orderRecord->orderAddress->billingzipcode, ENT_SUBSTITUTE),
				'city'            => htmlentities($this->orderRecord->orderAddress->billingcity, ENT_SUBSTITUTE),
				'country'         => htmlentities($this->orderRecord->orderAddress->billingcountry_2_code, ENT_SUBSTITUTE),
				'email'           => htmlentities($this->orderRecord->orderAddress->billingemail, ENT_SUBSTITUTE),
				'phone'           => htmlentities($this->orderRecord->orderAddress->billingphone, ENT_SUBSTITUTE),
				),
		'product'             => 'invoice',
		'url_return'          => $this->notificationUrl,
		'url_cancel'          => $this->cancelUrl,		
);

$i = 0;
foreach ($this->orderRecord->positions as $position) {
	$i++;
	$params['articleList'][] = array(
		'number'      => $i,
		'name'        => htmlentities($position->productTitle, ENT_SUBSTITUTE),
		'description' => '',
		'type'        => 'goods',
		'quantity'    => $position->quantity,
		'netPrice'    => $position->totalUnreducedNet,
		'tax'         => $position->taxRate,
	);
	
}

if ($this->orderRecord->delivery && $this->orderRecord->delivery->priceGross != 0) {
	$i++;
	$params['articleList'][] = array(
		'number'      => $i,
		'name'        => htmlentities(KText::_('Delivery'), ENT_SUBSTITUTE),
		'description' => '',
		'type'        => 'shipment',
		'quantity'    => 1,
		'grossPrice'  => $this->orderRecord->delivery->priceGross,
		'tax'         => $this->orderRecord->delivery->taxRate,
	);
}

if ($this->orderRecord->payment && $this->orderRecord->payment->priceGross != 0) {
	$i++;
	$params['articleList'][] = array(
			'number'      => $i,
			'name'        => htmlentities(KText::_('Payment'), ENT_SUBSTITUTE),
			'description' => '',
			'type'        => 'handling',
			'quantity'    => 1,
			'grossPrice'  => $this->orderRecord->payment->priceGross,
			'tax'         => $this->orderRecord->payment->taxRate,
	);
}

if ($this->orderRecord->usesDiscount) {
	$i++;
	$params['articleList'][] = array(
			'number'      => $i,
			'name'        => htmlentities($this->orderRecord->discount->title, ENT_SUBSTITUTE),
			'description' => '',
			'type'        => 'voucher',
			'quantity'    => 1,
			'grossPrice'  => $this->orderRecord->totalDiscountGross,
			'tax'         => 0,
	);
}

$response = $bs->callMethod('prepareOrder', $params);

if (!$response || strtolower($response->ack) != 'ok') {
	foreach ($response->errorList as $error) {
		$message = 'BillSAFE returned an error during prepareOrder. Error number is "'.$error->code.'", Error message is "'.$error->message.'".';
		KLog::log($message,'payment');
		KLog::log($message,'error');
	}
	?>
	<script type="text/javascript">
	cbrequire(['cbj'], function(cbj) {
		cbj('.wrapper-psp-bridge').show();
		cbj('.trigger-place-order').removeClass('processing');
	})
	</script>
	<p><?php echo KText::_('An error occured while processing your order. Please try another payment method or call us for assistance.');?></p>
	<?php
}
else {
	if ($response->token) {
		$db = KenedoPlatform::getDb();
		$query = "UPDATE #__cbcheckout_order_records SET `transaction_id` = '".$db->getEscaped($response->token)."' WHERE `id` = ".intval($this->orderRecord->id);
		$db->setQuery($query);
		$db->query();
		
		$testMode = $this->orderRecord->payment->params->get('testmode',0);
		if ($testMode) {
			$url = 'https://sandbox-payment.billsafe.de/V200';
		}
		else {
			$url = 'https://payment.billsafe.de/V200';
		}
		$url .= '?token='.$response->token;
		?>
		<script type="text/javascript">
			window.location.href = '<?php echo $url;?>';
		</script>
		<?php
	}
	
}
