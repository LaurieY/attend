<?php

class MyAjax extends Controller {
//	protected $f3;
//	protected $db;

	function beforeroute() {
//$f3->set('message','');
	$f3=$this->f3;
	$auth_logger = new Log('auth.log');
	$auth_logger->write( 'Entering MyAjax beforeroute URI= '.$f3->get('URI'  ) );
	$auth_logger->write( "MyAjax beforeroute  Session user_id = ".$f3->get('SESSION.user_id')); 
	//$auth_logger->write( "MyAjax beforeroute $_SESSION = ".
	//var_export($_SESSION);
	//$auth_logger->write( "MyAjax beforeroute SESSION = ".var_export($f3->get('SESSION')));
	//$auth_logger->write( "MyAjax beforeroute $this = ".var_export($this->f3,true));
	//var_export($f3->get('SESSION'));
	}

	function afterroute() {
//		echo Template::instance()->render('layout.htm');	
	}

	function __construct() {

        $this->f3=Base::instance();
$f3=$this->f3;
        $db=new DB\SQL(
            $f3->get('db_dns') . $f3->get('db_name'),
            $f3->get('db_user'),
            $f3->get('db_pass')
        );	

		$this->f3=$f3;	
		$this->db=$db;
	}
	public function members() //for POST membergrid
	 {
	 $f3=$this->f3;
		 $members =	new Member($this->db);
	 
	 $f3->set('page_head','User List');
	 $admin_logger = new Log('admin.log');
	$admin_logger->write('in fn members');
	$admin_logger->write('in fn members '.get_class($this->db)." Parent is ".get_parent_class($this->db)."\n");
	 $admin_logger->write( "In Ajax POST membergrid fn members Session user_id = ".$f3->get('SESSION.user_id')); 

/**	$class_methods = get_class_methods('DB\SQL');
	foreach ($class_methods as $method_name) {
    $admin_logger->write('in fn members class methods '.$method_name."\n");
	}**/
	
	$admin_logger->write('GET _search = '.$_GET['_search']."\n");
if ($f3->get('GET._search')=='true'){
// set up filters
$filters = $f3->get('GET.filters');
$admin_logger->write('in fn members filters= '.$filters."\n");

$where = "";
        if (isset($filters)) {
            $filters = json_decode($filters);
            $where = " where ";
            $whereArray = array();
            $rules = $filters->rules;
/********************************/
 $groupOperation = $filters->groupOp;
        foreach($rules as $rule) {

            $fieldName = $rule->field;
            $admin_logger->write('in fn members old fieldname = '.$fieldName."\n");
			$admin_logger->write('in fn members old fielddata = '.$rule->data."\n");
			//$admin_logger->write('in fn members quoted fielddata = '.$this->db->quote($rule->data)."\n");
			$fieldData =$rule->data;
			//$fieldData =str_replace("'", "",$this->db->quote($rule->data)); // not necessary, the quote only add spurious quoted that I have to remove
			$admin_logger->write('in fn members new fielddata = '.str_replace("'", "",$fieldData)."\n");
		   //$fieldData = mysqli_real_escape_string($members,$rule->data); 
            switch ($rule->op) {
           case "eq":
                $fieldOperation = " = ".$fieldData."";
                break;
           case "ne":
                $fieldOperation = " != ".$fieldData."";
                break;
           case "lt":
                $fieldOperation = " < '".$fieldData."'";
                break;
           case "gt":
                $fieldOperation = " > '".$fieldData."'";
                break;
           case "le":
                $fieldOperation = " <= '".$fieldData."'";
                break;
           case "ge":
                $fieldOperation = " >= '".$fieldData."'";
                break;
           case "nu":
                $fieldOperation = " = ''";
                break;
           case "nn":
                $fieldOperation = " != ''";
                break;
           case "in":
                $fieldOperation = " IN (".$fieldData.")";
                break;
           case "ni":
                $fieldOperation = " NOT IN '".$fieldData."'";
                break;
           case "bw":
                $fieldOperation = " LIKE '".$fieldData."%'";
                break;
           case "bn":
                $fieldOperation = " NOT LIKE '".$fieldData."%'";
                break;
           case "ew":
                $fieldOperation = " LIKE '%".$fieldData."'";
                break;
           case "en":
                $fieldOperation = " NOT LIKE '%".$fieldData."'";
                break;
           case "cn":
                $fieldOperation = " LIKE '%".$fieldData."%'";
                break;
           case "nc":
                $fieldOperation = " NOT LIKE '%".$fieldData."%'";
                break;
            default:
                $fieldOperation = "";
                break;
                }
            if($fieldOperation != "") $whereArray[] = $fieldName.$fieldOperation;
        }
        if (count($whereArray)>0) {
            $where .= join(" ".$groupOperation." ", $whereArray);
        } else {
            $where = "";
        }

/*******
           foreach($rules as $rule) {
                $whereArray[] = $rule->field." like '%".$rule->data."%'";
            }
            if (count($whereArray)>0) {
                $where .= join(" and ", $whereArray);
            } else {
                $where = "";
            }


*********/ 
        }   
	$admin_logger->write('in fn members where= '.$where."\n");
	/**********************  Now get the resulting xml via SWL using this where selection ******/
	$whh =	$this->getresult_where($where);
	
	$admin_logger->write('in fn members where result = '.$whh."\n");
echo $whh;
}
else {
echo $this->getresult_where("where 1");
}  //end of else of _search
} // end of function  members


private function getresult_where( $where_to_use)
{
 $f3=$this->f3;
  $admin_logger = new Log('admin.log');
header("Content-type: text/xml;charset=utf-8");
 $page = $_GET['page']; 
 
 
 $sidx = $_GET['sidx']; 
 $sord = $_GET['sord']; 
 //$fred = $f3->get('db_user');
 $db = mysqli_connect('localhost', $f3->get('db_user'),  $f3->get('db_pass'),$f3->get('db_name')) or die("Connection Error: " . mysql_error()); 
 //mysqli_select_db($f3->get('db_name')) or die("Error connecting to db."); 
 // calculate the number of rows for the query. We need this for paging the result 
$result = mysqli_query($db,"SELECT COUNT(*) AS count FROM members ".$where_to_use); 
$row = mysqli_fetch_array($result,MYSQL_ASSOC); 
$count = $row['count']; 
  $limit = $count;  // temporary force all rows
//  $limit = $_GET['rows'];  // temporary comment out  to force all rows need this if non-local grid, i.e. loadOnce=false

// calculate the total pages for the query 
if( $count > 0 && $limit > 0) { 
              $total_pages = ceil($count/$limit); 
} else { 
              $total_pages = 0; 
} 

// if for some reasons the requested page is greater than the total 
// set the requested page to total page 
if ($page > $total_pages) $page=$total_pages;
 
// calculate the starting position of the rows 
$start = $limit*$page - $limit;
 
// if for some reasons start position is negative set it to 0 
// typical case is that the user type 0 for the requested page 
if($start <0) $start = 0; 
 
// the actual query for the grid data 
//$SQL = "SELECT id,surname	, forename, membnum FROM members ORDER BY $sidx $sord LIMIT $start , $limit"; 
//$SQL = "SELECT id, FROM members ORDER BY $sidx $sord LIMIT $start , $limit"; 

/************Get Total paid for this selection  ************/
 $SQL_total="select sum(amtpaidthisyear)  as amt from members ".$where_to_use;
 $result = mysqli_query($db, $SQL_total ) or die("Couldn't execute query.".mysql_error()); 
 $row = mysqli_fetch_array($result,MYSQL_ASSOC); 
 $amt_total = $row['amt'];
 
 
 $SQL = "SELECT id,surname ,forename,membnum ,phone,mobile,email,membtype,location,paidthisyear,amtpaidthisyear,datejoined FROM members  ".$where_to_use." ORDER BY $sidx $sord LIMIT $start , $limit"; 
 $admin_logger->write('in getresult_where SQL = '. $SQL."\n");
 $result = mysqli_query( $db,$SQL ) or die("Couldn't execute query.".mysql_error()); 
$s = "<?xml version='1.0' encoding='utf-8'?>";
$s .=  "<rows>";
$s .= "<page>".$page."</page>";
$s .= "<total>".$total_pages."</total>";
$s .= "<records>".$count."</records>";

$s .= '<userdata name="email">Total</userdata>';   # name = target column's name
$s .= '<userdata name="amtpaidthisyear">'.$amt_total.'</userdata>'; 
   
 
// be sure to put text data in CDATA
/*
while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
    $s .= "<row id='". $row['id']."'>";  
	
    $s .= "<cell>". $row['surname']."</cell>"; 
	$s .= "<cell>". $row['forename']."</cell>";
	$s .= "<cell>". $row['membnum']."</cell>"; 	
   // $s .= "<cell>". $row['mobile']."</cell>";
	//$s .= "<cell>". $row['phone']."</cell>";
	//$s .= "<cell>". $row['email']."</cell>";
    $s .= "</row>";
}  
*/
 while($row = mysqli_fetch_array($result,MYSQL_ASSOC)) {
   foreach($row as $key => $value)
      {if ($key=='id') {$s .= "<row id='". $value."'>";  }
	  else
	  { $s .= "<cell>". "$value"."</cell>";
	  
	  }
         //$key holds the table column name
       
   
   }
$s .= "</row>"; 
	
	} 
$s .= "</rows>"; 

$admin_logger->write('in getresult_where result = '.$s."\n");
	return $s;

	
}
public function users()
	 {
	 $f3=$this->f3;
	 
	 $f3->set('page_head','User List');

header("Content-type: text/xml;charset=utf-8");
 $page = $_GET['page']; 
 $limit = $_GET['rows']; 
 $sidx = $_GET['sidx']; 
 $sord = $_GET['sord']; 
 //$fred = $f3->get('db_user');
 $db = mysql_connect('localhost', $f3->get('db_user'),  $f3->get('db_pass')) or die("Connection Error: " . mysql_error()); 
 mysql_select_db($f3->get('db_name')) or die("Error connecting to db."); 
 // calculate the number of rows for the query. We need this for paging the result 
$result = mysql_query("SELECT COUNT(*) AS count FROM mem_users"); 
$row = mysql_fetch_array($result,MYSQL_ASSOC); 
$count = $row['count']; 
 
// calculate the total pages for the query 
if( $count > 0 && $limit > 0) { 
              $total_pages = ceil($count/$limit); 
} else { 
              $total_pages = 0; 
} 
 
// if for some reasons the requested page is greater than the total 
// set the requested page to total page 
if ($page > $total_pages) $page=$total_pages;
 
// calculate the starting position of the rows 
$start = $limit*$page - $limit;
 
// if for some reasons start position is negative set it to 0 
// typical case is that the user type 0 for the requested page 
if($start <0) $start = 0; 
 
// the actual query for the grid data 
//$SQL = "SELECT id,surname	, forename, membnum FROM members ORDER BY $sidx $sord LIMIT $start , $limit"; 
//$SQL = "SELECT id, FROM members ORDER BY $sidx $sord LIMIT $start , $limit"; 
 $SQL = "SELECT id,username ,email,role FROM mem_users ORDER BY $sidx $sord LIMIT $start , $limit"; 
$result = mysql_query( $SQL ) or die("Couldn't execute query.".mysql_error()); 
$s = "<?xml version='1.0' encoding='utf-8'?>";
$s .=  "<rows>";
$s .= "<page>".$page."</page>";
$s .= "<total>".$total_pages."</total>";
$s .= "<records>".$count."</records>";

//$s .= '<userdata name="email">Total</userdata>';   # name = target column's name
//$s .= '<userdata name="amtpaidthisyear">1b2b3b</userdata>';
   
 
// be sure to put text data in CDATA

 while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
   foreach($row as $key => $value)
      {if ($key=='id') {$s .= "<row id='". $value."'>";  }
	  else
	  { $s .= "<cell>". "$value"."</cell>";
	  
	  }
         //$key holds the table column name
       
   
   }
$s .= "</row>"; 
	
	} 
$s .= "</rows>"; 
	echo $s;
} // end of function  users
public function edituser()
	 {
	 $f3=$this->f3; 
	 	$admin_logger = new Log('admin.log');
	$admin_logger->write('in edituser');
	// $memuser = new DB\SQL\Mapper($this->db, 'mem_users',array("id","username","email","role")); 
	// $f3->get('POST.oper');

	  //$this->f3->set('mem_users',$user->all());
	// echo (' POST.oper = '.$f3->get('POST.oper'));
	$mem_user =	new User($this->db);
 $f3->set('mem_user',$mem_user);
	 switch ($f3->get('POST.oper')) {
    case "add":
        // do mysql insert statement here
		$mem_user->copyfrom('POST');
		$salt=$f3->get('security_salt'); 
		$encrypt_pwd =crypt($mem_user->password, '$2y$12$' . $salt); //generate the password
		
		$admin_logger->write('in edituser uname '.$mem_user->username);
		$admin_logger->write('in edituser pwd '.$mem_user->password);
		$mem_user->password = $encrypt_pwd ;
		$admin_logger->write('in edituser pwd '.$mem_user->password);
		$admin_logger->write('in edituser pwd'.$mem_user->password."##\n");
		$mem_user->save();
    break;
    case "edit":
		  
		  
		 // $f3->get('mem_user')->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ) );
		  $mem_user->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ) );
	$admin_logger->write('in edituser '.$f3->get('mem_user')->username);
	  $mem_user->copyfrom('POST');
	
	  $mem_user->update();
        // do mysql update statement here
	//	/
    break;
    case "del":
        // do mysql delete statement here
    break;
}
	// echo $f3->get('POST.oper');
	}

