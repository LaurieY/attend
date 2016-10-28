<?php

class MyClass extends DB\SQL\Mapper {
 public function __construct( ) {
 	$f3=Base::instance();
	//var_export($f3->get('SESSION',false));	
 $admin_logger = new MyLog('admin.log');
	$admin_logger->write('in writeemailpdf construct SESSION '.var_export($f3->get('SESSION',true)),true);
 }


	
	} //end of Class MyClass