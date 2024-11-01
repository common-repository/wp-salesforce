<?php
/**
 * WP Salesforce plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly


class SforceSmLBAdmin {

	public function __construct() {

	}

	public function setEventObj()
	{
		$obj = new mainCrmHelper();
		return $obj;
	}

	public function user_module_mapping_view() {
		include ('views/form-usermodulemapping.php');
	}

	public function mail_sourcing_view() {
		include('views/form-campaign.php');
	}

	public function new_lead_view() {
		global $lb_crm;
		include ('views/form-managefields.php');
	}

	public function new_contact_view() {
		global $lb_crm;
		$module = "Contacts";
		$lb_crm->setModule($module);
		include ('views/form-managefields.php');
	}


	public function show_form_crm_forms() {
		include ('views/form-crmforms.php');
	}


	public function show_salesforce_crm_config() {
		include('views/form-salesforcecrmconfig.php');
	}



	public function salesforceproSettings( $salesSettArray  )
	{
		$result=[];
		$sales_config_array = $salesSettArray['REQUEST'];
		$fieldNames = array(
			'key' => __('Consumer Key' , SM_LB_URL ),
			'secret' => __('Consumer Secret' , SM_LB_URL ),
			'callback' => __('Callback URL' , SM_LB_URL ),
			'smack_email' => __('Smack Email' , SM_LB_URL ),
			'email' => __('Email id' , SM_LB_URL ),
			'emailcondition' => __('Emailcondition' , SM_LB_URL ),
			'debugmode' => __('Debug Mode', SM_LB_URL ),
		);

		foreach ($fieldNames as $field=>$value){

			if(isset($sales_config_array[$field]))
			{
				$config[$field] = $sales_config_array[$field];
			}
		}
		$WPCapture_includes_helper_Obj = new WPCapture_includes_helper_PRO();
		$activateplugin = $WPCapture_includes_helper_Obj->ActivatedPlugin;
		$exist_config = get_option( "wp_{$activateplugin}_settings" );
		if( !empty( $exist_config ) )
			$config = array_merge($exist_config, $config);
		$resp =  update_option("wp_{$activateplugin}_settings", $config);
		$sales_resp['resp'] = $resp;
		$result['error'] = 0;
		$successresult = "Settings Saved";
		$result['success'] = $successresult;
		return $result;
	} 


}
global $lb_crm;
$lb_crm = new SforceSmLBAdmin();
