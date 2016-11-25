<?php

class Attendee extends DB\SQL\Mapper {

    public function __construct(DB\SQL $db) {
        parent::__construct($db,'attendees');
    }
function all() {
       // $this->load();
	   
	   $fw=Base::instance();
		//var_dump($fw);// need to filter by current u3ayear
		$this->load( );
		//$this->first();
		
        return $this->query;
    }
	
function get_tree($event_id) {
	$fw=Base::instance();
//	require_once 'krumo/class.krumo.php'; 
/*************  some code to identify the number of sub attendees per requester_id
*************	might not be needed yet
***********************************
$this-$this->rcount = "count(*)";
	$r2 = $this->select('count(*) as rcount, requester_id' ,array('event_id='.$event_id),array('group'=>'requester_id'));
	krumo($this->db->log());
	foreach($r2 as $anr2) {
		if($anr2->requester_id >0) 	{	krumo('rid= '.$anr2->requester_id);		krumo('  '.($anr2->rcount)+1);}
	}
	************/
	/**$this->level="if(requester_id>0, 1,0)";
	$this->isLeaf='if(requester_id=0, "false","true")';**/
	$this->level="if(requester_id<>id, 1,0)";
	$this->isLeaf='if(requester_id=id, "false","true")';
	
	$r1=$this->find(array('event_id=?',$event_id),array('order'=>'id ASC'));
	foreach ($r1 as $anattendee) {
/**	krumo('id= '	.$anattendee->id);
	krumo(	$anattendee->name);
	krumo('requester_id= '.	$anattendee->requester_id);
	krumo('level = '.	$anattendee->level);
	krumo('isLeaf = '.	$anattendee->isLeaf);**/
		}
	//krumo($this->query);
	return $r1;
	
}
function add($attendee,$comment,$event_info,$requester_id,$request_over_limit) {
	//krumo($request_over_limit);
		$fw=Base::instance();
		// check if potentially over the limit, not actually over if the person is already booked
		// only update any comments if it already exists, never change the request status if already exists
/*		if($request_over_limit) 
			$request_status = 'Waitlisted';
		else
			$request_status = 'Booked';*/
		//krumo($attendee);	
		// check for the existence of a member already for this event, duplicate members not valid, just ignored and returned 'existed'
		// it may not be a duplicate if one person is adding multiples but is unlikley so flag it in the email
		// duplicate Guest members names have to be ignored as well, may be valid but cannot tell
		if($attendee['member_guest'] =='M') $this->load(array('member_guest = "M" and event_id =? and event_date =? and membnum =?',
			$event_info['event_id'], $event_info['event_date'],$attendee['number'] ));
		if($attendee['member_guest'] =='G') $this->load(array('member_guest = "G" and event_id =? and event_date =? and name =?' ,
			$event_info['event_id'], $event_info['event_date'],$attendee['name'] ));

		if(!$this->dry()) { // duplicate person for the event 
	//	krumo('$request_status = '.$request_status);
		//krumo('$this->request_status = '.$this->request_status);
	//	if($comment != $this->request_comment || ($this->request_status != $request_status)) {
		// the comment has changed so add the new comment OR the request status has changed 
		if($comment != $this->request_comment ) {
			// the comment has changed so add the new comment 
						if(is_null($this->request_comment)) $this->request_comment="";			
							$this->request_comment=$this->request_comment.'/'.$comment;
							//$this->request_status = $request_status;
							//krumo($this->request_status);
							$this->save();
							return(array('updated',$this->id,$this->request_status));
						}
						return array('existed',$this->id,$this->request_status); //No change so no need for a db action
					}
	//	krumo($this);
// a new entry	
// if the requester_id is -1 then this is the requester for the overall request so don't see the requester_id till after the 1st save	
//		if($requester_id<>0) {
	$this->requester_id  = $requester_id;
//	}*/
		if($request_over_limit) 
			$request_status = 'Waitlisted';
		else
			$request_status = 'Booked';
		$this->created_at=date("Y-m-d H:i:s");	
		$this->request_comment=$comment;
		$this->request_status = $request_status;
		$this->requester = $attendee['requester'];
		$this->requester_email=$attendee['email'];	// this was added by the calling function for all the attendees
		$this->name=$attendee['name'];
		$this->membnum=$attendee['number'];
		$this->member_guest=$attendee['member_guest'];
		$this->event_id=$event_info['event_id'];
		$this->event_date=$event_info['event_date'];
	//	krumo($this->requester);

		$resp = $this->save();

		if($requester_id==0) { // this was the requester entry so add its own id into the record
							$this->requester_id  =$this->id;
							// no leave it as 0 for the hierarchy search
	
							$this->save();
							}		
		return(array('added',$this->id,$this->request_status));
		
		
	}
function update_count($attendee,$event_info) {
	$fw=Base::instance();
require_once 'krumo/class.krumo.php'; 
	//$attendee['number'] ;
	$this->load(array('member_guest = "M" and requester= true and event_id =? and event_date =? and membnum =?',
			$event_info['event_id'], $event_info['event_date'],$attendee['number'] ));
//krumo($this->id);
	$requester_id = $this->id;
	$this->rcount='count(*)';
//		$r2 = $this->select('count(*) as rcount, requester_id' ,array('event_id='.$event_info['event_idz']),array('group'=>'requester_id'));
		$r2count = $this->count(array('event_id=? AND requester_id =?',$event_info['event_id'],$requester_id));
//krumo(	 $r2count);
	$this->load(array('member_guest = "M" and requester= true and event_id =? and event_date =? and membnum =?',
			$event_info['event_id'], $event_info['event_date'],$attendee['number'] ));
	$this->request_count= $r2count;
//krumo(	$this->request_count);
//krumo(	$this->id	);
	
	$this->save();
	return $this->cast();
			
}

	
	}