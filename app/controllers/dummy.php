<?php
namespace{
class TestController extends Controller {
		private $u3ayear;
		private $attend;
		private $persons;
		private $attendee1;
		private $attendee2;
		private $attendee3;
		private $comment_ary;
		private $eventInfo;
		private $event;
		private $attendee;
		private $baseUrl;
	    public function afterroute() { // allows simple non views activities
	}
	    public function beforeroute() {
//$f3->set('message','');
	$f3=$this->f3;
	$this->baseUrl = 'http://testattend.u3a.world';
	$test_logger = new MyLog('Test.log');
	$test_logger->write( 'Entering TestController beforeroute URI= '.$f3->get('URI'  ) );
	$options= new Option($this->db);
	$this->u3ayear = $f3->get('SESSION.u3ayear');
	if($this->u3ayear =='') {$options->initu3ayear();		
	$this->u3ayear = $f3->get('SESSION.u3ayear');}
    echo $f3->get('AUTOLOAD');
	$this->attend = new AttendanceAjaxController();
	$this->attendee1 = array('name' => 'Laurie Yates','number'=>180,'email' => 'laurie@lyates.com','memberGuest' => 'M');
	$this->attendee2 = array('name' => 'Junior 1 Yates','number'=>NULL,'email'=>NULL,'memberGuest' => 'G');
	$this->attendee3 = array('name' => 'Junior 2 Yates','number'=>NULL,'email'=>NULL,'memberGuest' => 'G');
	$this->attendee4 = array('name' => 'Susan Yates','number'=>181,'email' => 'laurie@lyates.com','memberGuest' => 'M');
	$this->attendee5 = array('name' => 'Susan Elizabeth Yates','number'=>1081,'email' => 'laurie@lyates.com','memberGuest' => 'M');
	
	$this->comment_ary=array('comment' => 'Ello');	
	$this->eventInfo  = array('number_of_names'=>1,'eventId'=>99999,'event_name' => 'Test Event','eventDate'=> '2016-12-07',
	'event_contact_email' => 'laurie@lyates.com','event_type' => 'event','event_limit'=>2,'active' => 'Y');
	$this->event = new \Event($this->db);
	$this->attendee = new Attendee($this->db);	
	}
    public function unitattend() {
	$f3 = Base::instance();

	$testnum = $f3->get('PARAMS.test');
	$test = new Test;
	//krumo($test);
	if ($testnum <>0) {
		$this->unitattend1($test,$testnum);
		//krumo($test); //LEY
		}
	else {
	
		for($i=1;$i<11	;$i++) {
		$this->unitattend1($test,$i);
		//krumo($test);	
		//krumo($testnum);
		//krumo($test->results()); //LEY
		}
	}
	foreach ($test->results() as $key=> $result) {
    echo $result['text'].' :- <b>';
    if ($result['status'])
        echo 'Pass';
    else
        echo 'Fail ('.$result['source'].')';
    echo '<p></b>';
}		
	}
    public function unitattend1(&$test,$testnum) {  //return test object
	$f3 = Base::instance();
	//$testnum = $f3->get('PARAMS.test');
	require_once 'krumo/class.krumo.php'; 
		$uselog = $f3->get('uselog');
	$api_logger = new MyLog('api.log');
	$api_logger->write( 'Entering unitattend1 #69',$uselog  );	
	// Set up
	//$test=new Test;
	//krumo($test);

	// This is where the tests begin
/*		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);*/
/*
 *
 * test can add an attendee to an existing event
 *
 */
	$this->persons =array();
	$this->persons[] = $this->attendee1; 
	
	 switch($testnum) {
		case 1:	
		$this->db->exec('delete from events where eventId = :id', array(':id' => $this->eventInfo['eventId']));	
		$this->db->exec('delete from events where eventId = :id', array(':id' => $this->eventInfo['eventId']));	
		$this->db->exec('delete from events where eventId = :id', array(':id'=>0));	//Needed to allow it to be repeatable as I don't create the event in a direct call to addAttendees
		//$this->event->add($eventInfo);
	//	krumo($this->eventInfo);
		$attend_resp =$this->attend->addAttendees($this->persons,$this->comment_ary, $this->eventInfo);
		//check the attendee added
		$test->expect(
			$attend_resp!= array(),
			'Test 1a:- Not Blank Array from addAttendees'
		);
		//check the event wasn't added
		$event_resp = $this->event->exists($this->eventInfo);
		$test->expect(
			!$event_resp,
			'Test 1b:- False for event now exists from  addAttendees'
		);		

		$this->event->add($this->eventInfo);
		$event_resp = $this->event->exists($this->eventInfo);
		$test->expect(
			$event_resp,
			'Test 1c:- true for event now exists from  add an event'
		);		
		
		$this->db->exec('delete from events where eventId = :id', array(':id'=>0));	//Needed to allow it to be repeatable as I don't create the event in a direct call to addAttendees		
		break;
		case 2:
		$this->event->add($this->eventInfo);
		$this->db->exec('delete from events where eventId = :id', array(':id' => $this->eventInfo['eventId']));	
		$this->db->exec('delete from attendees where eventId = :id', array(':id' => $this->eventInfo['eventId']));	
		$this->db->exec('delete from events where eventId = :id', array(':id'=>0));	//Needed to allow it to be repeatable as I don't create the event in a direct call to addAttendees

		$attend_resp =$this->attend->addAttendees($this->persons,$this->comment_ary, $this->eventInfo);
	//	krumo($attend_resp);
			$test->expect(
			$attend_resp['ok'],
			'Test 2:- ok=> true in array from addAttendees for 1 person '
		);

			$test->expect(
			($attend_resp['added']==1),
			'Test 2:- added =1 in array from addAttendees for 1 person received '.$attend_resp['added']
		);		
		$this->db->exec('delete from events where eventId = :id', array(':id'=>0));	//Needed to allow it to be repeatable as I don't create the event in a direct call to addAttendees
		break;
		case 3:

/*
 *
 * now try via a POST to addAttend 
 *
 */
	//	krumo(array(':id' => $this->eventInfo['eventId']));
		$this->db->exec('delete from events where eventId = :id', array(':id' => $this->eventInfo['eventId']));	
		$this->db->exec('delete from attendees where eventId = :id', array(':id' => $this->eventInfo['eventId']));	
		//break;
		$body_all= array('eventInfo' => $this->eventInfo, 'persons' => $this->persons, 'comment' => $this->comment_ary);
		$body_all_json = json_encode($body_all);//krumo($body_all_json);
		//$attendees_post = json_encode();
		$testResp=$this->myiMock('addAttend',NULL,NULL, $body_all_json); 
	//	krumo($testResp);
			$test->expect($testResp== 'Booked',
			'Test 3:- POST to addAttend expect "Booked" ,received '.var_export($testResp,true) 
		)	;	
		break;
		/****** clear relevant attendees set limit to 2********/
		
		case 4: //  Ensure event limit is 2
		$this->eventInfo['event_limit'] =1;	
		$eventId=$this->eventInfo['eventId'];
		
		$this->db->exec('delete from events where eventId = :id', array(':id' => $this->eventInfo['eventId']));	
		$this->db->exec('delete from attendees where eventId = :id', array(':id' => $this->eventInfo['eventId']));	
		$this->event->load(array('eventId=?',$this->eventInfo['eventId']));
		/*********  The event has been deleted it should be dry ******/
			$test->expect($this->event->dry(), 
			'Test 4a:- delete Event check eventId not exists, received '.$this->event->dry() );	
		$testResp =$this->event->add($this->eventInfo);
		//krumo($testResp);
		/*********  The event has been created it should be NOT dry ******/		
		$this->event->load(array('eventId=?',$this->eventInfo['eventId']));
			$test->expect(!$this->event->dry(), 
			'Test 4b:- delete Event check eventId does exists, received '.$this->event->dry() );	
		/*********  The event has been created it should have the correct eventId ******/		
			$test->expect($testResp->eventId == $this->eventInfo['eventId'],
			'Test 4ca:- Create Event check eventId, received '.var_export($testResp->eventId,true) );	
			
				$test->expect(!$this->event->eventFull,
			'Test 4cb:- Create Event check eventFull false, received '.var_export($this->event->eventFull,true) );	
			
	
		/*********  The event has been created try to add 3 people should return Waiting ******/	
			$this->persons =array();
			$this->persons[] = $this->attendee1; 
			$this->persons[] = $this->attendee2; 
			$this->persons[] = $this->attendee3; 
			$this->eventInfo['number_of_names'] =3;
		$body_all= array('eventInfo' => $this->eventInfo, 'persons' => $this->persons, 'comment' => $this->comment_ary);
		$body_all_json = json_encode($body_all);//krumo($body_all_json);
		//$attendees_post = json_encode();
		$url='http://testattend.u3a.world/addAttend';
//		$testResp=$this->myiMock('addAttend',NULL,NULL, $body_all_json); 

		$options = array(    'method'  => 'POST',    'content' => $body_all_json);
//		$testResp=\Web::instance()->request($url,$options); //('POST /addAttend [sync]',NULL,NULL, $body_all_json); 
		$testResp=$this->myiMock('addAttend',NULL,NULL, $body_all_json); 
		//krumo($testResp);
			$test->expect($testResp== 'Booked',
			'Test 4d:- POST to addAttend expect "Booked" ,received '.var_export($testResp,true) 
		)	;	
		$this->event->load(array('eventId=?',$this->eventInfo['eventId']));
			$test->expect($this->event->eventCurrentCount== 3,
			'Test 4e:- POST to addAttend expect eventCurrentCount ==3 ,received '.$this->event->eventCurrentCount
		)	;	;krumo($this->event->cast());
//die(); 
		$this->attendee->load(array('requestStatus = "Waitlisted" and eventId=?',$this->eventInfo['eventId']));
			$test->expect($this->attendee->dry(),
			'Test 4f:- POST to addAttend expect attendees as Waitlisted to be  dry,received '.$this->attendee->dry()
		)	;
		
//		$attendees_count= $this->attendee->count(array('requestStatus = "Waitlisted" and eventId=?',$this->eventInfo['eventId']));
		$attendees_count= $this->attendee->count();
		//	krumo($attendees_count);
		
			$test->expect($attendees_count==3,
			'Test 4g:- POST to addAttend expect only 3 attendees, received '.$attendees_count
		)	;		

		$this->attendee->load(array('requestStatus = "Waitlisted" and eventId=?',$this->eventInfo['eventId']));
			$test->expect($this->attendee->dry(),
			'Test 4h:- POST to addAttend expect attendees as Waitlisted to be  dry,received '.$this->attendee->dry()
		)	;
		$api_logger->write( 'Entering unitattend1 4I #226',$uselog  );	
	//	krumo("4I");
		// Now try to add same attendees again check not duplicated attendees and they are added ok
	//	krumo($body_all_json);
		$testResp=$this->myiMock('addAttend',NULL,NULL, $body_all_json); 
		$attendees_count= $this->attendee->count();
			$test->expect($attendees_count==3,
			'Test 4i:- POST to addAttend expect only 3 attendees, received '.$attendees_count
		)	;
	//	krumo("4J");
			$api_logger->write( 'Entering unitattend1  4J #236',$uselog  );	
		$test->expect($testResp== 'Booked',
			'Test 4j:- POST to addAttend expect "Booked" ,received '.var_export($testResp,true) 
		)	;	
	//Now set attendess to be just the 1st name, expect it to be NOT booked as per policy-1-B

		$this->persons =array();
		$this->persons[] = $this->attendee4; 
		$this->eventInfo['number_of_names'] =2;	
		$body_all= array('eventInfo' => $this->eventInfo, 'persons' => $this->persons, 'comment' => $this->comment_ary);
		$body_all_json = json_encode($body_all);//krumo($body_all_json);		
	
		$testResp=$this->myiMock('addAttend',NULL,NULL, $body_all_json); 
	//	krumo($testResp);
			$test->expect($testResp!= 'Booked',
			'Test 4k:- POST to addAttend expect NOT  "Booked" ,received '.var_export($testResp,true) 
		)	;	
		// Now try adding #1 and #4 should get #1 booked #4 waitlisted
		$this->persons =array();
		$this->persons[] = $this->attendee1; 
		$this->persons[] = $this->attendee5; 
		$this->eventInfo['number_of_names'] =2;	

		$body_all= array('eventInfo' => $this->eventInfo, 'persons' => $this->persons, 'comment' => $this->comment_ary);
		$body_all_json = json_encode($body_all);//krumo($body_all_json);		
	//krumo("4L");
		$testResp=$this->myiMock('addAttend',NULL,NULL, $body_all_json); 
			$test->expect($testResp!= 'Booked',
			'Test 4l:- POST to addAttend expect NOT  "Booked" ,received '.var_export($testResp,true) 
		)	;	
		$this->event->load(array('eventId=?',$this->eventInfo['eventId']));
			$test->expect($this->event->eventCurrentCount== 5,
			'Test 4m:- POST to addAttend expect eventCurrentCount ==5 ,received '.$this->event->eventCurrentCount
		)	;	

		$waitlisted_count= $this->attendee->count(array('requestStatus = "Waitlisted" and eventId=?',$this->eventInfo['eventId']));
			$test->expect($waitlisted_count ==2,
			'Test 4n:- POST to addAttend expect count attendees as Waitlisted to be 2,received '.$waitlisted_count
		)	;
		
		break;
		case 5:
		$this->db->exec('delete from events where eventId = :id', array(':id' => $this->eventInfo['eventId']));	
		$this->db->exec('delete from attendees where eventId = :id', array(':id' => $this->eventInfo['eventId']));	
		$this->persons =array();
		$this->persons[] = $this->attendee1;
		$this->eventInfo['number_of_names'] =1;
		$this->eventInfo['event_limit'] =3;
		$testResp =$this->event->add($this->eventInfo);
		$this->comment_ary= array('comment'=>"ONE");
		$body_all= array('eventInfo' => $this->eventInfo, 'persons' => $this->persons, 'comment' => $this->comment_ary);
		$body_all_json = json_encode($body_all);		
//krumo("5A");	die();
		$testResp=$this->myiMock('addAttend',NULL,NULL, $body_all_json); 
			$test->expect($testResp == 'Booked',
			'Test 5a:- POST to addAttend expect   "Booked" ,received '.var_export($testResp,true) 
		)	;
			$this->event->load(array('eventId=?',$this->eventInfo['eventId']));
			$test->expect($this->event->eventCurrentCount== 1,
			'Test 5b:- POST to addAttend expect eventCurrentCount ==1 ,received '.$this->event->eventCurrentCount
		)	;	
		//krumo($this->event->eventId);
	//krumo("5B");	die();
		$this->attendee->load(array('eventId=?',$this->eventInfo['eventId']));
			$test->expect($this->attendee->count()== 1,
			'Test 5c:- POST to addAttend expect eventCurrentCount ==1 ,received '.$this->attendee->count()
		)	;	
	
			$test->expect($this->attendee->requestComment== 'ONE',
			'Test 5d:- POST to addAttend expect requestComment == ONE ,received '.$this->attendee->requestComment
		)	;	
		$this->persons =array();
		$this->persons[] = $this->attendee1;
		$this->persons[] = $this->attendee2;
		$this->eventInfo['number_of_names'] =2;		
		$this->comment_ary= array('comment'=>" TWO");
		$body_all= array('eventInfo' => $this->eventInfo, 'persons' => $this->persons, 'comment' => $this->comment_ary);
		$body_all_json = json_encode($body_all);		
	//krumo("5A");
		$testResp=$this->myiMock('addAttend',NULL,NULL, $body_all_json); 	
			$test->expect($testResp == 'Booked',
			'Test 5e:- POST to addAttend expect   "Booked" ,received '.var_export($testResp,true) 
		)	;
			$this->event->load(array('eventId=?',$this->eventInfo['eventId']));
			$test->expect($this->event->eventCurrentCount== 2,
			'Test 5f:- POST to addAttend expect eventCurrentCount ==2 ,received '.$this->event->eventCurrentCount
		)	;			
			$attendee_count = $this->attendee->count(array('requestStatus ="Booked" and eventId=?',$this->eventInfo['eventId']));
			$test->expect($attendee_count== 2,
			'Test 5g:- POST to addAttend expect Booked count  ==2 ,received '.$attendee_count
		)	;
			// maybe use sql eqivalent SELECT * FROM `attendees` group by `requesterId`
			$this->attendee->load(array('eventId=?',$this->eventInfo['eventId']));	
			$requesterId=$this->attendee->requesterId;
			$this->attendee->load(array('id=? ',$requesterId));	
			$test->expect($this->attendee->requestComment == 'ONE/ TWO',
			'Test 5g:- POST to addAttend expect requestComment == ONE/  TWO ,received '.$this->attendee->requestComment
		)	;	
	break;	
	case 6:
		$this->db->exec('delete from events where eventId = :id', array(':id' => $this->eventInfo['eventId']));	
		$this->db->exec('delete from attendees', array());	
		$this->persons =array();
		$this->persons[] = $this->attendee1;
		$this->persons[] = $this->attendee2;
		$this->eventInfo['number_of_names'] =2;
		$this->eventInfo['event_limit'] =1;
		$testResp =$this->event->add($this->eventInfo);
		$this->comment_ary= array('comment'=>"ONE");
		$body_all= array('eventInfo' => $this->eventInfo, 'persons' => $this->persons, 'comment' => $this->comment_ary);
		$body_all_json = json_encode($body_all);		

		$testResp=$this->myiMock('addAttend',NULL,NULL, $body_all_json); 
		$attendee_count = $this->attendee->count(array('requestStatus ="Booked" and eventId=?',$this->eventInfo['eventId']));
			$test->expect($attendee_count== 2,
			'Test 6a:- POST to addAttend expect Booked count  ==2 ,received '.$attendee_count
		)	;
		$this->persons =array();
		$this->persons[] = $this->attendee4;
		$this->persons[] = $this->attendee3;
		$body_all= array('eventInfo' => $this->eventInfo, 'persons' => $this->persons, 'comment' => $this->comment_ary);
		$body_all_json = json_encode($body_all);
		$testResp=$this->myiMock('addAttend',NULL,NULL, $body_all_json); 
		$attendee_count = $this->attendee->count(array('requestStatus ="Booked" and eventId=?',$this->eventInfo['eventId']));
			$test->expect($attendee_count== 2,
			'Test 6b:- POST to addAttend expect Booked count  ==2 ,received '.$attendee_count
		)	;
		$attendee_count = $this->attendee->count(array('requestStatus ="Waitlisted" and eventId=?',$this->eventInfo['eventId']));
			$test->expect($attendee_count== 2,
			'Test 6c:- POST to addAttend expect Waitlisted count  ==2 ,received '.$attendee_count
		)	;
		$this->event->load(array('eventId=?',$this->eventInfo['eventId']));
			$test->expect($this->event->eventCurrentCount== 4,
			'Test 6d:- POST to addAttend expect eventCurrentCount ==4 ,received '.$this->event->eventCurrentCount
		)	;	
		// increase event capacity
		$this->eventInfo['event_limit'] =4;
		$testResp =$this->event->add($this->eventInfo);
		// ensure no more attendees marked booked
		$testResp=$this->myiMock('addAttend',NULL,NULL, $body_all_json); 
		$attendee_count = $this->attendee->count(array('requestStatus ="Booked" and eventId=?',$this->eventInfo['eventId']));
			$test->expect($attendee_count== 2,
			'Test 6e:- POST to addAttend expect Booked count  ==2 ,received '.$attendee_count
		)	;
		$attendee_count = $this->attendee->count(array('requestStatus ="Waitlisted" and eventId=?',$this->eventInfo['eventId']));
			$test->expect($attendee_count== 2,
			'Test 6f:- POST to addAttend expect Waitlisted count  ==2 ,received '.$attendee_count
		)	;
		$this->event->load(array('eventId=?',$this->eventInfo['eventId']));
			$test->expect($this->event->eventCurrentCount== 4,
			'Test 6g:- POST to addAttend expect eventCurrentCount ==4 ,received '.$this->event->eventCurrentCount
		)	;
		
		$this->persons =array();
		$this->persons[] = $this->attendee5;
		$this->persons[] = $this->attendee4;
		$body_all= array('eventInfo' => $this->eventInfo, 'persons' => $this->persons, 'comment' => $this->comment_ary);
		$body_all_json = json_encode($body_all);
	//	krumo("6H");	
		$testResp=$this->myiMock('addAttend',NULL,NULL, $body_all_json); 
		$attendee_count = $this->attendee->count(array('requestStatus ="Booked" and eventId=?',$this->eventInfo['eventId']));
			$test->expect($attendee_count== 2,
			'Test 6h:- POST to addAttend expect Booked count  ==2 ,received '.$attendee_count
		)	;
		$attendee_count = $this->attendee->count(array('requestStatus ="Waitlisted" and eventId=?',$this->eventInfo['eventId']));
			$test->expect($attendee_count== 3,
			'Test 6i:- POST to addAttend expect Waitlisted count  ==3 ,received '.$attendee_count
		)	;
		$this->event->load(array('eventId=?',$this->eventInfo['eventId']));
			$test->expect($this->event->eventCurrentCount== 5,
			'Test 6j:- POST to addAttend expect eventCurrentCount ==5 ,received '.$this->event->eventCurrentCount
		)	;

	break;	
	case 7:
		$this->db->exec('delete from events where eventId = :id', array(':id' => $this->eventInfo['eventId']));	
		$this->db->exec('delete from attendees', array());	
		$this->persons =array();
		$this->persons[] = $this->attendee1;
		$this->persons[] = $this->attendee2;
		$this->eventInfo['number_of_names'] =2;
		$this->eventInfo['event_limit'] =0;
		$testResp =$this->event->add($this->eventInfo);
		$body_all= array('eventInfo' => $this->eventInfo, 'persons' => $this->persons, 'comment' => $this->comment_ary);
		$body_all_json = json_encode($body_all);		

		$testResp=$this->myiMock('addAttend',NULL,NULL, $body_all_json); 	
		$attendee_count = $this->attendee->count(array('requestStatus ="Booked" and eventId=?',$this->eventInfo['eventId']));
			$test->expect($attendee_count== 2,
			'Test 7a:- POST to addAttend expect Booked count  ==2 ,received '.$attendee_count
		)	;	
	//	krumo($testResp);
		$test->expect($testResp== 'Booked',
			'Test 7b:- POST to addAttend expect response Booked  ,received '.$testResp
		)	;	
        //krumo("7C ");krumo($this->eventInfo);  
		$this->persons[] = $this->attendee1;
		$this->persons[] = $this->attendee3;	
        
		$body_all= array('eventInfo' => $this->eventInfo, 'persons' => $this->persons, 'comment' => $this->comment_ary);
		$body_all_json = json_encode($body_all);		
		$testResp=$this->myiMock('addAttend',NULL,NULL, $body_all_json); 	
		$attendee_count = $this->attendee->count(array('requestStatus ="Booked" and eventId=?',$this->eventInfo['eventId']));
			$test->expect($attendee_count== 3,
			'Test 7c:- POST to addAttend expect Booked count  ==3 ,received '.$attendee_count
		)	;	
	//	krumo($testResp);
		$test->expect($testResp== 'Booked',
			'Test 7d:- POST to addAttend expect response Booked  ,received '.$testResp
		)	;	
		
		
	break;
	 }
	// krumo($test->results());
	 //return;
	return $test;

/***$f3->set('QUIET',TRUE);  // do not show output of the active route
$this->myiMock('addAttend');  // set the route that f3 will run
// mocking test here
$f3->set('QUIET',FALSE); // allow test results to be shown later
$f3->clear('ERROR');  // clear any errors		***/
	// Display the results; not MVC but let's keep it simple

}

private function clear_attendees($eventId) {
	//$attendee->load(array('eventId =?',$eventId)));
	$this->db->exec('delete from attendees where eventId = :id', array(':id' => $eventId));
	
}

private function clear_event($eventId) {
    $this->db->exec('delete from events where eventId = :id', array(':id' => $eventId));
    }

private function fiddle() {
require_once 'krumo/class.krumo.php'; 	
		$f3 = Base::instance();
		
$json1= '{   "total": "xxx", 
  "page": "yyy", 
  "records": "zzz",
  "rows" : [
    {"id" :"1", "cell" :["cell11", "cell12", "cell13"]},
    {"id" :"2", "cell":["cell21", "cell22", "cell23"]}  ]}';	
krumo( $json1);
echo '<br>';
 krumo(json_decode($json1,true));
$json2= '{   "totalpages" : 1, 
  "currpage" : 1,
  "totalrecords" : 84,
  "eventdata" : [    {"id" : "1","event_name":"cell11", "eventDate" :"cell12", "event_contact_email" :"cell13"},
    {"id" : "2","event_name":"cell21", "eventDate" :"cell22", "event_contact_email" :"cell23"} ]}';
  krumo($json2);
  echo '<br>';
  krumo(json_decode($json2,true));
 $this->event->load(array('eventId=?',$this->eventInfo['eventId']));
 
