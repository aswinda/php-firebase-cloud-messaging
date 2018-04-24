<?php

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
	/*
		Set the devices to send to
		@param $deviceIds array of device tokens to send to
	*/
	function setDevices($deviceIds){
		if(is_array($deviceIds)){
			$this->devices = $deviceIds;
		} else {
			$this->devices = array($deviceIds);
		}
	}
	
	public function send(Message $message)
    {
        return $this->guzzleClient->post(
            $this->getApiUrl(),
            [
                'headers' => [
                    'Authorization' => sprintf('key=%s', $this->apiKey),
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode($message)
            ]
        );
    }
    
	function send($message, $data = false){
		
		if(!is_array($this->devices) || count($this->devices) == 0){
			throw new GCMPushMessageArgumentException("No devices set");
		}
		
		if(strlen($this->serverApiKey) < 8){
			throw new GCMPushMessageArgumentException("Server API Key not set");
		}
		
		$fields = array(
			'registration_ids'  => $this->devices,
			'data'              => array( "message" => $message ),
		);
		
		if(is_array($data)){
			foreach ($data as $key => $value) {
				$fields['data'][$key] = $value;
			}
		}
		$headers = array( 
			'Authorization: key=' . $this->serverApiKey,
			'Content-Type: application/json'
		);
		// Open connection
		$ch = curl_init();
		
		// Set the url, number of POST vars, POST data
		curl_setopt( $ch, CURLOPT_URL, $this->url );
		
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
class GCMPushMessageArgumentException extends Exception {
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}