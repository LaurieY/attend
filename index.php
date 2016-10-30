<?php
// *****************  mcrud index.php *
//$f3=require('lib/base.php');
require 'vendor/autoload.php';
$f3 = require('lib/base.php');
$f3->set('CACHE',FALSE);
$f3->config('config/config.ini');
$f3->set('TZ', 'Europe/Madrid');



$f3->route('GET /app/views/feespertypes [ajax]','AjaxController->getfeespertypes');


$f3->route('GET /usergrid [ajax]','AjaxController->users');
$f3->route('POST /edituser [ajax]','AjaxController->edituser');

$f3->route('GET /attend','AttendanceController->index');
$f3->route('GET /membergrid [ajax]','AjaxController->members');
$f3->route('GET /payments','MemberController->payments'); 
$f3->route('GET /wherefees','MemberController->wherearefees'); 
$f3->route('GET /wherefeesgrid [ajax]','AjaxController->wherefeesgrid');

$f3->route('POST /editmember [ajax]','AjaxController->editmember');

$f3->route('POST /app/views/amtpaid [ajax]','AjaxController->amtpaid'); 
$f3->route('POST /app/views/feewhere [ajax]','AjaxController->feewhere');
$f3->route('POST /app/views/markpaid [ajax]','AjaxController->markpaid'); 
$f3->route('POST /app/views/markwaived [ajax]','AjaxController->markwaived'); 
$f3->route('POST /app/views/markunpay [ajax]','AjaxController->markunpay'); 
$f3->route('POST /app/views/editwherefees [ajax]','AjaxController->editwherefees'); 

$f3->route('GET /email1','EmailController->email1');
$f3->route('GET /subscribe1','EmailController->subscribe1'); 
$f3->route('GET /subscribe2','EmailController->subscribe2'); 
$f3->route('GET /batchsubscribe2','EmailController->batch_subscribe2'); 

$f3->route('GET /','LoginController->startup');
$f3->route('GET /login','LoginController->login');
$f3->route('GET /logout','LoginController->logout');
$f3->route('POST /login','LoginController->auth');
$f3->route('GET /admin','AdminController->index'); //
$f3->route('GET /fees','AdminController->fees'); //
$f3->route('GET /feesgrid','AdminAjaxController->feesgrid');
$f3->route('POST /editfees','AdminAjaxController->editfees');

$f3->route('GET /trail','MemberController->trail');

$f3->route('GET /trailgrid','AjaxController->trail');
$f3->route('GET /users','UserController->index');
//$f3->route('GET /user/update/@usr','UserController->update');
$f3->route('GET /changeme','UserController->changeme');
$f3->route('POST /changeme','UserController->changeme');

$f3->route('GET /nocookie','AdminController->nocookie');

$f3->route('GET /exports','MemberController->exports'); // generates all required email lists on page load
/**$f3->route('POST /app/views/export[ajax]','AjaxController->export');
$f3->route('POST /app/views/export','AjaxController->export');
$f3->route('POST /app/views/downloads [ajax]','Downloads->index');
//$f3->route('POST /app/views/downloads [ajax]','AjaxController->markpaid');
$f3->route('POST /exports','AjaxController->exports');  */

$f3->route('GET /downloads/@filename',
    function($f3,$args) {
	$mypdf= new ReportController();
	
 $dlfilename='downloads/email_list_'.$args['filename'].'.pdf';
$mypdf->writeemailpdf($dlfilename,$args['filename']);
 // now generate the pdf file appropriate
 //MemberController::writeemailpdf1($args['filename']);
        // send() method returns FALSE if file doesn't exist
        if (!Web::instance()->send($dlfilename,NULL,512,TRUE))
                  // Generate an HTTP 404
        $f3->error(404);
    }
);

$f3->route('GET /options','OptionController->index');
$f3->route('GET /optiongrid','OptionAjaxController->optiongrid');
$f3->route('POST /editoption','OptionAjaxController->editoption');
   

$f3->route('GET /rollover','MemberController->rollover');
$f3->route('GET /reverserollover','MemberController->reverserollover1');
$f3->route('GET /reverserollover2','MemberController->reverserollover2');


$f3->route('GET /getsubscribers','MpzController->getlist2');
$f3->route('GET /subscribertest','MpzController->subscribertest');