 krumo($this->event->event_name);
 
 $event_count= $this->event->count(array('active = "Y"'));
	krumo($event_count);
$events=$this->event->find(array(	'active = "Y"'));

$event_array = array('totalpages'=>1,'currpage'=>1,'totalrecords' => $event_count,'eventdata'=>array());
foreach ($events as $eventnum=>$event) {
	//krumo($event);
		$event_array['eventdata'][] = array('id' => $event->id,'event_name' => $event->event_name,'eventDate' => $event->eventDate,'event_contact_email' => $event-> event_contact_email,
			'event_limit' => $event->event_limit,'eventCurrentCount' => $event->eventCurrentCount,'eventFull' => $event->eventFull);
	//	$event_array['eventdata'][] = array('id' => $event->id,'cell'=>array($event->event_name,$event->eventDate,$event-> event_contact_email));
		
		}
	krumo($event_array);	
	krumo(json_encode($event_array));
}

/***************** Unused TESTS *******************/
    public function testattend2() { /******  various test     public functions **/
		$f3 = Base::instance();
		$uselog = $f3->get('uselog');
	$api_logger = new MyLog('api.log');
		$api_logger->write( 'Entering testattend2',$uselog  );
	require_once 'krumo/class.krumo.php'; 		
	$web = Web::instance();
$url = 'http://testattend.u3a.world/addeventpost';
//$eventInfo =array('eventId'=>1001);
		$eventInfo =array('eventId'=> 2574, 'event_name' =>'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
		'eventDate' => '2016-11-29','event_type' => 'event','event_limit'=>55, 'eventCurrentCount'=>11	,'event_contact_email' => 'laurie29.lyates@gmail.com','active' => 'Y');
krumo($eventInfo);
	$body_all=array('eventInfo' => $eventInfo);
	$body_all_json = json_encode($body_all);
$options = array(
    'method'  => 'POST',
   // 'content' => http_build_query($body_all_json));
    'content' => $body_all_json);
$resp =  Web::instance()->request($url, $options);
	$api_logger->write( 'testattend2 resp #463 = '.var_export($resp,true),$uselog  );	
krumo($resp);
}
    public function testattend() { /******  various test     public functions **/
	$f3 = Base::instance();
	require_once 'krumo/class.krumo.php'; 
	$event = new Event($this->db);
	
	/*********  Now test change of date to 23rd *****/
		$eventInfo =array('eventId'=> 2574, 'event_name' =>'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
		'eventDate' => '2016-11-29','event_type' => 'event','event_limit'=>55, 'eventCurrentCount'=>11	,'event_contact_email' => 'laurie29.lyates@gmail.com','active' => 'Y');
//krumo("Event ".$eventInfo['eventDate']);
$eventInfo_json = json_encode($eventInfo);
	$event->reset();	
	$this->add_event($eventInfo_json);
	//return 0;
	
	
	
$event->load(array('eventId =?',2574));
$event->erase();
	$eventInfo =array('eventId'=> 2574, 'event_name' =>'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
		'eventDate' => '2016-10-09','event_type' => 'event','event_limit'=>55, 'eventCurrentCount'=>9,'event_contact_email' => 'laurie9.lyates@gmail.com','active' => 'Y');
	$eventInfo_json = json_encode($eventInfo);
	//krumo("Brand new Event ".$eventInfo['eventDate']);
	$this->add_event($eventInfo_json);
$test1 = $event->load();

/*********  Now test change of date to 10th *****/
		$eventInfo =array('eventId'=> 2574, 'event_name' =>'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
		'eventDate' => '2016-10-10','event_type' => 'event','event_limit'=>55, 'eventCurrentCount'=>10	,'event_contact_email' => 'laurie10.lyates@gmail.com','active' => 'Y');
			$event->reset();
			$eventInfo_json = json_encode($eventInfo);
			//krumo(" Event ".$eventInfo['eventDate']);
			$this->add_event($eventInfo_json);
	//return 0;		
			
	/*********  Now test change of date to 23rd *****/
		$eventInfo =array('eventId'=> 2574, 'event_name' =>'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
		'eventDate' => '2016-10-29','event_type' => 'event','event_limit'=>55, 'eventCurrentCount'=>29	,'event_contact_email' => 'laurie29.lyates@gmail.com','active' => 'Y');
//krumo("Event ".$eventInfo['eventDate']);
	$event->reset();
$eventInfo_json = json_encode($eventInfo);	
	$this->add_event($eventInfo_json);
	$test2 = $event->load();


}
    public function testattend3(){ //test of remote delete
$url = 'http://testattend.u3a.world/event';
$options = array(
    'method'  => 'POST',
      'content' =>array('id'=>3002,'action' => 'trash'));

	$resp =  Web::instance()->request($url, $options);
	//krumo($resp);
}
    public function myiMock ($pattern, $args, $headers, $body) {
	$url=$this->baseUrl.'/'.$pattern;
	$options = array(    'method'  => 'POST',    'content' => $body);
return Web::instance()->request($url,$options)['body'];	
}

}
}
