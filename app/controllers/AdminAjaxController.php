<?php

class AdminAjaxController extends Controller {
	
	function afterroute() {

	}
	
	function feesgrid() {
	$f3=$this->f3;
	$fees =	new Fees($this->db);
	$uselog=$f3->get('uselog');
	$admin_logger = new MyLog('admin.log');
	$admin_logger->write('in feesgrid ',$uselog);
	//$f3->set('page_head','Fees List');	
	header("Content-type: text/xml;charset=utf-8");
	$page = $_GET['page']; 
	$limit = $_GET['rows']; 
	$sidx = $_GET['sidx']; 
	$sord = $_GET['sord']; 
	// get count of records
	$u3ayear=$f3->get('SESSION.u3ayear');
	$admin_logger->write('in fn feesgrid u3ayear= '.$u3ayear,$uselog);
	$count=$fees->count("acyear = '".$u3ayear."'");
	if( $count > 0 && $limit > 0) { 
              $total_pages = ceil($count/$limit); 
	} else { 
              $total_pages = 0; 
		}
		$admin_logger->write('in fn feesgrid count= '.$count,$uselog);
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
	
	$fees->load("acyear = '".$u3ayear."'");
	$i=0;
	while ( !$fees->dry() ) {  // gets dry when we passed the last record
	//$admin_logger->write('in fn feesgrid with i='.$i,$uselog);
	$response->rows[$i]['id']=$fees['id'];
	$response->rows[$i]['cell']=array($fees['membtype'],$fees['feetopay'],$fees['firstyearfee'],$fees['acyear']);
	// moves forward even when the internal pointer is on last record
	$i++;
	$fees->next();
					}
	echo json_encode($response);
	} // end of feesgrid

public function editfees() {
	 $f3=$this->f3; 
	 	$admin_logger = new MyLog('admin.log');
		$uselog=$f3->get('uselog');
		$admin_logger->write('in editfees');	
		$fees =	new Fees($this->db);
 
	 switch ($f3->get('POST.oper')) {
    case "add":  //**********************   ADD an fees
	
		$fees->copyfrom('POST');
	
		$admin_logger->write('in editfees membtype '.$fees->membtype,$uselog);
		//$admin_logger->write('in editfees feesvalue '.$feess->feesvalue,$uselog);
		
		$fees->save();
		
    break;
    case "edit":
		  
		  
		 // $f3->get('mem_user')->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ) );
		  $fees->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ) );
	$admin_logger->write('in editfees membtype '.$fees->membtype,$uselog);
		
	  $fees->copyfrom('POST');
	
	  $fees->update();
        // do mysql update statement here
	//	/
    break;
    case "del":
	$fees->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ) );
	
		
		$admin_logger->write('in delfees membtype '.$fees->membtype,$uselog);
		
		
		$fees->erase();
		
	
        // do mysql delete statement here
    break;
}		
	
	}
function editmailinglist () {
		 $f3=$this->f3; 
	 	$admin_logger = new MyLog('admin.log');
		$uselog=$f3->get('uselog');
		$admin_logger->write('in editmailinglist');	
		$mailinglist =	new Mailinglist($this->db);
 
	 switch ($f3->get('POST.oper')) {
    case "add":  //**********************   ADD an mailinglist
	
		$mailinglist->copyfrom('POST');
	
		$admin_logger->write('in editmailinglist membtype '.$mailinglist->memberselection,$uselog);
		//$admin_logger->write('in editmailinglist feesvalue '.$feess->feesvalue,$uselog);
		
		$mailinglist->save();
		
    break;
    case "edit":
		  
		  
		 // $f3->get('mem_user')->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ) );
		  $mailinglist->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ) );
	$admin_logger->write('in editmailinglist membselection '.$mailinglist->memberselection,$uselog);
		
	  $mailinglist->copyfrom('POST');
	
	  $mailinglist->update();
        // do mysql update statement here
	//	/
    break;
    case "del":
	$mailinglist->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ) );
	
		
		$admin_logger->write('in delmailinglist memberselection '.$mailinglist->memberselection,$uselog);
		
		
		$mailinglist->erase();
		
	
        // do mysql delete statement here
    break;
	 }
	}	
function mailinglistgrid () {
	$f3=$this->f3;
	$mailinglist =	new Mailinglist($this->db);
	$uselog=$f3->get('uselog');
	$admin_logger = new MyLog('admin.log');
	$admin_logger->write('in Mailinglist ',$uselog);
	
	header("Content-type: text/xml;charset=utf-8");
	$page = $_GET['page']; 
	$limit = $_GET['rows']; 
	$sidx = $_GET['sidx']; 
	$sord = $_GET['sord']; 
	// get count of records
	//$u3ayear=$f3->get('SESSION.u3ayear');
	$admin_logger->write('in fn Mailinglistgrid ',$uselog);
	//$count=$fees->count("acyear = '".$u3ayear."'");
	$count=$mailinglist->count();
	if( $count > 0 && $limit > 0) { 
              $total_pages = ceil($count/$limit); 
	} else { 
              $total_pages = 0; 
		}
		$admin_logger->write('in fn Mailinglistgrid count= '.$count,$uselog);
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
	
	//$fees->load("acyear = '".$u3ayear."'");
	$mailinglist->load();
	$i=0;
	while ( !$mailinglist->dry() ) {  // gets dry when we passed the last record
	//$admin_logger->write('in fn mailinglistgrid with i='.$i,$uselog);
	$response->rows[$i]['id']=$mailinglist['id'];
	$response->rows[$i]['cell']=array($mailinglist['memberselection'],$mailinglist['mmurl'],$mailinglist['mmlist'],$mailinglist['mmpwd'],$mailinglist['memberquery']);
	// moves forward even when the internal pointer is on last record
	$i++;
	$mailinglist->next();
					}
	echo json_encode($response);
	} // end of mailinglistgrid
	
	}