<?php

namespace aswinda;

class CloudMessaging 
{
	const URL = 'https://fcm.googleapis.com/fcm/send';
    const ADD_SUBSCRIPTION_URL = 'https://iid.googleapis.com/iid/v1:batchAdd';
    const REMOVE_SUBSCRIPTION_URL = 'https://iid.googleapis.com/iid/v1:batchRemove';

    private $apiKey;
	private $devices = array();

	public function __construct($apiKey)
	{
		return $this->setApiKey($apiKey);
	}
	
	public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }
	
	function setDevices($deviceIds)
	{
		if(is_array($deviceIds)){
			$this->devices = $deviceIds;
		} else {
			$this->devices = array($deviceIds);
		}
	}

	function send($message, $devices = [], $data = false)
	{
		if(!empty($devices))
			$this->devices = $devices;

		if(!is_array($this->devices) || count($this->devices) == 0)
		{
			throw new CloudMessagingArgumentException("No devices set");
		}
		
		if(empty($this->apiKey))
		{
			throw new CloudMessagingArgumentException("Server API Key not set");
		}
		
		$fields = array(
			'registration_ids'  => $this->devices,
			'data'              => array( "message" => $message ),
		);
		
		if(is_array($data))
		{
			foreach ($data as $key => $value) {
				$fields['data'][$key] = $value;
			}
		}

		$headers = array( 
			'Authorization: key=' . $this->apiKey,
			'Content-Type: application/json'
		);

		// Open connection
		$ch = curl_init();
		
		// Set the url, number of POST vars, POST data
		curl_setopt( $ch, CURLOPT_URL, self::URL );
		
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		
		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );
		
		// Avoids problem with https certificate
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
		
		// Execute post
		$result = curl_exec($ch);
		
		// Close connection
		curl_close($ch);
		
		return $result;
	}
}

class CloudMessagingArgumentException extends \Exception 
{
    public function __construct($message, $code = 0, Exception $previous = null) 
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}