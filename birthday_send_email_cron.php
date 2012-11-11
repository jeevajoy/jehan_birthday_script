<?php

define('MTL_BIRTHDAY_SEND_FROM_EMAIL_ADDRESS', 'info@ttc.co.uk');
define('MTL_BIRTHDAY_SEND_MSG_TEMPLATE_ID', '48');
 
// bootstrap the environment and run the processor
session_start();
require_once '../civicrm.config.php';
require_once 'CRM/Core/Config.php';
$config = CRM_Core_Config::singleton();

CRM_Utils_System::authenticateScript(FALSE);

$run = birthday_email();


/* function to send birthday email
*/

function birthday_email(){ 
	 $today = date('Y-m-d');
	 $sql = "SELECT * FROM civicrm_contact cc WHERE birth_date ='$today' AND  
	      NOT EXISTS (SELECT  * FROM mtl_civicrm_brithday_sent bs WHERE cc.id = bs.contact_id  AND date_sent='$today' ) LIMIT 0, 100";        
	 $dao = CRM_Core_DAO::executeQuery($sql); 
	 while($dao->fetch()){
         $send_email = mtl_birthday_send_email_template($dao,$today);
		 if($send_email){
                 echo "Email Sent";
		 }
	}
          
 
}

/***function to  send email to contacts in groups
   *input Params Contact Details and associated Content  
   *****/
function mtl_birthday_send_email_template($contact, $date) { 
       $contactID = $contact->id;
      
       $email_sql = "SELECT * FROM civicrm_email WHERE contact_id = '$contactID' AND  is_primary =1";
       $email_dao = CRM_Core_DAO::executeQuery( $email_sql ); 
       $email_dao -> fetch();
     
       if(  $email_dao->email){
       $email = $email_dao->email;          
       $query = "SELECT * FROM civicrm_msg_template WHERE id=".MTL_BIRTHDAY_SEND_MSG_TEMPLATE_ID;     
       $dao = CRM_Core_DAO::executeQuery( $query );       
       if(!$dao->fetch()){ 
          print("Not able to get Email Template");
          exit;
       }
       
      require_once('api/api.php');
  
      $contactDetails = civicrm_api("Contact","get", array ('version'=>'3','q' =>'civicrm/ajax/rest', 'sequential' =>'1','contact_id'=>$contactID));
      $contactS = $contactDetails['values'][0];
         
      $text   = $dao->msg_text;
      $html   = $dao->msg_html;
      $subject  = $dao->msg_subject; 
      require_once("CRM/Core/BAO/Domain.php");   
      $domain   = CRM_Core_BAO_Domain::getDomain();   
      
      require_once("CRM/Mailing/BAO/Mailing.php");
      $mailing = new CRM_Mailing_BAO_Mailing;
      $mailing->body_text = $text;
      $mailing->body_html = $html;      
      $tokens = $mailing->getTokens(); 
      
      require_once("CRM/Utils/Token.php");    
      $subject = CRM_Utils_Token::replaceDomainTokens($subject, $domain, true, $tokens['text'],true);
      $text    = CRM_Utils_Token::replaceDomainTokens($text,    $domain, true, $tokens['text'],true);
      $html    = CRM_Utils_Token::replaceDomainTokens($html,    $domain, true, $tokens['html'],true);  
   
      if ($contact) {
          $subject = CRM_Utils_Token::replaceContactTokens($subject, $contactS, false, $tokens['text']);
          $text    = CRM_Utils_Token::replaceContactTokens($text,    $contactS, false, $tokens['text']);
          $html    = CRM_Utils_Token::replaceContactTokens($html,    $contactS, false, $tokens['html']); 
      }

      $params['text']       = $text;
      $params['html']       = $html;
      $params['subject']    = $subject;   
      $params['from']       = MTL_BIRTHDAY_SEND_FROM_EMAIL_ADDRESS;
      $params['toName']     = $email;
      $params['toEmail']    = $email;
         
      require_once 'CRM/Utils/Mail.php';       
      $sent = CRM_Utils_Mail::send( $params); 
      }
      
      if($sent){
       $sql = "INSERT INTO `mtl_civicrm_brithday_sent` (`id`, `contact_id`, `is_sent`, `date_sent`) VALUES ('','$contactID','1','$date')";
       $dao = CRM_Core_DAO::executeQuery(  $sql );
      }
          
      return $sent;     
}

