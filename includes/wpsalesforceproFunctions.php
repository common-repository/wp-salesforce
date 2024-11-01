<?php
/**
 * WP Salesforce plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

include_once(SM_LB_SFORCE_DIR.'lib/SmackSalesForceApi.php');

class mainCrmHelper{

	public $consumerkey;
	public $consumersecret;
	public $callback;
	public $instanceurl;
	public $accesstoken;
	public $result_emails;
	public $result_ids;
	public $result_products;
	public $url;
	public function __construct()
	{
		$WPCapture_includes_helper_Obj = new WPCapture_includes_helper_PRO();
		$activateplugin = $WPCapture_includes_helper_Obj->ActivatedPlugin;
		$SettingsConfig = get_option("wp_{$activateplugin}_settings");
		if(isset($_REQUEST['crmtype']))
		{
			$crmtype = sanitize_text_field($_REQUEST['crmtype']);
			$SettingsConfig = get_option("wp_{$crmtype}_settings");
		}
		else
		{
			$SettingsConfig = get_option("wp_{$activateplugin}_settings");
		}
		$this->consumerkey = isset($SettingsConfig['key']) ? $SettingsConfig['key'] : '' ;
		$this->consumersecret = isset($SettingsConfig['secret']) ? $SettingsConfig['secret'] : '' ;
		$this->url = "";
		$this->callback = isset($SettingsConfig['callback']) ? $SettingsConfig['callback'] : '' ;
		$this->instanceurl = isset($SettingsConfig['instance_url']) ? $SettingsConfig['instance_url'] : '' ;
		$this->accesstoken = isset($SettingsConfig['access_token']) ? $SettingsConfig['access_token'] : '' ;
	}

	public function getCrmFields( $module )
	{
		$module = $this->moduleMap( $module );
		$recordInfo = Sforce_GetCrmModuleFields( $this->instanceurl, $this->accesstoken , $module );
		$config_fields = array();
		$AcceptedFields = Array( 'textarea' => 'text' , 'string' => 'string' , 'email' => 'email' , 'boolean' => 'boolean', 'picklist' => 'picklist' , 'varchar' => 'string' , 'url' => 'url' , 'phone' => 'phone' , 'multipicklist' => 'multipicklist',  'radioenum' => 'radioenum', 'currency' => 'currency' , 'date' => 'date' , 'datetime' => 'date' , 'int' => 'string' , 'double' => 'string');
		if($recordInfo)
		{
			$j=0;
			$count=count($recordInfo['fields']);
			for($i=0;$i<$count;$i++)
			{
				if(( $recordInfo['fields'][$i]['type'] != 'id' ) && ( $recordInfo['fields'][$i]['updateable'] == 1 ) && ( $recordInfo['fields'][$i]['type'] != 'reference' ) && ( $recordInfo['fields'][$i]['name'] != 'EmailBouncedReason' ) && ( $recordInfo['fields'][$i]['type'] != 'datetime' ) )
				{
					$config_fields['fields'][$j]['name'] = $recordInfo['fields'][$i]['name'];
					$config_fields['fields'][$j]['label'] = $recordInfo['fields'][$i]['label'];
					$config_fields['fields'][$j]['order'] = $j;
					$config_fields['fields'][$j]['publish'] = 1;
					$config_fields['fields'][$j]['display_label'] = $recordInfo['fields'][$i]['label'];
					if( ($recordInfo['fields'][$i]['nillable'] != 1 ) && ( $recordInfo['fields'][$i]['type'] != 'boolean' ))
					{
						$config_fields['fields'][$j]['wp_mandatory'] = 1;
						$config_fields['fields'][$j]['mandatory'] = 2;
					}
					else
					{
						$config_fields['fields'][$j]['wp_mandatory'] = 0;
					}
					if($recordInfo['fields'][$i]['type'] == 'picklist' || $recordInfo['fields'][$i]['type'] == 'multipicklist' )
					{
						foreach( $recordInfo['fields'][$i]['picklistValues'] as $picklistkey => $picklistvalue )
						{
							$config_fields['fields'][$j]['type']['picklistValues'][$picklistkey] = $picklistvalue;
						}
						$config_fields['fields'][$j]['type']['defaultValue'] = "";
						$config_fields['fields'][$j]['type']['name'] = $AcceptedFields[$recordInfo['fields'][$i]['type']];
					}
					else
					{
						$config_fields['fields'][$j]['type']['name'] = $AcceptedFields[$recordInfo['fields'][$i]['type']];
					}

					$j++;
				}
			}
			$config_fields['check_duplicate'] = 0;
			$config_fields['isWidget'] = 0;
			$config_fields['update_record'] = 0;
			$users_list = $this->getUsersList();
			$config_fields['assignedto'] = $users_list['id'][0];
			$config_fields['module'] = $module;
			return $config_fields;
		}
	}

	public function getUsersList()
	{
		$user_details=[];
		$records = Sforce_Getuser( $this->instanceurl, $this->accesstoken );
		if(!isset($records))
		{
			$allowed_html = ['div' => ['class' => true, 'id' => true, 'style' => true, ], 
			'a' => ['id' => true, 'href' => true, 'title' => true, 'target' => true, 'class' => true, 'style' => true, 'onclick' => true,], 
			'strong' => [], 
			'i' => ['id' => true, 'onclick' => true, 'style' => true, 'class' => true, 'aria-hidden' => true, 'title' => true ], 
			'p' => ['style' => true, 'name' => true, 'id' => true, ], 
			'img' => ['id' => true, 'style' => true, 'class' => true, 'align' => true, 'src' => true, 'width' => true, 'height' => true, 'border' => true, ], 
			'table' => ['id' => true, 'class' => true, 'style' => true, 'height' => true, 'cellspacing' => true, 'cellpadding' => true, 'border' => true, 'width' => true, 'align' => true, 'background' => true, 'frame' => true, 'rules' => true, ], 
			'tbody' => [], 
			'br' => ['bogus' => true, ], 
			'tr' => ['id' => true, 'class' => true, 'style' => true, ], 
			'th' => ['id' => true, 'class' => true, 'style' => true, ], 
			'hr' => ['id' => true, 'class' => true, 'style' => true,], 
			'h3' => ['style' => true, ], 
			'td' => ['style' => true, 'id' => true, 'align' => true, 'width' => true, 'valign' => true, 'class' => true, 'colspan' => true, ], 
			'span' => ['style' => true, 'class' => true, ], 
			'h1' => ['style' => true, ], 
			'thead' => [], 
			'tfoot' => ['id' => true, 'style' => true, ], 
			'figcaption' => ['id' => true, 'style' => true, ], 
			'h4' => ['id' => true, 'align' => true, 'style' => true, ],
			'h2' => ['id' => true, 'align' => true, 'style' => true, 'class' => true],
			'script' => [],
			'select' => ['id' => true, 'name' => true, 'class' => true, 'data-size' =>true, 'data-live-search' =>true, 'onchange' => true],
			'option' => ['value' => true, 'selected' => true],
			'label' =>['id' => true, 'class' =>true],
			'input' => ['type' => true, 'value' => true, 'id' => true, 'name' => true, 'class' => true, 'onclick' => true],
			'form' => ['method' => true, 'name' => true, 'id' => true, 'action' => true]];
			$content ="<div style='  font-size:16px;text-align:center'> Please <a href='admin.php?page=wp-leads-builder-any-crm'>configure </a> your CRM </div>";
			echo wp_kses($content,$allowed_html);
			// echo "<div style='  font-size:16px;text-align:center'> Please <a href='admin.php?page=wp-leads-builder-any-crm'>configure </a> your CRM </div>";
			die();
		}
		// foreach($records['recentItems'] as $record) {
		// 	$user_details['user_name'][] = $record['Name'] ;
		// 	$Name = explode(" ",$record['Name']);
		// 	$user_details['first_name'][]= $Name[0];
		// 	$user_details['last_name'][] = $Name[1];
		// 	$user_details['id'][] = $record['Id'];
		// }
		$user_details['user_name'][] = $records['name'] ;
		$Name = explode(" ",$records['name']);
		$user_details['first_name'][]= $Name[0];
		$user_details['last_name'][] = $Name[1];
		$user_details['id'][] = $records['user_id'];
		return $user_details;
	}

	public function getAssignedToList()
	{
		$user_list_array=[];
		$count=count($users_list['user_name']);
		$users_list = $this->getUsersList();
		for($i = 0; $i < $count ; $i++)
		{
			$user_list_array[$users_list['id'][$i]] = $users_list['user_name'][$i];
		}
		return $user_list_array;
	}

	public function mapUserCaptureFields( $user_firstname , $user_lastname , $user_email )
	{
		$post = array();
		$post['FirstName'] = $user_firstname;
		$post['LastName'] = $user_lastname;
		$post[$this->duplicateCheckEmailField()] = $user_email;
		return $post;
	}

	public function assignedToFieldId()
	{
		return "OwnerId";
	}

	public function createRecordOnUserCapture( $module , $module_fields )
	{
		$module = $this->moduleMap( $module );
		$data=[];

		$record = Sforce_CreateRecord( $module_fields , $this->instanceurl, $this->accesstoken , "Contact" );

		if( isset($record['result']['message']) && ( $record['result']['message'] == "Record(s) added successfully" ) )
		{
			$data['result'] = "success";
			$data['failure'] = 0;
		}
		else
		{
			$data['result'] = "failure";
			$data['failure'] = 1;
			$data['reason'] = "failed adding entry";
		}
		return $data;
	}


	public function createRecord( $module , $module_fields )
	{	
		$module = $this->moduleMap( $module );
		$data=[];
		$record = Sforce_CreateRecord( $module_fields , $this->instanceurl, $this->accesstoken , $module );
		if( isset($record['id']))
		{
			$data['result'] = "success";
			$data['failure'] = 0;
		}
		else
		{
			$data['result'] = "failure";
			$data['failure'] = 1;
			$data['reason'] = "failed adding entry";
		}
		return $data;
	}

	public function checkEmailPresent( $module , $email )
	{
		$module = $this->moduleMap( $module );
		$result_lastnames=[];
		$WPCapture_includes_helper_Obj = new WPCapture_includes_helper_PRO();
		$activateplugin = $WPCapture_includes_helper_Obj->ActivatedPlugin;
		$result_emails = array();
		$result_ids = array();
		$records = Sforce_GetRecord( $this->instanceurl , $this->accesstoken , $module , array( "Email" => $email ) );
		if( isset( $records['records'] ) && is_array($records['records']))
		{
			foreach( $records['records'] as $key => $record )
			{
				$result_lastnames[] = "Last Name";
				$result_emails[] = $email; 
				$result_ids[] = $record['Id'];
				$email_present = "yes";
			}
		}
		$this->result_emails = $result_emails;
		$this->result_ids = $result_ids;
		if($email_present == 'yes')
			return true;
		else
			return false;
	}

	public function duplicateCheckEmailField()
	{
		return "Email";
	}

	public function moduleMap( $module )
	{
		$modules_Map = array( "Lead" => "Lead" , "Leads" => "Lead" , "Contact" => "Contact" , "Contacts" => "Contact" );
		return $modules_Map[$module];
	}
}
