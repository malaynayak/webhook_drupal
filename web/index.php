<?php

/**
 * @file index.php 
 */

$uri = $_SERVER['REQUEST_URI'];
$path_array = explode("/",ltrim($uri,"/"));
if(empty($path_array[2])){
	http_response_code(404);
}

/*
 * constructs a responce for api.ai
 * @response array 
 * results obtained from webservice
 */
function makeWebhookResult($response){
		$members_name = array_map(function($obj){
			return $obj->title;
		}, json_decode($response));
		$members = implode($members_name,",");
    return [
        "speech" => $members,
        "displayText" => $members,
        # "data": data,
        # "contextOut": [],
        "source" => "apiai-drupal-webhook-sample"
    ];
}

/*
 * generates json response
 */
function generateResponse($response){
	header('Content-Type: application/json');
	echo json_encode($response);
}

$base_url = "https://duwdt.ply.st/api/";
$output = [];
print_r($path_array);exit;
if($path_array[2] == "webhook"){
	$post_data = json_decode(file_get_contents('php://input'));
	if(isset($post_data->result)){
		$result = $post_data->result;
		if(isset($result->action)){
			$action = $result->action;
		}

		if(isset($result->parameters)){
			$parameters = $result->parameters;
		}

		if(isset($result->resolvedQuery)){
			$resolvedQuery = $result->resolvedQuery;
		}
		
		if(!empty($resolvedQuery)){
			if(stripos($resolvedQuery, "team")){
				$responce = file_get_contents($base_url."team-members/");
				$output  = makeWebhookResult($responce);
				generateResponse($output);
				exit;
			}
		}

		if(isset($parameters->expertise) 
			&& !empty($parameters->expertise)){
			$val = strtolower($parameters->expertise);
			$responce = file_get_contents($base_url."team-members?field_expertise=".$val);
			$output  = makeWebhookResult($responce);
			generateResponse($output);
			exit;
		}
	}
}
