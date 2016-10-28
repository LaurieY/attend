<?php

class ApiController extends Controller {
		private $u3ayear;
	function beforeroute() {
	}
	function afterroute() {
// allows ajax calls to work
	}


/**** Gets a membership number with first and surnames and returns the status - Paid/Unpaid/Non-existent together with recorded names
************/

/**** Gets a membership number with first and surnames and returns the status - Paid/Unpaid/Non-existent together with recorded names
************/
/**************
*********
an array containing 2 arrays, 1st entry is array with number of names and event information, 2nd entry is an array containing the names an info.
The outer array post vvalue has the name 'body'
******/


/*******************************

********* addattend  adds a structure to an attendance list  ***********
********* receives data in the form of a json string constining
********* array event_info ,   array persons , string comments

********* return true or false

*********************************/
public function addattend() {
		$f3=Base::instance();
		$uselog=$f3->get('uselog');
	$api_logger = new MyLog('api.log');
	$options =	new Option($this->db);
	$this->u3ayear = $f3->get('SESSION.u3ayear');
		if($this->u3ayear =='') {$options->initu3ayear();		
		$this->u3ayear = $f3->get('SESSION.u3ayear');}
	$body=json_decode($f3->get('BODY'),true);
	$api_logger->write( " #188 in addattend = ".var_export($body,true),$uselog);
	$event_ok = $this->add_event($event_info);  // add the event if it doesn't exist
	if (!$event_ok) { /******** For some reason the event doesn't exist and can't be added  *****/
	$api_logger->write( "ERROR #191 in addattend **** EVENT Cant be added ****= ".var_export($event_info,true),$uselog);
		return false;
	}
	
	return true;
	
}
/**************  add_event($event_info)           *
*********** Checks if the evnt details are already present, if not create it
*********   ********/
function add_event($event_info) {
	
	
	return true;
}



function first_last($s) {
    /* assume first name is followed by a whitespace character. take everything after for last. middle initial will be returned as part of last. */
    $pos = strpos($s,' ');
    if ($pos == FALSE) { // if space is not found... call if first name
        return array($s,''); 
    }
    $first = substr($s, 0 , $pos);
    $last = substr($s,$pos + 1);    
    return array($first,$last);
}

}