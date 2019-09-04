<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckoutpspbridge */

$params['HAENDLERNR'] = $this->orderRecord->payment->params->get('partner_id','');
$params['AUSWAHL'] = ($this->orderRecord->payment->params->get('choose',0) == 1) ? 'J':'N' ;
if ($this->orderRecord->payment->params->get('choose',0) == 0) {
	$params['BRAND'] = $this->orderRecord->payment->params->get('brand','');
}
$params['SERVICENAME'] = "DIALOG";
$params['BETRAG'] = round($this->orderRecord->payableAmount * 100, 0); // VR PAY requires the total without comma
$params['WAEHRUNG'] = CONFIGBOX_CURRENCY_CODE;
$params['INFOTEXT'] = KText::sprintf('Order ID %s from %s',$this->orderRecord->id,$this->shopData->shopname);
$params['ARTIKELANZ'] = 0;
$params['VERWENDANZ'] = 0;

$prefix = KPATH_SCHEME . '://' . KPATH_HOST;

$params['URLANTWORT'] = $this->notificationUrlSecure;
$params['URLAGB'] = $prefix . KLink::getRoute('index.php?option=com_configbox&view=terms');
$params['URLFEHLER'] = $this->failureUrl;;
$params['URLABBRUCH'] = $this->cancelUrl;
$params['URLERFOLG'] = $this->successUrl;
$params['REFERENZNR'] = $this->orderRecord->id;
$params['ZAHLART'] = "KAUFEN";

$query = http_build_query($params, '', '&');

$url = 'https://payinte.vr-epay.de/pbr/transaktion';
$userPwd = $this->orderRecord->payment->params->get('partner_id','') . ":" . $this->orderRecord->payment->params->get('password','');

$key = 'VRPAY_URL_'.$this->orderRecord->id;

$redirectUrl = KSession::get($key);

if (empty($redirectUrl)) {
	$request = curl_init();
	curl_setopt($request, CURLOPT_URL, $url);
	curl_setopt($request, CURLOPT_USERPWD, $userPwd);
	curl_setopt($request, CURLOPT_HTTP_VERSION, 1.1);
	curl_setopt($request, CURLOPT_POST, 1);
	curl_setopt($request, CURLOPT_POSTFIELDS, $query);
	curl_setopt($request, CURLOPT_SSLVERSION, 3);
	curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($request, CURLOPT_HEADER, 1);
	curl_setopt($request, CURLOPT_FOLLOWLOCATION, 0);
	curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($request, CURLOPT_CONNECTTIMEOUT, '20' );

	$post_response = curl_exec($request);
	$info = curl_getinfo($request);
	curl_close ($request);

	$message = str_replace("\r","", $post_response);
	$message = explode("\n\n", $message);

	switch ( $info['http_code'] ) {
		case "302":
			$message = str_replace("\r","", $post_response);
			$message = explode("\n\n", $message);
			$header_array = explode("\n",$message[0]);
			foreach($header_array as $value) {
				$param = explode(": ",$value);
				if(strtoupper($param[0]) == 'LOCATION') {
					$redirectUrl = $value;
					KSession::set($key, str_ireplace('Location: ', '', $redirectUrl));
				}
			}
			break;
		default:
			KLog::log('Error retrieving VR-Pay virtuell payment window URL. Response data was "'.$post_response.'".','error','Cannot retrieve VR-Pay payment window URL, check payment method settings and ConfigBox error log file.');
			break;
	}
}
?>

<div>
	<a class="trigger-redirect-to-psp" href="<?php echo $redirectUrl;?>"> </a>
</div>
