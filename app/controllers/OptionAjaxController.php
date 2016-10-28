<?php

class OptionAjaxController extends Controller {
	
	function afterroute() {

	}
public function editoption()
	 {
	 $f3=$this->f3; 
	 	$admin_logger = new MyLog('admin.log');
		$uselog=$f3->get('uselog');
		$admin_logger->write('in editoption');	
		$options =	new Option($this->db);
 
	 switch ($f3->get('POST.oper')) {
    case "add":  //**********************   ADD an Option
	
		$options->copyfrom('POST');
	
		$admin_logger->write('in editoption optionname '.$options->optionname,$uselog);
		$admin_logger->write('in editoption optionvalue '.$options->optionvalue,$uselog);
		
		$options->save();
		
    break;
    case "edit":
		  
		  
		 // $f3->get('mem_user')->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ) );
		  $options->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ) );
	$admin_logger->write('in editoption optionvalue '.$options->optionvalue,$uselog);
		
	  $options->copyfrom('POST');
	$admin_logger->write('in editoption optionvalue '.$options->optionvalue,$uselog);
	  $options->update();
        // do mysql update statement here
	//	/
    break;
    case "del":
	$options->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ) );
	
		
		$admin_logger->write('in deloption optionname '.$options->optionname,$uselog);
		
		
		$options->erase();
		
	
        // do mysql delete statement here
    break;
}	
	
	
	}
	
	public function optiongrid() {
	$f3=$this->f3;
	$options =	new Option($this->db);
	$uselog=$f3->get('uselog');
	$admin_logger = new MyLog('admin.log');
	//$admin_logger->write('in fn optiongrid #128 ',$uselog);
	$f3->set('page_head','Option List');
	
	header("Content-type: text/xml;charset=utf-8");
	$page = $_GET['page']; 
	$limit = $_GET['rows']; 
	$sidx = $_GET['sidx']; 
	$sord = $_GET['sord']; 
	// get count of records
	
	$count=$options->count();
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
 $response = new stdClass();
// if for some reasons start position is negative set it to 0 
// typical case is that the user type 0 for the requested page 
	if($start <0) $start = 0; 
	$response->page = 1;
	$response->page = $page;
	$response->total = $total_pages;
	$response->records = $count;
	


$options->load();  // by default, loads the 1st record
$i=0;
while ( !$options->dry() ) {  // gets dry when we passed the last record
	$response->rows[$i]['id']=$options['id'];
	$response->rows[$i]['cell']=array($options['optionname'],$options['optionvalue']);
	// moves forward even when the internal pointer is on last record
	$i++;
	$options->next();
}
echo json_encode($response);
	
/*	echo "<?xml version='1.0' encoding='utf-8'?><rows><page>1</page><total>1</total><records>1</records><row id='1154'><cell>f</cell><cell>c</cell></row></rows>";
	
*/	

	
	}
public function index(){
		$f3=$this->f3;
		$uselog=$f3->get('uselog');
		$admin_logger = new MyLog('auth.log');
		$admin_logger->write( 'Entering OptionsControllerindex' , $uselog );
		$f3->set('page_head','Amend Options');
		$f3->set('page_role',$f3->get('SESSION.user_role'));
        $f3->set('message', $f3->get('PARAMS.message'));
		
		$f3->set('view','option/optionlist.htm');
		$f3->set('SESSION.lastseen',time()); 		

}

	
	} // end of class