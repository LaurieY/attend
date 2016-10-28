<?php

class CheckController extends Controller {
//	protected $f3;
//	protected $db;
private $sheetfilename;

	function beforeroute() {
//$f3->set('message','');
	$f3=$this->f3;
	$auth_logger = new MyLog('auth.log');
	$auth_logger->write( 'Entering CheckController beforeroute URI= '.$f3->get('URI'  ) );
	$options= new Option($this->db);
	$options->initu3ayear();
	}

	


/**	function afterroute() {
//		echo Template::instance()->render('layout.htm');	
	}**/
function check() { // to compare an uploaded spreadsheet with the live database
		$f3=Base::instance();

		//$f3=$this->f3;
	$uselog=$f3->get('uselog');
	
	$check_logger = new MyLog('check.log');

	$check_logger->write( 'Entering check'  );
//				var_export($check_logger);
	
	$f3->set('page_head',"Check Page ");
		$f3->set('page_role','user');
        $f3->set('message', $f3->get('PARAMS.message'));
		$f3->set('view','check/upload.htm');
	}
function check2() { // to compare an uploaded spreadsheet with the live database
		$f3=Base::instance();

		//$f3=$this->f3;
	$uselog=$f3->get('uselog');
	
	$check_logger = new MyLog('check.log');

		//$check_logger->write( 'Entering check with PHP '.var_export(phpinfo()) );
	$check_logger->write( 'Entering check'  );
//				var_export($check_logger);
	
	$f3->set('page_head',"Check Page ");
		$f3->set('page_role','nonuser');
        $f3->set('message', $f3->get('PARAMS.message'));
		$f3->set('view','check/upload2.htm');
	}
function getupload() {
		$f3= $this->f3;
		$f3->set('UPLOADS','uploads/'); // don't forget to set an Upload directory, and make it writable!
				//var_export(pathinfo($f3->get('UPLOADS'), PATHINFO_BASENAME));
				//var_export($f3->get('UPLOADS'));
		$web = \Web::instance();
		$overwrite = true; // set to true, to overwrite an existing file; Default: false
		$slug = true; // rename file to filesystem-friendly version
		$files = $web->receive(function($file,$formFieldName){
				var_export($file);		
				if(($file['type'] !="application/vnd.ms-excel")&&($file['type'] !="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") &&($file['type'] !="application/vnd.ms-excel.sheet.macroEnabled.12")&&($file['type'] !="application/vnd.oasis.opendocument.spreadsheet")) {
				print " Not a valid spreadsheet file type".var_export($file,true);
				$this->f3->set('message', " Not a valid spreadsheet file TYPE ".var_export($file,true));
				$this->f3->set('view','check/upload.htm');
				return false;
				}
				//E.G. For Excel .xls" ["type"]=> string(24) "application/vnd.ms-excel"
				//E.G. For .xlsx" ["type"]=> string(65) "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
				/**if($file['size'] > (2 * 1024 * 1024)) // if bigger than 2 MB
					return false; // this file is not valid, return false will skip moving it
*/
				// everything went fine, hurray!
				return true; // allows the file to be moved from php tmp dir to your defined upload dir
			},
			$overwrite,
			$slug);
			//var_export($files);
			//var_export(array_values($files));
	if (empty($files) ||!array_values($files)[0]) {		
			$f3->set('view','check/upload.htm');
			$f3->set('page_head',"Check Page ");
			$f3->set('page_role','user');
			$f3->set('message',"Not a Spreadsheet File");
				
				}
				else {        
				$f3->set('page_head',"Sheet Checked Members Not Paid");
				$f3->set('message',"Spreadsheet ".array_keys($files)[0]." Uploaded");
				$f3->set('view','check/checked.htm');
				$processresult=$this->processspreadsheet(array_keys($files)[0]);
				//var_export($processresult);
				
				}
		//var_export($files);
		
		$f3->set('page_role','user');

	}
function uploadgrid() {
	$f3=$this->f3;
        $user = new User($this->db);
        $f3->set('users',$user->all());
        $f3->set('page_head','Spreadsheet Check');
        $f3->set('message', $f3->get('PARAMS.message'));
        $f3->set('view','check/checkedgrid.htm');
		
		$f3->set('page_role',$f3->get('SESSION.user_role'));
}
function getupload2() {
		$f3= $this->f3;
		$uselog=$f3->get('uselog');
		$check_logger = new MyLog('check.log');
		$f3->set('UPLOADS','uploads/'); // don't forget to set an Upload directory, and make it writable!
				//var_export(pathinfo($f3->get('UPLOADS'), PATHINFO_BASENAME));
				//var_export($f3->get('UPLOADS'));
		$web = \Web::instance();
		$overwrite = true; // set to true, to overwrite an existing file; Default: false
		$slug = true; // rename file to filesystem-friendly version
		$files = $web->receive(function($file,$formFieldName){
				//var_export($file);		
				if(($file['type'] !="application/vnd.ms-excel")&&($file['type'] !="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") &&($file['type'] !="application/vnd.ms-excel.sheet.macroEnabled.12")&&($file['type'] !="application/vnd.oasis.opendocument.spreadsheet")) {
				print " Not a valid spreadsheet file type".var_export($file,true);
				$this->f3->set('message', " Not a valid spreadsheet file TYPE ".var_export($file,true));
				$this->f3->set('view','check/upload.htm');
				return false;
				}
				//E.G. For Excel .xls" ["type"]=> string(24) "application/vnd.ms-excel"
				//E.G. For .xlsx" ["type"]=> string(65) "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
				/**if($file['size'] > (2 * 1024 * 1024)) // if bigger than 2 MB
					return false; // this file is not valid, return false will skip moving it
*/
				// everything went fine, hurray!
				return true; // allows the file to be moved from php tmp dir to your defined upload dir
			},
			$overwrite,
			$slug);
			//var_export($files);
			//var_export(array_values($files));
	if (empty($files) ||!array_values($files)[0]) {		
			$f3->set('view','check/upload.htm');
			$f3->set('page_head',"Check Page ");
			$f3->set('page_role','user');
			$f3->set('message',"Not a Spreadsheet File");
				
				}
				else {        
				$f3->set('page_head',"SpreadSheet Checker");
				$f3->set('message',"Spreadsheet ".array_keys($files)[0]." Uploaded");
				$f3->set('view','check/checkedgrid.htm');
							$f3->set('page_role','nonuser');
				$f3->set('SESSION.sheetfilename',array_keys($files)[0]);
//$this->sheetfilename= array_keys($files)[0];

				$check_logger->write( 'in  check #155 filename='. $f3->get('SESSION.sheetfilename'),true);
				//printf("file to process %s ",$this->sheetfilename);
				//$processresult=$this->processspreadsheet2(array_keys($files)[0]);
				//var_export($processresult);
				//echo "freddd";
				//echo $processresult;
				
				}
		//var_export($files);
		
		//$f3->set('page_role','user');

	}


function processspreadsheet0($inputFileName){  // first version
	$f3=Base::instance();3;
	$uselog=$f3->get('uselog');
	$check_logger = new MyLog('check.log');

	$check_logger->write( 'Entering processspreadsheet'  );
		/** Include PHPExcel */
		error_reporting(E_ALL);
		ini_set('display_errors', TRUE);
		ini_set('display_startup_errors', TRUE);
		define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
		date_default_timezone_set('Europe/Madrid');
		require_once('vendor/Classes/PHPExcel.php');
		require_once('vendor/Classes/PHPExcel/IOFactory.php');

		//  Read the Excel workbook
		try {
			$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
		} catch(Exception $e) {
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
		}
		//  Get worksheet dimensions
		$sheet = $objPHPExcel->getSheet(0); 
		$highestRow = $sheet->getHighestRow(); 
	//printf("HighestRow = $highestRow<br>");
		$highestColumn = $sheet->getHighestColumn();
	//printf("HighestColumn = $highestColumn<br>");
$rowdata =array();
		//  Loop through each row of the worksheet in turn
		for ($row = 1; $row <= $highestRow; $row++){ 
			//  Read a row of data into an array
			$rowdata[$row-1] = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
											NULL,
											TRUE,
											FALSE);
			//  Insert row data array into your database of choice here
		}

