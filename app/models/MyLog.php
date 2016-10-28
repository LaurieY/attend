<?php

class MyLog extends Log {

    public function __construct( $file) {
        parent::__construct($file);
    }
	function write($text,$uselog = false,$format='r') {
	if($uselog	)	{parent::write($text,$format='r');}
		
		}
	
	}