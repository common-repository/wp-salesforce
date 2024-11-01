<?php
/**
 * WP Salesforce plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

if(!function_exists("Sforce_Getaccess"))
{
function Sforce_Getaccess( $config , $code ) {
	$token_url = "https://login.salesforce.com/services/oauth2/token";
	if (!isset($code) || $code == "") {
	    die("Error - code parameter missing from request!");
	}
	 $params = "code=" . $code
	     . "&grant_type=authorization_code"
	     . "&client_id=" . $config['key']
	     . "&client_secret=" . $config['secret']
	     . "&redirect_uri=" . urlencode($config['callback']);
    $args = array(
        'body' => $params
    );
    $response =  wp_remote_post($token_url, $args ) ;
    $status = wp_remote_retrieve_response_code($response);
    
	if ( $status != 200 ) {
	    die("Error: call to token URL $token_url failed with status $status");
	}
    $data = wp_remote_retrieve_body($response);
    $body = json_decode($data, true);
    return $body;
}


function Sforce_GetCrmModuleFields( $instance_url, $access_token , $module = "Lead" )
{
    $url = "$instance_url/services/data/v23.0/sobjects/{$module}/describe/";
    $args = array(
        'headers' => array(
            'Authorization' => 'OAuth '.$access_token
         )
    );
    $response = wp_remote_retrieve_body( wp_remote_get($url, $args ) );
    $body = json_decode($response, true);
    return $body;
}

function Sforce_Getuser( $instance_url, $access_token , $module = "Lead" )
{
    $url = "$instance_url/services/oauth2/userinfo";
    $args = array(
        'headers' => array(
            'Authorization' => 'OAuth '.$access_token
         )
    );
    $response = wp_remote_retrieve_body( wp_remote_get($url, $args ) );
    $body = json_decode($response, true);
    return $body;
}

function Sforce_GetRecord( $instance_url, $access_token , $module = "Lead" , $extraparams = array() )
{
    $params = "";
    if(!empty($extraparams) )
    {
	$params = "+where";
	foreach( $extraparams as $key => $value )
	{
		$params .= "+{$key}+=+'{$value}'";
	}
    }
    $url = "$instance_url/services/data/v23.0/query/?q=SELECT+Id,Email+from+{$module}{$params}";
	$args = array(
        'headers' => array(
            'Authorization' => 'OAuth '.$access_token
         )
    );
    $response = wp_remote_retrieve_body( wp_remote_get($url, $args ) );
    $body = json_decode($response, true);
    return $body;
}

function Sforce_CheckProductPresent($instance_url, $access_token , $module = "Product2" )
{
    $url = "$instance_url/services/data/v23.0/query/?q=SELECT+Id,Name+from+{$module}";
    $args = array(
        'headers' => array(
            'Authorization' => 'OAuth '.$access_token
         )
    );
    $response = wp_remote_retrieve_body( wp_remote_get($url, $args ) );
    $body = json_decode($response, true);
    return $body;
}

//Get Record by ID
function Sforce_GetRecordById($instance_url, $access_token , $module = "Lead" , $id )
{
    $url = "$instance_url/services/data/v23.0/sobjects/Lead/{$id}";
    $args = array(
        'headers' => array(
            'Authorization' => 'OAuth '.$access_token
         )
    );
    $response = wp_remote_retrieve_body( wp_remote_get($url, $args ) );
    $body = json_decode($response, true);
    return $body;
}

//Remove Converted Lead
function Sforce_RemoveConvertedLead($instance_url, $access_token , $module = "Lead" , $lead_no )
{
    $url = "$instance_url/services/data/v23.0/sobjects/Lead/{$lead_no}";
    $args = array(
        'headers' => array(
            'Authorization' => 'OAuth '.$access_token
         )
    );
    $response = wp_remote_retrieve_body( wp_remote_get($url, $args ) );
    $body = json_decode($response, true);
    return $body;
}
//Get Account ID
function Sforce_GetAccountId( $instance_url, $access_token , $module = "Account" , $extraparams = array() )
{
    $params = "";
    if(!empty($extraparams) )
    {
        $params = "+where";
        foreach( $extraparams as $key => $value )
        {
                $params .= "+{$key}+=+'{$value}'";
        }
    }
    $url = "$instance_url/services/data/v23.0/query/?q=SELECT+Id+from+{$module}{$params}";
    $args = array(
        'headers' => array(
            'Authorization' => 'OAuth '.$access_token
         )
    );
    $response = wp_remote_retrieve_body( wp_remote_get($url, $args ) );
    $body = json_decode($response, true);
    return $body;
}

function Sforce_CreateRecord( $data_array, $instance_url, $access_token , $module = "Lead" ) {
    $url = "$instance_url/services/data/v23.0/sobjects/{$module}/";
    $content = json_encode($data_array);
    $args = array(
        'headers' => array(
            'Authorization' => 'OAuth '.$access_token,
            'Content-type' => 'application/json'
         ),
        'body' => $content
    );
    $response = wp_remote_retrieve_body( wp_remote_post($url, $args ) );
    $body = json_decode($response, true);
    return $body;
}

function Sforce_UpdateRecord( $data_array, $instance_url, $access_token ,  $id , $module = "Lead" ) {
    $url = "$instance_url/services/data/v23.0/sobjects/{$module}/$id";
    $content = json_encode($data_array);
    $args = array(
        'headers' => array(
            'Authorization' => 'OAuth '.$access_token,
            'Content-type' => 'application/json'
         ),
        'body' => $content
    );
    $response = wp_remote_post($url, $args );
    $status = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    if ( $status != 201 && $status != 204) {
        die("Error: call to URL $url failed with status $status");
    }
    else
    {
	$data['id'] = $id;
    }
    return $data;
}
}
?>
