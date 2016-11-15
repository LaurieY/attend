<?php


class AttendanceAjaxController extends Controller {
	private $u3ayear;
	function afterroute() { // allows simple non views activities
	}
function beforeroute() {
//$f3->set('message','');
	$f3=$this->f3;
	$auth_logger = new MyLog('auth.log');
	$auth_logger->write( 'Entering CheckController beforeroute URI= '.$f3->get('URI'  ) );
	$options= new Option($this->db);
	$this->u3ayear = $f3->get('SESSION.u3ayear');
	if($this->u3ayear =='') {$options->initu3ayear();		
	$this->u3ayear = $f3->get('SESSION.u3ayear');}
	}
	function attendance_list() {
	$f3=Base::instance();
	$uselog=$f3->get('uselog');
	$check_logger = new MyLog('attendance.log');
	require_once 'krumo/class.krumo.php'; 
	$check_logger->write( 'Entering attendance_list',$uselog  );	
			
	define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
	date_default_timezone_set('Europe/Madrid');
	require_once('vendor/Classes/PHPExcel.php');
	require_once('vendor/Classes/PHPExcel/IOFactory.php');
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator('Laurie Yates')
						 //->setLastModifiedBy('Maarten Balliauw')
						 ->setTitle('U3A International Attendance List')
						 ->setSubject('Attendance List')
						 ->setDescription('TU3A International Attendance List using latest membership list')
						// ->setKeywords('office PHPExcel php')
						/*->setCategory('Test result file')*/;

// Create the worksheet
	$objPHPExcel->setActiveSheetIndex(0);
	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

	/******  Read from the members database for all members active for this u3ayear  */
	 $member = new Member($this->db);
     $all_members=$member->all_by_surname();
	 //krumo($all_members);
	$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Email')
					->setCellValue('B1', 'Membership Num')
					->setCellValue('C1', 'Forename')
					->setCellValue('D1', 'Surname')
					->setCellValue('E1', 'Paid?')
					->setCellValue('F1', 'Attending?');
					$dataArray = array();
	 foreach($all_members as $amember) {
	 //print_r($amember['email']." ".$amember['membnum']." ".$amember['forename']." ".$amember['surname'] ." ". $amember['paidthisyear']." <br>");
	 $dataArray[] =array($amember['email'],$amember['membnum'],$amember['forename'],$amember['surname'],$amember['paidthisyear']);
	 }
	 $objPHPExcel->getActiveSheet()->fromArray($dataArray, NULL, 'A2');
	 $lastrow= count($dataArray);
	 $lastrow1= count($dataArray)+1;
	 $lastrow3= count($dataArray)+3;
	 
//	 krumo($lastrow);
//	 krumo('F'.$lastrow3);
//	  krumo('=SUM(F2:'.$lastrow1.')');
	 //Now go to end of col F  +1 and insert a sum 
	 
	
	$objPHPExcel->getActiveSheet()->setCellValue(
    'D'.$lastrow3	,     'Attendee Count');
	$objPHPExcel->getActiveSheet()->setCellValue(
    'F'.$lastrow3	,     '=subtotal(3,F2:F'.$lastrow1.')');
	$objPHPExcel->getActiveSheet()->getStyle('D'.$lastrow3.':F'.$lastrow3)
    ->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
	$objPHPExcel->getActiveSheet()->getStyle('D'.$lastrow3.':F'.$lastrow3)
    ->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
		
	$objPHPExcel->getActiveSheet()->setAutoFilter('F1:F'.$lastrow1);	
	$dlfilename= "downloads/attendance.xlsx";
	 $objWriter->save($dlfilename);
	        // send() method returns FALSE if file doesn't exist
       if (!Web::instance()->send($dlfilename,NULL,512,TRUE))                        $f3->error(404);
		print_r(" Spreadsheet Downloaded <br>");
}




function action_event_post () {
		$f3=Base::instance();
		$uselog=$f3->get('uselog');
		$api_logger = new MyLog('api.log');
		$api_logger->write( 'Entering action_event_post #114 POST='.var_export($f3->get('POST'),true),$uselog  ) ;	
		// POST contains only the id and the action to be performed
		
		$action = $f3->get('POST')['action'];
		$event = new Event($this->db);
		$api_logger->write( 'Entering action_event_post #119 with action'.var_export($action,true),$uselog  ) ;
		switch ($action){
		case 'trash':	
// mark the event as not active
			$event_id=$f3->get('POST')['id'];
			$event->load(array('event_id =?',$event_id));
			if(!$event->dry() ){	$api_logger->write( 'Entering action_event_post TRASH POST',$uselog  ) ;
			$event->active ='N';
			$event->save();	}	
		break;
		case 'untrash':	
// mark the event as active iff the event_date is in the future and the event exists
			$event_id=$f3->get('POST')['id'];
			$api_logger->write( 'Entering action_event_post UNTRASH POST',$uselog  ) ;
			$event->load(array('event_id =?',$event_id));
			if(!$event->dry() ){ //event exists, it should anyway, else do nothing
				
			$now =new DateTime(date("Y-m-d")) ; $api_logger->write( " untrash #136",$uselog  );
			$db_date = new DateTime($event->event_date);
			$api_logger->write( 'IN action_event_post UNTRASH POST db date'.var_export($db_date,true),$uselog  ) ;
			$api_logger->write( 'IN action_event_post UNTRASH POST $now'.var_export($now,true),$uselog  ) ;
			if($db_date >= $now ){ 
				$api_logger->write( 'IN action_event_post UNTRASH POST future date',$uselog  ) ;
			// date is not in past then update active to Y
				$event->active ='Y';
				$event->save();	}	
				}				
		break;
		case 'delete':	
			$event_id=$f3->get('POST')['id'];
			$event->load(array('event_id =?',$event_id));
			if(!$event->dry() ){ //event exists, it should anyway, else do nothing
			$event_trail = array();
			$event->copyto('event_trail');
			$api_logger->write( 'IN action_event_post DELETE POST event_trail = '.var_export($f3->get('event_trail'),true),$uselog  ) ;
			$event_archive=  new EventArchive($this->db);
			$event_archive->add('event_trail');
				$event->erase();
			}
		break;
		
		case 'daily1':
		//******* Run daily job

		//******* The take POST parameter as a json array of arrays 
		//******* Each array in the form 
		//******* 'event_info'=> array('event_id','event_name','event_date','event_type','event_contact_email','event_limit'
		$daily_array = json_decode($f3->get('POST')['daily'],true);
	//	$api_logger->write( 'IN action_event_post Daily Job  = '.var_export($f3->get('POST'),true),$uselog  ) ;
	//	$api_logger->write( 'IN action_event_post Daily Array  = '.var_export($daily_array,true),$uselog  ) ;
		$this->do_daily1($daily_array);
		break;
		
		}


	//	return($f3->get('BODY'));
}
/**********************   First change all past events to active ='N'  *********
***********************   Then go through each still active event received and update with any info received *******
***********************   Each received event is in the future (inc today) ***********************************/
function do_daily1($daily_array) {
		require_once 'krumo/class.krumo.php'; 
		$changes_ary =array('adds'=>0,'updates'=>0,'deactivates'=>0);
		$f3=Base::instance();
		$uselog=$f3->get('uselog');
		$api_logger = new MyLog('api.log');
		$api_logger->write( 'Entering do_daily1 #185 =',$uselog  ) ;	

		$event = new Event($this->db);	
		$past_events = $event->past();
	//	krumo("an event = ".var_export($past_events,true));
		//		$api_logger->write( 'do_daily1 #190 '.var_export($past_events,true),$uselog  ); 
		foreach($past_events as $an_event) {
	//		krumo("an event = ".var_export($an_event,true));
	//		krumo("an event ID = ".var_export($an_event->event_id	,true));
		$an_event->active='N';
		$an_event->save();
		$changes_ary['deactivates']++;
		}
		/*************  Now go through the received array  $daily_array  **/
		$api_logger->write( 'do_daily1 #198 '.var_export($daily_array,true),$uselog  );
		foreach($daily_array as $an_event) {
		
			$event_id = $an_event['event_id'];
			$an_event['event_type']= explode("pods",$an_event['event_type'])[1] ;
						//
			$an_event['active']='Y';	
	$api_logger->write( 'do_daily1 #204 '.var_export($an_event,true),$uselog  );			
			$resp=$this->add_event($an_event);
			//update the tally of changes
				switch ($resp) {
				case 'add':
				$changes_ary['adds']++;
				break;			
				case 'update':
				$changes_ary['updates']++;
				break;			
				case 'deactivate':
				$changes_ary['deactivates']++;
				break;
			}
			
			
			$api_logger->write( 'do_daily1 #209 '.var_export($resp,true),$uselog  );
		}
		// now send email to webmaster on results
		$this->email_daily_results($changes_ary);
}
function email_daily_results ($changes_ary) {
	
			$f3=Base::instance();
		$uselog=$f3->get('uselog');
		$api_logger = new MyLog('api.log');
		//require_once	'vendor/swiftmailer/swiftmailer/lib/swift_required.php';
	$text = "Daily Attendance System Cron Job";
	$subject = 'Daily Attendance System Cron Job';
	$from='attendance@u3a.world';
	$to = 'laurie@u3a.international';
	//$bcclist =	$options->find("optionname='emailbcc'");	
	$docdir = $f3->get('BASE')."docs/";
	$letters=parse_ini_file($docdir.'letters.ini',true); 
	if (!$letters)	
		{$email_logger->write( "In email letters Failed parse_ini\n");
		return print("Failed parse_ini\n");
		}
		// Use PHP Mail instead of Swift
	/********$transport = Swift_SmtpTransport::newInstance('mail.u3a.world', 25);
	$transport->setUsername('laurie@u3a.international');
	$transport->setPassword('Leyisnot70');
	$swift = Swift_Mailer::newInstance($transport);
	$message = new Swift_Message($subject);            *****/
	$ht0= $letters['header']['css'];  krumo($ht0);
	$cid =$letters['daily_cron_events']['letter']; $api_logger->write( "In email_daily_results sending #251 ".var_export($cid,true), $uselog);
	$now =date("Y-m-d") ;
	$cid3 = str_replace("*|today|*",$now,$cid);
	$cid4 = str_replace("*|deactivates|*",$changes_ary['deactivates'],$cid3);	
	$cid5 = str_replace("*|updates|*",$changes_ary['updates'],$cid4);	
	$cid = str_replace("*|adds|*",$changes_ary['adds'],$cid5);	
	$htp1 = $letters['pstyle']['p1'];  // get the replacement inline css for <p1> tags					
	$htp2 = $letters['pstyle']['p2'];
	$cid=str_replace("<p1>",$htp1,$cid);					
	$cid=str_replace("<p2>",$htp2,$cid);
	$html = 	$ht0.$cid;
	/*** $message->setFrom($from);
	$message->setBody($html, 'text/html');
	$message->setTo($to);
	$api_logger->write( "In joiner_email email adding Bcc as " . var_export($bcc,true), $uselog);
	$message->setBcc($bcc);

	$message->addPart($text, 'text/plain');
$api_logger->write( "In email_daily_results sending #268 ".var_export($message,true), $uselog);
if ($recipients = $swift->send($message, $failures)) {
$api_logger->write( "In email_daily_results succesfully sent ", $uselog);
//$email_logger->write( "In joiner_email email ".$html, $uselog);
 return 0;
} else {
	$api_logger->write( "In email_daily_results UNsuccesfully sent with error ".print_r($failures,true), $uselog);
	$api_logger->write( "In email_daily_results UNsuccesfully sent with recipients".print_r($recipients,true), $uselog);

 echo "There was an error:\n";
 return print_r($failures);
 
}
**/
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: '.$from. "\r\n".
   'Reply-To: webmaster@u3a.international' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
	$api_logger->write( "In email_daily_results sending #288 ".var_export($html,true), $uselog);
	mail($to, $subject, $html, $headers);
}

/********** when sent to the bin  mark the event as inactive BODY just contains the ID ***/
function trash_event_post () {
		$f3=Base::instance();
		$uselog=$f3->get('uselog');
		$api_logger = new MyLog('api.log');
		$api_logger->write( 'Entering trash_event_post #116 BODY='.var_export($f3->get('BODY'),true),$uselog  ) ;	
		$api_logger->write( 'Entering trash_event_post #116 POST='.var_export($f3->get('POST'),true),$uselog  ) ;	
		// BODY contains only the id
		$id=$f3->get('POST')['id'];
		//$id=$f3->get('BODY');
		$event = new Event($this->db);
		$event->trash($id);
		return($f3->get('BODY'));
}

		
/**************  add_event($event_info)           *
*********** Checks if the event details are already present, if not create it
****  If the date has changed for that event_id AND the previous date was in the past it's likely that it is a new event produced by an edit of a previous event 
***** In that case assume it is a new event entry, change the old one by moving the event and attached attendee entries to the archived status 
***** and change the active field to 'N'
*********   ********/
function add_event_post () {
		$f3=Base::instance();
		$uselog=$f3->get('uselog');
		$api_logger = new MyLog('api.log');
		$api_logger->write( 'Entering add_event_post #120 POST='.var_export($f3->get('POST'),true),$uselog  ) ;	
		
		$event_info = json_decode($f3->get('BODY'),true)['event_info'];
		$api_logger->write( 'Entering add_event_post #240 BODY decoded='.var_export($event_info,true),$uselog  ) ;	
		$api_logger->write( 'Entering add_event_post #241 ID='.var_export($event_info['event_id'],true),$uselog  ) ;
		
		$resp=$this->add_event($event_info);
	//echo "From add_event_post with result = ".$resp;
}
/****  add an event  return 'add' if an event added or 'changed' if it is changed ***/
function add_event($event_body) {
	$f3=Base::instance();
		$uselog=$f3->get('uselog');
		$api_logger = new MyLog('api.log');
		$api_logger->write( 'Entering add_event #253 POST='.var_export($event_body,true),$uselog  ) ;	
	if ($event_body !=NULL) {//krumo(" Parameter received ");
	}
	else{// Get POST params as its an api call not a local call

	}
	//$event_info=json_decode($event_body_json,true)['event_info'];
	$event_info= $event_body;
	//krumo($event_info_json);
	//krumo($event_info);
	//krumo($event_info['event_id']);
	
		//$uselog=$f3->get('uselog');
	$event = new Event($this->db);
	/****   Populate the event property with the pre-existing event, if it exists then examine if it has any major changes, including date    */
	$existing_event =$event->exists($event_info);
	
	if (!$event->exists($event_info)){ 	$api_logger->write( " add_event #270",$uselog  ) ; 
	if(!$event->add($event_info)) { //krumo("Failed on add#267");
	return false;		}
else  {	//krumo("added brand new event #268");
return 'add'; }
	
	}
	else 
	{/** existing active event so compare **/
		$now =new DateTime(date("Y-m-d")) ; $api_logger->write( " add_event #279",$uselog  );
		$sent_date = new DateTime($event_info['event_date']);
		$db_date = new DateTime($event->event_date);
		$diff = $db_date->diff($sent_date)->format("%r%a");
		//krumo($diff);
		//krumo($event->event_date);
		//krumo("db date before now ");//krumo($event->event_date < $now );
		//krumo($sent_date);
		//krumo($now);
		//krumo($sent_date > $now );
		if($db_date < $now ) { /** db date in the past  then  deactivate **/
					$api_logger->write( " add_event #290 db date =".$event->event_date,$uselog  );//Now deactivate entry 
					$event->active ='N';
					$event->save(); 
					if ($sent_date <$now)  {/** sent date also in the past update entry and return **/
					$event_info['active']='N';
						if(!$event->add($event_info)) { //krumo("Failed on add ");
						return false;		}
						return 'deactivate';	}
						else    /***  db date in past sent date in future create new entry  ***/
						{ $event->reset(); $api_logger->write( " add_event #299",$uselog  );
						//krumo("adding new event with date in the future");
							if(!$event->add($event_info)) { //krumo("Failed on add ");
							return false;		}
							return 'update';
						}
					}
		else   /***  db date in future update entry  ***/
					{ 		$api_logger->write( 'add_event #307 POST='.var_export($event->event_current_count,true),$uselog  ) ;	

						$event_info['event_current_count'] =$event->event_current_count;
					if(!$event->add($event_info)) {// krumo("Failed on add ");
					return false;		}	
					return update;
					}

	}

}
/*************************************************************************************************************
******************  Called via http from Wordpress 										************
******************  responds with a message indicating if the names are added 			************
******************  and also the number that have been  Confirmed or Waitlisted			************
******************  the message format from add_attendees is an array 'ok' true or false, 'confirmed' a count, 'waitlisted' a count 	************
******************	This can handle either policy whether to wait all or only a part of a request ************
**************************************************************************************************************/
function addattend() {
		$f3=Base::instance();
		$uselog=$f3->get('uselog');
	$api_logger = new MyLog('api.log');
	$options =	new Option($this->db); 
	$api_logger->write( " #388 in addattend BODY= ".var_export($f3->get('BODY'),true),$uselog);
	$api_logger->write( " #389 in addattend POST= ".var_export($f3->get('POST'),true),$uselog);
	$this->u3ayear = $f3->get('SESSION.u3ayear');
		if($this->u3ayear =='') {$options->initu3ayear();		
		$this->u3ayear = $f3->get('SESSION.u3ayear');}
	$body=json_decode($f3->get('BODY'),true);
	//krumo($body);
	$api_logger->write( " 388 in addattend body decoded = ".var_export($body,true),$uselog);
	$event_info = $body['event_info'];
	$event_info['active']='Y';
	$api_logger->write( " #391 in addattend body decoded  = ".var_export($body,true),$uselog);
	$event_ok = $this->add_event($event_info);  // add the event if it doesn't exist
		if (!$event_ok) { /******** For some reason the event doesn't exist and can't be added  *****/
	$api_logger->write( "ERROR #394 in addattend **** EVENT Cant be added ****= ".var_export($event_info,true),$uselog);
		return "ERROR Event Cant be added";
	}
	
	$persons = $body['persons'];
	$comment = $body['comment'];
	
	$attendees_ok= $this->add_attendees($persons, $comment,$event_info);
	//krumo($attendees_ok);
	/************ Add people_count to the event record	event_current_count ***************/
	$api_logger->write( ' #402 in addattend with attendees_ok response = '.var_export($attendees_ok,true),$uselog);
//return($attendees_ok['response']); return;
		$event_id = $event_info['event_id'];
		if(!$attendees_ok['ok']) return $attendees_ok['response'];
		switch ($attendees_ok['response']){ 
		case 'Booked': 
		case 'Waitlisted':
		return $attendees_ok['response'];
		break;
	
		//$event
		break;
		default:
		return "Attendees Added with response ".var_export( $attendees_ok['response'],true);
		}
}
/***********************************************************************************************
*************	before adding check if the number of persons would take the event over its limit
*************	if so make sure the return message  says "Request in Waitlist" ****************
*************	Called internally from addattend which has decoded the message into an array, and as a test point  ****
******************  the message format ex add_attendees is an array 'ok' true or false, 'confirmed' a count, 'waitlisted' a count 	************

************************************************************************************************/

function add_attendees($attendees,$comment_ary, $event_info) {
	$f3=Base::instance();
	$uselog=$f3->get('uselog');
		require_once 'krumo/class.krumo.php'; 
	$api_logger = new MyLog('api.log');
	$api_logger->write( 'Entering add_attendees #420 attendees ='.var_export($attendees,true),$uselog  );	
	$comment = $comment_ary['comment']; $api_logger->write( 'Entering add_attendees #414 comment ='.var_export($comment,true),$uselog  );	
	$attendee_responses = array();
	$requester_email = $attendees[0]['email'];
	$api_logger->write( 'In add_attendees #424 attendees ='.var_export($attendees,true),$uselog  );	
	$requester_id=0;
	$event = new Event($this->db);
	$event->load(array('event_id =?',$event_info['event_id']));
	//krumo($event->event_id);
	$event_limit=$event->event_limit;
	$event_current_count =$event->event_current_count;
	$number_of_attendees= count($attendees);
	$return_message = 'Attendees added OK';
	//	krumo('attendees = ');krumo($attendees);krumo('event full = '.$event->event_full);
	if ( $event->event_full or (($event_limit >0) and (0 > ($event_limit - $event_current_count)))) {$request_over_limit = true ;
//	krumo('request over limit '.$request_over_limit);
	}
	
	
	foreach($attendees as $akey=>$person) {
		if($akey ==0) 	$person['requester'] = true;
		//krumo($person);
		/** add with the person details together with the event id and event date as keys  ***/
		/**** check that the person hasn't already been added for that event, if it has just not add again but remember that and email it ***/
			$attendee = new Attendee($this->db);
			$person['email'] = $requester_email;
			$api_logger->write( 'Adding attendees #435 with requester_id '.$requester_id,$uselog  );$api_logger->write( 'Adding attendees #424 with akey'.$akey,$uselog  );
		//die();
		$resp =$attendee->add($person,$comment,$event_info,$requester_id,$request_over_limit);
	//	krumo($resp);
			$api_logger->write( 'Adding attendees #437 with response '.var_export($resp,true),$uselog  );	
			// $resp[0] is of the form Updated, Existed, Added
			$attendee_responses[] =array('name'=>$person['name'],'response'=>$resp[0], 'id'=>$resp[1],'request_status'=>$resp[2]);
				if($akey ==0) { 
				//its the requester, the first person so remember the id that has been returned for  any other persons to be inserted into the attendees entries
				$requester_id = $resp[1];

				}

			}
/***********************************************************************************************************
**********	 At this point we have received an array of array responses 'name' & 'response' ****************
**********	 The overall status of the request would be either 'Booked' or 'Waitlisted'		****************	
**********	 	
			//***********  Now generate email response to adding names ***/
			$resp_text ='';
			$people_count=0;$waiting_count=0;
		foreach($attendee_responses as $akey=>$a_response) {
		//	krumo($a_response['response']);
			switch($a_response['response']) {
				case 'existed': // the case where there is no comment change
				case 'updated': // the case where the comment is updated
			 //was already in the database for this event get the attendee request_status
			if($a_response['request_status'] =='Booked')  	$people_count++;
			else $waiting_count++;
			 
			$resp_text .= $a_response['name']." was already in the system as attending this event<br>\n";
			break;
			case 'added':
			$event_current_count++;
				if($a_response['request_status'] =='Booked')	{
							
					$people_count++;
					}
				else{ // add to waiting count 	
				
					$waiting_count++;
					}
			
			$resp_text .= $a_response['name']." has been added to the list to attend this event<br>\n";	
			break;
			default:
			krumo(' Hit default #490 with case '.var_export($a_response,true).' in person # '.$akey);
			}
				
		}	
/***********  Now update the event_current_count in the event table **************/
	$event->load(array('event_id =?',$event_info['event_id']));
//	krumo($event_current_count);krumo($event->event_limit);krumo($event->event_full);
	$event->event_current_count =$event_current_count;
	//krumo($event->event_limit - $event_current_count) ;
	if(($event->event_limit - $event_current_count )<0	) $event->event_full = true;
//	krumo($event->event_full);
	$event->save();
	//krumo($waiting_count);
	$api_logger->write( $people_count.' attendees and waitlisted '.$waiting_count.' with email text  '.$resp_text,$uselog  );
	$resp=array('ok'=>true, 'response'=>'Booked','added'=> $people_count, 'waitlisted'=>$waiting_count);
	if($waiting_count >0) $resp['response']='Waitlisted';
	
	return $resp;
}

function get_event_info(){
	$f3=Base::instance();
	$uselog=$f3->get('uselog');
	$api_logger = new MyLog('api.log');
	$api_logger->write( 'Entering get_event_info with id = '.var_export($f3->get('PARAMS.id'),true),$uselog  );	
 /**** Get event details & pass back to wordpress in an array format
 
'event_type' 'event_limit' 'event_current_count'
***************************/
	$event= new Event($this->db);
	$event->load(array('event_id =?',$f3->get('PARAMS.id')));
	$api_logger->write( 'get_event_info #531 with event_type  = '.$event->event_type,$uselog  );	
	//$api_logger->write( 'get_event_info #532 with event_type sub  = '.explode("pods",$event->event_type)[1] ,$uselog  );	
	$event_info = array('event_type'=>$event->event_type , 
		'event_limit'=> $event->event_limit, 'event_current_count'=>$event->event_current_count);
	$api_logger->write( 'get_event_info #535 with event_info  = '.var_export($event_info,true),$uselog  );	
	$api_logger->write( 'get_event_info #536 with event_info encoded  = '.json_encode($event_info),$uselog  );	
	echo json_encode($event_info);
	
}
function event_grid() {
//require_once 'krumo/class.krumo.php'; 	
	$f3=Base::instance();
	$event = new Event($this->db);		
/******$json1= '{   "total": "xxx", 
  "page": "yyy", 
  "records": "zzz",
  "rows" : [
    {"id" :"1", "cell" :["cell11", "cell12", "cell13"]},
    {"id" :"2", "cell":["cell21", "cell22", "cell23"]}  ]}';	
//krumo( $json1);
//echo '<br>';
 //krumo(json_decode($json1,true));
$json2= '{   "totalpages" : 1, 
  "currpage" : 1,
  "totalrecords" : 84,
  "eventdata" : [    {"id" : "1","event_name":"cell11", "event_date" :"cell12", "event_contact_email" :"cell13"},
    {"id" : "2","event_name":"cell21", "event_date" :"cell22", "event_contact_email" :"cell23"} ]}';
  //krumo($json2);
 // echo '<br>';
 // krumo(json_decode($json2,true));
 //$this->event->load(array('event_id=?',$this->event_info['event_id']));
 
 //krumo($this->event->event_name);
 *******/
 $event_count= $event->count(array('active = "Y"'));
//	krumo($event_count);
$events=$event->find(array(	'active = "Y"'), array('order'=>'event_current_count DESC , event_date ASC'));

$event_array = array('totalpages'=>1,'currpage'=>1,'totalrecords'=>$event_count,'eventdata'=>array());
foreach ($events as $eventnum=>$anevent) {
	//krumo($event);
		$event_array['eventdata'][] = array('id'=>$anevent->event_id,'event_name'=>$anevent->event_name,'event_date'=>$anevent->event_date,'event_contact_email'=>$anevent-> event_contact_email,
			'event_limit'=>$anevent->event_limit,'event_current_count'=>$anevent->event_current_count,'event_full'=>$anevent->event_full);
	//	$event_array['eventdata'][] = array('id'=>$event->id,'cell'=>array($event->event_name,$event->event_date,$event-> event_contact_email));
		
		}
	//krumo($event_array);	
//	krumo(json_encode($event_array));
	echo json_encode($event_array);
}
function attendee_grid() {
//require_once 'krumo/class.krumo.php'; 	
	$f3=Base::instance();
	$uselog=$f3->get('uselog');
	$attendee_logger = new MyLog('attendee.log');
	$attendee_logger->write('in fn attendee_grid #598 PARAMS= '.var_export($f3->get('PARAMS'),true),$uselog);
	$attendee = new Attendee($this->db);		
 	$event_id = $f3->get('PARAMS.eventid');
	$attendee_logger->write('in fn attendee_grid #601 PARAMS.eventid = '.var_export($f3->get('PARAMS.eventid'),true),$uselog);

 $attendee_count= $attendee->count(array('event_id=?',$event_id));
//	krumo($event_count);
$attendees=$attendee->find(array('event_id=?',$event_id));

$attendee_array = array('totalpages'=>1,'currpage'=>1,'totalrecords'=>$attendee_count,'attendeedata'=>array());
foreach ($attendees as $attendeenum=>$anattendee) {
	//krumo($event);
		$attendee_array['attendeedata'][] = array('id'=>$anattendee->id,'name'=>$anattendee->name,'membnum'=>$anattendee->membnum,'member_paid'=>$anattendee-> member_paid,
			'member_guest'=>$anattendee->member_guest,'requester_email'=>$anattendee->requester_email,'request_status'=>$anattendee->request_status,
			'request_comment'=>$anattendee->request_comment,'created_at'=>$anattendee->created_at);
	//	$event_array['eventdata'][] = array('id'=>$event->id,'cell'=>array($event->event_name,$event->event_date,$event-> event_contact_email));
		
		}
	//krumo($event_array);	
//	krumo(json_encode($event_array));
	echo json_encode($attendee_array);
}
function fiddle(){
	require_once 'krumo/class.krumo.php'; 
	$empl = '{	"rows":[
		{"emp_id":"10","name":"Albert","salary":"1000.00","boss_id":null,"level":0,"isLeaf":"false","loaded":"true","expanded":"true"},
		{"emp_id":"11","name":"Bert","salary":"900.00","boss_id":"10","level":1,"isLeaf":"true","loaded":"true","expanded":"true"},
		{"emp_id":"12","name":"Chuck","salary":"900.00","boss_id":"10","level":1,"isLeaf":"false","loaded":"true","expanded":"true"},
		{"emp_id":"13","name":"Donna","salary":"800.00","boss_id":"12","level":2,"isLeaf":"true","loaded":"true","expanded":"true"},
		{"emp_id":"14","name":"Eddie","salary":"700.00","boss_id":"12","level":2,"isLeaf":"true","loaded":"true","expanded":"true"},
		{"emp_id":"15","name":"Fred","salary":"600.00","boss_id":"12","level":2,"isLeaf":"true","loaded":"true","expanded":"true"}
	],
	"total":6,
	"page":1
	}';
	krumo($empl);
	echo '<br>';
	krumo(json_decode($empl,true));	
	$emp2 = '{	"rows":[
		{"id":"10","name":"Albert","member_guest":"M","requester_id":null,"level":0,"isLeaf":"false","loaded":"true","expanded":"true"},
		{"id":"11","name":"Bert","member_guest":"G","requester_id":"10","level":1,"isLeaf":"true","loaded":"true","expanded":"true"},
		{"id":"12","name":"Chuck","member_guest":"G","requester_id":"10","level":1,"isLeaf":"false","loaded":"true","expanded":"true"},
		{"id":"13","name":"Donna","member_guest":"M","requester_id":"12","level":0,"isLeaf":"true","loaded":"true","expanded":"true"},
		{"id":"14","name":"Eddie","member_guest":"G","requester_id":"13","level":1,"isLeaf":"true","loaded":"true","expanded":"true"},
		{"id":"15","name":"Fred","member_guest":"G","requester_id":"13","level":1,"isLeaf":"true","loaded":"true","expanded":"true"}
	],
	"total":6,
	"page":1
	}';	
	echo '<br>';
	krumo(json_decode($emp2,true));	
	$attendee = new Attendee($this->db);		
	krumo($attendee->get_tree(99999));
}
function data_json2() {
/***		$emp2 = '{	"rows":[
		{"id":"10","name":"Albert","member_guest":"M","requester_id":null,"level":0,"isLeaf":"false","loaded":"true","expanded":"true"},
		{"id":"11","name":"Bert","member_guest":"G","requester_id":"10","level":1,"isLeaf":"true","loaded":"true","expanded":"true"},
		{"id":"12","name":"Chuck","member_guest":"G","requester_id":"10","level":1,"isLeaf":"true","loaded":"true","expanded":"true"},
		{"id":"13","name":"Donna","member_guest":"M","requester_id":null,"level":0,"isLeaf":"false","loaded":"true","expanded":"true"},
		{"id":"14","name":"Eddie","member_guest":"G","requester_id":"13","level":1,"isLeaf":"true","loaded":"true","expanded":"true"},
		{"id":"15","name":"Fred","member_guest":"G","requester_id":"13","level":1,"isLeaf":"true","loaded":"true","expanded":"true"}
	],
	"total":6,
	"page":1
	}';  ***/
	 $f3=$this->f3;
	$uselog=$f3->get('uselog');
	$attendee_logger = new MyLog('attendee.log');
	$attendee_logger->write('in fn data_json2 #667 PARAMS= '.var_export($emp2,true),$uselog);
	$event_id = $f3->get('PARAMS.eventid');
$attendee = new Attendee($this->db);	
$att2 =$attendee->get_tree($event_id) 	;
$attendee_array = array('pages'=>1,'currpage'=>1,'total'=>count($att2),'rows'=>array());
foreach ($att2 as $anattendee) {
	//krumo($event);
		$attendee_array['rows'][] = array('id'=>$anattendee->id,'name'=>$anattendee->name,'membnum'=>$anattendee->membnum,'member_paid'=>$anattendee-> member_paid,
			'member_guest'=>$anattendee->member_guest,'requester_id'=>$anattendee->requester_id,
			'requester_email'=>$anattendee->requester_email,'request_status'=>$anattendee->request_status,
			'request_comment'=>$anattendee->request_comment,'created_at'=>$anattendee->created_at, 'level'=>$anattendee->level, 'isLeaf'=>$anattendee->isLeaf,"loaded"=>"true","expanded"=>"true");
	//	$event_array['eventdata'][] = array('id'=>$event->id,'cell'=>array($event->event_name,$event->event_date,$event-> event_contact_email));
		
		}
		//	$attendee_logger->write('in fn data_json2 #679 PARAMS= '.var_export($attendee_array,true),$uselog);
				$attendee_logger->write('in fn data_json2 #680 PARAMS= '.var_export(json_encode($attendee_array),true),$uselog);
		echo json_encode($attendee_array);
}
function data_json() {
	echo '{	"rows":[
		{"emp_id":"10","name":"Albert","salary":"1000.00","boss_id":null,"level":0,"isLeaf":"false","loaded":"true","expanded":"true"},
		{"emp_id":"11","name":"Bert","salary":"900.00","boss_id":"10","level":1,"isLeaf":"true","loaded":"true","expanded":"true"},
		{"emp_id":"12","name":"Chuck","salary":"900.00","boss_id":"10","level":1,"isLeaf":"false","loaded":"true","expanded":"true"},
		{"emp_id":"13","name":"Donna","salary":"800.00","boss_id":"12","level":2,"isLeaf":"true","loaded":"true","expanded":"true"},
		{"emp_id":"14","name":"Eddie","salary":"700.00","boss_id":"12","level":2,"isLeaf":"true","loaded":"true","expanded":"true"},
		{"emp_id":"15","name":"Fred","salary":"600.00","boss_id":"12","level":2,"isLeaf":"true","loaded":"true","expanded":"true"}
	],
	"total":6,
	"page":1
	}';
}
function xml_events() {
		 
	 $f3=$this->f3;
		 $members =	new Event($this->db);
	 $uselog=$f3->get('uselog');
	
	 $attendee_logger = new MyLog('attendee.log');
	$attendee_logger->write('in fn events #559 u3ayear='.$f3->get('SESSION.u3ayear'),$uselog);
	 $f3->set('page_head','User List');
$attendee_logger->write('in fn events #561 GET._search='.$f3->get('GET._search'),$uselog);
if ($f3->get('GET._search')=='true'){ 
// set up filters
$filters = $f3->get('GET.filters');
$attendee_logger->write('in fn events filters= '.$filters,$uselog);

$where = "";
 
$attendee_logger->write('in fn events where= '.$where,$uselog);
	$f3->set('SESSION.lastseen',time()); 
	/**********************  Now get the resulting xml via SWL using this where selection ******/
	$whh =	$this->getresult_where($where);
	
	$attendee_logger->write('in fn events #574where result = '.$whh."\n");
echo $whh;
	}
	else {
//$u3ayear = $f3->get('SESSION.u3ayear');
$attendee_logger->write("in fn events where result #579 " ,$uselog);
$res1 =$this->getresult_where("where active='Y'");
$attendee_logger->write("in fn events where result #581 , res1=".var_export($res1,true) ,$uselog);
echo $res1;
} 
}
function getresult_where( $where_to_use){

 $f3=$this->f3;
  $attendee_logger = new MyLog('attendee.log');
$uselog=$f3->get('uselog');
  $attendee_logger->write('in getresult_where #590 $where_to_use = '.$where_to_use,$uselog);
header("Content-type: text/xml;charset=utf-8");
 $page = $_GET['page']; 
  
	$sidx = $_GET['sidx']; 
	$sord = $_GET['sord']; 
	 $attendee_logger->write('in getresult_where #596 $sidx = '.$sidx.' $sord= '.$sord,$uselog);
	$extrasort = ', event_date ASC';
 //$fred = $f3->get('db_user');
	$db = mysqli_connect('localhost', $f3->get('db_user'),  $f3->get('db_pass'),$f3->get('db_name')) or die("Connection Error: " . mysql_error()); 
$attendee_logger->write('in getresult_where #600 ',$uselog);
 // calculate the number of rows for the query. We need this for paging the result 
	$result = mysqli_query($db,"SELECT COUNT(*) AS count FROM events ".$where_to_use); 
	$attendee_logger->write('in getresult_where #603 '.var_export($result,true),$uselog);
	$row = mysqli_fetch_array($result,MYSQL_ASSOC); 
	$count = $row['count']; 
	$limit = $count;  // temporary force all rows
//  $limit = $_GET['rows'];  // temporary comment out  to force all rows need this if non-local grid, i.e. loadOnce=false

// calculate the total pages for the query 
	if( $count > 0 && $limit > 0) { 
				$total_pages = ceil($count/$limit); 
	} else { 
              $total_pages = 0; 
	} 

// if for some reasons the requested page is greater than the total 
// set the requested page to total page 
	if ($page > $total_pages) $page=$total_pages;
 
// calculate the starting position of the rows 
	$start = $limit*$page - $limit;
 
// if for some reasons start position is negative set it to 0 
// typical case is that the user type 0 for the requested page 
	if($start <0) $start = 0; 
 

 // the actual query for the grid data 
 // Fetch extra columns to allow for the icons columns in the events grid
// $SQL = "SELECT id,surname ,forename,membnum ,phone,mobile,email,membtype,location,paidthisyear,amtpaidthisyear,feewhere,fyear,u3ayear,3,datejoined FROM members  ".$where_to_use." ORDER BY $sidx $sord ". $extrasort. " LIMIT $start , $limit"; 
 $SQL = "SELECT id,event_name,event_date ,event_contact_email,event_limit,event_current_count,event_full,3 FROM events  ".$where_to_use." ORDER BY $sidx $sord ". $extrasort. " LIMIT $start , $limit"; 

 
 
 $attendee_logger->write('in getresult_where SQL = '. $SQL."\n",$uselog);
 $result = mysqli_query( $db,$SQL ) or die("Couldn't execute query.".mysql_error()); 
$s = "<?xml version='1.0' encoding='utf-8'?>";
$s .=  "<rows>";
$s .= "<page>".$page."</page>";
$s .= "<total>".$total_pages."</total>";
$s .= "<records>".$count."</records>";

$s .= '<userdata name="forename">Total LastFY</userdata>';   # name = target column's name
$s .= '<userdata name="phone">'.$amt_total_lastfy.'</userdata>'; 
$s .= '<userdata name="email">Total ThisFY</userdata>';   # name = target column's name
$s .= '<userdata name="location">'.$amt_total_thisfy.'</userdata>'; 
   
 
// be sure to put text data in CDATA
/*
while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
    $s .= "<row id='". $row['id']."'>";  
	
    $s .= "<cell>". $row['surname']."</cell>"; 
	$s .= "<cell>". $row['forename']."</cell>";
	$s .= "<cell>". $row['membnum']."</cell>"; 	
   // $s .= "<cell>". $row['mobile']."</cell>";
	//$s .= "<cell>". $row['phone']."</cell>";
	//$s .= "<cell>". $row['email']."</cell>";
    $s .= "</row>";
}  
*/
 while($row = mysqli_fetch_array($result,MYSQL_ASSOC)) {
   foreach($row as $key => $value)
      {if ($key=='id') {$s .= "<row id='". $value."'>";  }
	  else
	  { $s .= "<cell>". "$value"."</cell>";
	  
	  }
         //$key holds the table column name
       
   
   }
$s .= "</row>"; 
	
	} 
$s .= "</rows>"; 

//$attendee_logger->write('in getresult_where #415 result = '.$s,$uselog);
	return $s;

	
}
}
 