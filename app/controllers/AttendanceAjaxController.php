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
public function attendance_list() {
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


public function addattend() {
		$f3=Base::instance();
		$uselog=$f3->get('uselog');
	$api_logger = new MyLog('api.log');
	$options =	new Option($this->db); 
	$api_logger->write( " #91 in addattend BODY= ".var_export($f3->get('BODY'),true),$uselog);
	$api_logger->write( " #92 in addattend POST= ".var_export($f3->get('POST'),true),$uselog);
	$this->u3ayear = $f3->get('SESSION.u3ayear');
		if($this->u3ayear =='') {$options->initu3ayear();		
		$this->u3ayear = $f3->get('SESSION.u3ayear');}
	$body=json_decode($f3->get('BODY'),true);
	$api_logger->write( " #97 in addattend body decoded = ".var_export($body,true),$uselog);
	$event_info = $body['event_info'];
	$event_info['active']='Y';
	$api_logger->write( " #100 in addattend body decoded  = ".var_export($body,true),$uselog);
	$event_ok = $this->add_event($event_info);  // add the event if it doesn't exist
		if (!$event_ok) { /******** For some reason the event doesn't exist and can't be added  *****/
	$api_logger->write( "ERROR #99 in addattend **** EVENT Cant be added ****= ".var_export($event_info,true),$uselog);
		echo "ERROR Event Cant be added";
	}
	
	$persons = $body['persons'];
	$comment = $body['comment'];
	$attendees_ok= $this->add_attendees($persons, $comment,$event_info);
	/************ Add people_count to the event record	event_current_count ***************/
	
		$event_id = $event_info['event_id'];
		if(!$attendees_ok['ok']) echo $attendees_ok['response'];
		switch ($attendees_ok['response']){ 
		case 'Attendees added OK': echo $attendees_ok['response'];
		break;
		case 'Attendees added to waiting list':
		echo $attendees_ok['added']." Added and ".$attendees_ok['waitlisted']." Waitlisted";
		//$event
		break;
		default:
		echo "Attendees Added with response ".var_export( $attendees_ok['response'],true);
		}
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
function add_attendees($attendees,$comment_ary, $event_info) {
	$f3=Base::instance();
	$uselog=$f3->get('uselog');
	$api_logger = new MyLog('api.log');
	$api_logger->write( 'Entering add_attendees #413 attendees ='.var_export($attendees,true),$uselog  );	
	$comment = $comment_ary['comment']; $api_logger->write( 'Entering add_attendees #414 comment ='.var_export($comment,true),$uselog  );	
	$attendee_responses = array();
	$requester_email = $attendees[0]['email'];
	//$requester_comment = $comment;
	$requester_id=0;
	$event = new Event($this->db);
	$event->load(array('event_id =?',$event_info['event_id']));
	$event_limit=$event->event_limit;
	$event_current_count =$event->event_current_count;
	foreach($attendees as $akey=>$person) {
		/** add with the person details together with the event id and event date as keys  ***/
		/**** check that the person hasn't already been added for that event, if it has just not add aggain but remember that and email it ***/
			$attendee = new Attendee($this->db);
			$person['email'] = $requester_email;
			$api_logger->write( 'Adding attendees #424 with requester_id '.$requester_id,$uselog  );$api_logger->write( 'Adding attendees #424 with akey'.$akey,$uselog  );
			$resp =$attendee->add($person,$comment,$event_info,$requester_id);
				$api_logger->write( 'Adding attendees #426 with response '.var_export($resp,true),$uselog  );	
				$attendee_responses[] =array('name'=>$person['name'],'response'=>$resp[0]);
				if($akey ==0) { 
				//its the requester, the first person so remember the id that has been returned for  any other persons to be inserted into the attendees entries
				$requester_id = $resp[1];
					}

			}
			//***********  Now generate email response to adding names ***/
			$resp_text ='';
			$people_count=0;$waiting_count=0;
		foreach($attendee_responses as $a_response) {
			switch($a_response['response']) {
				case 'exists':
				case 'updated':
			 //was already in the database for this event
			$resp_text .= $a_response['name']." was already in the system as attending this event<br>\n";
			break;
			case 'added':
				if($event_current_count -$event_limit > 0)	{
					$event_current_count++;
					$people_count++;
					}
				else{ // add to waiting list 	
					$event_current_count++;
					$waiting_count++;
					}
			$people_count++;
			$resp_text .= $a_response['name']." has been added to the list to attend this event<br>\n";	
			break;
			}
				
		}	

		
		$api_logger->write( 'Added '.$people_count.' attendees and waitlisted '.$waiting_count.' with email text  '.$resp_text,$uselog  );
	
	return array('ok'=>true, 'response'=>'Attendees added OK','added'=> $people_count, 'waitlisted'=>$waiting_count);
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
	$api_logger->write( 'get_event_info #471 with event_type  = '.$event->event_type,$uselog  );	
	//$api_logger->write( 'get_event_info #472 with event_type sub  = '.explode("pods",$event->event_type)[1] ,$uselog  );	
	$event_info = array('event_type'=>$event->event_type , 
		'event_limit'=> $event->event_limit, 'event_current_count'=>$event->event_current_count);
	echo json_encode($event_info);
	
}

function testattend2() { /******  various test functions **/
		$f3=Base::instance();
		$uselog=$f3->get('uselog');
	$api_logger = new MyLog('api.log');
		$api_logger->write( 'Entering testattend2',$uselog  );
	require_once 'krumo/class.krumo.php'; 		
	$web = Web::instance();
$url = 'http://testattend.u3a.world/addeventpost';
//$event_info =array('event_id'=>1001);
		$event_info =array('event_id'=> 2574, 'event_name' =>'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
		'event_date' => '2016-11-29','event_type'=>'event','event_limit'=>55, 'event_current_count'=>11	,'event_contact_email'=>'laurie29.lyates@gmail.com','active'=>'Y');
krumo($event_info);
	$body_all=array('event_info'=>$event_info);
	$body_all_json = json_encode($body_all);
$options = array(
    'method'  => 'POST',
   // 'content' => http_build_query($body_all_json));
    'content' => $body_all_json);
$resp =  Web::instance()->request($url, $options);
	$api_logger->write( 'testattend2 resp #463 = '.var_export($resp,true),$uselog  );	
krumo($resp);
}
function testattend() { /******  various test functions **/
	$f3=Base::instance();
	require_once 'krumo/class.krumo.php'; 
	$event = new Event($this->db);
	
	/*********  Now test change of date to 23rd *****/
		$event_info =array('event_id'=> 2574, 'event_name' =>'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
		'event_date' => '2016-11-29','event_type'=>'event','event_limit'=>55, 'event_current_count'=>11	,'event_contact_email'=>'laurie29.lyates@gmail.com','active'=>'Y');
//krumo("Event ".$event_info['event_date']);
$event_info_json = json_encode($event_info);
	$event->reset();	
	$this->add_event($event_info_json);
	//return 0;
	
	
	
$event->load(array('event_id =?',2574));
$event->erase();
	$event_info =array('event_id'=> 2574, 'event_name' =>'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
		'event_date' => '2016-10-09','event_type'=>'event','event_limit'=>55, 'event_current_count'=>9,'event_contact_email'=>'laurie9.lyates@gmail.com','active'=>'Y');
	$event_info_json = json_encode($event_info);
	//krumo("Brand new Event ".$event_info['event_date']);
	$this->add_event($event_info_json);
$test1 = $event->load();

/*********  Now test change of date to 10th *****/
		$event_info =array('event_id'=> 2574, 'event_name' =>'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
		'event_date' => '2016-10-10','event_type'=>'event','event_limit'=>55, 'event_current_count'=>10	,'event_contact_email'=>'laurie10.lyates@gmail.com','active'=>'Y');
			$event->reset();
			$event_info_json = json_encode($event_info);
			//krumo(" Event ".$event_info['event_date']);
			$this->add_event($event_info_json);
	//return 0;		
			
	/*********  Now test change of date to 23rd *****/
		$event_info =array('event_id'=> 2574, 'event_name' =>'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
		'event_date' => '2016-10-29','event_type'=>'event','event_limit'=>55, 'event_current_count'=>29	,'event_contact_email'=>'laurie29.lyates@gmail.com','active'=>'Y');
//krumo("Event ".$event_info['event_date']);
	$event->reset();
$event_info_json = json_encode($event_info);	
	$this->add_event($event_info_json);
	$test2 = $event->load();


}
function testattend3(){ //test of remote delete
$url = 'http://testattend.u3a.world/event';
$options = array(
    'method'  => 'POST',
      'content' =>array('id'=>3002,'action'=>'trash'));

	$resp =  Web::instance()->request($url, $options);
	//krumo($resp);
}
}
	