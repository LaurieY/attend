<?php

class MailchimpController extends Controller {
	private $u3ayear;
	private $paidstatus;
	private $mailchimp_api;
	private $listselect;
	private $listnameandid=array();
	private $fp;
	private $listdetail;
	private $onlyemails=array();
	private $mcemails =array();
	private $emailsadded =array();
	private $emailsdeleted =array();
	
	
	
		function afterroute() {
// allows ajax calls to work
	}
	    function beforeroute(){
		$f3=$this->f3;
	$options =	new Option($this->db);
	$this->u3ayear = $f3->get('SESSION.u3ayear');
	if($this->u3ayear =='') {$options->initu3ayear();		
	$this->u3ayear = $f3->get('SESSION.u3ayear');}
	$this->paidstatus="('Y','N','W')";
	
	$this->mailchimp_api = $options->find("optionname='mailchimp_api'")[0]['optionvalue'];
//	$listselectfile = fopen('docs/´listselect.txt'´'r');
	$buffer = file_get_contents('docs/listselect.txt',NULL,NULL,0,(1024*1024));
	//printf("<br>#32 Buffer = %s <br>",$buffer);
	eval('$this->listselect ='.$buffer.';');
	//	printf("<br>#34 listselect = %s <br>",var_export($this->listselect,true));

/*********	$this->listselect = array(
	//	'U3A Marbella and Inland List'=>array('sqlselect'=>"select email, forename,surname, location, membtype , membnum from members where u3ayear = '".$this->u3ayear."' and status ='Active' and email <> '' and paidthisyear in ".$this->paidstatus."  group by email order by email"),
	//	'U3A Marbella and Inland not yet paid'=>array('sqlselect'=>"select email, forename,surname, location, membtype , membnum from members where u3ayear = '".$this->u3ayear."' and status ='Active' and email <> '' and paidthisyear ='N'  group by email order by email"),
	//	'Group Leaders'=>array('sqlselect'=>"select email, forename,surname, location, membtype , membnum from members where u3ayear = '".$this->u3ayear."' and status ='Active' and email <> '' and paidthisyear in ".$this->paidstatus." and membtype in ('GL','ATGL') group by email order by email"),
	//	'Administration Team'=>array('sqlselect'=>"select email, forename,surname, location, membtype , membnum from members where u3ayear = '".$this->u3ayear."' and status ='Active' and email <> '' and paidthisyear in ".$this->paidstatus." and membtype in ('AT','ATGL') group by email order by email"),
		'TESTnotyetpaid'=>array('sqlselect'=>"select email, forename,surname, location, membtype , membnum from members where u3ayear = '".$this->u3ayear."' and status ='Active' and email <> '' and paidthisyear ='N'  group by email order by email"),
	);
	*********/
	
		$this->fp = fopen('docs/DailyMailchimpCheck.txt', 'w');

//var_export($this);
	}
function run_dailymailchimpcheck() {
	$f3=$this->f3;
	$uselog=$f3->get('uselog');
		require_once 'vendor/drewm/mailchimp-api/src/MailChimp.php';
	fwrite($this->fp,"Start Daily MailChimp Check at ".date('Y-m-d H:i:s') ."\n");
	$mailchimp_logger = new MyLog('mailchimp.log');
	$mailchimp_logger->write('Running dailymailchimpcheck' ,$uselog);
	fwrite($this->fp,"Values Before Changes \n");
	fwrite($this->fp,"------------------------------------------\n\n");
	$listsandnumbers = $this->getlistinfos();
	//	fwrite($this->fp,"var_export of listsandnumbers ".var_export($listsandnumbers,true)." \n");
	$this->writelistinfos($listsandnumbers);
	$this->dailymailchimpcheck($listsandnumbers);
	sleep(30);
	fwrite($this->fp,"Running a second time \n");
	print("<br> Running a second time <br>");
		$this->dailymailchimpcheck($listsandnumbers); // run again
	
	
	printf("<br> List of added emails %s <br>",var_export($this->emailsadded,true));
	
	foreach($this->emailsadded as $emaillist=>$addedemails) {
		fwrite($this->fp,"Emails Added to ". $emaillist." \n");
		fwrite($this->fp,"------------------- \n");
		foreach($addedemails as $addedemail) {
			fwrite($this->fp,$addedemail." \n");
		}
	}
	fwrite($this->fp,"------------------- \n");	
	
	printf("<br> List of deleted emails %s <br>",var_export($this->emailsdeleted,true));
	
	foreach($this->emailsdeleted as $emaillist=>$removedemails) {
		fwrite($this->fp,"Emails Removed from ". $emaillist." \n");
		fwrite($this->fp,"------------------- \n");
		foreach($removedemails as $removedemail) {
			fwrite($this->fp,$removedemail." \n");
		}
	}
	fwrite($this->fp,"------------------- \n");
	
	$repcon= new ReportController();
	fflush($this->fp);
	fclose($this->fp);
	$buffer = file_get_contents('docs/DailyMailchimpCheck.txt',NULL,NULL,0,(1024*1024));
	var_export($buffer);
	if(!defined('NEWLINE'))	define('NEWLINE',"\n");
//$to_emailaddress='laurie2@lyates.com';
$from_emailaddress='laurie@u3a.international';

$subject='Daily Mailchimp Changes';
$headers = 'From: '.$from_emailaddress . "\r\n".
   'Reply-To: webmaster@u3a.international' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
//$headers .= 'Cc: laurie@u3a.es' . "\r\n";
 $mail_content = $buffer;
//		 return mail($to_emailaddress, $subject, $mail_content, "From: $from_emailaddress".NEWLINE."Reply-To:$from_emailaddress".NEWLINE.$add_header);
	$options= new Option($this->db);
	$mcreportlist=$options->find("optionname='mcreportemail'");
	$updatemailchimp = $options->find("optionname='updatemailchimp'")[0]['optionvalue'];
	foreach ($mcreportlist as $mcmail) {
	
		if(strtoupper($updatemailchimp)=='TRUE'){
			mail($mcmail->optionvalue, $subject, $mail_content, $headers);
		}}

print '<br> Daily Mailchimp Check Run Completed<br>';	
	
}
function getmailchimplists(){ //return the json for a jqgrid for mailchimp lists
	//use \DrewM\MailChimp\MailChimp;
		$f3=$this->f3;
		$uselog=$f3->get('uselog');
		$email_logger = new Log('email.log');
		$u3ayear = $f3->get('SESSION.u3ayear');
		$members =	new Member($this->db);
		$options =	new Option($this->db);
		$mailchimp_api = $options->find("optionname='mailchimp_api'")[0]['optionvalue'];
		//$fred= $mailchimp_api[0]['optionvalue'];
		$email_logger->write( "In getmailchimplistsgrid with API".$mailchimp_api , $uselog  );
		require_once 'vendor/drewm/mailchimp-api/src/MailChimp.php';
//setAuth( string $user, [string $password = ''], [string $scheme = self::AUTH_BASIC])
	//	$request = new HTTP_Request2('http://us2.api.mailchimp.com/3.0/lists');

// This will set credentials for Basic auth
	//	$request->setAuth('apikey', $mailchimp_api, HTTP_Request2::AUTH_BASIC);	
	//	$listsjson = $request->send()->getBody();

	$mailchimp = new DrewM\MailChimp\Mailchimp($mailchimp_api);

	$listsphp = $mailchimp->get('lists');
		$email_logger->write( "In getmailchimplistsgrid #63".var_export($listsphp,true) , $uselog  );

		$returnlist = array();
		$i=0;
		foreach ($listsphp['lists'] as $list) {
		$returnlist[$i][ 'name'] = $list['name'];
		$returnlist[$i][ 'id'] = $list['id'];
		$returnlist[$i][ 'beamer_address'] = $list['beamer_address'];
		$returnlist[$i][ 'member_count'] = $list['stats']['member_count'];
		$returnlist[$i][ 'cleaned_count'] = $list['stats']['cleaned_count'];
		$returnlist[$i][ 'unsubscribe_count'] = $list['stats']['unsubscribe_count'];
		
		$i++;
		}		
		//var_export($returnlist);
		$email_logger->write( "In getmailchimplists #38".var_export($returnlist,true), $uselog  );
		return $returnlist;
			}
function getmailchimplistsgrid(){ //return the json for a jqgrid for mailchimp lists
		$f3=$this->f3;
		$uselog=$f3->get('uselog');
		$email_logger = new Log('email.log');
		$emc= new EmailController;
		$returnlist = $this->getmailchimplists();
		$email_logger->write( "In getmailchimplistsgrid #85".var_export($returnlist,true) , $uselog  );
		
		echo $emc->arraytojson($returnlist);
}	

function mailchimplistdetail() {
	$f3=$this->f3; 
	//if ($listnum == '') {$listnum=$f3->get('PARAMS.listnum');}
	$returnlist=$this->getmailchimplistdetail();
			$emc= new EmailController;
			echo $emc->arraytojson($returnlist);
}
/********************
**	Check if the list has already been fetched into $this->listdetail[$listnum]
**
**	OTHERWISE fetch all the members, 100 at a time so as not to exceed reasonable timeouts
**		
*********************/
function getmailchimplistdetail($listnum ='') {  
		$f3=$this->f3; 
		$uselog=$f3->get('uselog');
			$time_start2 = microtime(true);
		$email_logger = new MyLog('email.log');
		if ($listnum == '') {$listnum=$f3->get('PARAMS.listnum');}
		//$email_logger->write('in mailchimplistdetail #133 for list = '.$listnum,$uselog);
		$options =	new Option($this->db);
		$mailchimp_api = $options->find("optionname='mailchimp_api'")[0]['optionvalue'];
		//require_once 'HTTP/Request2.php';
		require_once 'vendor/drewm/mailchimp-api/src/MailChimp.php';
		$mailchimp = new DrewM\MailChimp\MailChimp($mailchimp_api);
		
		$time_end = microtime(true);
	/*		$time = $time_end - $time_start2;
			print " starting to get MC list members after ".$time;/*
*/
		if (!empty($this->listdetail[$listnum])){
			$returnlist=$this->listdetail[$listnum];
			
		}
		else{
		$listcount = $mailchimp->get('lists/'.$listnum)['stats']['member_count'];
		$listcount = $listcount+$mailchimp->get('lists/'.$listnum)['stats']['cleaned_count'];
		$listcount =  $listcount+$mailchimp->get('lists/'.$listnum)['stats']['unsubscribe_count'];
				//var_export($listcount); 
		$per_batch        = 100;
		$total_to_process = $listcount;
		$num_batches      = ceil($total_to_process / $per_batch);

/**
 * Process all relevant rows/entries in batches of size $per_batch
 */
 	$returnlist = array();
for ($page=1; $page <= $num_batches; $page++)
{		$offset = ($page-1)*$per_batch;
$argsarray=array('count'=>$per_batch,'offset'=>$offset,'fields'=>'members.id,members.email_address,members.merge_fields,members.status');
//var_export('lists/'.$listnum.'/members?count='.$per_batch.'&offset='.$offset);
	$listsphp = $mailchimp->get('lists/'.$listnum.'/members',$argsarray,30);
//		$listsphp = $mailchimp->get('lists/'.$listnum.'/members/?count='.$per_batch.'&offset='.$offset.'&fields=members.id',array(),30);
//		$listsphp = $mailchimp->get('lists/'.$listnum.'/members/',,30);
		//var_export($listsphp);
/*			
/*$time_end = microtime(true);
			$time = $time_end - $time_start2;
			print " fetched MC list members after ".$time;
			*/
		$i=$offset;
		foreach ($listsphp['members'] as $list) {
		$returnlist[$i][ 'membname'] = $list['merge_fields']['FNAME'].' '.$list['merge_fields']['LNAME'];
		$returnlist[$i][ 'email'] = $list['email_address'];
		$returnlist[$i][ 'status'] = $list['status'];
		$returnlist[$i][ 'membnum'] = $list['merge_fields']['MNUM'];
		$i++;
		}	
			}
			$this->listdetail[$listnum]=$returnlist;
		}// of else
		//	print " 155    ";
		//var_export($this->listdetail[$listnum]);
/*			
			$time_end = microtime(true);
			$time = $time_end - $time_start2;
			print " split MC list members after ".$time;
*/			
		//$email_logger->write('in mailchimplistdetail #165 for list = '.$listnum.' '.var_export($listsphp,true) ,$uselog);

		// This will set credentials for Basic auth
		//$request->setAuth('apikey', $mailchimp_api, HTTP_Request2::AUTH_BASIC);	
		//$listsjson = $request->send()->getBody();
		//$email_logger->write( "In mailchimplistdetail #149".$listsjson , $uselog  );
		//$listsphp = json_decode($listsjson,true);
		////$email_logger->write( "In mailchimplistdetail #172".var_export($listsphp,true) , $uselog  );
		////$email_logger->write( "In mailchimplistdetail #173".var_export($listsphp['members'],true) , $uselog  );

		//$email_logger->write( "In mailchimplistdetail #184".var_export($returnlist,true) , $uselog  );

		//$emc= new EmailController;
		//$email_logger->write( "In mailchimplistdetail #187 ".var_export($emc->arraytojson($returnlist),true) , $uselog  );
		//$email_logger->write( "In mailchimplistdetail #188 ".$emc->arraytojson($returnlist) , $uselog  );
		
		return $returnlist;
}
public function dailymailchimpcheck($listsandnumbers) {
	$f3=$this->f3;
	$uselog=$f3->get('uselog');
	//$u3ayear = $f3->get('SESSION.u3ayear');
	//var_export($this);
	//var_export($this->u3ayear);
	$paidstatus="('Y','N','W')";

/************** 
	$listselect contains the names of the interesting lists and sql to get the corresponding members from the db
***************/

	require_once 'vendor/drewm/mailchimp-api/src/MailChimp.php';

/**********
**  NOW look for db members not in Mailchimp for each list 
***********/

	$missingall = $this->missingfrommc($listsandnumbers); // contains all lists and numbers
/******	foreach($this->onlyemails as $listname=>$onlyemails){
		$excessall[$listname] =array_diff($this->onlyemails[$listname],$this->mcemails[$listname]); //things in 1st not in the 2nd
	}

	printf("<br>@214 Excessall = %s <br>",var_export($excessall,true));
		fwrite($this->fp,"<br>  #214 Excessall = ".var_export($excessall,true)."<br>");
		****/
	//		var_export($missing);
	if(!empty($missingall)) {
		//print " #207-----------          ";
		//var_export($missingall);
		foreach ($missingall as $listname=>$listmissing) {
		fwrite($this->fp,"\n------------------------------------------\n");
		fwrite($this->fp,"Emails to add to Mailchimp list ".$listname."\n");
		$listnum = $listmissing['id'];
	//	print " #213   $listname          ";
	//	var_export($listmissing);
		foreach($listmissing['missing'] as $missing) {
	//	print " #216   $listname          ";
	//	var_export($missing);
		// **********  addemailtomc
		if ($this->addemailtomc($listname,$listnum,$missing)){
			$this->emailsadded[$listname][]=$missing['email'];
			//fwrite($this->fp,"Email ".$missing['email']." added  to Mailchimp for list = ".$listname."\n" )	;	
			};	
		}
	}
	}
	else {
		fwrite($this->fp,"\n------------------------------------------\n");
		fwrite($this->fp,"No Emails to add to Mailchimp lists \n");
	
	}
	
/**********
**  NOW look for emails in Mailchimp but not in db for each list 
***********/
	//$missing = $this->excessinmc($listsandnumbers); // contains all lists and numbers
	
	//printf("<br>this->mcemails %s<br>",var_export($this->mcemails,true));
	//printf("<br>this->onlyemails %s<br>",var_export($this->onlyemails,true));
	foreach($this->mcemails as $alistname => $amcemails) {
			$excessinmca[$alistname] =  array_diff($this->mcemails[$alistname],$this->onlyemails[$alistname]); //things in 1st not in the 2nd
	//		$excessinmc[$alistname]=array_values($excessinmca[$alistname]);
	//$excessinmca =  array_diff($this->mcemails,$this->onlyemails);
	}
	printf("<br>#306 Excess emails in MChimp %s<br>",var_export($excessinmca,true));
		//fwrite($this->fp,"#307 Excess emails in MChimp = ".var_export($excessinmca,true)."\n");
	$mailchimpd = new DrewM\MailChimp\Mailchimp($this->mailchimp_api);
	$j=0;
	$options =	new Option($this->db);
		$updatemailchimp = $options->find("optionname='updatemailchimp'")[0]['optionvalue'];

	foreach($excessinmca as $excesslistname=>$oneexcesslist) {
	if(empty($oneexcesslist) ) {fwrite($this->fp,"No emails to remove from list ".$excesslistname."\n"); }

		printf( " An excess for list %s =%s<br>",$excesslistname,var_export($oneexcesslist,true)) ; 	
		//fwrite($this->fp,"\n An excess ".var_export($oneexcesslist,true)." to be removed from Mailchimp for list = ".$excesslistname."\n" );

		foreach($oneexcesslist as $oneexcess) {
			$oneexcessmd5 = md5($oneexcess);
			$oneexcessary = array();
			$oneexcessary['id']=md5($oneexcess);
			$oneexcessary['status']='unsubscribed';
			$oneexcessary['email_address']=$oneexcess;
			$listnumd=$this->listnameandid[$excesslistname];
			

		
		printf("<br> About to remove  email  %s from list %s<br>",$oneexcess,$excesslistname );
		//fwrite($this->fp,"<br> About to remove  email ".$oneexcess." from list ".$excesslistname);
		if(strtoupper($updatemailchimp)=='TRUE'){
		//	foreach($delarray as $delemail) {
	$member= md5($oneexcess); 
	$result1 = $mailchimpd->delete('lists/'.$listnumd.'/members/'.$member );
	if(!$result1) {
		printf(" deleted %s  <br>" ,$oneexcess);
		fwrite($this->fp," Removed Email ".$oneexcess." FROM for list = ".$excesslistname."\n" );
		$this->emailsdeleted[$excesslistname][] =$oneexcess;
	}
	else{
		printf(" deleted %s with result %s <br>",$oneexcess,var_export($result1,true));
		fwrite($this->fp," Removed Email ".$oneexcess." FROM for list = ".$excesslistname."\n" );
		fwrite($this->fp," With Result ".var_export($result1['detail'],true)."\n" );
	}
	//$result = $mailchimpd->patch('lists/'.$listnumd.'/members/'.$oneexcessmd5.'/',$oneexcessary );
	/*** if(!$result) {
			$mailchimp_logger->write("Error Removing Email ".$oneexcess." FAILED for list = ".$excesslistname ,$uselog);
			fwrite($this->fp,"Error Removing Email ".$oneexcess." FAILED for list = ".$excesslistname."\n" );
			}
			else
			{
			fwrite($this->fp," Removed Email ".$oneexcess." FROM for list = ".$excesslistname."\n" );
			fwrite($this->fp," With Result ".var_export($result,true)."\n" );
		//	var_export($result);
			}
			
		}	**/
			$j++;
	}
	}
	}



	
	fwrite($this->fp,"\n\n========================================\n");
	fwrite($this->fp,"Values After Changes \n");
	fwrite($this->fp,"------------------------------------------\n\n");
	$listsandnumbers = $this->getlistinfos();
	
	$this->writelistinfos($listsandnumbers);

	//$this->writelistinfos();

	

}
/*******************************
**
**  Write  each list details out to the email file 
**
*************************************/
function getlistinfos() {
	foreach($this->listselect as $alist=>$sql) {
	/*** $listsandnumbers0 gets an assoc array containing the count of each status of members **/
	$listsandnumbers0[$alist] =$this->getlistsandnumbers($alist);
	$this->listnameandid[$alist]=$listsandnumbers0[$alist]['id'];
	}
	return $listsandnumbers0;
}
function writelistinfos($listsandnumbers0)	{
	foreach($this->listselect as $alist=>$sql) {
  	fwrite($this->fp,"------------------------------------------\n");
	fwrite($this->fp,"List ".$alist."\n");
	//fwrite($this->fp,"SQL ".var_export($sql,true)."\n");
	//fwrite($this->fp,"Contents ".var_export($listsandnumbers0[$alist],true)."\n");
	fwrite($this->fp,"------------------------------------------\n");
	
	foreach($listsandnumbers0[$alist]['status'] as $status=>$count) {
		fwrite($this->fp,$status."\t\t\t\t".$count."\n");
		if (($status=='cleaned_count')&& ($count>0)) {
			
		foreach($listsandnumbers0[$alist]['cleaned'] as $cleaned) {
			fwrite($this->fp,"Cleaned \t\t\t\t".$cleaned['email_address']." ".$cleaned['FNAME']." ".$cleaned['LNAME']." ".$cleaned['MNUM']."\n");

		}		
		}
		if (($status=='unsubscribe_count')&& (!empty($listsandnumbers0[$alist]['unsubscribed'] ) )) {
		foreach($listsandnumbers0[$alist]['unsubscribed'] as $unsubscribed) {
			fwrite($this->fp,"Unsubscribed \t\t\t\t".$unsubscribed['email_address']." ".$unsubscribed['FNAME']." ".$unsubscribed['LNAME']." ".$unsubscribed['MNUM']."\n");

		}		
		}
		}
		
		
	}	
	return ;
}

/*************************************
**
**  Get the names and id's of all the relevant lists and the members numbers for each
**
*************************************/
function getlistsandnumbers($alistname) {
	$f3=Base::instance();
	$mailchimp = new DrewM\MailChimp\Mailchimp($this->mailchimp_api);
	$listsphp = $mailchimp->get('lists');
	printf("<br> #431 listphp = %s<br>",var_export($listsphp,true));
	$returnlist = array();
/*********  find the list I want and get its id then get its counts  ***/
		foreach ($listsphp['lists'] as $list) {  // one of the lists we are interested in
		if($list['name'] ==$alistname){
		$returnlist[ 'id'] = $list['id'];
		$returnlist[ 'name'] = $list['name'];
//		$returnlist[ 'beamer_address'] = $list['beamer_address'];
		$returnlist[ 'status'][ 'member_count'] = $list['stats']['member_count'];
		$returnlist[ 'status'][ 'cleaned_count'] = $list['stats']['cleaned_count'];
		$returnlist[ 'status'][ 'unsubscribe_count'] = $list['stats']['unsubscribe_count'];
		// Now get the list of cleaned and unsubscribed email and name values
		if($list['stats']['cleaned_count'] !=0) {		
		$cleanedlist = $mailchimp->get('lists/'.$returnlist[ 'id'].'/members',array('status'=>'cleaned'));
		$i=0;
		foreach ($cleanedlist['members'] as $cleanedmember) {
		//printf(" Cleaned member #350 %s <br>",var_export($cleanedmember,true)); 

		$returnlist[ 'cleaned'][$i]['email_address']= $cleanedmember['email_address'];
		$returnlist[ 'cleaned'][$i]['FNAME']= $cleanedmember['merge_fields']['FNAME'];
		$returnlist[ 'cleaned'][$i]['LNAME']= $cleanedmember['merge_fields']['LNAME'];
		$returnlist[ 'cleaned'][$i]['MNUM']= $cleanedmember['merge_fields']['MNUM'];
		//printf(" Cleaned member #354 %s <br>",var_export($returnlist[ 'cleaned'][$i],true)); 
		
		$i++;}
		//printf(" Cleaned members #357 %s <br>",var_export($returnlist[ 'cleaned'],true)); 
		}
		if($list['stats']['unsubscribe_count'] !=0) {		
		$unsubscribedlist = $mailchimp->get('lists/'.$returnlist[ 'id'].'/members',array('status'=>'unsubscribed'));
		$i=0;
		foreach ($unsubscribedlist['members'] as $unsubscribedmember) {
		//printf(" unsubscribed member #363 %s <br>",var_export($unsubscribedmember,true)); 

		$returnlist[ 'unsubscribed'][$i]['email_address']= $unsubscribedmember['email_address'];
		$returnlist[ 'unsubscribed'][$i]['FNAME']= $unsubscribedmember['merge_fields']['FNAME'];
		$returnlist[ 'unsubscribed'][$i]['LNAME']= $unsubscribedmember['merge_fields']['LNAME'];
		$returnlist[ 'unsubscribed'][$i]['MNUM']= $unsubscribedmember['merge_fields']['MNUM'];
		//printf(" Unsubscribed member #369 %s <br>",var_export($returnlist[ 'unsubscribed'][$i],true)); 
		
		$i++;}
		//printf("Unsubscribed members #372 %s <br>",var_export($returnlist[ 'unsubscribed'],true)); 
		}
		
		

		
		}

		
}
return $returnlist;
}
/*****************
**find all the db members that aren't in the relevant Mailchimp list
** return assoc array keyed by listname, entries for id then array of the missing values which is an assoc array of email, names,loc,type and membnum
*******************/
function missingfrommc($listsandnumbers) {
		$returnarray = array();
	// for each mailchimp list get the members list and get the relevant db entries
	// compare the two and construct an assoc array to return
	// the mailchimp list returns the email address only	
	foreach($this->listselect as $listname=>$listsql) {
		$listid =$listsandnumbers[$listname]['id']	;
		$mcdetails = $this->getmailchimplistdetail($listid);
		$mcemails =array();
		$i=0;

		foreach($mcdetails as $amember){
			if($amember['status']!='unsubscribed') $mcemails[$i] = strtolower($amember['email']);
			$i++;
		}		
	//	print "   mcemails for ".$listname."*****";
	//	var_export($mcemails);		// get the unique emails appropriate from members db
	//	echo "getting sql select ".var_export($listsql,true)."  ";
		$membemails= $this->db->exec($listsql['sqlselect']);
		$onlyemails =array();
		$i=0;
		foreach($membemails as $amember){
		$onlyemails[$i] = strtolower($amember['email']);
		$i++;
		}
	//		print "   onlyemails for ".$listname."*****";
	//	var_export($onlyemails);
	$this->onlyemails[$listname] = $onlyemails;
	$this->mcemails[$listname] = $mcemails;
		$notinmc =array_diff($onlyemails,$mcemails);	

	//			print "   notinmc for ".$listname."*****";
	//	var_export($notinmc);
/*******************
**  Now have an array of email addresses
**	so need to construct a set of arrays 
**  includes an array keyed by the list name
**  then value is an assoc with keys 'id' for list number
**	 and 'missing' is an array of emails. 
**	each email array contains email, FNAME,LNAME,LOC,MTYPE and MNUM 
**	 the values come from $membemails
**	That is passed eventually to addemailtomc
*********************/
	//var_export($notinmc);

//print " #367 ".$listname."  ";
	$returnarray[$listname]['id']=$listid;
// print " #369 ".$listid."  ";
//var_export(	$returnarray);
	$j=0;
	$returnarray[$listname]['missing']=array();	
	foreach($notinmc as $eml) {
//print "309 ".$eml."  ";

 
/**********  Now get the details for that particular email from $membemails***/
	foreach($membemails as $amember){
		if (strtolower($amember['email']) ==strtolower($eml)) {
			//print " Found one ".$eml;			$returnarray[$listname]['missing'][$j]=array();
			$returnarray[$listname]['missing'][$j]['email']=$eml;
			$returnarray[$listname]['missing'][$j]['FNAME']=$amember['forename'];
			$returnarray[$listname]['missing'][$j]['LNAME']=$amember['surname'];
			$returnarray[$listname]['missing'][$j]['LOC']=$amember['location'];
			$returnarray[$listname]['missing'][$j]['MTYPE']=$amember['membtype'];
			$returnarray[$listname]['missing'][$j]['MNUM']=$amember['membnum'];
			}
			$j++;
		}

		}
			
	}


	//	 print " #396 ";var_export(	$returnarray);
	return $returnarray;
}

function addemailtomc($listname,$listnum,$amember) {
	$mailchimp = new DrewM\MailChimp\Mailchimp($this->mailchimp_api);
//	print "    #401  ";
//	var_export($amember);
	$newmember= array();
	$newmember['id']=md5($amember['email']);
	$newmember['email_address']=$amember['email'];
	$newmember['status']='subscribed';
	$newmember['merge_fields']['FNAME']=$amember['FNAME'];
	$newmember['merge_fields']['LNAME']=$amember['LNAME'];
	$newmember['merge_fields']['LOC']=$amember['LOC'];
	$newmember['merge_fields']['MTYPE']=$amember['MTYPE'];
	$newmember['merge_fields']['MNUM']=$amember['MNUM'];
	//print " 342   ";
	//var_export($newmember);
		$options =	new Option($this->db);
		$updatemailchimp = $options->find("optionname='updatemailchimp'")[0]['optionvalue'];
		if(strtoupper($updatemailchimp)=='TRUE'){
		$result = $mailchimp->post('lists/'.$listnum.'/members',$newmember );
		if (!$result) {
//			$mailchimp_logger->write('Error Member '.$newmember['email_address'].' FAILED to be added to Mailchimp for list = '.$listnum ,$uselog);
		return false;
		}
		else
		{
			if ($result['status']=='subscribed') {
		//print " Result from adding member = ".var_export($result,true);
		//$mailchimp_logger->write('Members '.var_export($result,true).' added to Mailchimp for list = '.$listnum  ,$uselog);
		//$mailchimp_logger->write('Members '.$result['email_address'].' added to Mailchimp for list = '.$listnum  ,$uselog);
		return true;
		} 
			else
			{//$mailchimp_logger->write('Error Member '.$newmember['email_address'].' FAILED to be added to Mailchimp for list = '.$listnum.' with result '.$result['status'].' detail ' .$result['detail'],$uselog);
				return false;
		}}
		} // of if Update
	}



function mctest() {// various test actions
	// delete 2 entries from ATS.
	//$this->mailchimp_api
	//$this->listselect['ATS'];
	//$listnum = '0ef7c93efc'; //main u3a list
	$listnum = '9e30db94b7'; //justme list
	require_once 'vendor/drewm/mailchimp-api/src/MailChimp.php';
	$mailchimpd = new DrewM\MailChimp\Mailchimp($this->mailchimp_api);
	$argsarray=array('fields'=>'id,name');

	$laurie2 = md5('laurie2@lyates.com');
	$laurie3 = md5('laurie3@lyates.com');
	$delarray= array('laurie.lyates@gmail.com');
	foreach($delarray as $delemail) {
	$member= md5($delemail);
	$result1 = $mailchimpd->delete('lists/'.$listnum.'/members/'.$member );			
	
	//$result1 = $mailchimpd->delete('lists/'.$listnum.'/members/'.$laurie2 );
	//sleep(10);
	////
	printf(" deleted %s with result %s <br>",$delemail,var_export($result1,true));
	}
	//$result1b =$mailchimpd->get('lists/'.$listnum,$argsarray);
	//$result2 = $mailchimpd->delete('lists/'.$listnum.'/members/'.$laurie3 );
	

	
}
public function testreadlist () {
	$f3=$this->f3;
	//$result =$this->mc_readlist("3b8ffb6a74");  // test is list Laurie
	$listnum=$f3->get('PARAMS.listnum');
	$result =$this->mc_readlist($listnum);  // test is copy of main list
	
require_once 'krumo/class.krumo.php';krumo($result);
}

function mc_readlist($list_id){
$f3=$this->f3;
$uselog=$f3->get('uselog');
require_once 'vendor/drewm/mailchimp-api/src/MailChimp.php';
require_once 'krumo/class.krumo.php';

$mailchimp_logger = new MyLog('mailchimp.log');
$mailchimp_logger->write('Running mc_readlist' ,$uselog);

$mailChimp = new DrewM\MailChimp\MailChimp($this->mailchimp_api);
//  Get the number of the members
$listcount= $mailChimp->get("lists/$list_id")['stats']['member_count'];
		$per_batch        = 100;
		$total_to_process = $listcount;$argsarray=array('count'=>$listcount,'offset'=>0,'fields'=>'members.id,members.email_address,members.merge_fields,members.status');
		$num_batches      = ceil($total_to_process / $per_batch);
/**
 * Process all relevant rows/entries in batches of size $per_batch
 */
		$returnlist = array();
//$result =$mailChimp->get("lists/$list_id/members",$argsarray,30);

for ($page=1; $page <= $num_batches; $page++)
{		$offset = ($page-1)*$per_batch;
		$argsarray=array('count'=>$per_batch,'offset'=>$offset,'fields'=>'members.id,members.email_address,members.merge_fields,members.status');
		$listsphp = $mailChimp->get('lists/'.$list_id.'/members',$argsarray,30);
	$i=$offset;
		foreach ($listsphp['members'] as $list) {
//			krumo($list);
			array_push($returnlist,$list);
		$i++; }

		}	
			

//krumo($returnlist);
//print_r("-------mc_readlist---------<br>");
//foreach($returnlist as $anemail) print_r($anemail['email_address']."<br>");
//print_r("-------end of readlist---------<br>");
return $returnlist;
}	

public function testclearextras() {
	
	//$listnum=$f3->get('PARAMS.listnum');	
	//$this->clearextras("b65a344481");
	//$listnum="b65a344481"; //copy of main
	$mc_lists =$this->getmailchimplists();
	$target_list = "U3A Marbella and Inland List";
	foreach($mc_lists as $alist) {
		if($alist['name'] ==$target_list) $listnum=$alist['id'];
	}
	print_r(" Target list id = ".$listnum." <br>");
	//$listnum="b65a344481"; //main
	$this->clearextras($listnum);
}
function clearextras($list_id)  {
$f3=$this->f3;
$uselog=$f3->get('uselog');
require_once 'vendor/drewm/mailchimp-api/src/MailChimp.php';
require_once 'vendor/drewm/mailchimp-api/src/Batch.php';
require_once 'krumo/class.krumo.php';

$mailchimp_logger = new MyLog('mailchimp.log');
$mailchimp_logger->write('Running clearextras' ,$uselog);
//print_r(" MC API = ".$this->mailchimp_api);
$mailChimp = new DrewM\MailChimp\MailChimp($this->mailchimp_api);
//$list_id = "3b8ffb6a74";
//$result = $mailChimp->get("lists/$list_id/members");

$all_mc_members = $this->mc_readlist($list_id);
//krumo($all_mc_members);
//$all_mc_members = $result['members'];

//krumo($all_mc_members);



//Just a simple patch

foreach( $all_mc_members as $mc_member) {

	$mail_id = $mc_member['id'];
//	krumo('lists/$list_id/members/'.$mail_id);
//	krumo($mc_member);
//	krumo($mc_member['merge_fields']);
//	krumo($mc_member['merge_fields']['FNAME']);
	$mc_merge =$mc_member['merge_fields'];
	if($mc_merge['EXTRAMNUM']<>'') {
	//	krumo($mc_member['merge_fields']['MNUM']);
				$result = $mailChimp->patch("lists/$list_id/members/".$mail_id, [
                'email_address' => 'laurie_lyates@hotmail.com',
                'status'        => 'subscribed',
				'merge_fields' => [
				'FNAME' => $mc_merge['FNAME'], 
				'LNAME' => $mc_merge['LNAME'], 'LOC'=> $mc_merge['LOC'] ,'MTYPE'=> $mc_merge['MTYPE'], 'MNUM'=>$mc_merge['MNUM'],'EXTRAMNUM' =>'','EXTRAFNAME' =>'','EXTRALNAME' =>'']]);
	}
}
	
//krumo($result); 
//print_r("<br>finished");
}	

public function checkstatus() {
	$f3=$this->f3;
	$batch_id = $f3->get('PARAMS.batchid');
require_once 'vendor/drewm/mailchimp-api/src/MailChimp.php';
require_once 'vendor/drewm/mailchimp-api/src/Batch.php';
require_once 'krumo/class.krumo.php'; 
//$batch_id='bf1f2fc68e';
$mailchimp = new DrewM\MailChimp\MailChimp($this->mailchimp_api);
$batch     = $mailchimp->new_batch($batch_id);
//$mailChimp->new_batch($batch_id);
$result = $batch->check_status();

krumo($result); 
}

/*********  get a list of all thos members where they share an emal address
*****
********* Result like 2	aljogreen@gmail.com	Alan,JoyceGreen,Green	727,758
*******
*******/
public function update_shared_emails() {
	//	$list_id="3b8ffb6a74";  //Laurie list
		$listnum="b65a344481";  //Copy of main list
	$mc_lists =$this->getmailchimplists();
	$target_list = "U3A Marbella and Inland List";
	foreach($mc_lists as $alist) {
		if($alist['name'] ==$target_list) $listnum=$alist['id'];
	}
	print_r(" Target list id = ".$listnum." <br>");	
	$this->get_shared_emails($listnum);
}
function get_shared_emails ($list_id) {
	$f3=$this->f3;
	$uselog=$f3->get('uselog');
	require_once 'vendor/drewm/mailchimp-api/src/MailChimp.php';
	require_once 'vendor/drewm/mailchimp-api/src/Batch.php';
	require_once 'krumo/class.krumo.php';
	$mailchimp_logger = new MyLog('mailchimp.log');
	$mailchimp_logger->write('Running get_shared_emails' ,$uselog);
	
	$shared_email_select = "SELECT count(*), LOWER(email) as email, GROUP_CONCAT(forename) GroupedFName,  GROUP_CONCAT(surname) GroupedLName, GROUP_CONCAT(membnum) GroupedNum
FROM members where u3ayear = '".$this->u3ayear."' and status ='Active' and email <> ''
GROUP BY email having count(*) =2 order by email";

	$shared_emails= $this->db->exec($shared_email_select);
	print_r("Count of shared emails = ".count($shared_emails));
	krumo($shared_emails);
//	print_r("----  Shared Emails--<br>");
//	foreach($shared_emails as $anemail) {		print_r($anemail['email']."<br>");	}
	//krumo(array_column($shared_emails, 'email'));
	/*** Now go through this list and create the updates to the mailchimp list - For testing use list Laurie  id 3b8ffb6a74
	***
	*** take the email form shared_emails and find it in the mailchimp list
	****/

 $mc_list =$this->mc_readlist($list_id)	;
 
//krumo($mc_list);

 /*** go through the mailchimp list looking for that email in the shared_emails list
 **  The assumption is that the Mailchimp list is correct and has been updated already
 **/
 $usedarray = array();
 $mailchimp = new DrewM\MailChimp\MailChimp($this->mailchimp_api);

//	print_r("----- all the found emails ----<br>");

	foreach($mc_list as $akey=>$amember ) {
	//	if(substr($amember['email_address'],0,5) =='carri') print_r("-------- At carri as ".$amember['email_address']." <br>");
		$thekey = array_search(strtolower ($amember['email_address']), array_column($shared_emails, 'email'));
			//	if(substr($amember['email_address'],0,5) =='carri') print_r('-------- At carri and $thekey = '.$thekey.'<br>');
		if($thekey !== false ) {
			//krumo($amember['email_address']);
			//print_r($amember['email_address']."<br>");

			$usedarray[] = $amember['email_address'];
		
		/**** Found an entry in Mailchimp list corresponding to one of the shared emails 
		Now construct a replacement mailchimp entry 
		***/
		//krumo($akey);	// the key into the mailchimp member list
	//krumo($thekey);	 // the index into $shared emails	
	//	krumo($amember);  // the mailchimp list entry to form the basis of an updated entry
		$extrafname = explode(',',$shared_emails[$thekey]['GroupedFName'])[1];
		$extralname = explode(',',$shared_emails[$thekey]['GroupedLName'])[1];
		$extranum = explode(',',$shared_emails[$thekey]['GroupedNum'])[1];
	//	krumo($extrafname);krumo($extralname);krumo($extranum);
		$result = $mailchimp->patch("lists/$list_id/members/".$amember['id'], [  // get mail id from wherever
                'email_address' => 'laurie_lyates@hotmail.com',
                'status'        => 'subscribed',
				'merge_fields' => [
				'FNAME' => $amember['merge_fields']['FNAME'], 
				'LNAME' => $amember['merge_fields']['LNAME'], 'LOC'=> $amember['merge_fields']['LOC'] ,'MTYPE'=> $amember['merge_fields']['MTYPE'], 'MNUM'=>$amember['merge_fields']['MNUM'],
				'EXTRAMNUM' =>$extranum,'EXTRAFNAME' =>$extrafname,'EXTRALNAME' =>$extralname]]);
	
//		krumo($result);
	
		
		}

	}
 	krumo($usedarray);
 	print_r("Count of shared emails added to MC = ".count($usedarray)." <br>");
	
}
function array_column_multi(array $input, array $column_keys) {
    $result = array();
    $column_keys = array_flip($column_keys);
    foreach($input as $key => $el) {
        $result[$key] = array_intersect_key($el, $column_keys);
    }
    return $result;
}
}