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
}