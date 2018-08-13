
<?php

	require('./vendor/autoload.php');	
	require_once './setting.php';

class Linebot {

	private $channelAccessToken;
	private $channelSecret;
	public $webhookResponse;
	public $webhookEventObject;
	private $apiReply;
	private $apiPush;
	
	
	public function __construct(){
		
		$this->channelAccessToken = Setting::getChannelAccessToken();
		$this->channelSecret = Setting::getChannelSecret();
		$this->apiReply = Setting::getApiReply();
		$this->apiPush = Setting::getApiPush();
		$this->webhookResponse = file_get_contents('php://input');
		$this->webhookEventObject = json_decode($this->webhookResponse, true);
	}
	
	private function httpPost($api,$body){
		$ch = curl_init($api); 
		curl_setopt($ch, CURLOPT_POST, true); 
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST'); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body)); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, 
			array( 
			'Content-Type: application/json; charset=UTF-8', 
			'Authorization: Bearer '.$this->channelAccessToken)
		); 
		$result = curl_exec($ch); 
		curl_close($ch); 
		return $result;
	}
	
	public function reply($arrayMessage){
		$api = $this->apiReply;
		$webhook = $this->webhookEventObject;
		$replyToken = $webhook->{"events"}[0]->{"replyToken"}; 
		$body["replyToken"] = $replyToken;
		$body["messages"][0] = $arrayMessage;
		
		$result = $this->httpPost($api,$body);
		return $result;
	}
	
	public function replyUsingText($text){
		$arrayMessage = array(
			"type" => "text",
			"text" => $text
		);
		$this->reply($arrayMessage);
	}

	public function replyUsingButtonTemplate($text, $json){
		$arrayMessage = array(
			"type" => "template",
			"altText" => "Cuaca berdasarkan nama kota",
			"template" => json_decode($json, true)
		);
		$this->reply($arrayMessage);
	}
	
	public function getMessageText(){
		$webhook = $this->webhookEventObject;
		$messageText = $webhook->{"events"}[0]->{"message"}->{"text"}; 
		return $messageText;
	}

	public function getEventType(){
		$webhook = $this->webhookEventObject;
		$res = $webhook ->{"events"}[0]->{"type"};
		return $res;
	}

	public function getRoomChatType(){
		$webhook = $this->webhookEventObject;
		$res = $webhook->{"events"}[0]->{"source"}->{"type"};
		return $res;
	}

	public function getMessageType(){
		$webhook = $this->webhookEventObject;
		$res = $webhook->{"events"}[0]->{"message"}->{"type"};
		return $res;
	}
	
	public function postbackEvent(){
		$webhook = $this->webhookEventObject;
		$postback = $webhook->{"events"}[0]->{"postback"}->{"data"}; 
		return $postback;
	}
	
	public function getUserId(){
		$webhook = $this->webhookEventObject;
		$userId = $webhook->{"events"}[0]->{"source"}->{"userId"}; 
		return $userId;
	}
	
	public function leaveGroup($groupId){
		// $this->replyUsingText('Terimakasih! Kalau ada apa apa invite lagi ya!');
		// $this->replyUsingText($leaveGroupApi);
		// Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
		// $ch = curl_init();
		
		// curl_setopt($ch, CURLOPT_URL, "https://api.line.me/v2/bot/group/" . $groupId . "/leave");
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// curl_setopt($ch, CURLOPT_POST, 1);
		
		// $headers = array();
		// $headers[] = "Authorization: Bearer W1yPDkFgGLAQGVeoD5KxzCKJ7Dh8v3ulaBFrZiZHmQQJ5XSRibF72VloI3TbKu+agQp5zkM7qb+HxSQQNPJhChECs3qqppPjtQbwhK7jF23GYFoKRyXwgUaOrkkrMVpM4jxGeoNvuQNgIpbo1/sivQdB04t89/1O/w1cDnyilFU=";
		// curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		// $result = curl_exec($ch);
		// if (curl_errno($ch)) {
			// 		echo 'Error:' . curl_error($ch);
			// }
			// curl_close ($ch);
			// return $result;
			
		$leaveGroupApi = "https://api.line.me/v2/bot/group/". $groupId . "/leave";
		$context = stream_context_create(array(
			"http" => array(
				"method"=> "POST",
				"header"=> implode("\r\n",''),
				"content" => []
			)
		));
		return $this->httpPost($leaveGroupApi, $context);
		// $this->replyUsingText($curlInit);
	}


	public function printGroupId(){
		$webhook = $this->webhookEventObject;
		$groupId = $webhook->{"events"}[0]->{"source"}->{"groupId"};
		$this->replyUsingText($groupId);
	}

	public function getGroupId(){
		$webhook = $this->webhookEventObject;
		$groupId = $webhook->{"events"}[0]->{"source"}->{"groupId"};
		return $groupId;
	}

	public function getWeatherBasedOnCityName($cityName){

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.openweathermap.org/data/2.5/weather?q=" . $cityName . "&units=metric&appid=7ebe6283071c0714ac48cb89e4d82964",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"Cache-Control: no-cache",
				"Postman-Token: 534ca2eb-133d-4d4d-b067-cb112fc6236f"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
		$responseDecoded = json_decode($response);
		$tempInString = (string) $responseDecoded->main->temp;
		$text = "Suhu di " .$responseDecoded->name . " saat ini: " . $tempInString . " °C";
		$title = "Cuaca di " . $responseDecoded->name;
		$json = '{
      "type": "buttons",
      "imageBackgroundColor": "#FFFFFF",
      "title": "' . $title . '",
      "text": "' . $text .'",
      "actions": [
          {  
            "type":"message",
            "label":"Terimakasih!",
            "text":"Wah! makasih"
          }
      ]
  	}';
		// $this->replyUsingText("Suhu di " . $responseDecoded->name . " saat ini: " .$tempInString . " C");
		$this->replyUsingButtonTemplate($text, $json);
	}

	public function processMessageUsingDialogFlow($update){
		if($update["queryResult"]["action"] == "weather"){
			$cityName = $update["queryResult"]["parameters"]["geo-city"];
			error_log("hello, this is a test!");
			error_log($cityName);

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => "https://api.openweathermap.org/data/2.5/weather?q=" . $cityName . "&units=metric&appid=7ebe6283071c0714ac48cb89e4d82964",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "GET",
				CURLOPT_HTTPHEADER => array(
					"Cache-Control: no-cache",
					"Postman-Token: 534ca2eb-133d-4d4d-b067-cb112fc6236f"
				),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);
			$responseDecoded = json_decode($response);
			$tempInString = (string) $responseDecoded->main->temp;
			error_log($tempInString);
			$text = "Suhu di " .$responseDecoded->name . " saat ini: " . $tempInString . " °C";
			$title = "Cuaca di " . $responseDecoded->name;
			$arrayResponse = array(
				"source" => $update["responseId"],
				"fulfillmentText"=>$text,
				"payload" => array(
					"data"=> array(
						"line" => array (
							'type' => 'template',
							'altText' => 'this is a buttons template',
							'template' => 
							array (
								'type' => 'buttons',
								'thumbnailImageUrl' => 'https://example.com/bot/images/image.jpg',
								'title' => 'Menu',
								'text' => 'Please select',
								'actions' => 
								array (
									0 => 
									array (
										'type' => 'postback',
										'label' => 'Buy',
										'data' => 'action=buy&itemid=123',
									),
									1 => 
									array (
										'type' => 'postback',
										'label' => 'Add to cart',
										'data' => 'action=add&itemid=123',
									),
									2 => 
									array (
										'type' => 'uri',
										'label' => 'View detail',
										'uri' => 'http://example.com/page/123',
										),
									),
								),
							),
						),
						),
					);
			error_log(json_encode($this->webhookEventObject));
			error_log(json_encode($arrayResponse));
			echo json_encode($arrayResponse);
		}		
	}

	public function getUserName($userId){
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.line.me/v2/bot/profile/" . $userId,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"Authorization: Bearer W1yPDkFgGLAQGVeoD5KxzCKJ7Dh8v3ulaBFrZiZHmQQJ5XSRibF72VloI3TbKu+agQp5zkM7qb+HxSQQNPJhChECs3qqppPjtQbwhK7jF23GYFoKRyXwgUaOrkkrMVpM4jxGeoNvuQNgIpbo1/sivQdB04t89/1O/w1cDnyilFU=",
				"Cache-Control: no-cache",
				"Postman-Token: 90611f09-abb4-4773-a90c-5b5914152b5a"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		
		$responseDecoded = json_decode($response);
		$userDisplayName = $responseDecoded->displayName;
		return $userDisplayName;
	}
}