		$membnumcol=$this->findmembnumcol($rowdata);
		$i=0;
		$membnumstocheck =array();
		foreach($rowdata as $rownum=>$arow) {
			$membnumstocheck[$i] = $arow[0][$membnumcol];
			$i++;
		}
	//var_export($membnumstocheck);
	 	$u3ayear = $f3->get('SESSION.u3ayear');
		$sqlselect="select surname as 'Surname',forename as 'Forename',membnum as 'Membnum',phone as 'Phone',mobile as 'Mobile' ,email as 'Email' ,paidthisyear as 'Paid' from members where u3ayear='".$u3ayear."' and status='Active' order by Membnum ASC 	";	
		$checkmembers= $this->db->exec($sqlselect);
		//get just the membnums from the current database
		$checkmembersmembnum =array();
		$checkmembersto =array();
				$i=0;
		foreach($checkmembers as $amember){
		$checkmembersto[$amember['Membnum']] = $amember;
		$checkmembersmembnum[$i] = $amember['Membnum'];
		$i++;		
		}
		$unknowns =array_diff($membnumstocheck,$checkmembersmembnum);
		$unpaids =array();
		$i=0;
		foreach($membnumstocheck as $amembernum){
			if (!empty($checkmembersto[$amembernum])){ 
			if ($checkmembersto[$amembernum]['Paid'] !='Y')
		$unpaids[$i] =$checkmembersto[$amembernum]['Forename']."_".$checkmembersto[$amembernum]['Surname']." ".$checkmembersto[$amembernum]['Membnum'];
			$i++;}
		}
						//printf(" Unpaids = <br>");
						//var_export($unpaids);

		
		$f3->set('missings',$unpaids);
		return true;
	}
