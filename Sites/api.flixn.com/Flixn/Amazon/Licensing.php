<?php

class FlixnAmazonLicensing
{
	const SERVICE_VERSION = '2008-04-28';

    protected  $_awsAccessKeyId = null;
    protected  $_awsSecretAccessKey = null;

	public function __construct($awsAccessKeyId, $awsSecretAccessKey)
	{
		if(!isset($awsAccessKeyId) || !isset($awsSecretAccessKey))
			throw new Exception('No access id or key');

		$this->_awsAccessKeyId = $awsAccessKeyId;
		$this->_awsSecretAccessKey = $awsSecretAccessKey;		
	}
	
	public function ActivateHostedProduct($product_id, $activationKey)
	{
		$product_token = file_get_contents('devpayproducttokens/'.$product_id.'.txt',FILE_USE_INCLUDE_PATH);
		$time = date("Y-m-d\TH:i:s\Z", time()-date("Z"));

		$enc_string = 'ActionActivateHostedProduct'
					. 'ActivationKey' . $activationKey
					. 'AWSAccessKeyId' . $this->_awsAccessKeyId
					. 'ProductToken' . $product_token
					. 'SignatureVersion1'
					. 'Timestamp' . $time
					. 'Version' . self::SERVICE_VERSION;

		$signature = trim(self::_sign($enc_string, $this->_awsSecretAccessKey));

		$activation_URL = 'https://ls.amazonaws.com/?'
						. 'Action=ActivateHostedProduct'
						. '&ActivationKey=' . urlencode($activationKey)
						. '&AWSAccessKeyId=' . urlencode($this->_awsAccessKeyId)
						. '&ProductToken=' . urlencode($product_token)
						. '&Signature=' . urlencode($signature)
						. '&SignatureVersion=1'
						. '&Timestamp=' . urlencode($time)
						. '&Version=' . urlencode(self::SERVICE_VERSION);
		
		$response = file_get_contents($activation_URL);

		$responseXML = new SimpleXMLElement($response);

		if ($responseXML->Error)
			return false;
		else {
			$result = $responseXML->ActivateHostedProductResult;
			return array('UserToken' => $result->UserToken,
						 'PersistentIdentifier' => $result->PersistentIdentifier);
		}
	}
	
	private function _sign($data)
	{
        return base64_encode(
            pack("H*", sha1((str_pad($this->_awsSecretAccessKey, 64, chr(0x00))
            ^(str_repeat(chr(0x5c), 64))) .
            pack("H*", sha1((str_pad($this->_awsSecretAccessKey, 64, chr(0x00))
            ^(str_repeat(chr(0x36), 64))) . $data))))
        );
    }
}