$f3->route('GET /weeklyxmail','ReportController->weeklyxmail');
$f3->route('GET /weeklyreports/@reportset','ReportController->weeklyreports');

$f3->route('GET /financialxmail/@fyear',
  function($f3,$args) {
	$myexcel= new ReportController();

$dlfilename='downloads/Financial_report_'.$args['fyear'].'.xlsx';
//echo date('H:i:s') , " downloadfile is ",$dlfilename , "\n";
$myexcel->financialxmail($args['fyear']);

        // send() method returns FALSE if file doesn't exist
        if (!Web::instance()->send($dlfilename,NULL,512,TRUE))
                  // Generate an HTTP 404
        $f3->error(404);
    }
	);

$f3->route('GET /financialxmail2','ReportController->financialxmail2');
$f3->route('GET /mailto1joiner/@membnum', 'EmailController->mailto1joiner');
$f3->route('GET /mailinglists','AdminController->mailinglists'); //

$f3->route('GET /mailinglistgrid','AdminAjaxController->mailinglistgrid');
$f3->route('POST /editmailinglist','AdminAjaxController->editmailinglist');
$f3->route('GET /mail_mime_test/@membnum', 'EmailController->mailto1joiner');
$f3->route('GET /mailmantest', 'EmailController->mailmantest');
$f3->route('GET /mailinglistdetail/@listnum', 'EmailController->mailinglistdetail');
$f3->route('GET /dailymailmancheck','EmailController->daily_mailman_check');
$f3->route('GET /dailymailman','ReportController->	');
$f3->route('GET /mailmissing/@listname', 'EmailController->mailmissing');
$f3->route('GET /mailrewrite/@listname', 'EmailController->mailrewrite');
$f3->route('GET /getmailchimplistsgrid','MailchimpController->getmailchimplistsgrid');
$f3->route('GET /mailchimplists','AdminController->mailchimplists'); // mailchimp lists from the nav view
$f3->route('GET /mailchimplistdetail/@listnum', 'MailchimpController->mailchimplistdetail');
$f3->route('GET /dailymailchimpcheck', 'MailchimpController->dailymailchimpcheckz');
$f3->route('GET /run_dailymailchimpcheck', 'MailchimpController->run_dailymailchimpcheck');
$f3->route('GET /mctest', 'MailchimpController->mctest');

$f3->route('GET /readlist/@listnum', 'MailchimpController->testreadlist');
$f3->route('GET /clearextras', 'MailchimpController->testclearextras');
$f3->route('GET /checkstatus/@batchid', 'MailchimpController->checkstatus');
$f3->route('GET /updatesharedemails', 'MailchimpController->update_shared_emails'); 
$f3->route('GET /restoresubscriber/@membnum', 'MailchimpController->subscribe_from_unsubscribed'); 

$f3->route('GET /check', 'CheckController->check2');
$f3->route('GET /check2', 'CheckController->check2');
$f3->route('POST /app/views/check/uploads','CheckController->getupload');
$f3->route('POST /app/views/check/uploads2','CheckController->getupload2');
$f3->route('GET /checkedgrid [ajax]','CheckController->getupload2');
$f3->route('GET /checkedjqgrid [ajax]','CheckAjaxController->checkedjqgrid');

$f3->route('GET /attendancelist2','AttendanceController->attendance_list2');
$f3->route('POST /app/views/attendance/attendance_list_name','AttendanceController->attendance_list_name'); 
$f3->route('GET /app/views/attendance/attendance_list_name','AttendanceController->attendance_list_name'); 



//$f3->route('POST /checknumber1','ApiController->checknumber1'); 
//$f3->route('POST /checknumber','ApiController->checknumber2'); 
//$f3->route('GET /getnames','ApiController->getnames'); 
$f3->route('POST /addattend','AttendanceAjaxController->addattend'); 
$f3->route('POST /addeventpost','AttendanceAjaxController->add_event_post');

$f3->route('POST /event','AttendanceAjaxController->action_event_post'); 
 
 
$f3->route('POST /addevent','AttendanceAjaxController->add_event'); 
$f3->route('GET /testattend','AttendanceAjaxController->testattend'); 
$f3->route('GET /testattend2','AttendanceAjaxController->testattend2'); 
$f3->route('GET /testattend3','AttendanceAjaxController->testattend3'); 

$f3->route('GET /do_daily1','AttendanceAjaxController->do_daily1');





$f3->run();