function processspreadsheet($inputFileName){ // second version to handle no membnum
	$f3=Base::instance();
	$uselog=$f3->get('uselog');
	$check_logger = new MyLog('check.log');

	$check_logger->write( 'Entering processspreadsheet'  );
		/** Include PHPExcel */
		error_reporting(E_ALL);
		ini_set('display_errors', TRUE);
		ini_set('display_startup_errors', TRUE);
		define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
		date_default_timezone_set('Europe/Madrid');
		require_once('vendor/Classes/PHPExcel.php');
		require_once('vendor/Classes/PHPExcel/IOFactory.php');

		//  Read the Excel workbook
		try {
			$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
		} catch(Exception $e) {
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
		}
		//  Get worksheet dimensions
		$sheet = $objPHPExcel->getSheet(0); 
		$highestRow = $sheet->getHighestRow(); 
					//printf("HighestRow = $highestRow<br>");
		$highestColumn = $sheet->getHighestColumn();
					//printf("HighestColumn = $highestColumn<br>");
		$rowdata =array();
		//  Loop through each row of the worksheet in turn
		for ($row = 1; $row <= $highestRow; $row++){ 
			//  Read a row of data into an array
			$rowdata[$row-1] = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
											NULL,
											TRUE,
											FALSE);
			//  Insert row data array into your database of choice here
		}
/**************  

** find the members column number by search & if that fails by option value

**************/
	//	printf(" <br><br>contents of rowdata 0 ");
	//	printf(var_export($rowdata[0])." <br><br>");
	//	printf(" contents of rowdata 1 ".var_export($rowdata[1])." <br><br>");
	/********** strip the top level of the rows data  *****/
		$output = array();   
		
				
		foreach($rowdata as $key=>$row) {
			$output[] = $row[0];
			}
			$rowdata = $output;
	//	printf(" <br><br>contents of output  ");
	//	printf(var_export($output)." <br><br>");
		//printf(" contents of rowdata 0 ".var_export($rowdata[0])." <br><br>");
		//printf(" contents of rowdata 1 ".var_export($rowdata[1])." <br><br>");
		$membnumcol=$this->findmembnumcol($rowdata);
		if($membnumcol == -1) $membnumcol=$this->findmembnumcolbyheader($rowdata);
		// strip any headers by removing rows from 0 until the number column is blank or a number
		$rowdata=$this->stripheaders($rowdata,$membnumcol);
		$i=0;
		$u3ayear = $f3->get('SESSION.u3ayear');
		$number_not_match =array();
		$name_not_match =array();
		$non_existent =array();
		$non_existent_name =array();
		$no_number = array();
		$not_paid =array();
		$not_paid_name =array();
		$numtocheck =0;

		$membnumstocheck =array();
		//printf("<br> ^^^^^^^^^^^^ %s<br>",var_export($rowdata[0]));
		foreach($rowdata as $rownum=>$arow) {
		//	printf("<br> ^^^^^^^^^^^^ %s<br>",var_export($arow));
			$numtocheck=$arow[$membnumcol];
			if(is_null($numtocheck)) {$no_number[$rownum] =$arow;
			
			continue;}
			$sqlselect_num="select surname as 'Surname',forename as 'Forename',membnum as 'Membnum',phone as 'Phone',mobile as 'Mobile' ,email as 'Email' ,paidthisyear as 'Paid' from members where u3ayear='".$u3ayear."' and status='Active' and membnum = '".$numtocheck."' ";	

		$amember= $this->db->exec($sqlselect_num);
			//printf("<br> ++++++++ %s<br>",var_export($amember));
/**************
**
**  		First check for membernumber non-existant in database
** 
**************/
		if (empty($amember)) {$non_existent[$numtocheck] =$arow;
		//printf("<br> member number not in database %s <br>",var_export($arow,true));
		continue;}
		else {
		$is_member = $amember[0]['Membnum'];
		$is_paid = ($amember[0]['Paid']=='Y');
		if(!$is_paid) {$not_paid[$numtocheck] =$arow;
				//printf("<br>Membnum %s NOT paid",var_export($amember[0],true));
		}
		//printf("<br>Membnum %s is Member",$is_member);
		
		//var_export($is_member);
		}
		//		$membnumstocheck[$i] = $arow[0][$membnumcol];
		$i++;
		}
		//var_export($membnumstocheck);
				
