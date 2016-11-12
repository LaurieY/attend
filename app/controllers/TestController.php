<?php
class TestController extends Controller {
		private $u3ayear;
		private $attend;
		private $persons;
		private $attendee1;
		private $attendee2;
		private $attendee3;
		private $comment_ary;
		private $event_info;
		private $event;
		private $attendee;
	function afterroute() { // allows simple non views activities
	}
	function beforeroute() {
//$f3->set('message','');
	$f3=$this->f3;
	$test_logger = new MyLog('Test.log');
	$test_logger->write( 'Entering CheckController beforeroute URI= '.$f3->get('URI'  ) );
	$options= new Option($this->db);
	$this->u3ayear = $f3->get('SESSION.u3ayear');
	if($this->u3ayear =='') {$options->initu3ayear();		
	$this->u3ayear = $f3->get('SESSION.u3ayear');}
	$this->attend = new AttendanceAjaxController();
	$this->attendee1 = array('name'=>'Laurie Yates','number'=>180,'email'=>'laurie@lyates.com','member_guest'=>'M');
	$this->attendee2 = array('name'=>'Junior 1 Yates','number'=>NULL,'email'=>NULL,'member_guest'=>'G');
	$this->attendee3 = array('name'=>'Junior 2 Yates','number'=>NULL,'email'=>NULL,'member_guest'=>'G');
	
	$this->comment_ary=array('comment'=>'Ello');	
	$this->event_info  = array('number_of_names'=>1,'event_id'=>99999,'event_name'=>'Test Event','event_date'=> '2016-12-07',
	'event_contact_email'=>'laurie@lyates.com','event_type'=>'event','event_limit'=>2,'active'=>'Y');
	$this->event = new Event($this->db);
	$this->attendee = new Attendee($this->db);	
	}
function unitattend() {
	$f3=Base::instance();
	$testnum = $f3->get('PARAMS.test');
	$test = new Test;
	//krumo($test);
	if ($testnum <>0) {
		$this->unitattend1($test,$testnum);
		//krumo($test); //LEY
		}
	else {
	
		for($i=1;$i<5	;$i++) {
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
function unitattend1(&$test,$testnum) {  //return test object
	$f3=Base::instance();
	//$testnum = $f3->get('PARAMS.test');
	require_once 'krumo/class.krumo.php'; 
	// Set up
	//$test=new Test;
	//krumo($test);

	// This is where the tests begin
/*		$test->expect(
			is_null($f3->get('ERROR')),
			'No errors expected at this point'
		);*/
/************  test can add an attendee to an existing event************/
	$this->persons =array();
	$this->persons[] = $this->attendee1; 
	
	 switch($testnum) {
		case 1:	
		$this->db->exec('delete from events where event_id = :id', array(':id'=>$this->event_info['event_id']));	
		$this->db->exec('delete from events where event_id = :id', array(':id'=>$this->event_info['event_id']));	
		$this->db->exec('delete from events where event_id = :id', array(':id'=>0));	//Needed to allow it to be repeatable as I don't create the event in a direct call to add_attendees
		//$this->event->add($event_info);
		krumo($this->event_info);
		$attend_resp =$this->attend->add_attendees($this->persons,$this->comment_ary, $this->event_info);
		//check the attendee added
		$test->expect(
			$attend_resp!= array(),
			'Test 1:- Not Blank Array from add_attendees'
		);
		//check the event wasn't added
		$event_resp = $this->event->exists($this->event_info);
		$test->expect(
			!$event_resp,
			'Test 1:- False for event now exists from  add_attendees'
		);		

		$this->event->add($this->event_info);
		$event_resp = $this->event->exists($this->event_info);
		$test->expect(
			$event_resp,
			'Test 1:- true for event now exists from  add an event'
		);		
		
		$this->db->exec('delete from events where event_id = :id', array(':id'=>0));	//Needed to allow it to be repeatable as I don't create the event in a direct call to add_attendees		
		break;
		case 2:
		$this->event->add($this->event_info);
		$this->db->exec('delete from events where event_id = :id', array(':id'=>$this->event_info['event_id']));	
		$this->db->exec('delete from attendees where event_id = :id', array(':id'=>$this->event_info['event_id']));	
		$this->db->exec('delete from events where event_id = :id', array(':id'=>0));	//Needed to allow it to be repeatable as I don't create the event in a direct call to add_attendees

		$attend_resp =$this->attend->add_attendees($this->persons,$this->comment_ary, $this->event_info);
		krumo($attend_resp);
			$test->expect(
			$attend_resp['ok'],
			'Test 2:- ok=> true in array from add_attendees for 1 person '
		);

			$test->expect(
			($attend_resp['added']==1),
			'Test 2:- added =1 in array from add_attendees for 1 person received '.$attend_resp['added']
		);		
		$this->db->exec('delete from events where event_id = :id', array(':id'=>0));	//Needed to allow it to be repeatable as I don't create the event in a direct call to add_attendees
		break;
		case 3:
		/*******  now try via a POST to addattend *********/
		krumo(array(':id'=>$this->event_info['event_id']));
		$this->db->exec('delete from events where event_id = :id', array(':id'=>$this->event_info['event_id']));	
		$this->db->exec('delete from attendees where event_id = :id', array(':id'=>$this->event_info['event_id']));	
		//break;
		$body_all= array('event_info'=>$this->event_info, 'persons'=>$this->persons, 'comment'=>$this->comment);
		$body_all_json = json_encode($body_all);//krumo($body_all_json);
		//$attendees_post = json_encode();
		$test_resp=$f3->mock('POST /addattend',NULL,NULL, $body_all_json); 
		krumo($test_resp);
			$test->expect($test_resp== 'Booked',
			'Test 3:- POST to addattend expect "Booked" ,received '.var_export($test_resp,true) 
		)	;	
		break;
		/****** clear relevant attendees set limit to 2********/
		
		case 4: //  Ensure event limit is 2
		$this->event_info['event_limit'] =1;	
		$event_id=$this->event_info['event_id'];
		
		$this->db->exec('delete from events where event_id = :id', array(':id'=>$this->event_info['event_id']));	
		$this->db->exec('delete from attendees where event_id = :id', array(':id'=>$this->event_info['event_id']));	
		$this->event->load(array('event_id=?',$this->event_info['event_id']));
		/*********  The event has been deleted it should be dry ******/
			$test->expect($this->event->dry(), 
			'Test 4a:- delete Event check event_id not exists, received '.$this->event->dry() );	
		$test_resp =$this->event->add($this->event_info);
		//krumo($test_resp);
		/*********  The event has been created it should be NOT dry ******/		
		$this->event->load(array('event_id=?',$this->event_info['event_id']));
			$test->expect(!$this->event->dry(), 
			'Test 4b:- delete Event check event_id does exists, received '.$this->event->dry() );	
		/*********  The event has been created it should have the correct event_id ******/		
			$test->expect($test_resp->event_id == $this->event_info['event_id'],
			'Test 4c:- Create Event check event_id, received '.var_export($test_resp->event_id,true) );	
		/*********  The event has been created try to add 3 people should return Waiting ******/	
			$this->persons =array();
			$this->persons[] = $this->attendee1; 
			$this->persons[] = $this->attendee2; 
			$this->persons[] = $this->attendee3; 
			$this->event_info['number_of_names'] =3;
		$body_all= array('event_info'=>$this->event_info, 'persons'=>$this->persons, 'comment'=>$this->comment);
		$body_all_json = json_encode($body_all);//krumo($body_all_json);
		//$attendees_post = json_encode();
		$test_resp=$f3->mock('POST /addattend',NULL,NULL, $body_all_json); 
	//	krumo($test_resp);
			$test->expect($test_resp== 'Booked',
			'Test 4d:- POST to addattend expect "Booked" ,received '.var_export($test_resp,true) 
		)	;	
		$this->event->load(array('event_id=?',$this->event_info['event_id']));
			$test->expect($this->event->event_current_count== 3,
			'Test 4e:- POST to addattend expect event_current_count ==3 ,received '.$this->event->event_current_count
		)	;		
		$this->attendee->load(array('request_status = "Waitlisted" and event_id=?',$this->event_info['event_id']));
			$test->expect($this->attendee->dry(),
			'Test 4f:- POST to addattend expect attendees as Waitlisted to be  dry,received '.$this->attendee->dry()
		)	;
		
//		$attendees_count= $this->attendee->count(array('request_status = "Waitlisted" and event_id=?',$this->event_info['event_id']));
		$attendees_count= $this->attendee->count();
		//	krumo($attendees_count);
		
			$test->expect($attendees_count==3,
			'Test 4g:- POST to addattend expect only 3 attendees, received '.$attendees_count
		)	;		

		$this->attendee->load(array('request_status = "Booked" and event_id=?',$this->event_info['event_id']));
			$test->expect(!$this->attendee->dry(),
			'Test 4h:- POST to addattend expect attendees as Booked to be NOT dry,received '.$this->attendee->dry()
		)	;
	
		// Now try to add again check not duplicated attendees
	//	krumo($body_all_json);
		$test_resp=$f3->mock('POST /addattend',NULL,NULL, $body_all_json); 
		$attendees_count= $this->attendee->count();
			$test->expect($attendees_count==3,
			'Test 4i:- POST to addattend expect only 3 attendees, received '.$attendees_count
		)	;
		$test->expect($test_resp== 'Waitlisted',
			'Test 4j:- POST to addattend expect "Waitlisted" ,received '.var_export($test_resp,true) 
		)	;	
	//Now set attendess to be just the 1st name, expect it to be NOT booked as per policy-1-B
	
		$this->persons =array();
		$this->persons[] = $this->attendee1; 
		$this->event_info['number_of_names'] =1;	
		$body_all= array('event_info'=>$this->event_info, 'persons'=>$this->persons, 'comment'=>$this->comment);
		$body_all_json = json_encode($body_all);//krumo($body_all_json);		
	
	$test_resp=$f3->mock('POST /addattend',NULL,NULL, $body_all_json); 
	//	krumo($test_resp);
			$test->expect($test_resp!= 'Booked',
			'Test 4k:- POST to addattend expect NOT  "Booked" ,received '.var_export($test_resp,true) 
		)	;	
		break;
		//$test->expect($test_resp[1]=='Attendees added OK'
	 }
	// krumo($test->results());
	 //return;
	return $test;

/***$f3->set('QUIET',TRUE);  // do not show output of the active route
$f3->mock('POST /addattend');  // set the route that f3 will run
// mocking test here
$f3->set('QUIET',FALSE); // allow test results to be shown later
$f3->clear('ERROR');  // clear any errors		***/
	// Display the results; not MVC but let's keep it simple

}

private function clear_attendees($event_id) {
	//$attendee->load(array('event_id =?',$event_id)));
	$this->db->exec('delete from attendees where event_id = :id', array(':id'=>$event_id));
	
}
private function clear_event($event_id) {
	//$attendee->load(array('event_id =?',$event_id)));
	$this->db->exec('delete from events where event_id = :id', array(':id'=>$event_id));
	
}





function unithello() {
	//$f3=require(__DIR__.'/lib/base.php'); // path to f3
$f3=Base::instance();
// Set up
$test=new Test;
include('hello.php');

// This is where the tests begin
$test->expect(
    is_callable('hello'),
    'hello() is a function'
);

// Another test
$hello=hello();
$test->expect(
    !empty($hello),
    'Something was returned'
);

// This test should succeed
$test->expect(
    is_string($hello),
    'Return value is a string'
);

// This test is bound to fail
$test->expect(
    strlen($hello)==13,
    'String length is 13'
);

// Display the results; not MVC but let's keep it simple
foreach ($test->results() as $result) {
    echo $result['text'].'<br>';
    if ($result['status'])
        echo 'Pass';
    else
        echo 'Fail ('.$result['source'].')';
    echo '<br>';
}
	
}

/***************** Unused TESTS *******************/
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
