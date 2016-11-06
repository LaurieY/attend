<?php

class Attendee extends DB\SQL\Mapper {

    public function __construct(DB\SQL $db) {
        parent::__construct($db,'attendees');
    }
	public function all() {
       // $this->load();
	   
	   $fw=Base::instance();
		//var_dump($fw);// need to filter by current u3ayear
		$this->load( );
		//$this->first();
		
        return $this->query;
    }
	public function add($attendee,$comment,$event_info,$requester_id) {
		$fw=Base::instance();
		
		// check for the existence of a member already for this event, duplicate members not valid, just ignored and returned 'exists'
		// it may not be a duplicate if one person is adding multiples but is unlikley so flag it in the email
		// duplicate Guest members names have to be allowed, may be valid
		$person = $this->load(array('member_guest = "M" and event_id =? and event_date =? and membnum =?',
			$event_info['event_id'], $event_info['event_date'],$attendee['number'] ));
		if(!$this->dry()) { // duplicate member for the event 
		if($comment != $this->request_comment) {// the comment has changed so add the new comment 
						if(is_null($this->request_comment)) $this->request_comment="";			
						$this->request_comment=$this->request_comment.$comment;
						$this->save();
						return(array('updated',$this->id));
						}
					return(array('exists',$this->id));
					}
		//if(is_null($this->request_comment)) $this->request_comment="";	
// a new entry	
// if the requester_id is -1 then this is the requester for the overall request so don't see the requester_id till after the 1st save	
		if($requester_id<>0) $this->requester_id  = $requester_id;
		$this->created_at=date("Y-m-d H:i:s");	
		$this->request_comment=$comment;
		$this->requester_email=$attendee['email'];	// this was added by the calling function for all the attendees
		$this->name=$attendee['name'];
		$this->membnum=$attendee['number'];
		$this->member_guest=$attendee['member_guest'];
		$this->event_id=$event_info['event_id'];
		$this->event_date=$event_info['event_date'];
		
		$resp = $this->save();
		if($requester_id==0) { // this was the requester entry so add its own id into the record
							$this->requester_id  =$this->id;
							$this->save();
							}		
		return(array('added',$this->id));
		
		
	}
}