/***********************
**
**  Now also check that the name in the sheet corresponds to the membership database names
**
** Assume the columns are  membcolnum-2 for surename and membcolnum -1 for forename
************************/
		$membsurnamecol = intval($membnumcol)-2;
		$membforenamecol = intval($membnumcol)-1;
		
		$membamestocheck =array();
		//printf("<br> ^^^^^^^^^^^^ %s<br>",var_export($rowdata[0]));
		foreach($rowdata as $rownum=>$arow) {
		//	printf("<br> ^^^^^^^^^^^^ %s<br>",var_export($arow));
			$surname=$arow[$membsurnamecol];
			$forename=$arow[$membforenamecol];
			//if(is_null($numtocheck)) continue;
/***********************************************
**
**  		Check for membername non-existant even though there is a number
** 
**************************************************/
		$amember =$this->isnameindatabase($surname,$forename);
		if(empty($amember)  ){
			$name_not_match[$surname."_".$forename] =$arow;
			//printf("<br> Name not in database %s <br>",var_export($arow,true));
			continue;}
	/*	else {
		$is_member = $amember[0]['Membnum'];
		$is_paid = ($amember[0]['Paid']=='Y');
		if(!$is_paid) {$not_paid[$numtocheck] =$arow; 
				//printf("<br>Membnum %s NOT paid",var_export($amember[0],true));
		}
		//printf("<br>Membnum %s is Member",$is_member);
		
		//var_export($is_member);
		} */
		//		$membnumstocheck[$i] = $arow[0][$membnumcol];
		$i++;
		}


/************************************

** If there is no membership number in the sheet just check the name (set in array $no_number[$rownum] =$arow

** If it isn't in db add to non_existent_name array keyed by forename_surname

** exists in db but is not paid add names and the db version of number to not_paid_name array keyed by forename_surname

** 

*****************************************/
		$membsurnamecol = intval($membnumcol)-2;
		$membforenamecol = intval($membnumcol)-1;
		foreach($no_number as $rownum=>$arow)  {
		$surname=$arow[$membsurnamecol];
		$forename=$arow[$membforenamecol];	
		$amember =$this->isnameindatabase($surname,$forename);
		if(empty($amember)  ){
			$non_existent_name[$surname."_".$forename] =$arow;
		//	printf("<br> Name not in database %s <br>",var_export($arow,true));
			continue;}
		else {
						unset($no_number[$rownum]);
		$is_member = $amember[0]['Membnum'];
		$is_paid = ($amember[0]['Paid']=='Y');
			if(!$is_paid) {$not_paid_name[$surname."_".$forename."_".$is_member] =$arow;
			}
			else{// if no number but name matches and is paid so take out of $no_number array
			unset($no_number[$rownum]);
			}
		}
		}
