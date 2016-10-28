<?php

class MemberController extends Controller {
	function beforeroute() {
	$f3=$this->f3;
	//$f3=$this->f3;
	 $f3->set('message','');
	$auth_logger = new MyLog('auth.log');
	$uselog=$f3->get('uselog');
	$auth_logger->write( 'Entering MemberController beforeroute URI= '.$f3->get('URI'  ),$uselog );
	$auth_logger->write( 'Entering MemberController beforeroute u3ayear= '.$f3->get('SESSION.u3ayear') ,$uselog);
	$auth_logger->write( 'Entering MemberController beforeroute u3ayear= '.$f3->get('SESSION.u3ayear' ) ,$uselog);
	
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
//debug_backtrace();	
}
function check_cookie()
{$auth_logger = new MyLog('auth.log');
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
/**public function sessionly ()
{ $this->f3->set('page_head','Session info');

$this->f3->set('lyvar','in before');
$this->f3->set('view','member/session.htm');
}
**/

public function index()	
	{
	$f3=$this->f3;
	$uselog=$f3->get('uselog');
	$auth_logger = new MyLog('auth.log');
	$auth_logger->write( 'Entering index'  );	  
$options= new Option($this->db);
		$options->initu3ayear();
		$options->initlastu3ayear();
		$options->initmjl1start();
		$options->initemailsettings();
		
		
	//		$auth_logger->write( ' in Login and result from  initu3ayear = '.$u3ayear, true);
	//		$auth_logger->write( ' in Login and result from  initu3ayear = '.$f3->get('SESSION.u3ayear'), true);	
	$auth_logger->write( 'In MembersController index with u3ayear = '.$f3->get('SESSION.u3ayear'),$uselog  );	     
	$auth_logger->write( 'In MembersController index with lastu3ayear = '.$f3->get('SESSION.lastu3ayear'),$uselog   );	       
	$auth_logger->write( 'In MembersController index with allowwelcomeemail  = '.$f3->get('SESSION.allowwelcomeemail'),$uselog   );	       
	$auth_logger->write( 'In MembersController index with welcomemail_fromaddress  = '.$f3->get('SESSION.welcomemail_fromaddress'),$uselog   );	       
		   $member = new Member($this->db);
        $f3->set('members',$member->all());  //LEY is this needed
		$auth_logger->write( 'In MembersController index #96 with u3ayear = '.$f3->get('SESSION.u3ayear'),$uselog  );	 
		$auth_logger->write( 'In MembersController index #97 with role = '.$f3->get('SESSION.user_role'),$uselog  );	 
		$f3->set('page_head',"Member List ".$f3->get('SESSION.u3ayear'));
		$f3->set('page_role',$f3->get('SESSION.user_role'));
        $f3->set('message', $f3->get('PARAMS.message'));
		//$f3->set('listn', $f3->get('PARAMS.mylist'));


	 // $f3->set('listnn','member/list.htm');
	$f3->set('view','member/list.htm');
		if($f3->get('SESSION.user_role') =='register') 	{$f3->set('view','attendance/list.htm'); $f3->set('page_head',"Member Attendance List ");}
		$f3->set('SESSION.lastseen',time()); 
			$auth_logger->write( 'In MembersController index #98 with u3ayear = '.$f3->get('SESSION.u3ayear'),$uselog  );	
	}


public function payments ()
		{
	$f3=$this->f3;
		$auth_logger = new MyLog('auth.log');
	$auth_logger->write( 'Entering payments'  );
		   $member = new Member($this->db);
    $f3->set('members',$member->all());
	$f3->set('page_head','Update Payments');
	$f3->set('page_role',$f3->get('SESSION.user_role'));
	if ($f3->get('SESSION.user_role') =='user' ) {//don't allow any changes for standard user so payments not allowed
	$this->f3->reroute('/login');
	}
	$f3->set('message', $f3->get('PARAMS.message'));	//NEEDED in Header 
	$f3->set('view','member/listpaid.htm');
	$f3->set('SESSION.lastseen',time()); 
	$f3->set('u3astartmonth', $f3->get('SESSION.u3astartmonth'));
	}
	public function wherearefees ()
		{
	$f3=$this->f3;
		$auth_logger = new MyLog('auth.log');
	$auth_logger->write( 'Entering wherearefees'  );
		   $member = new Member($this->db);
    $f3->set('members',$member->all());
	$f3->set('page_head','Where are Fees');
	$f3->set('page_role',$f3->get('SESSION.user_role'));
	if ($f3->get('SESSION.user_role') =='user' ) {//don't allow any changes for standard user so payments not allowed
	$this->f3->reroute('/login');
	}
	$f3->set('message', $f3->get('PARAMS.message'));	//NEEDED in Header 
	$f3->set('view','member/listfees.htm');
	$f3->set('SESSION.lastseen',time()); 
	$f3->set('u3astartmonth', $f3->get('SESSION.u3astartmonth'));
	}
/**********  show a grid for the audit trail table IFF logged in with role admin ******/
/**********  show a grid for the audit trail table IFF logged in with role admin ******/

public function trail() {
$f3=$this->f3;
	$auth_logger = new MyLog('auth.log');
	$auth_logger->write( 'Entering trail'  );	
if ($f3->get('SESSION.user_role')==="admin"){
		   $trail = new Trail($this->db);
        $f3->set('trail',$trail->all());
		$f3->set('page_head','Manage Audit Trail List');
		$f3->set('page_role',$f3->get('SESSION.user_role'));
        $f3->set('message', $f3->get('PARAMS.message'));
		//$f3->set('listn', $f3->get('PARAMS.mylist'));
		//$f3->set('listnn','member/list.htm');
		$f3->set('view','admin/trail.htm');
		$f3->set('SESSION.lastseen',time()); 

}
}
function exports(){// generate all the likely export files for downloading
	$f3=$this->f3;	
//require('vendor/fpdf.php');
	$admin_logger = new MyLog('admin.log');
	$uselog=$f3->get('uselog');
	//$uselog =false;
	//$f3->set('message', $f3->get('PARAMS.message'));
	$u3ayear= $f3->get('SESSION.u3ayear');
	
	//$admin_logger->write('in MemberController $u3ayear='.$u3ayear,$uselog);
	// Now fetch the required data sets-  all, Admin Team , GL's
	//$result=$this->emails('all');
$dldir=$f3->get('downloads');
$admin_logger->write('in exports dldir = '.$dldir,$uselog);
	/***********   all these replaced by similar functions for PDFs in AjaxController ***************
	
	$result=$this->emails('all');
		$resp=$this->writeemails($result,'all');
			//$this->writeemailpdf($result,'all');
			//$this->writeemailpdf0($result,'all0');
		$result=$this->emails('cm');
		$resp=$this->writeemails($result,'cm');
		
		$result=$this->emails('gl');
		$resp=$this->writeemails($result,'gl');
		//$admin_logger->write('in exports written emails gl resp=  '.$resp,$uselog);
		$result=$this->emails('all',"('N')");
		$resp=$this->writeemails($result,'unpaid');
		$result=$this->emails('all',"('W')");
		$resp=$this->writeemails($result,'willpay');

		$lastu3ayear = $f3->get('lastu3ayear'); //Last year's members
		$result=$this->emails('all',"('Y')",$lastu3ayear);
		$resp=$this->writeemails($result,'lastyear');
		************************************************************************/
		
		$f3->set('view','member/exports.htm'); 
		$f3->set('page_head','Primary Member Lists');
		$f3->set('page_role',$f3->get('SESSION.user_role'))		;
		$f3->set('fyear', getdate()['year']);
		
	
	}

	

/********  Replaced by PDF functions in AjacController
function writeemails($data,$theset) {
		$f3=$this->f3;

		$dldir=$f3->get('BASE').$f3->get('downloads');
		$resp=99;
		
		//$resp=$f3->write($dldir.'/email_list_'.$theset.'.csv',var_export($data,TRUE));
		
			$out = "";
		foreach($data as $arr) {
				$out .= implode(",", $arr)."\n" ;
				}
		$resp=$f3->write($dldir.'/email_list_'.$theset.'.csv',$out.",,,,");
		return $resp;
	
	
		}
function emails($setofmembers='all',$paidstatus="('Y','N','W')",$u3ayear=NULL ) {
    	$f3=$this->f3;       
		$db=new DB\SQL(
            $f3->get('db_dns') . $f3->get('db_name'),
            $f3->get('db_user'),
            $f3->get('db_pass')
        );	
	 $u3ayear = isset($u3ayear) ? $u3ayear :$f3->get('u3ayear');

  // $u3ayear= $f3->get('u3ayear');
   $emailfilename='membersemails-'.$setofmembers.'.csv';
	switch($setofmembers){
		case 'all':
		$thesql="select forename,surname,location,membtype,membnum,email from members where u3ayear='".$u3ayear."' and status='Active' and paidthisyear in ".$paidstatus." order by membnum ASC";
		
		break;
		case 'cm':
		$thesql="select forename,surname,location,membtype,membnum,email from members where u3ayear='".$u3ayear."' and status='Active' and membtype in ('AT','ATGL')"." order by membnum ASC";
		
		break;
		case 'gl':
		$thesql="select forename,surname,location,membtype,membnum,email from members where u3ayear='".$u3ayear."' and status='Active' and membtype in ('GL','ATGL')"." order by membnum ASC";
		
		break;


		default:
		$thesql="select forename,surname,location,membtype,membnum,email from members where u3ayear='never'"." order by membnum ASC";
		break;
		}
		
		return $db->exec($thesql);

		
        
    }
	************/
	function reverserollover1 () {
	$f3=$this->f3;
		$uselog=$f3->get('uselog');
		$admin_logger = new MyLog('admin.log');
		$admin_logger->write( 'Entering reverseRollover1' ,$uselog );
	if ($f3->get('SESSION.user_role')=='editor'||$f3->get('SESSION.user_role')=='admin') {
		$f3->set('page_head',"Reverse Rollover ".$f3->get('SESSION.u3ayear'));
		$f3->set('page_role',$f3->get('SESSION.user_role'));
		$f3->set('message', $f3->get('PARAMS.message'));	//NEEDED in Header 
		$f3->set('view','member/reverse1.htm');
		$f3->set('SESSION.lastseen',time()); 
		$f3->set('revu3ayear',$f3->get('SESSION.u3ayear')); 
	
	} else{
			$f3->set('page_head',"Member List ".$f3->get('SESSION.u3ayear'));
		$f3->set('page_role',$f3->get('SESSION.user_role'));
		$f3->set('message', $f3->get('PARAMS.message'));	//NEEDED in Header 
		$f3->set('view','member/list.htm');
		$f3->set('SESSION.lastseen',time()); 
	}
	}
	function reverserollover2 () {
		$f3=$this->f3;
		$uselog=$f3->get('uselog');
		$admin_logger = new MyLog('admin.log');
		$admin_logger->write( 'Entering reverseRollover2' ,$uselog );
		if ($f3->get('SESSION.user_role')=='editor'||$f3->get('SESSION.user_role')=='admin') {
		$admin_logger->write( 'Entering reverseRollover2 B' ,$uselog );
		
		$db=$this->db;
		//$member = new Member($this->db);
		$thisnewu3ayear= $f3->get('SESSION.u3ayear');
		$theoldu3ayear= $f3->get('SESSION.lastu3ayear');
			$db->begin();
$delsql = <<<EOT
	delete from members  where u3ayear ="$thisnewu3ayear" and status ="Active"

EOT;
$del2sql = <<<EOT
	delete from feespertypes  where acyear ="$thisnewu3ayear"

EOT;

		$admin_logger->write( 'In ReverseRollover Pt1 delsql='.$delsql ,$uselog );	
		$copyresult=$db->exec($delsql);
		$copyresult=$db->exec($del2sql);
		$db->commit();
		$admin_logger->write( 'In ReverseRollover Pt1 copyresult='.$copyresult ,$uselog );	
}		
		$f3->set('page_head',"Member List ".$f3->get('SESSION.u3ayear'));
		$f3->set('page_role',$f3->get('SESSION.user_role'));
		$f3->set('message', $f3->get('PARAMS.message'));	//NEEDED in Header 
		$f3->set('view','member/list.htm');
		$f3->set('SESSION.lastseen',time()); 		
	
	}
function rollover () {
		$f3=$this->f3;
		$uselog=$f3->get('uselog');
		$admin_logger = new MyLog('admin.log');
		$admin_logger->write( 'Entering Rollover' ,$uselog );
		
		$db=$this->db;
		//$member = new Member($this->db);
		$thisnewu3ayear= $f3->get('SESSION.u3ayear');
		$theoldu3ayear= $f3->get('SESSION.lastu3ayear');
		$fyear = date("Y-m-d H:i:s");
/***************** put rollover date into datepaid field even though paid is "N"
***************/
	$options= new Option($this->db);
	$monthoption=$options->find("optionname='u3a_year_start_month'");
	$rollovermonth = $monthoption[0]->optionvalue;
	$rolloverdate= date("Y-m-d H:i:s",mktime(0,0,0,$rollovermonth,1, date("Y")));

		$db->begin();
		$copysql = <<<EOT
				INSERT INTO members(surname, forename, membnum, 
				phone, mobile, email, membtype, location, paidthisyear, 
				amtpaidthisyear, datejoined, datepaid, feewhere, fyear, 
				u3ayear, status, created_at, updated_at) select surname, forename, membnum, 
				phone, mobile, email, membtype, location, 'N', 
				0, datejoined, "$rolloverdate", " ", "$fyear", "$thisnewu3ayear",
				status, now(), now() from members m2 where m2.u3ayear ="$theoldu3ayear" and status ='Active'
EOT;


		$admin_logger->write( 'In Rollover Pt1 copysql='.$copysql ,$uselog );	
		$copyresult=$db->exec($copysql);
		
	
	//$admin_logger->write( 'In Rollover Pt1 copyresult='.$copyresult ,$uselog );
	//$admin_logger->write( 'In Rollover Pt1 log '.$db->log() ,$uselog );
/**  Now make non payer type Paid with today's date  *****/

		$pt2sql = <<<EOT
		UPDATE members set paidthisyear='Y', datepaid =CURDATE()  where u3ayear ="$thisnewu3ayear" and status ='Active' and membtype in ('AT','ATS','GL','GLS','ATGL')
EOT;
//$admin_logger->write( 'In Rollover Pt2sql='.$pt2sql ,$uselog );	
		$pt2result=$db->exec($pt2sql);
	
		//$admin_logger->write( 'In Rollover Pt2 pt1result='.$pt2result ,$uselog );
		//$admin_logger->write( 'In Rollover Pt2 log '.$db->log() ,$uselog );
/**  Now migrate  MJL2 to M *****/

		$pt3sql = <<<EOT
		UPDATE members set membtype='M',paidthisyear='N',amtpaidthisyear=0  where u3ayear ="$thisnewu3ayear" and status ='Active' and membtype = 'MJL2'
EOT;
		$pt3result=$db->exec($pt3sql);

/**  Now migrate  MJL1 to MJL2 and paid with amt 0 *****/
//		UPDATE members set membtype='MJL2',paidthisyear='Y',amtpaidthisyear=0  where u3ayear ="$thisnewu3ayear" and status ='Active' and membtype ='MJL1'
		$pt4sql = <<<EOT
		UPDATE members set membtype='MJL2',paidthisyear='Y',amtpaidthisyear=0 , datepaid =CURDATE() where u3ayear ="$thisnewu3ayear" and status ='Active' and membtype ='MJL1'
EOT;
		$pt4result=$db->exec($pt4sql);
		/**  Now migrate  MJL1 to MJL2 and paid with amt 0 *****/
		$pt5sql = <<<EOT
		insert into feespertypes (membtype ,feetopay ,firstyearfee,acyear) 
		select membtype ,feetopay ,firstyearfee,"$thisnewu3ayear" from feespertypes f2 where f2.acyear ="$theoldu3ayear"
EOT;
		$pt5result=$db->exec($pt5sql);
$db->commit();
		
		$f3->set('page_head',"Fees Table ".$f3->get('SESSION.u3ayear'));
		$f3->set('page_role',$f3->get('SESSION.user_role'));
		$f3->set('message', $f3->get('PARAMS.message'));	//NEEDED in Header 
		$f3->set('view','admin/fees.htm'); //Reroute to show the fees table automatically to allow edits
		$f3->set('SESSION.lastseen',time()); 

	}
} // end of Class MemberController