<?php

class CheckAjaxController extends Controller {
	
	function afterroute() {

	}
	function checkedjqgrid() {
	$f3=Base::instance();
	$uselog=$f3->get('uselog');
	$check_logger = new MyLog('check.log');
$check_logger->write( 'in  checkjqgrid #173 filename='. $f3->get('SESSION.sheetfilename') ,true);
	$f3->set('page_head',"SpreadSheet Checker");
					$f3->set('message',"Spreadsheet Uploaded");
								$f3->set('page_role','nonuser');
					$f3->set('view','check/checkedgrid.htm');
	$filetoprocess=$f3->get('SESSION.sheetfilename');
	//printf("file to process %s ",$this->sheetfilename);
	$chkcontroller = new CheckController;
				$processresult=$chkcontroller->processspreadsheet2($filetoprocess);
			$check_logger->write( 'in  checkjqgrid #181 processresult='. var_export($processresult,true) ,true);
					//	$emc= new EmailController;
						echo $processresult;
					//	echo $emc->arraytojson2($processresult);


	//echo "freddd";
}
}