/****************		
		printf("<br>Member numbers Not in database %s <br>", var_export($non_existent,true));
		printf("<br>Member numbers in database but NOT paid %s <br>", var_export($not_paid,true));
		printf("<br>Member names Not in database %s <br>", var_export($name_not_match,true));
		printf("<br>Members with no number %s <br>", var_export($no_number,true));
		printf("<br>Members with no number and unmatched name%s <br>", var_export($non_existent_name,true));
		printf("<br>Members with no number matched name but not paid %s <br>", var_export($not_paid_name,true));
*/
		$f3->set('missings1',$this->stripmissingsrow($non_existent));
		$f3->set('missings2',$this->stripmissingsrow($not_paid));
		$f3->set('missings3',$this->stripmissingsrow($name_not_match));
		$f3->set('missings4',$this->stripmissingsrow($no_number));
		$f3->set('missings5',$this->stripmissingsrow($non_existent_name));
		$f3->set('missings6',$this->stripmissingsrow($not_paid_name));
		
		return true;
	}
/***********************
**
** Vs 2 outputs a copy of the spreadsheet rows with annotation, an array is constructed that is output via  f3 view template
**
** Find the number column, strip the headers 
**
** each remaining spredshhet row, if there is a number check if paid mark Row as Paid or Not paid in column5
** if no number check names, if names check if paid, mark Row as Paid or Not paid in column5
** 
** if neither number or name check ok then mark in Column5 that neither number or name can be found in database
**
**************************/
	
function processspreadsheet2($inputFileName){ // second version to handle no membnum
	$f3=Base::instance();
	$uselog=$f3->get('uselog');
	$check_logger = new MyLog('check.log');

	$check_logger->write( 'Entering processspreadsheet2' ,true );
		/** Include PHPExcel */
		error_reporting(E_ALL);
		ini_set('display_errors', TRUE);
		ini_set('display_startup_errors', TRUE);
		define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
		date_default_timezone_set('Europe/Madrid');
		require_once('vendor/Classes/PHPExcel.php');
		require_once('vendor/Classes/PHPExcel/IOFactory.php');

		//  Read the Excel workbook
		try {
			$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
		} catch(Exception $e) {
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
		}
		//  Get worksheet dimensions
		$sheet = $objPHPExcel->getSheet(0); 
		$highestRow = $sheet->getHighestRow(); 
		$check_logger->write( "HighestRow  = ".$highestRow."<br>",$uselog);
				//	printf("HighestRow  = %s<br>",$highestRow);
		$highestColumn = $sheet->getHighestColumn();
				//	printf("HighestColumn = %s<br>",$highestColumn);
					$check_logger->write( "HighestColumn = ".$highestColumn."<br>",$uselog);

		$rowdata =array();
		//  Loop through each row of the worksheet in turn
		for ($row = 1; $row <= $highestRow; $row++){ 
			//  Read a row of data into an array
			$rowdata[$row-1] = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
											NULL,
											TRUE,
											FALSE);
			
		}
/**************  

** find the members column number by search & if that fails by option value

**************/
	//	printf(" <br><br>contents of rowdata 0 ");
	//	printf(var_export($rowdata[0])." <br><br>");
	//	printf(" contents of rowdata 1 ".var_export($rowdata[1])." <br><br>");
	/********** strip the top level of the rows data  *****/
		$output = array();   
		
				
		foreach($rowdata as $key=>$row) {
			$output[] = $row[0];
			}
			$rowdata = $output;
