<?php

class OptionController extends Controller {
	
	function afterroute() {
	echo Template::instance()->render('layout.htm');	
	}
	
	
	
	
	public function optiongrid() {
	$f3=$this->f3;
	$options =	new Option($this->db);
	$uselog=$f3->get('uselog');
	$admin_logger = new MyLog('admin.log');
	$admin_logger->write('in fn optiongrid #128 ',$uselog);
	$f3->set('page_head','Option List');
	
	header("Content-type: text/xml;charset=utf-8");
	$page = $_GET['page']; 
	$sidx = $_GET['sidx']; 
	$sord = $_GET['sord']; 
	// get count of records
	echo "<?xml version='1.0' encoding='utf-8'?><rows><page>1</page><total>1</total><records>1</records><row id='1154'><cell>f</cell><cell>c</cell></row></rows>";
	
	

	
	}
public function index(){
		$f3=$this->f3;
		$uselog=$f3->get('uselog');
		$admin_logger = new MyLog('auth.log');
		$admin_logger->write( 'Entering OptionsControllerindex' , $uselog );
		$f3->set('page_head','Manage Options');
		$f3->set('page_role',$f3->get('SESSION.user_role'));
        $f3->set('message', $f3->get('PARAMS.message'));
		
		$f3->set('view','option/optionlist.htm');
		$f3->set('SESSION.lastseen',time()); 		

}

	/*public   function initu3ayear(){
	$fw=Base::instance();
	//var_export($fw->get('SESSION',false)); //LEY
	
  $today = getdate();
	  $thismon= $today['mon'];
	  $thisyear = (string) $today['year'];
	  $lastyear = (string) $today['year'] -1;
	  $nextyear = (string) $today['year'] +1;
	  $this->load('optionname="u3a_year_start_month"');
	$whichmonth = $this->optionvalue;
	$fw->set('SESSION.u3astartmonth', $whichmonth);
	if(!$fw->exists('SESSION.u3ayear')) {
	  //'select optionvalue from options where optionname ="u3a_year_start_month" ';
	  if ($thismon <$whichmonth)
		$fw->set('SESSION.u3ayear', $lastyear.'-'.$thisyear);
		else
		$fw->set('SESSION.u3ayear',  $thisyear.'-'.$nextyear);
		//print_r($fw->get('u3ayear'));
		}
	return $fw->get('SESSION.u3ayear');
}
public  function initlastu3ayear(){
	$fw=Base::instance();
	if(!$fw->exists('SESSION.lastu3ayear')) {
  $today = getdate();
	  $thismon= $today['mon'];
	  $thisyear = (string) $today['year'];
	  $lastyear = (string) $today['year'] -1;
	  $lastbutoneyear = (string) $today['year'] -2;
	  $whichmonth = $this->optionvalue;
	  if ($thismon <$whichmonth)
		$fw->set('SESSION.lastu3ayear',  $lastbutoneyear.'-'.$lastyear);
		else
		$fw->set('SESSION.lastu3ayear',  $lastyear.'-'.$thisyear);
		}
			return $fw->get('SESSION.lastu3ayear');
}	*****/

	} // end of class