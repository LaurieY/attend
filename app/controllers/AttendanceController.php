<?php



class AttendanceController extends Controller {
	private $u3ayear;
	

/*function afterroute() { // allows simple non views activities
	}*/	
function beforeroute() {
//$f3->set('message','');
	$f3=$this->f3;
	$auth_logger = new MyLog('auth.log');
	$uselog=$f3->get('uselog');
	$auth_logger->write( 'Entering AttendanceController beforeroute URI= '.$f3->get('URI'  ),$uselog);
	$options= new Option($this->db);
	$this->u3ayear = $f3->get('SESSION.u3ayear');
	if($this->u3ayear =='') {$options->initu3ayear();		
	$this->u3ayear = $f3->get('SESSION.u3ayear');}
	
		if (!$f3->get('COOKIE.PHPSESSID')){
			$f3->set('message','Cookies must be enabled to enter this area');
			$auth_logger->write( ' COOKIE PHPSESSID NOT set contents = '.var_export($f3->get('COOKIE'), true));
			$f3->reroute('/');
			}
	if($f3->get('SESSION.user_id')){$auth_logger->write( "Session user_id = ".$f3->get('SESSION.user_id')); 
	$auth_logger->write( "Session lastseen = ".$f3->get('SESSION.lastseen')); 
	$auth_logger->write( "Session expiry config secs = ".($f3->get('user_expiry'))*($f3->get('user_expiry_mult'))); 
	//$auth_logger->write( "Session expiry secs = ".($f3->get('user_expiry'))*($f3->get('user_expiry_mult'))); 
	$auth_logger->write( "Session time now = ".time());
	$auth_logger->write( "Session lastseen  expiry = ".($f3->get('SESSION.lastseen')+(($f3->get('user_expiry'))*($f3->get('user_expiry_mult'))))); 

	}
	else
	{$auth_logger->write( "In membercontroller beforeroute Session user_id NOT set");
	$auth_logger->write( "Session lastseen = ".$f3->get('SESSION.lastseen')); 
	$auth_logger->write( "Session expiry config secs = ".($f3->get('user_expiry'))*($f3->get('user_expiry_mult'))); 
	//$auth_logger->write( "Session expiry secs = ".($f3->get('user_expiry'))*($f3->get('user_expiry_mult'))); 
	$auth_logger->write( "Session time now = ".time());
	$auth_logger->write( "Session lastseen  expiry = ".($f3->get('SESSION.lastseen')+(($f3->get('user_expiry'))*($f3->get('user_expiry_mult'))))); 
}
	$relogincondition= true;
	$relogincondition = (!$f3->get('SESSION.user_id'))||( $f3->get('SESSION.lastseen')+($f3->get('user_expiry')*($f3->get('user_expiry_mult')))<time());
	$auth_logger->write( 'beforeroute with relogincondition a ='.$relogincondition);
	//if ((!($f3->get('URI')=='/login' )&&!($f3->get('URI')=='/logout' ))&&$relogincondition      ) 
	if ($relogincondition)
	// not login or logout and not a session user_id already then need to force a login
	{$auth_logger->write( 'Exiting beforeroute with relogincondition ='.$relogincondition);
	$auth_logger->write( 'Exiting beforeroute with reroute to login');	 
	$this->f3->reroute('/login');
		}
	$auth_logger->write( 'Exiting beforeroute URI= '.$f3->get('URI'  ));
	$auth_logger->write( 'Exiting beforeroute page_head set to = '.$f3->get('page_head'  ));		
	}
function check_cookie(){
$auth_logger = new MyLog('auth.log');
$f3=$this->f3;
	$auth_logger->write( 'Entering check_cookie URI= '.$f3->get('URI'  ) );
	
    setcookie('COOK_CHK', uniqid(), time()+60);
    if(!isset($_COOKIE['COOK_CHK']))
    {$auth_logger->write( 'check_cookie !isset' );
        header('Location: ' . $_SERVER['PHP_SELF']);
    }
    else
    {$auth_logger->write( 'check_cookie isset inner' );
        return TRUE;
    }
$auth_logger->write( 'check_cookie isset outer' );
    return TRUE;
}
function index () { // show event grid and treegrid of attendees
	$f3=$this->f3;
	$uselog=$f3->get('uselog');
	$auth_logger = new MyLog('auth.log');
	$auth_logger->write( 'Entering AttendanceController index'  );	  
	$options= new Option($this->db);
		$options->initu3ayear();
		$options->initlastu3ayear();
		//$options->initmjl1start();
		$options->initemailsettings();
		$event = new Event($this->db);
		$attendee = new Attendee($this->db);
		$f3->set('event',$event->future());
        $f3->set('attendee',$attendee->all());  //LEY is this needed
		$auth_logger->write( 'In attendanceController index #86 with u3ayear = '.$f3->get('SESSION.u3ayear'),$uselog  );	 
		$auth_logger->write( 'In attendanceController index #87 with role = '.$f3->get('SESSION.user_role'),$uselog  );	 
		$f3->set('page_head',"Event List ".$f3->get('SESSION.u3ayear'));
		$f3->set('page_role',$f3->get('SESSION.user_role'));
        $f3->set('message', $f3->get('PARAMS.message'));
		//$f3->set('listn', $f3->get('PARAMS.mylist'));


	 // $f3->set('listnn','member/list.htm');
	$f3->set('view','event/list.htm');
		$auth_logger->write( 'In attendanceController index #97 with SESSION.user_role = '.$f3->get('SESSION.user_role'),$uselog  );	 
	$user_role = $f3->get('SESSION.user_role');
		if(($user_role =='register') or ($user_role =='admin'))	{$f3->set('view','event/list.htm'); $f3->set('page_head',"Member Attendance List ");}
		$f3->set('SESSION.lastseen',time()); 
			$auth_logger->write( 'In attendanceController index #101 with u3ayear = '.$f3->get('SESSION.u3ayear'),$uselog  );
	
}
function index2 () { // show event grid and indented of attendees
	$f3=$this->f3;
	$uselog=$f3->get('uselog');
	$auth_logger = new MyLog('auth.log');
	$auth_logger->write( 'Entering AttendanceController index2'  );	  
	$options= new Option($this->db);
		$options->initu3ayear();
		$options->initlastu3ayear();
		//$options->initmjl1start();
		$options->initemailsettings();
		$event = new Event($this->db);
		$attendee = new Attendee($this->db);
		$f3->set('event',$event->future());
        $f3->set('attendee',$attendee->all());  //LEY is this needed
		$auth_logger->write( 'In attendanceController index2 #118 with u3ayear = '.$f3->get('SESSION.u3ayear'),$uselog  );	 
		$auth_logger->write( 'In attendanceController index2 #119 with role = '.$f3->get('SESSION.user_role'),$uselog  );	 
		$f3->set('page_head',"Event List ".$f3->get('SESSION.u3ayear'));
		$f3->set('page_role',$f3->get('SESSION.user_role'));
        $f3->set('message', $f3->get('PARAMS.message'));
		//$f3->set('listn', $f3->get('PARAMS.mylist'));


	 // $f3->set('listnn','member/list.htm');
	$f3->set('view','event/list2.htm');
		$auth_logger->write( 'In attendanceController index #97 with SESSION.user_role = '.$f3->get('SESSION.user_role'),$uselog  );	 
	$user_role = $f3->get('SESSION.user_role');
		if(($user_role =='register') or ($user_role =='admin'))	{$f3->set('view','event/list2.htm'); $f3->set('page_head',"Member Attendance List ");}
		$f3->set('SESSION.lastseen',time()); 
			$auth_logger->write( 'In attendanceController index #101 with u3ayear = '.$f3->get('SESSION.u3ayear'),$uselog  );
	
}
function index3 () {  //Show 3 grids
	$f3=$this->f3;
	$uselog=$f3->get('uselog');
	$auth_logger = new MyLog('auth.log');
	$auth_logger->write( 'Entering AttendanceController index'  );	  
	$options= new Option($this->db);
		$options->initu3ayear();
		$options->initlastu3ayear();
		//$options->initmjl1start();
		$options->initemailsettings();
		$event = new Event($this->db);
		$attendee = new Attendee($this->db);
		$f3->set('event',$event->future());
        $f3->set('attendee',$attendee->all());  //LEY is this needed
		$auth_logger->write( 'In attendanceController index #118 with u3ayear = '.$f3->get('SESSION.u3ayear'),$uselog  );	 
		$auth_logger->write( 'In attendanceController index #119 with role = '.$f3->get('SESSION.user_role'),$uselog  );	 
		$f3->set('page_head',"Event List ".$f3->get('SESSION.u3ayear'));
		$f3->set('page_role',$f3->get('SESSION.user_role'));
        $f3->set('message', $f3->get('PARAMS.message'));
		//$f3->set('listn', $f3->get('PARAMS.mylist'));


	 // $f3->set('listnn','member/list.htm');
	$f3->set('view','event3/list.htm');
		$auth_logger->write( 'In attendanceController index #127 with SESSION.user_role = '.$f3->get('SESSION.user_role'),$uselog  );	 
	$user_role = $f3->get('SESSION.user_role');
		if(($user_role =='register') or ($user_role =='admin'))	{$f3->set('view','event/list3.htm'); $f3->set('page_head',"Member Attendance List-3 ");}
		$f3->set('SESSION.lastseen',time()); 
			$auth_logger->write( 'In attendanceController index #132 with u3ayear = '.$f3->get('SESSION.u3ayear'),$uselog  );
	
}

function attendance_list2() {	
	$f3=Base::instance();
	$uselog=$f3->get('uselog');
	$check_logger = new MyLog('attendance.log');
	require_once 'krumo/class.krumo.php'; 
		$check_logger->write( 'Entering attendance_list2',$uselog  );	
	//	krumo($f3->get('PARAMS.message'));
	//	krumo($f3->get('SESSION.user_role'));
	$check_logger->write( 'Role is '.$f3->get('SESSION.user_role'),$uselog  );	
	$f3->set('message', $f3->get('PARAMS.message'));
	$f3->set('page_role',$f3->get('SESSION.user_role'));
	$f3->set('view','attendance/list.htm'); 
	$f3->set('page_head',"Member Attendance List ");
}
function attendance_list_name() {
	$f3=Base::instance();
	$uselog=$f3->get('uselog');
	$check_logger = new MyLog('attendance.log');
	require_once 'krumo/class.krumo.php'; 
	//krumo($f3->get('POST'));
	//krumo($f3->get('POST.eventname'));
	$eventname=$f3->get('POST.eventname');
	if ($eventname=='') {$f3->set('view','attendance/list.htm'); $f3->set('page_head',"Member Attendance List ");}
	else 
	{
	$check_logger->write( 'Entering attendance_list',$uselog  );	
	$check_logger->write( 'Name is '.$f3->get('POST.eventname'),$uselog  );	

			
	define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
	date_default_timezone_set('Europe/Madrid');
	require_once('vendor/Classes/PHPExcel.php');
	require_once('vendor/Classes/PHPExcel/IOFactory.php');
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator('Laurie Yates')
						 //->setLastModifiedBy('Maarten Balliauw')
						 ->setTitle('U3A International Attendance List for '.$eventname)
						 ->setSubject('Attendance List')
						 ->setDescription('TU3A International Attendance List using latest membership list')
						// ->setKeywords('office PHPExcel php')
						/*->setCategory('Test result file')*/;

// Create the worksheet
	$objPHPExcel->setActiveSheetIndex(0);
	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
$objPHPExcel->getActiveSheet()->getPageSetup()
->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

$objPHPExcel->getActiveSheet()->getHeaderFooter()
->setOddHeader('&C&BAttendance List for '.$eventname);
$objPHPExcel->getActiveSheet()->getHeaderFooter()
->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');
	/******  Read from the members database for all members active for this u3ayear  */
	 $member = new Member($this->db);
     $all_members=$member->all_by_surname_paid();
	 //krumo($all_members);
	$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Num')
					->setCellValue('B1', 'Forename')
					->setCellValue('C1', 'Surname')
					->setCellValue('D1', 'Reserved')
					->setCellValue('E1', 'Attend');
	$styleArray = array(
    'font' => array(
        'bold' => true,    ),    'alignment' => array(        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,  ));
	$objPHPExcel->getActiveSheet()->getStyle('B1:E1')->applyFromArray($styleArray);				
	
	$dataArray = array();
	foreach($all_members as $amember) {
	$dataArray[] =array($amember['membnum'],$amember['forename'],$amember['surname']);	}
	
	 $objPHPExcel->getActiveSheet()->fromArray($dataArray, NULL, 'A3');
	 $lastrow	= count($dataArray);
	 $lastrow1= count($dataArray)+2;
	 $lastrow3= count($dataArray)+4;
	 
//	 krumo($lastrow);
//	 krumo('F'.$lastrow3);
//	  krumo('=SUM(F2:'.$lastrow1.')');
	 //Now go to end of col F  +1 and insert a sum 
	 
	
	//$objPHPExcel->getActiveSheet()->setCellValue(    'D'.$lastrow3	,     'Attendee Count');
	$objPHPExcel->getActiveSheet()->setCellValue(    'D2'	,     '=subtotal(3,D3:D'.$lastrow1.')');
	//$objPHPExcel->getActiveSheet()->getStyle('D'.$lastrow3.':D'.$lastrow3)   ->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
	//$objPHPExcel->getActiveSheet()->getStyle('D'.$lastrow3.':F'.$lastrow3)    ->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
		
	$objPHPExcel->getActiveSheet()->setAutoFilter('D3:D'.$lastrow1);	
	$dlfilename= "downloads/".$eventname.".xlsx";
	 $objWriter->save($dlfilename);
	        // send() method returns FALSE if file doesn't exist
       if (!Web::instance()->send($dlfilename,NULL,512,TRUE))                        $f3->error(404);
		print_r(" Spreadsheet Downloaded <br>");
}
}
}	
