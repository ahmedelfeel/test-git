<?php
header("Cache-Control: no-cache, must-revalidate"); 
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
 
require_once('controllers/nusoap.php');

$settings = parse_ini_file('configuration/server_settings.php');

foreach($settings as $key => $value) {
		define($key, $value);
		// Test Git
	}


$link   = mysql_connect(OOZ_SET_DATABASE_HOST,OOZ_SET_DATABASE_USER,OOZ_SET_DATABASE_PWD);
		  mysql_select_db(OOZ_SET_DATABASE_NAME);

$resultIU = mysql_query("select item_id,custom_field_22,custom_field_28 FROM aims_items where custom_field_3!='Closed' and  custom_field_19='ADSL' and custom_field_18='Data Down' and group_security='20' ",$link);
$counter=0;
while (@$rowIU=mysql_fetch_array($resultIU))
{
	$client = new nusoap_client(MATRIXURL.'/webservice/server.php?wsdl', true);
	$input['account']=$rowIU['custom_field_22'];
	 echo  $rowIU['item_id'].'==='.$rowIU['custom_field_22'].'==='; 
	 echo  $client->call("operational_status",$input);
	 echo '<br>';
	 if($client->call("operational_status",$input)=='up' || $client->call("operational_status",$input)==1 || $client->call("operational_status",$input)=="1")
	 {
	 	$counter++;
		mysql_query("update aims_items set custom_field_3='In Progress', group_security='31',user_security='3',core_log_updated='".time()."'  where item_id='".$rowIU['item_id']."'",$link);
		echo "update aims_items set custom_field_3='In Progress', group_security='31',user_security='3',core_log_updated='".time()."'  where item_id='".$rowIU['item_id']."'<br/>";
	 	//$logIDRow=mysql_fetch_array(mysql_query("select max(id) as LastID  from aims_core_log",$link));
	 	//$logID=$logIDRow['LastID'];
	 	$statusSquRow=mysql_fetch_array(mysql_query("select max(log_status_sequence) as LastStatusSqu FROM aims_core_log  where item_id='".$rowIU['item_id']."'",$link));
	 	$log_status_sequence=$statusSquRow['LastStatusSqu'];
	 	$groupSquRow=mysql_fetch_array(mysql_query("select max(log_supportgroup_sequence) as LastStatusSqu FROM aims_core_log  where item_id='".$rowIU['item_id']."'",$link));
	 	$log_supportgroup_sequence=$groupSquRow['LastStatusSqu'];
	 	//$logID++;
		$log_status_sequence++;
		mysql_query("insert into aims_core_log (id,item_id,create_date,item_identifier,security_id,role_id,log_status_sequence,status_text) values (NULL,'".$rowIU['item_id']."','".time()."','1','1','0','".$log_status_sequence."','In Progress')",$link);
		$log_supportgroup_sequence++;
		$logID++;	
		mysql_query("insert into aims_core_log (id,item_id,create_date,item_identifier,security_id,role_id,log_supportgroup_sequence,support_group) values (NULL,'".$rowIU['item_id']."','".time()."','1','1','0','".$log_supportgroup_sequence."','CC Second Level Support')",$link);
		
		$client = new nusoap_client(MATRIXURL.'webservice/server.php?wsdl', true);
		$input['account']=$rowIU['custom_field_22'];
		$MaxAttainableSpeed=$client->call("getMaxAttainableSpeed",$input);
 		$groupSquRow=mysql_fetch_array(mysql_query("select max(log_item_sequence) as log_item_sequence FROM aims_core_log  where item_id='".$rowIU['item_id']."'",$link));
	 	$log_supportgroup_sequence=$groupSquRow['log_item_sequenced'];
	 	//$logID++;
		mysql_query("insert into aims_core_log (id,item_id,create_date,item_identifier,security_id,role_id,log_item_sequence,log_text ) values (NULL,'".$rowIU['item_id']."','".time()."','1','1','0','.$log_item_sequence.' ,'Max Attainable Speed : ".$MaxAttainableSpeed."' )",$link);
		echo "insert into aims_core_log (id,item_id,create_date,item_identifier,security_id,role_id,log_item_sequence,log_text ) values (NULL,'".$rowIU['item_id']."','".time()."','1','1','0','.$log_item_sequence.' ,'Max Attainable Speed : ".$MaxAttainableSpeed."' )<br>";

		
		
		//$wsdlURL= "http://212.103.165.46/SMSWS/SMSWS?wsdl";
		/*$wsdlURL=SMSURL;
		$namespaceURL	= "urn:SMSWS/types";
		//$login['String_1'] ='ttsaims';
		//$login['String_2']= '123456789';
		$login['String_1'] =SMSUSER;
		$login['String_2']= SMSPASSWORD;
		
		 $mobileNumber =$row['custom_field_28'];
		 $msg['String_1'] =$mobileNumber;
		 $msg['String_2'] = 'Dear Customer, We have performed a test on your line and its working fine now. TEData Support Team';
		 $client = new nusoap_client($wsdlURL, false);
		 $client->soap_defencoding = 'UTF-8';
		 $client->call('checkUser',$login,$namespaceURL);
		if($client->call('sendSMS',$msg,$namespaceURL))
		{
		
		}*/
		$wsdlURL=SMSURL;
		$namespaceURL	= "http://sms.tedata.net/";
		$mobileNumber =$rowIU['custom_field_28'];
		//echo $mobileNumber;
	    $message = 'Dear Customer, We have performed a test on your line and its working fine now. TEData Support Team';
	    $pramters = array('aPhone'=>$mobileNumber,'aSMSBody'=>$message,'username'=>SMSUSER,'password'=>SMSPASSWORD,'optional_user'=>'');
		//print_r($pramters);
 	    $client=new nusoap_client($wsdlURL,false);
	    $client->soap_defencoding='UTF-8';
		$result=$client->call('sendSMS',$pramters,$namespaceURL);
		//print_r($result);
		//echo SMSUSER.'<br>';
		//echo die($result.'<br/>');
		if(!preg_match("/^error/i",$result))
		{
		 $logMessage='SMS send :Dear Customer We have performed a test on your line and it is working fine now';
		}
		else
		{
		 $logMessage='SMS was not send';
		}
        $groupSquRow=mysql_fetch_array(mysql_query("select max(log_item_sequence) as log_item_sequence FROM aims_core_log  where item_id='".$rowIU['item_id']."'",$link));
	 	$log_supportgroup_sequence=$groupSquRow['log_item_sequenced'];
	 	//$logID++;
		//echo die('log_supportgroup_sequence');
		 mysql_query("insert into aims_core_log (id,item_id,create_date,item_identifier,security_id,role_id,log_item_sequence,log_text ) values ('NULL','".$rowIU['item_id']."','".time()."','1','1','0','.$log_item_sequence.' ,'".$logMessage."' )",$link);
		 echo "insert into aims_core_log (id,item_id,create_date,item_identifier,security_id,role_id,log_item_sequence,log_text ) values ('NULL','".$rowIU['item_id']."','".time()."','1','1','0','.$log_item_sequence.' ,'".$logMessage."' )<br>";
	 }	
	
}
		
 $LastCounterResult=mysql_query("SELECT MAX(id) AS lastID FROM aims_tickets_count");
 $lastID=mysql_result($LastCounterResult,0,"lastID")+1;
 if($counter>0){
  mysql_query("INSERT INTO aims_tickets_count (id,ticket_count,creation_date) 
            VALUES(".$lastID.",".$counter.",CURRENT_TIMESTAMP)");
 }

//////////////////////////////////////// End of IU////////////////////////////////////////////
//////////////////////////////////////// CR# 1728 By Hassan on 23, Oct 2012///////////////////
$resultFollow = mysql_query("select item_id,custom_field_22,custom_field_28 FROM aims_items where custom_field_3!='Closed' and  custom_field_19='ADSL' and custom_field_18='Data Down' and group_security='49' ",$link);
$counter=0;
while (@$rowFollow=mysql_fetch_array($resultFollow))
{
	$client = new nusoap_client(MATRIXURL.'/webservice/server.php?wsdl', true);
	$input['account']=$rowFollow['custom_field_22'];
	 echo  $rowFollow['item_id'].'==='.$rowFollow['custom_field_22'].'==='; 
	 echo  $client->call("operational_status",$input);
	 echo '<br>';
	 if($client->call("operational_status",$input)=='up' || $client->call("operational_status",$input)==1 || $client->call("operational_status",$input)=="1")
	 {
	 	$counter++;
		mysql_query("update aims_items set custom_field_3='Waiting for customer', group_security='2',user_security='3',core_log_updated='".time()."'  where item_id='".$rowFollow['item_id']."'",$link);
		echo "update aims_items set custom_field_3='Waiting for customer', group_security='2',user_security='3',core_log_updated='".time()."'  where item_id='".$rowFollow['item_id']."'<br>";
	 	//$logIDRow=mysql_fetch_array(mysql_query("select max(id) as LastID  from aims_core_log",$link));
	 	//$logID=$logIDRow['LastID'];
	 	$statusSquRow=mysql_fetch_array(mysql_query("select max(log_status_sequence) as LastStatusSqu FROM aims_core_log  where item_id='".$rowFollow['item_id']."'",$link));
	 	$log_status_sequence=$statusSquRow['LastStatusSqu'];
	 	$groupSquRow=mysql_fetch_array(mysql_query("select max(log_supportgroup_sequence) as LastStatusSqu FROM aims_core_log  where item_id='".$rowFollow['item_id']."'",$link));
	 	$log_supportgroup_sequence=$groupSquRow['LastStatusSqu'];
	 	//$logID++;
		$log_status_sequence++;
		mysql_query("insert into aims_core_log (id,item_id,create_date,item_identifier,security_id,role_id,log_status_sequence,status_text) values (NULL,'".$rowFollow['item_id']."','".time()."','1','1','0','".$log_status_sequence."','waiting for customer')",$link);
		$log_supportgroup_sequence++;
		$logID++;
		mysql_query("insert into aims_core_log (id,item_id,create_date,item_identifier,security_id,role_id,log_supportgroup_sequence,support_group) values (NULL,'".$rowFollow['item_id']."','".time()."','1','1','0','".$log_supportgroup_sequence."','MCU Call Center')",$link);
		
		$client = new nusoap_client(MATRIXURL.'webservice/server.php?wsdl', true);
		$input['account']=$rowFollow['custom_field_22'];
		$MaxAttainableSpeed=$client->call("getMaxAttainableSpeed",$input);
 		$groupSquRow=mysql_fetch_array(mysql_query("select max(log_item_sequence) as log_item_sequence FROM aims_core_log  where item_id='".$rowFollow['item_id']."'",$link));
	 	$log_supportgroup_sequence=$groupSquRow['log_item_sequenced'];
	 	//$logID++;
		mysql_query("insert into aims_core_log (id,item_id,create_date,item_identifier,security_id,role_id,log_item_sequence,log_text ) values (NULL,'".$rowFollow['item_id']."','".time()."','1','1','0','.$log_item_sequence.' ,'Max Attainable Speed : ".$MaxAttainableSpeed."' )",$link);
		echo "insert into aims_core_log (id,item_id,create_date,item_identifier,security_id,role_id,log_item_sequence,log_text ) values (NULL,'".$rowFollow['item_id']."','".time()."','1','1','0','.$log_item_sequence.' ,'Max Attainable Speed : ".$MaxAttainableSpeed."' )<br>";
		$wsdlURL=SMSURL;
		$namespaceURL	= "http://sms.tedata.net/";
		$mobileNumber =$rowFollow['custom_field_28'];
	    $message = 'Dear Customer, We have performed a test on your line and its working fine now. TEData Support Team';
	    $pramters = array('aPhone'=>$mobileNumber,'aSMSBody'=>$message,'username'=>SMSUSER,'password'=>SMSPASSWORD,'optional_user'=>'');
 	    $client=new nusoap_client($wsdlURL,false);
	    $client->soap_defencoding='UTF-8';
		$result=$client->call('sendSMS',$pramters,$namespaceURL);
		if(!preg_match("/^error/i",$result))
		{
		 $logMessage='SMS send :Dear Customer We have performed a test on your line and it is working fine now';
		}
		else
		{
		 $logMessage='SMS was not send';
		}
        $groupSquRow=mysql_fetch_array(mysql_query("select max(log_item_sequence) as log_item_sequence FROM aims_core_log  where item_id='".$rowFollow['item_id']."'",$link));
	 	$log_supportgroup_sequence=$groupSquRow['log_item_sequenced'];
	 	//$logID++;
		//echo die('log_supportgroup_sequence');
		 mysql_query("insert into aims_core_log (id,item_id,create_date,item_identifier,security_id,role_id,log_item_sequence,log_text ) values ('NULL','".$rowFollow['item_id']."','".time()."','1','1','0','.$log_item_sequence.' ,'".$logMessage."' )",$link);
		 echo "insert into aims_core_log (id,item_id,create_date,item_identifier,security_id,role_id,log_item_sequence,log_text ) values ('NULL','".$rowFollow['item_id']."','".time()."','1','1','0','.$log_item_sequence.' ,'".$logMessage."' )<br>";
	 }	
	
}
		
 $LastCounterResult=mysql_query("SELECT MAX(id) AS lastID FROM aims_tickets_count");
 $lastID=mysql_result($LastCounterResult,0,"lastID")+1;
 if($counter>0){
  mysql_query("INSERT INTO aims_tickets_count (id,ticket_count,creation_date) 
            VALUES(".$lastID.",".$counter.",CURRENT_TIMESTAMP)");
 }
?>