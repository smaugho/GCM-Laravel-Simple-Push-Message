<?php namespace App\Push;
/*
	Class to send push notifications using Google Cloud Messaging for Android and Laravel

	Example usage
	-----------------------
	$an = new GCMPushMessage($apiKey);
	$an->sendFromDB($tableName, $message);
	-----------------------
	
	$apiKey Your GCM api key
	$tableName The table where are stored all yours
	$message The mesasge you want to push out

	@author Adrián Rivero

	Adapted from:
	https://github.com/mattg888/GCM-PHP-Server-Push-Message

*/
use DB;
use Exception;

class GCMPushMessage {

	var $url = 'https://android.googleapis.com/gcm/send';
	var $serverApiKey = "";
	var $devices = array();
	
	/*
		Constructor
		@param $apiKeyIn the server API key
	*/
	public function __construct($apiKeyIn){
		$this->serverApiKey = $apiKeyIn;
	}

	/*
		Set the devices to send to
		@param $deviceIds array of device tokens to send to
	*/
	public function setDevices($deviceIds){
	
		if(is_array($deviceIds)){
			$this->devices = $deviceIds;
		} else {
			$this->devices = array($deviceIds);
		}
	
	}

	public function sendFromDB($message, $users_id = null, $table = 'gcms') {
        $count = DB::table($table)->count();
        $lower = 0;

        if (!$users_id) {
            $higher = DB::table($table)->orderBy("id", "desc")->first()->id;
            echo "Sending from ".$lower." to ".$higher.". (".$count." devices)\n";
        } else {
            echo "Sending to ".(count($users_id) >=2 ? $users_id[0]." and ".$users_id[1] : count($users_id)." users")."\n";
        }

        $lastId = $lower;
        while (true) {
            //Get 1000 devices
            $devList = array();

            $devices = DB::table($table);
            if ($users_id) $devices = $devices->whereIn('user_id', $users_id);
            $devices = $devices->where("id", ">", $lastId)->orderBy("id")->take(1000)->get();

            if ($users_id) {
                echo "Found ".count($devices)."\n";
            }

            //If there's no more devices, return
            if (!count($devices)) return "GCM Sent";
            $lastId = end($devices)->id;

            //Create the $devList
            foreach($devices as $device) $devList[] = $device->registeredId;

            //Send the messages to those devices using GCM
            $this->setDevices($devList);
            $resp = $this->send($message);

            if (!$resp) return "GCM Error";

            //Process the GCM server response
            try {
                $json = json_decode($resp);

                //If there's failure or canonical ids, process the results
                if ($json->failure || $json->canonical_ids) {
                    for ($i = 0; $i < count($devList); $i++) {
                        $result = $json->results[$i];

                        try {
                            if (property_exists($result, "error")) {
                                //If not registered the device, remove it from DB
                                if ($result->error == "NotRegistered") {
                                    $dev = DB::table($table)->where("registeredId", $devList[$i])->first();
                                    if ($dev) {
                                        DB::table($table)->delete($dev->id);
                                        echo "Removing device  " . $dev->id . " of user " . $dev->user_id . "\n";
                                    }
                                }
                            }
                        } catch (Exception $e){
                            echo "Exception removing ".$e->getMessage()."\n";
                        }

                        try {
                            //Check for canonical ids
                            if (property_exists($result, "message_id") && property_exists($result, "registration_id")) {
                                if ($result->message_id && $result->registration_id) {
                                    $dev = DB::table($table)->where("registeredId", $devList[$i])->first();
                                    if ($dev) {
                                        if (DB::table($table)->where("registeredId", $result->registration_id)->exists()) {
                                            DB::table($table)->delete($dev->id);
                                            echo "Unused device " . $dev->id . " of user " . $dev->user_id . "\n";
                                        } else {
                                            $dev->registeredId = $result->registration_id;
                                            $dev->save();
                                            echo "Changing device " . $dev->id . " of user " . $dev->user_id . "\n";
                                        }
                                    }
                                }
                            }
                        } catch (Exception $e){
                            echo "Exception  checking canonicals ".$e->getMessage()."\n";
                        }
                    }
                }
            } catch (Exception $e){
                echo "Exception sending ".$e->getMessage()."\n";
            }

        }
	}

	/*
		Send the message to the devices
		@param $message The message to send
		@param $data Array of data to accompany the message
	*/
	public function send($message){
		
		if(!is_array($this->devices) || count($this->devices) == 0){
			$this->error("No devices set");
            return null;
		}
		
		if(strlen($this->serverApiKey) < 8){
			$this->error("Server API Key not set, value provided: ".$this->serverApiKey);
            return null;
		}


		$fields = array(
			'registration_ids' => $this->devices,
			'data' => array("Object" => $message)
		);

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

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );

        // Execute post
		$result = curl_exec($ch);
		
		// Close connection
		curl_close($ch);

		return $result;
	}
	
	private function error($msg){
		echo "Android send notification failed with error:";
		echo "\t".$msg."\n";
	}
}