//printf(" #493  rows %s <br>",var_export($rowdata,true));
		$membnumcol=$this->findmembnumcol($rowdata);
		$membnumcol= -1; //Force it to find by column
		if($membnumcol == -1) {$membnumcol=$this->findmembnumcolbyheader($rowdata);
				$check_logger->write( '$membnumcol found using header as '.$membnumcol,$uselog  );}
				else{$check_logger->write( '$membnumcol found by col values as '.$membnumcol ,$uselog );}
				
		// strip any headers by removing rows from 0 until the number column is blank or a number
		// also strip any blank rows
		//printf(" #502  rows %s <br>",var_export($rowdata,true));
		$rowdata=$this->stripheaders($rowdata,$membnumcol);
		//$rowdata = $this->stripblankrows($rowdata); // taken care of in stripheaders
		$i=0;
		$u3ayear = $f3->get('SESSION.u3ayear');
		$number_not_match =array();
		$name_not_match =array();
		$non_existent =array();
		$non_existent_name =array();
		$no_number = array();
		$not_paid =array();
		$not_paid_name =array();
		$numtocheck =0;
		
		$membsurnamecol = intval($membnumcol)-2;
		$membforenamecol = intval($membnumcol)-1;
		$reportcol = intval($membnumcol)+2;

		$membnumstocheck =array();
		//printf("<br> ^^^^^^^^^^^^ %s<br>",var_export($rowdata[0]));
		foreach($rowdata as $rownum=>$arow) {
			$numtocheck=$arow[$membnumcol];
			if(!is_null($numtocheck)) {
				// check number is paid
				$sqlselect_num="select surname as 'Surname',forename as 'Forename',membnum as 'Membnum',phone as 'Phone',mobile as 'Mobile' ,email as 'Email' ,paidthisyear as 'Paid' from members where u3ayear='".$u3ayear."' and status='Active' and membnum = '".$numtocheck."' ";	
				$amember= $this->db->exec($sqlselect_num);	
				if (empty($amember)) {$non_existent[$numtocheck] =$arow;
		//printf("<br> member number not in database %s <br>",var_export($arow,true)); 
		// add number not in db to col5
				$rowdata[$rownum][$reportcol] = "Membership number not in database";
				}
				else // number in database, check paid
				{ if ($amember[0]['Paid']=='Y') {$rowdata[$rownum][$reportcol] = "Paid";
					}
					else
					{$rowdata[$rownum][$reportcol] = " NOT Paid";
					}
				}
			} 
			//  No Number in sheet so check name
			else
			{$no_number[$rownum] =$arow;
			$surname=$arow[$membsurnamecol];
			$forename=$arow[$membforenamecol];
			$amember =$this->isnameindatabase($surname,$forename);
			if(empty($amember)  ){
			$rowdata[$rownum][$reportcol] = "Name not in database";
			}
			else
			{// get member number if name matches
				$named_membnum=$amember[0]['Membnum'];
				$rowdata[$rownum][$membnumcol] = $named_membnum;
			//	$rowdata[$rownum][$membnumcol] = 12;
				if ($amember[0]['Paid']=='Y') {$rowdata[$rownum][$reportcol] = "Paid";
					//printf("<br> !!arow!!!! %s<br>",var_export($rowdata[$rownum],true));

					}
					else
					{$rowdata[$rownum][$reportcol] = " NOT Paid";
					}
			}
			
			}
			//printf("<br> ^^^arow^^^^^^ %s<br>",var_export($rowdata[$rownum],true));


		}
/****************		
		printf("<br>Member numbers Not in database %s <br>", var_export($non_existent,true));
		printf("<br>Member numbers in database but NOT paid %s <br>", var_export($not_paid,true));
		printf("<br>Member names Not in database %s <br>", var_export($name_not_match,true));
		printf("<br>Members with no number %s <br>", var_export($no_number,true));
		printf("<br>Members with no number and unmatched name%s <br>", var_export($non_existent_name,true));
		printf("<br>Members with no number matched name but not paid %s <br>", var_export($not_paid_name,true));
*/
/*		$f3->set('missings1',$this->stripmissingsrow($non_existent));
		$f3->set('missings2',$this->stripmissingsrow($not_paid));
		$f3->set('missings3',$this->stripmissingsrow($name_not_match));
		$f3->set('missings4',$this->stripmissingsrow($no_number));
		$f3->set('missings5',$this->stripmissingsrow($non_existent_name));
		$f3->set('missings6',$this->stripmissingsrow($not_paid_name));
*/		
		//$f3->set('sheet',$rowdata);
			$check_logger->write( 'processspreadsheet2 #604 '.var_export($rowdata,true),true  );

			$emc= new EmailController;
			return $emc->arraytojson2($rowdata);
	//	return true;
	}
/**************************
** gets the membernumber column number using a header row
**
****************************/

function findmembnumcolbyheader($rowdata) {
		$options= new Option($this->db);
		$membernumcolnum= $options->find("optionname='membnumcol'")[0]->optionvalue;
		//printf("<br> found ".var_export($membernumcolnum)." in findmembnumcolbyheader<br>");
		return $membernumcolnum;
		
}
	
