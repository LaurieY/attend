<?php
namespace{
class Attendee extends DB\SQL\Mapper {

    public function __construct(\DB\SQL $db) {
        parent::__construct($db,'attendees');
    }
function all() {
       // $this->load();
	   
	   $fw=\Base::instance();
		//var_dump($fw);// need to filter by current u3ayear
		$this->load( );
		//$this->first();
		
        return $this->query;
    }
	
function get_tree($eventId) {
	$fw=\Base::instance();
//	require_once 'krumo/class.krumo.php'; 
/*************  some code to identify the number of sub attendees per requesterId
*************	might not be needed yet
***********************************
$this-$this->rcount = "count(*)";
	$r2 = $this->select('count(*) as rcount, requesterId' ,array('eventId='.$eventId),array('group' => 'requesterId'));
	krumo($this->db->log());
	foreach($r2 as $anr2) {
		if($anr2->requesterId >0) 	{	krumo('rid= '.$anr2->requesterId);		krumo('  '.($anr2->rcount)+1);}
	}
	************/
	/**$this->level="if(requesterId>0, 1,0)";
	$this->isLeaf='if(requesterId=0, "false","true")';**/
	$this->level="if(requesterId<>id, 1,0)";
	$this->isLeaf='if(requesterId=id, "false","true")';
	
	$r1=$this->find(array('eventId=?',$eventId),array('order' => 'id ASC'));
	foreach ($r1 as $anattendee) {
/**	krumo('id= '	.$anattendee->id);
	krumo(	$anattendee->name);
	krumo('requesterId= '.	$anattendee->requesterId);
	krumo('level = '.	$anattendee->level);
	krumo('isLeaf = '.	$anattendee->isLeaf);**/
		}
	//krumo($this->query);
	return $r1;
	
}
function add($attendee,$comment,$event_info,$requesterId,$request_over_limit) {
	//krumo($request_over_limit);
		$fw=\Base::instance();
		// check if potentially over the limit, not actually over if the person is already booked
		// only update any comments if it already exists, never change the request status if already exists
/*		if($request_over_limit) 
			$requestStatus = 'Waitlisted';
		else
			$requestStatus = 'Booked';*/
		//krumo($attendee);	
		// check for the existence of a member already for this event, duplicate members not valid, just ignored and returned 'existed'
		// it may not be a duplicate if one person is adding multiples but is unlikley so flag it in the email
		// duplicate Guest members names have to be ignored as well, may be valid but cannot tell
		if($attendee['memberGuest'] =='M') $this->load(array('memberGuest = "M" and eventId =? and eventDate =? and membnum =?',
			$event_info['eventId'], $event_info['eventDate'],$attendee['number'] ));
		if($attendee['memberGuest'] =='G') $this->load(array('memberGuest = "G" and eventId =? and eventDate =? and name =?' ,
			$event_info['eventId'], $event_info['eventDate'],$attendee['name'] ));

		if(!$this->dry()) { // duplicate person for the event 
	//	krumo('$requestStatus = '.$requestStatus);
		//krumo('$this->requestStatus = '.$this->requestStatus);
	//	if($comment != $this->requestComment || ($this->requestStatus != $requestStatus)) {
		// the comment has changed so add the new comment OR the request status has changed 
		if($comment != $this->requestComment ) {
			// the comment has changed so add the new comment 
						if(is_null($this->requestComment)) $this->requestComment="";			
							$this->requestComment=$this->requestComment.'/'.$comment;
							//$this->requestStatus = $requestStatus;
							//krumo($this->requestStatus);
							$this->save();
							return(array('updated',$this->id,$this->requestStatus));
						}
						return array('existed',$this->id,$this->requestStatus); //No change so no need for a db action
					}
	//	krumo($this);
// a new entry	
// if the requesterId is -1 then this is the requester for the overall request so don't see the requesterId till after the 1st save	
//		if($requesterId<>0) {
	$this->requesterId  = $requesterId;
//	}*/
		if($request_over_limit) 
			$requestStatus = 'Waitlisted';
		else
			$requestStatus = 'Booked';
		$this->createdAt=date("Y-m-d H:i:s");	
		$this->requestComment=$comment;
		$this->requestStatus = $requestStatus;
		$this->requester = $attendee['requester'];
		$this->requesterEmail=$attendee['email'];	// this was added by the calling function for all the attendees
		$this->name=$attendee['name'];
		$this->membnum=$attendee['number'];
		$this->memberGuest=$attendee['memberGuest'];
		$this->eventId=$event_info['eventId'];
		$this->eventDate=$event_info['eventDate'];
	//	krumo($this->requester);

		$resp = $this->save();

		if($requesterId==0) { // this was the requester entry so add its own id into the record
							$this->requesterId  =$this->id;
							// no leave it as 0 for the hierarchy search
	
							$this->save();
							}		
		return(array('added',$this->id,$this->requestStatus));
		
		
	}
function updateCount($attendee,$event_info) {
	$fw=\Base::instance();
require_once 'krumo/class.krumo.php'; 
	//$attendee['number'] ;
	$this->load(array('memberGuest = "M" and requester= true and eventId =? and eventDate =? and membnum =?',
			$event_info['eventId'], $event_info['eventDate'],$attendee['number'] ));
//krumo($this->id);
	$requesterId = $this->id;
	$this->rcount='count(*)';
//		$r2 = $this->select('count(*) as rcount, requesterId' ,array('eventId='.$event_info['eventIdz']),array('group' => 'requesterId'));
		$r2count = $this->count(array('eventId=? AND requesterId =?',$event_info['eventId'],$requesterId));
//krumo(	 $r2count);
	$this->load(array('memberGuest = "M" and requester= true and eventId =? and eventDate =? and membnum =?',
			$event_info['eventId'], $event_info['eventDate'],$attendee['number'] ));
	$this->request_count= $r2count;
//krumo(	$this->request_count);
//krumo(	$this->id	);
	
	$this->save();
	return $this->cast();
			
}

	
	}
}