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
	$this->u3ayear = $f3->get('SESSION.u3ayear');
		if($this->u3ayear =='') {$options->initu3ayear();		
		$this->u3ayear = $f3->get('SESSION.u3ayear');}
	$body=json_decode($f3->get('BODY'),true);
	$event_info = $body['eventinfo'];
	$api_logger->write( " #188 in addattend = ".var_export($body,true),$uselog);
	$event_ok = $this->add_event($event_info);  // add the event if it doesn't exist
	if (!$event_ok) { /******** For some reason the event doesn't exist and can't be added  *****/
	$api_logger->write( "ERROR #191 in addattend **** EVENT Cant be added ****= ".var_export($event_info,true),$uselog);
		return false;
	}
	$persons = $body['persons'];
	$comments = $body['comment'];
	$attendees_ok= $this->add_attendees($persons, $comment);
	
	return true;
	
}

function action_event_post () {
		$f3=Base::instance();
		$uselog=$f3->get('uselog');
		$api_logger = new MyLog('api.log');
		$api_logger->write( 'Entering action_event_post #114 POST='.var_export($f3->get('POST'),true),$uselog  ) ;	
		// POST contains only the id and the action to be performed
		$event_id=$f3->get('POST')['id'];
		$action = $f3->get('POST')['action'];
		$event = new Event($this->db);
		$api_logger->write( 'Entering action_event_post #119 with action'.var_export($action,true),$uselog  ) ;
		switch ($action){
		case 'trash':	
// mark the event as not active
	
			$event->load(array('event_id =?',$event_id));
			if(!$event->dry() ){	$api_logger->write( 'Entering action_event_post TRASH POST',$uselog  ) ;
			$event->active ='N';
			$event->save();	}	
		break;
		case 'untrash':	
// mark the event as active iff the event_date is in the future and the event exists
			$api_logger->write( 'Entering action_event_post UNTRASH POST',$uselog  ) ;
			$event->load(array('event_id =?',$event_id));
			if(!$event->dry() ){ //event exists, it should anyway, else do nothing
				
			$now =new DateTime(date("Y-m-d")) ; $api_logger->write( " add_event #153",$uselog  );
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
		}

		$event->trash($id);
		return($f3->get('BODY'));
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
		$api_logger->write( 'Entering add_event_post #123 BODY decoded='.var_export($event_info,true),$uselog  ) ;	
		$api_logger->write( 'Entering add_event_post #124 ID='.var_export($event_info['event_id'],true),$uselog  ) ;	
		$resp=$this->add_event($f3->get('BODY'));
	echo "From add_event_post with result = ".$resp;
}
function add_event($event_body_json) {
	$f3=Base::instance();
		$uselog=$f3->get('uselog');
		$api_logger = new MyLog('api.log');
		$api_logger->write( 'Entering add_event #132 POST='.var_export(json_decode($event_body_json,true)['event_info'],true),$uselog  ) ;	
	if ($event_body_json !=NULL) {krumo(" Parameter received ");}
	else{// Get POST params as its an api call not a local call

	}
	$event_info=json_decode($event_body_json,true)['event_info'];
	krumo($event_info_json);
	krumo($event_info);
	krumo($event_info['event_id']);
	
		//$uselog=$f3->get('uselog');
	$event = new Event($this->db);
	/****   Populate the event property with the pre-existing event, if it exists then examine if it has any major changes, including date    */
	$existing_event =$event->exists($event_info);
	
	if (!$event->exists($event_info)){ 	$api_logger->write( " add_event #148",$uselog  ) ; if(!$event->add($event_info)) { krumo("Failed on add ");return false;		}
else  {	krumo("added brand new event");return true; }
	
	}
	else 
	{/** existing active event so compare **/
		$now =new DateTime(date("Y-m-d")) ; $api_logger->write( " add_event #153",$uselog  );
		$sent_date = new DateTime($event_info['event_date']);
		$db_date = new DateTime($event->event_date);
		$diff = $db_date->diff($sent_date)->format("%r%a");
		krumo($diff);
		krumo($event->event_date);
		krumo("db date before now ");krumo($event->event_date < $now );
		krumo($sent_date);
		krumo($now);
		krumo($sent_date > $now );
		if($db_date < $now ) { /** db date in the past  then  deactivate **/
					$api_logger->write( " add_event #164 db date =".$event->event_date,$uselog  );//Now deactivate entry 
					$event->active ='N';
					$event->save(); 
					if ($sent_date <$now)  {/** sent date also in the past update entry and return **/
					$event_info['active']='N';
						if(!$event->add($event_info)) { krumo("Failed on add ");return false;		}
						return true;	}
						else    /***  db date in past sent date in future create new entry  ***/
						{ $event->reset(); $api_logger->write( " add_event #172",$uselog  );
						krumo("adding new event with date in the future");
							if(!$event->add($event_info)) { krumo("Failed on add ");return false;		}
							return true;
						}
					}
		else   /***  db date in future update entry  ***/
					{ 		$api_logger->write( 'add_event #179 POST='.var_export($event->event_current_count,true),$uselog  ) ;	

						$event_info['event_current_count'] =$event->event_current_count;
					if(!$event->add($event_info)) { krumo("Failed on add ");return false;		}	
					return true;
					}
								
		
		
		
	}

	
	
	
}
function add_attendees($attendees,$comment) {
	
	
	return true;
}
function testattend2() { /******  various test functions **/
		$f3=Base::instance();
		$uselog=$f3->get('uselog');
	$api_logger = new MyLog('api.log');
		$api_logger->write( 'Entering testattend2',$uselog  );	
	$web = Web::instance();
$url = 'http://testattend.u3a.world/addeventpost';
$event_info =array('event_id'=>1001);
	$body_all=array('event_info'=>$event_info);
	$body_all_json = json_encode($body_all);
$options = array(
    'method'  => 'POST',
   // 'content' => http_build_query($body_all_json));
    'content' => $body_all_json);
$resp =  Web::instance()->request($url, $options);
	$api_logger->write( 'testattend2 resp = '.var_export($resp,true),$uselog  );	
krumo($resp);
}
function testattend() { /******  various test functions **/
	$f3=Base::instance();
	require_once 'krumo/class.krumo.php'; 
	$event = new Event($this->db);
	
	/*********  Now test change of date to 23rd *****/
		$event_info =array('event_id'=> 2574, 'event_name' =>'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
		'event_date' => '2016-11-29','event_type'=>'event','event_limit'=>55, 'event_current_count'=>11	,'event_contact_email'=>'laurie29.lyates@gmail.com','active'=>'Y');
krumo("Event ".$event_info['event_date']);
$event_info_json = json_encode($event_info);
	$event->reset();	
	$this->add_event($event_info_json);
	//return 0;
	
	
	
$event->load(array('event_id =?',2574));
$event->erase();
	$event_info =array('event_id'=> 2574, 'event_name' =>'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
		'event_date' => '2016-10-09','event_type'=>'event','event_limit'=>55, 'event_current_count'=>9,'event_contact_email'=>'laurie9.lyates@gmail.com','active'=>'Y');
	$event_info_json = json_encode($event_info);
	krumo("Brand new Event ".$event_info['event_date']);
	$this->add_event($event_info_json);
$test1 = $event->load();

/*********  Now test change of date to 10th *****/
		$event_info =array('event_id'=> 2574, 'event_name' =>'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
		'event_date' => '2016-10-10','event_type'=>'event','event_limit'=>55, 'event_current_count'=>10	,'event_contact_email'=>'laurie10.lyates@gmail.com','active'=>'Y');
			$event->reset();
			$event_info_json = json_encode($event_info);
			krumo(" Event ".$event_info['event_date']);
			$this->add_event($event_info_json);
	//return 0;		
			
	/*********  Now test change of date to 23rd *****/
		$event_info =array('event_id'=> 2574, 'event_name' =>'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
		'event_date' => '2016-10-29','event_type'=>'event','event_limit'=>55, 'event_current_count'=>29	,'event_contact_email'=>'laurie29.lyates@gmail.com','active'=>'Y');
krumo("Event ".$event_info['event_date']);
	$event->reset();
$event_info_json = json_encode($event_info);	
	$this->add_event($event_info_json);
	$test2 = $event->load();


}
function testattend3(){ //test of remote delete
$url = 'http://testattend.u3a.world/event';
$options = array(
    'method'  => 'POST',
      'content' =>array('id'=>3005,'action'=>'delete'));

	$resp =  Web::instance()->request($url, $options);
}
}
	