<?php

class EventArchive extends DB\SQL\Mapper {

    public function __construct(DB\SQL $db) {
        parent::__construct($db,'events_archive');
    }
	public function add($event_array) {
		$this->copyfrom($event_array);
		$this->save();
		
		
	}
	
}