/*********************
	** for each row for each column
	** look is it a number between 1 and 9999 then see if the the row below also has the same
	** if so that's the membernumber and exit with the column number 
	** if not next column
	**  if last column go to next row
	**  if nothing return -1
************************/

	
function findmembnumcol($rowdata){
	$testcol =1;	
	foreach($rowdata as $rownum=>$arow) {
		//print( "Row = ".(intval($rownum)+1)."<br>");
		foreach($arow as $acolnum=>$acolval  ) {
			$intcolval = intval($acolval);
			//	print( "cell ".$acolnum." = ".$intcolval."<br> ");
				if ($this->ismembnum($rowdata,$rownum,$acolnum,$intcolval)) {
				$testcol =  $acolnum;			
//printf(" Membnum col is ".	$testcol);	
				return 	$testcol;		
				}
			}
		}
	return -1;
	}
function ismembnum($rowdata,$rownum,$acolnum,$intcolval) {
		//printf("<br>#168<br>");
		//	printf(" Col is ".$acolnum."<br>");
		//var_export($rowdata[$rownum][0]);
		$nextrow=intval($rownum)+1;
		//var_export($rowdata[$nextrow][0]);
		if($nextrow >= count($rowdata))return false; // got to the end -1 so give up this method
		$firstval =intval($rowdata[$rownum][$acolnum]);

		$nextval = intval($rowdata[$nextrow][$acolnum]);
		//printf("<br> Firstval = ".$firstval."  NextVal = ".$nextval."<br>");
		if ($firstval >0 && $firstval <9999  & $nextval>0 and $nextval<9999) {
			// printf(" Found at col ".$acolnum." <br>");
			return true;
		}		
			return false;
	}

/**************
**
** strip any headers by removing rows from 0 until the number column is blank or a number, preserve sownumber from spreadsheet
**
**************/
function stripheaders($rowdata,$membnumcol)		{
//	printf(" stripping rows %s <br>",var_export($rowdata,true));
	foreach($rowdata as $key=>$arow) {
		//printf(" stripping row %s %s<br>",$key,var_export($arow,true));
	//if ((!is_int($arow[$membnumcol]))&& (strlen($arow[$membnumcol])>4 && (!is_null($arow[$membnumcol])))  {unset($rowdata[0][$key]);
//	if ((is_null($arow[1]))||(strlen($arow[$membnumcol])>4) || (is_null($arow[0])))  {unset($rowdata[$key]);
	if ((is_null($arow[1]))||((!is_numeric($arow[$membnumcol]))&&(!is_null($arow[$membnumcol])) )|| (is_null($arow[0])))  {unset($rowdata[$key]);
	
	}
	}
	//printf("!!!!!!<br><br>");
	//var_export($rowdata);
	//printf("*******<br><br>");
	//$rowdata=array_values($rowdata);
	//var_export($rowdata); 
	// leave rows indexed by rownum
		//printf(" stripped rows %s <br>",var_export($rowdata,true));
	return($rowdata);
	}
/*****************
**
** check for a member in the database, passing the names only
**
** return the query result  -  empty for no match
****************/
function isnameindatabase($surname,$forename  ){
	$f3=$this->f3;
	$u3ayear = $f3->get('SESSION.u3ayear');
	$sqlselect_name="select surname as 'Surname',forename as 'Forename',membnum as 'Membnum',phone as 'Phone',mobile as 'Mobile' ,email as 'Email' ,paidthisyear as 'Paid' from members where u3ayear=:u3ayear and status='Active' and surname sounds like :sname and forename sounds like :fname ";	
//	$sqlselect_name="select surname as 'Surname',forename as 'Forename',membnum as 'Membnum',phone as 'Phone',mobile as 'Mobile' ,email as 'Email' ,paidthisyear as 'Paid' from members where u3ayear='".$u3ayear."' and status='Active' and surname sounds like '".$surname."' and forename sounds like '".$forename."' ";	
	$amember= $this->db->exec($sqlselect_name, array(':u3ayear'=>$u3ayear,':sname'=>$surname,'fname'=>$forename));
	
	return $amember;
	}
	
/*****************************

** take an array of missing/anomaliesand return an array of strings representing the spreadsheet row

*********************************/	

function stripmissingsrow($missings) {
	$missings1 = array();
	$tab = '&nbsp;&nbsp;&nbsp;&nbsp;';
		foreach($missings as $arow) {
			$row='';
			foreach($arow as $acol) {
			$row.=$acol.$tab;	}
			$missings1[]=$row;
		}
	return 		$missings1;
}

function stripblankrows($rowdata) {
	
}
}