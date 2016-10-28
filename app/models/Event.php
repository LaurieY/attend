<?php

class Event extends DB\SQL\Mapper {

    public function __construct(DB\SQL $db) {
        parent::__construct($db,'events');
    }
	public function future() {
       // $this->load();
	   
	   $fw=Base::instance();
		//var_dump($fw);// need to filter by current u3ayear
		$yesterday= date("Y-m-d",time() - 60 * 60 * 24);
		$this->load(array('event_date >?',$yesterday  ) );
		//$this->first();
		
        return $this->query;
    }
	public function trash($event_id) {
		// mark the event as not active
	 $fw=Base::instance();	
	 $this->load(array('event_id =?',$event_id));
	 if(!$this->dry() ){
	$this->active ='N';
	$this->save();
	 }
	 
		
	}
	
    public function add($event_info) {
      //  $this->copyFrom('POST');
	    $fw=Base::instance();
		//$this->u3ayear=$fw->get('SESSION.u3ayear');
		$event_id = $event_info['event_id'];
	$this->load(array('event_id =?',$event_id));
	if($this->dry() ) $this->created_at=date("Y-m-d H:i:s");
		$this->event_id=$event_info['event_id'];	
		$this->event_name=$event_info['event_name'];	
		//$event_info['event_date'] = DateTime::createFromFormat('M  d,Y', $event_info['event_date'])->format('Y-m-d H:i:s'); //$mysql_date_string;	
		$this->event_date=$event_info['event_date'];	
		$this->event_type=$event_info['event_type'];	
		$this->event_limit=$event_info['event_limit'];	
		$this->active=$event_info['active'];	
		
		$this->event_current_count=$event_info['event_current_count'];	
	//	$this->number_of_names=$event_info['number_of_names'];	
		$this->event_contact_email=$event_info['event_contact_email'];	
		//$this->created_at=date("Y-m-d H:i:s");
		//$this->updated_at=date("Y-m-d H:i:s");
        $ret=$this->save();
		return $this;
    }
	
	public function exists($event_info) {
	$fw=Base::instance();	
	$event_id = $event_info['event_id'];
	$this->load(array('active ="Y" and event_id =?',$event_id));
	if ($this->dry()) return false;
		return true;
	}
}