public function editmember()
	 {
	 $f3=$this->f3; 
	 	$admin_logger = new Log('admin.log');
		$f3->set('admin_log',$admin_logger);
	$admin_logger->write('in editmember');

	$members1 =	new Member($this->db);
	$members =	new Member($this->db);
	$trail = new Trail($this->db);  // audit trail
 $f3->set('members',$members);
	 switch ($f3->get('POST.oper')) {
    case "add":
    
		
		/******  Find the next membership number as the highest+1**/
	
	
	
	$result=$this->db->exec('SELECT membnum FROM members order by membnum DESC LIMIT 1'); 
	

	$admin_logger->write('in addmember db log = '.$this->db->log()."\n");


	$admin_logger->write('in addmember maxmembnum row = '.$result[0]['membnum']."\n");

	$max_membnum = ((int) $result[0]['membnum'])+1;

		$members->copyfrom('POST');
		$admin_logger->write('in addmember maxmembnum = '.$members->membnum."\n");
		$members->membnum=$max_membnum;
		
		
		$admin_logger->write('in addmember paidthisyear = '.$members->paidthisyear);
		if ($members->paidthisyear=="Y")	{ 
		$thismember= $members->membnum;
		/***  calculate the amount paid  if added as zero ******/
		if ($members->amtpaidthisyear> 0) {$admin_logger->write('in addmember amountpaid = '.$members->amtpaidthisyear);
			}	
		
		else {
		$admin_logger->write('in addmember NO amount paidthisyear ');
		$feespertypes = new \DB\SQL\Mapper($this->db, 'feespertypes');
		$feespertypes->load(array('membtype =:membtype',array(':membtype'=> $f3->get('POST.membtype')) ) );
		$feetopay = $feespertypes->feetopay;
		$members->amtpaidthisyear = $feetopay;
		}}
		$admin_logger->write('in addmember surname '.$members->surname);
		$admin_logger->write('in addmember Forename '.$members->forename);
		
		$trail->copyfrom('POST');	
		$admin_logger->write('in addmember trail Surname '.$trail->surname);
		$admin_logger->write('in addmember trail editor/user_id will be'.$f3->get('SESSION.user_id'  ));
		var_dump($trail);
		var_dump($members);
		var_dump($f3->get('SESSION'));
		$members->save();
		
		$trail->change="add";
		$trail->editor=$f3->get('SESSION.user_id'  );
		//$trail->editor='laurie';
		$admin_logger->write('in addmember trail editor now  '.$trail->editor);
		$trail->save();
    break;
    case "edit":   //************************************ EDIT **//
	$members->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ) ); //this did work but its not the same as the paid code

		
	$admin_logger->write('in editmember for '.$members->surname.' membnum '.$members->membnum.' paidthis year '.$members->paidthisyear);
	$members->amtpaidthisyear=$this->get_amt_paid($members,($f3->get('POST.paidthisyear')=="Y"));
	
	
	/*********IF the field paidthisyear has been changed from N to Y then also update the amtpaidthisyear using feespertypes table *****/
	$admin_logger->write('in editmember after get_amt_paid '.$members->surname.' membnum '.$members->membnum.' amtpaidthis year '.$members->amtpaidthisyear);
	$admin_logger->write('In editmember membnum is '.$members->membnum. ' and of type '.gettype($members->membnum));
	
	
	//var_dump($members);  //LEY dumps in the http response
	
		$members->update();
	$xnum= $members->membnum;
								$admin_logger->write('In editrow xnum is '.$xnum. ' and of type '.gettype($xnum));
   $xpaid= $members->paidthisyear;
   $xpay= $members->amtpaidthisyear;
   //echo "membnum:".$xnum.",paidthisyear:".$xpaid.",amtpaidthisyear:".$xpay;
	 $arr = array('membnum' => $xnum, 'paidthisyear' => $xpaid, 'amtpaidthisyear' => $xpay);
	 $arrencoded= json_encode($arr);
	
	 $admin_logger->write('in editmember after jsonencode '.$arrencoded);
   echo $arrencoded;
        // do mysql update statement here
	//	/
    break;
    case "del":
        // do mysql delete statement here
		$members->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ) );
		$members->erase();
    break;
}
	// echo $f3->get('POST.oper');
	}
	/**************** get_amt_paid ******/
 function  get_amt_paid($members,$topay) {
		$f3=$this->f3; 
		$admin_logger=$f3->get('admin_log');
		$wasnotpaid=false;
		if ($members->paidthisyear=="N")	{$wasnotpaid=true;}
	/**** now fetch the existing row to check if paidthisyear is about to change ****/
	$admin_logger->write('in get_amt_paid for paidthisyear = '.$members->paidthisyear);
	$admin_logger->write('in get_amt_paid for wasnotpaid = '.$wasnotpaid);
	$admin_logger->write('in get_amt_paid for topay = '.$topay);	
	//$admin_logger->write('In get_amt_paid1 membnum is '.$members->membnum. ' and of type '.gettype($members->membnum));
		//$thismember= $members->membnum;
		$members->copyfrom('POST');
	// ********* calculate amount paid
	//$feespertpes =	new Feespertypes($this->db);
		$feespertypes = new \DB\SQL\Mapper($this->db, 'feespertypes');
		$feespertypes->load(array('membtype =:membtype',array(':membtype'=> $f3->get('POST.membtype')) ) );
	$admin_logger->write('in get_amt_paid /feespertype ='.$feespertypes->membtype.' feetopay '.$feespertypes->feetopay);
	//$admin_logger->write('In get_amt_paid2 membnum is '.$members->membnum. ' and of type '.gettype($members->membnum));
		//$feetopay = $feespertypes->feetopay;
		if($wasnotpaid && $topay)  return($feespertypes->feetopay);
		else return($members->amtpaidthisyear); //i.e. do not change amtpaidthis year
		
		
		//if(!$wasnotpaid && ($members->paidthisyear=="N")) { return(0);}
	
}	
function markpaid() { 
	 $f3=$this->f3; 
	
	 	$admin_logger = new Log('admin.log');
		$f3->set('admin_log',$admin_logger);
		$admin_logger->write('in markpaid membnum='.$this->f3->get('POST.membnum') );
		$members =	new Member($this->db);
		$members->load(array('membnum =:id',array(':id'=> $f3->get('POST.membnum')) ));
		$admin_logger->write('in markpaid after get_amt_paid '.$members->surname.' membnum '.$members->membnum.' amtpaidthis year '.$members->amtpaidthisyear);
								$admin_logger->write('In markpaid membnum is '.$members->membnum. ' and of type '.gettype($members->membnum));
	
	//var_dump($members);
		//var_dump($members);
		//var_dump($POST);
		
		
		$members->amtpaidthisyear=$this->get_amt_paid($members,true);
		$members->paidthisyear='Y';
		//$thismember= $members->membnum;
		$admin_logger->write('end of markpaid membnum='.$members->membnum." paid= ".$members->paidthisyear." amtpaid = ".$members->amtpaidthisyear );
		$admin_logger->write('In markpaid membnum is '.$members->membnum. ' and of type '.gettype($members->membnum));
		$members->update();
	//echo('Done that');	
	//echo $this->getresult_where("where 1");// No only return the changed contents of that	one row?
   $xnum= $members->membnum;
   $admin_logger->write('In markpaid xnum is '.$xnum. ' and of type '.gettype($xnum));
   $xpaid= $members->paidthisyear;
   $xpay= $members->amtpaidthisyear;
   //echo "membnum:".$xnum.",paidthisyear:".$xpaid.",amtpaidthisyear:".$xpay;
	 $arr = array('membnum' => $xnum, 'paidthisyear' => $xpaid, 'amtpaidthisyear' => $xpay);
	 	 $arrencoded= json_encode($arr);
	 $admin_logger->write('in editmember after jsonencode '.$arrencoded);
   echo $arrencoded;
   //echo json_encode($arr);
	}
} // end of class