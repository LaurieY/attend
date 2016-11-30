<?php
//namespace MYI;
class Event extends \DB\SQL\Mapper {
function __construct(\DB\SQL $db) {
        parent::__construct($db,'events');
    }
function future() {
       // $this->load();
	   
	   $fw=Base::instance();
		//var_dump($fw);// need to filter by current u3ayear
		$yesterday= date("Y-m-d",time() - 60 * 60 * 24);
		$this->load(array('eventDate >?',$yesterday  ) );
		//$this->first();
		
        return $this->query;
    }
function past() {
 
	   $fw=Base::instance();
		//var_dump($fw);// need to filter by current u3ayear
		$today= date("Y-m-d",time() );
		$this->load(array('eventDate <?',$today  ) );
		//$this->first();
		
        return $this->query;
    }
function trash($eventId) {
		// mark the event as not active
	 $fw=Base::instance();	
	 $this->load(array('eventId =?',$eventId));
	 if(!$this->dry() ){
	$this->active ='N';
	$this->save();
	 }
	 
		
	}
	
function add($event_info) {
      //  $this->copyFrom('POST');
	  require_once 'krumo/class.krumo.php'; 
	//  krumo($event_info);
	  
	    $fw=Base::instance();
		//$this->u3ayear=$fw->get('SESSION.u3ayear');
		$eventId = $event_info['eventId'];
	$this->load(array('eventId =?',$eventId));
	if($this->dry() ) {$this->createdAt=date("Y-m-d H:i:s");
	
	}
		$this->eventId=$event_info['eventId'];	
		$this->eventName=$event_info['eventName'];	
		//$event_info['eventDate'] = DateTime::createFromFormat('M  d,Y', $event_info['eventDate'])->format('Y-m-d H:i:s'); //$mysql_date_string;	
		$this->eventDate=$event_info['eventDate'];	
		$this->eventType=$event_info['eventType'];	
		$this->eventLimit=$event_info['eventLimit'];	
		$this->active=$event_info['active'];	
		
		if($this->dry() ) $this->eventCurrentCount=$event_info['eventCurrentCount'];	 // dont change current count if an update
				

		$this->eventContactEmail=$event_info['eventContactEmail'];	
		//$this->createdAt=date("Y-m-d H:i:s");
		//$this->updated_at=date("Y-m-d H:i:s");
        $ret=$this->save();
		return $ret;
    }
	
function exists($event_info) {
                include_once 'krumo/class.krumo.php';
	$fw=Base::instance();	
	$eventId = $event_info['eventId'];
	$this->load(array('active ="Y" and eventId =?',$eventId));
   // krumo($event_info);
   // krumo($this->dry());
	if ($this->dry()) return false;
		return true;
	}
}