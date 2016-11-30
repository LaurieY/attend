<?php

/**
 * Short description for file
 *
 * Long description (if any) ...
 *
 * PHP version 5
 *
 * All rights reserved.
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * + Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 * + Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation and/or
 * other materials provided with the distribution.
 * + Neither the name of the <ORGANIZATION> nor the names of its contributors
 * may be used to endorse or promote products derived
 * from this software without specific prior written permission.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  CategoryName
 * @package   AttendanceAjaxController
 * @author    Author's name <author@mail.com>
 * @copyright 2016 Author's name
 * @license   http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version   CVS: $Id:$
 * @link      http://pear.php.net/package/AttendanceAjaxController
 * @see       References to other sections (if any)...
 */

/**
 * Short description for class
 *
 * Long description (if any) ...
 *
 * @category  CategoryName
 * @package   AttendanceAjaxController
 * @author    Author's name <author@mail.com>
 * @copyright 2016 Author's name
 * @license   http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version   Release: @packageVersion@
 * @link      http://pear.php.net/package/AttendanceAjaxController
 * @see       References to other sections (if any)...
 */
namespace{
    class AttendanceAjaxController extends Controller
    {
        private $u3ayear;

    /**
     * Allows simple non views activities
     *
     * Allows simple non views activities
     *
     * @return void
     */
        public function afterroute()
        {
            // allows simple non views activities
        }

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return void
     * @access public
     */
        public function beforeroute()
        {
            //$f3->set('message','');
            $f3 = $this->f3;
            $authLogger = new MyLog('auth.log');
            $authLogger->write(
                'Entering CheckController beforeroute URI= '.$f3->get('URI')
            );
                 $options = new Option($this->db);
                 $this->u3ayear = $f3->get('SESSION.u3ayear');
            if ($this->u3ayear == '') {
                $options->initu3ayear();
                $this->u3ayear = $f3->get('SESSION.u3ayear');
            }
        }

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return void
     * @access public
     */
        public function attendanceList()
        {
            $f3 = Base::instance();
            $uselog = $f3->get('uselog');
            $checkLogger = new MyLog('attendance.log');
            include_once 'krumo/class.krumo.php';
            $checkLogger->write('Entering attendanceList', $uselog);

            define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
            dateDefaultTimezoneSet('Europe/Madrid');
            include_once 'vendor/Classes/PHPExcel.php';
            include_once 'vendor/Classes/PHPExcel/IOFactory.php';
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator('Laurie Yates')
              //->setLastModifiedBy('Maarten Balliauw')
            ->setTitle('U3A International Attendance List')
            ->setSubject('Attendance List')
            ->setDescription(
                'TU3A International Attendance List using latest membership list'
            )
                 // ->setKeywords('office PHPExcel php')
                 /*->setCategory('Test result file')*/;

                // Create the worksheet
                $objPHPExcel->setActiveSheetIndex(0);
                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

                /******  Read from the members database for all members active for
                this u3ayear  */
                $member = new Member($this->db);
                $allMembers = $member->allBySurname();
                //krumo($allMembers);
                $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Email')
                ->setCellValue('B1', 'Membership Num')
                ->setCellValue('C1', 'Forename')
                ->setCellValue('D1', 'Surname')
                ->setCellValue('E1', 'Paid?')
                ->setCellValue('F1', 'Attending?');
                $dataArray = array();
            foreach ($allMembers as $amember) {
                $dataArray[] = array($amember['email'], $amember['membnum'],
                $amember['forename'], $amember['surname'],
                $amember['paidthisyear'], );
            }
                $objPHPExcel->getActiveSheet()->fromArray($dataArray, null, 'A2');
                $lastrow = count($dataArray);
                $lastrow1 = count($dataArray)+1;
                $lastrow3 = count($dataArray)+3;
                //   krumo($lastrow);
                //   krumo('F'.$lastrow3);
                //    krumo('=SUM(F2:'.$lastrow1.')');
                //Now go to end of col F  +1 and insert a sum
                $objPHPExcel->getActiveSheet()->setCellValue(
                    'D'.$lastrow3,
                    'Attendee Count'
                );
                $objPHPExcel->getActiveSheet()->setCellValue(
                    'F'.$lastrow3,
                    '=subtotal(3,F2:F'.$lastrow1.')'
                );
                $objPHPExcel->getActiveSheet()->getStyle(
                    'D'.$lastrow3.':F'.$lastrow3
                )
                ->getBorders()->getTop()->setBorderStyle(
                    PHPExcel_Style_Border::BORDER_THICK
                );
                $objPHPExcel->getActiveSheet()->getStyle(
                    'D'.$lastrow3.':F'.$lastrow3
                )
                ->getBorders()->getBottom()->setBorderStyle(
                    PHPExcel_Style_Border::BORDERTHICK
                );
                $objPHPExcel->getActiveSheet()->setAutoFilter('F1:F'.$lastrow1);
                $dlfilename = "downloads/attendance.xlsx";
                $objWriter->save($dlfilename);
                // send() method returns FALSE if file doesn't exist
            if (!Web::instance()->send($dlfilename, null, 512, true)) {
                $f3->error(404);
            }
                printR(" Spreadsheet Downloaded <br>");
        }

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return void
     * @access public
     */
        public function actionEventPost()
        {
            $f3 = Base::instance();
            $uselog = $f3->get('uselog');
            $apiLogger = new MyLog('api.log');
            $apiLogger->write(
                'Entering actionEventPost #92 POST='.var_export($f3->get('POST'), true),
                $uselog
            );
                // POST contains only the id and the action to be performed

                $action = $f3->get('POST')['action'];
                $event = new Event($this->db);
                $apiLogger->write(
                    'Entering actionEventPost #97 with action'.var_export($action, true),
                    $uselog
                );
            switch ($action) {
                case 'trash':
                        // mark the event as not active
                    $eventId = $f3->get('POST')['id'];
                    $event->load(array('eventId = ?', $eventId));
                    if (!$event->dry()) {
                        $apiLogger->write(
                            'Entering actionEventPost TRASH POST',
                            $uselog
                        );
                        $event->active = 'N';
                        $event->save();
                    }
                    break;
                case 'untrash':
                    // mark the event as active iff the eventDate is in the future and the event exists
                    $eventId = $f3->get('POST')['id'];
                    $apiLogger->write(
                        'Entering actionEventPost UNTRASH POST',
                        $uselog
                    );
                        $event->load(array('eventId = ?', $eventId));
                    if (!$event->dry()) {
                                //event exists, it should anyway, else do nothing
                                $now = new DateTime(date("Y-m-d"));
                                $apiLogger->write(" untrash #136", $uselog);
                                $dbDate = new DateTime($event->eventDate);
                                $apiLogger->write(
                                    'IN actionEventPost UNTRASH POST db date'.var_export($dbDate, true),
                                    $uselog7
                                );
                                $apiLogger->write(
                                    'IN actionEventPost UNTRASH POST $now'.var_export($now, true),
                                    $uselog
                                );
                        if ($dbDate >= $now) {
                                                    $apiLogger->write(
                                                        'IN actionEventPost UNTRASH POST future date',
                                                        $uselog
                                                    );
                                                    // date is not in past then update active to Y
                                                    $event->active = 'Y';
                                                    $event->save();
                        }
                    }
                    break;
                case 'delete':
                    $eventId = $f3->get('POST')['id'];
                    $event->load(array('eventId = ?', $eventId));
                    if (!$event->dry()) { //event exists, it should anyway, else do nothing
                        $eventTrail = array();
                        $event->copyto('eventTrail');
                        $apiLogger->write(
                            'IN actionEventPost DELETE POST eventTrail = '.var_export($f3->get('eventTrail'), true),
                            $uselog
                        );
                        $eventArchive =  new EventArchive($this->db);
                        $eventArchive->add('eventTrail');
                        $event->erase();
                    }
                    break;

                case 'daily1':
                    //  Run daily job

                    //  The take POST parameter as a json array of arrays
                    //  Each array in the form

                    $dailyArray = json_decode($f3->get('POST')['daily'], true);
                    $apiLogger->write(
                        'IN actionEventPost Daily Job  = '.var_export($f3->get('POST'), true),
                        $uselog
                    );
                        $apiLogger->write(
                            'IN actionEventPost #146 Daily Array  = '.var_export($dailyArray, true),
                            $uselog
                        );
                        $this->doDaily1($dailyArray);
                    break;
            }

                //  return($f3->get('BODY'));
        }
    /****  First change all past events to active ='N'
    *  Then go through each still active event received
        and update with any info received
    *****   Each received event is in the future (inc today) */

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @param array $dailyArray Parameter description (if any) ...
     *
     * @return void
     * @access public
     */
        public function doDaily1($dailyArray)
        {
              include_once 'krumo/class.krumo.php';
              $changesAry = array('adds' => 0, 'updates' => 0, 'deactivates' => 0);
              $f3 = Base::instance();
              $uselog = $f3->get('uselog');
              $apiLogger = new MyLog('api.log');
              $apiLogger->write(
                  'Entering doDaily1 #164 dailyArray = '.var_export($dailyArray, true),
                  $uselog
              );

                  $event = new Event($this->db);
                  $pastEvents = $event->past();
                  krumo("an event = ".var_export($pastEvents, true));
                  //    $apiLogger->write( 'doDaily1 #169 '.var_export($pastEvents,true),$uselog  );
            foreach ($pastEvents as $anEvent) {
                krumo("an event = ".var_export($anEvent, true));
                krumo("an event ID = ".var_export($anEvent->eventId, true));
                $anEvent->active = 'N';
                $anEvent->save();
                $changesAry['deactivates']++;
            }
                  /*************  Now go through the received array  $dailyArray  **/
                  $apiLogger->write(
                      'doDaily1 #178 '.var_export($dailyArray, true),
                      $uselog
                  );
            foreach ($dailyArray as $anEvent) {
                $eventId = $anEvent['eventId'];
                $anEvent['eventType'] = explode("pods", $anEvent['eventType'])[1] ;
                  //
                $anEvent['active'] = 'Y';
             //$apiLogger->write( 'doDaily1 #204 '.var_export($anEvent,true),$uselog  );
                $resp = $this->addEvent($anEvent);
                krumo($resp);
                //update the tally of changes
                switch ($resp) {
                    case 'add':
                        $changesAry['adds']++;
                        break;
                    case 'update':
                        $changesAry['updates']++;
                        break;
                    case 'deactivate':
                        $changesAry['deactivates']++;
                        break;
                }

                $apiLogger->write('doDaily1 #209 '.var_export($resp, true), $uselog);
            }
                  // now send email to webmaster on results
                  $this->emailDailyResults($changesAry);
        }

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @param array $changesAry Parameter description (if any) ...
     *
     * @return string Return description (if any) ...
     * @access public
     */
        public function emailDailyResults($changesAry)
        {

               $f3 = Base::instance();
              $uselog = $f3->get('uselog');
              $apiLogger = new MyLog('api.log');
              //require_once  'vendor/swiftmailer/swiftmailer/lib/swiftRequired.php';
             $text = "Daily Attendance System Cron Job";
             $subject = 'Daily Attendance System Cron Job';
             $from = 'attendance@u3a.world';
             $to = 'laurie@u3a.international';
             //$bcclist =  $options->find("optionname='emailbcc'");
             $docdir = $f3->get('BASE')."docs/";
             $letters = parseIniFile($docdir.'letters.ini', true);
            if (!$letters) {
                $emailLogger->write("In email letters Failed parseIni\n");

                return print("Failed parseIni\n");
            }
              // Use PHP Mail instead of Swift
        /********$transport = Swift_SmtpTransport::newInstance('mail.u3a.world', 25);
             $transport->setUsername('laurie@u3a.international');
             $transport->setPassword('Leyisnot70');
             $swift = Swift_Mailer::newInstance($transport);
             $message = new Swift_Message($subject);            *****/
             $ht0 = $letters['header']['css'];
            krumo($ht0);
             $cid = $letters['dailyCronEvents']['letter'];
            $apiLogger->write(
                "In emailDailyResults sending #251 ".var_export($cid, true),
                $uselog
            );
                 $now = date("Y-m-d");
                 $cid3 = strReplace("*|today|*", $now, $cid);
                 $cid4 = strReplace("*|deactivates|*", $changesAry['deactivates'], $cid3);
                 $cid5 = strReplace("*|updates|*", $changesAry['updates'], $cid4);
                 $cid = strReplace("*|adds|*", $changesAry['adds'], $cid5);
                 $htp1 = $letters['pstyle']['p1'];  // get the replacement inline css for <p1> tags
                 $htp2 = $letters['pstyle']['p2'];
                 $cid = strReplace("<p1>", $htp1, $cid);
                 $cid = strReplace("<p2>", $htp2, $cid);
                 $html = $ht0.$cid;
         /*** $message->setFrom($from);
         $message->setBody($html, 'text/html');
         $message->setTo($to);
         $apiLogger->write( "In joinerEmail email adding Bcc as " . var_export($bcc,true), $uselog);
         $message->setBcc($bcc);

         $message->addPart($text, 'text/plain');
        $apiLogger->write( "In emailDailyResults sending #268 ".var_export($message,true), $uselog);
        if ($recipients = $swift->send($message, $failures)) {
        $apiLogger->write( "In emailDailyResults succesfully sent ", $uselog);
        //$emailLogger->write( "In joinerEmail email ".$html, $uselog);
         return 0;
        } else {
         $apiLogger->write( "In emailDailyResults UNsuccesfully sent with error ".printR($failures,true), $uselog);
         $apiLogger->write( "In emailDailyResults UNsuccesfully sent with recipients".printR($recipients,true), $uselog);

         echo "There was an error:\n";
         return printR($failures);

        }
        **/
                 $headers  = 'MIME-Version: 1.0'."\r\n";
                 $headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
                 $headers .= 'From: '.$from."\r\n".'Reply-To: webmaster@u3a.international'."\r\n".'X-Mailer: PHP/'.phpversion();
                 $apiLogger->write("In emailDailyResults sending #288 ".var_export($html, true), $uselog);
                 mail($to, $subject, $html, $headers);
        }

    /********** when sent to the bin  mark the event as inactive BODY just contains the ID ***/

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return mixed  Return description (if any) ...
     * @access public
     */
        public function trashEventPost()
        {
              $f3 = Base::instance();
              $uselog = $f3->get('uselog');
              $apiLogger = new MyLog('api.log');
              $apiLogger->write('Entering trashEventPost #116 BODY='.var_export($f3->get('BODY'), true), $uselog);
              $apiLogger->write('Entering trashEventPost #116 POST='.var_export($f3->get('POST'), true), $uselog);
              // BODY contains only the id
              $id = $f3->get('POST')['id'];
              //$id=$f3->get('BODY');
              $event = new Event($this->db);
              $event->trash($id);

              return($f3->get('BODY'));
        }

    /**************  addEvent($eventInfo)           *
    *********** Checks if the event details are already present, if not create it
    ****  If the date has changed for that eventId AND the previous date was in the past it's likely that it is a new event produced by an edit of a previous event
    ***** In that case assume it is a new event entry, change the old one by moving the event and attached attendee entries to the archived status
    ***** and change the active field to 'N'
    *********   ********/

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return void
     * @access public
     */
        public function addEventPost()
        {
              $f3 = Base::instance();
              $uselog = $f3->get('uselog');
              $apiLogger = new MyLog('api.log');
              $apiLogger->write('Entering addEventPost #120 POST='.var_export($f3->get('POST'), true), $uselog);

              $eventInfo = json_decode($f3->get('BODY'), true)['eventInfo'];
              $apiLogger->write('Entering addEventPost #240 BODY decoded='.var_export($eventInfo, true), $uselog);
              $apiLogger->write('Entering addEventPost #241 ID='.var_export($eventInfo['eventId'], true), $uselog);

              $resp = $this->addEvent($eventInfo);
             //echo "From addEventPost with result = ".$resp;
        }
    /****  add an event  return 'add' if an event added or 'changed' if it is changed ***/

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @param unknown $eventBody Parameter description (if any) ...
     *
     * @return mixed   Return description (if any) ...
     * @access public
     */
        public function addEvent($eventBody)
        {            include_once 'krumo/class.krumo.php';
             $f3 = Base::instance();
              $uselog = $f3->get('uselog');
              $apiLogger = new MyLog('api.log');
              $apiLogger->write('Entering addEvent #253 POST='.var_export($eventBody, true), $uselog);
            if ($eventBody != null) {//krumo(" Parameter received ");
            } else {// Get POST params as its an api call not a local call
            }
                 //$eventInfo=json_decode($eventBodyJson,true)['eventInfo'];
                 $eventInfo = $eventBody;
                 //krumo($eventInfoJson);
                 //krumo($eventInfo);
                 //krumo($eventInfo['eventId']);

                  //$uselog = $f3->get('uselog');
                 $event = new Event($this->db);
                 /****   Populate the event property with the pre-existing event, if it exists then examine if it has any major changes, including date    */
                 $existingEvent = $event->exists($eventInfo);

            if (!$event->exists($eventInfo)) {
                $apiLogger->write(" addEvent #270", $uselog);
                if (!$event->add($eventInfo)) { //krumo("Failed on add#267");
                    return false;
                } else {    //krumo("added brand new event #268");
                    return 'add';
                }
            } else {
                /****
                * existing active event so compare
                **/
                $now = new DateTime(date("Y-m-d"));
                $apiLogger->write(" addEvent #279", $uselog);
                $sentDate = new DateTime($eventInfo['eventDate']);
                $dbDate = new DateTime($event->eventDate);
                $diff = $dbDate->diff($sentDate)->format("%r%a");
                //krumo($diff);
                //krumo($event->eventDate);
                //krumo("db date before now ");//krumo($event->eventDate < $now );
                //krumo($sentDate);
                //krumo($now);
                //krumo($sentDate > $now );
                if ($dbDate < $now) {
                    /****
                    ** db date in the past  then  deactivate
                    **/
                    $apiLogger->write(" addEvent #290 db date =".$event->eventDate, $uselog);//Now deactivate entry
                    $event->active = 'N';
                    $event->save();
                    if ($sentDate < $now) {
                        /***
                        * sent date also in the past update entry and return
                        **/
                        $eventInfo['active'] = 'N';
                        if (!$event->add($eventInfo)) { //krumo("Failed on add ");
                            return false;
                        }

                        return 'deactivate';
                    } else {
                        /***  db date in past sent date in future create new entry  ***/
                        $event->reset();
                        $apiLogger->write(" addEvent #299", $uselog);
                        //krumo("adding new event with date in the future");
                        if (!$event->add($eventInfo)) { //krumo("Failed on add ");
                            return false;
                        }

                        return 'update';
                    }
                } else {
                    /***  db date in future update entry  ***/

                    $apiLogger->write('addEvent #307 POST='.var_export($event->eventCurrentCount, true), $uselog);

                       $eventInfo['eventCurrentCount'] = $event->eventCurrentCount;
                    if (!$event->add($eventInfo)) {// krumo("Failed on add ");
                        return false;
                    }

                    return update;
                }
            }
        }
    /**
    * Called via http from Wordpress
    *
    * @response with a message indicating if the names are added
    *  and also the number that have been  Confirmed or Waitlisted
    * the message format from addAttendees is an array 'ok' true or false, 'confirmed' a count, 'waitlisted' a count
    * This can handle either policy whether to wait all or only a part of a request
    *
    * @return string  Return description (if any) ...
    */
        public function addAttend()
        {            include_once 'krumo/class.krumo.php';
            $f3 = Base::instance();
            $uselog = $f3->get('uselog');
            $apiLogger = new MyLog('api.log');
            $options = new Option($this->db);
            $apiLogger->write(" #388 in addAttend BODY = ".var_export($f3->get('BODY'), true), $uselog);
            $apiLogger->write(" #389 in addAttend POST = ".var_export($f3->get('POST'), true), $uselog);
            $this->u3ayear = $f3->get('SESSION.u3ayear');
            if ($this->u3ayear == '') {
                $options->initu3ayear();
                $this->u3ayear = $f3->get('SESSION.u3ayear');
            }
             $body = json_decode($f3->get('BODY'), true);
            // krumo($body);
             $apiLogger->write(" 388 in addAttend body decoded = ".var_export($body, true), $uselog);
             $eventInfo = $body['eventInfo'];
             $eventInfo['active'] = 'Y';
             $apiLogger->write(" #626 in addAttend body decoded  = ".var_export($body, true), $uselog);
             $eventOk = $this->addEvent($eventInfo);  // add the event if it doesn't exist
            if (!$eventOk) { /******** For some reason the event doesn't exist and can't be added  *****/
                $apiLogger->write("ERROR #394 in addAttend **** EVENT Cant be added **** = ".var_export($eventInfo, true), $uselog);

                return "ERROR Event Cant be added";
            }

             $persons = $body['persons'];
             $comment = $body['comment'];

             $attendeesOk = $this->addAttendees($persons, $comment, $eventInfo);
             //krumo($attendeesOk);
             /************ Add peopleCount to the event record  eventCurrentCount ***************/
             $apiLogger->write(' #411 in addAttend with attendeesOk response = '.var_export($attendeesOk, true), $uselog);
            //return($attendeesOk['response']); return;
              $eventId = $eventInfo['eventId'];
            if (!$attendeesOk['ok']) {
                return $attendeesOk['response'];
            }
            switch ($attendeesOk['response']) {
                case 'Booked':
                case 'Waitlisted':
                    echo $attendeesOk['response'];
                    break;

                //$event
                break;
                default:
                    return "Attendees Added with response ".var_export($attendeesOk['response'], true);
            }
        }

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return mixed  Return description (if any) ...
     * @access public
     */
        public function oldaddAttend()
        {
              $f3 = Base::instance();
              $uselog = $f3->get('uselog');
             $apiLogger = new MyLog('api.log');
             $options = new Option($this->db);
             $apiLogger->write(" #388 in addAttend BODY = ".var_export($f3->get('BODY'), true), $uselog);
             $apiLogger->write(" #389 in addAttend POST = ".var_export($f3->get('POST'), true), $uselog);
             $this->u3ayear = $f3->get('SESSION.u3ayear');
            if ($this->u3ayear == '') {
                $options->initu3ayear();
                $this->u3ayear = $f3->get('SESSION.u3ayear');
            }
             $body = json_decode($f3->get('BODY'), true);
             //krumo($body);
             $apiLogger->write(" 388 in addAttend body decoded = ".var_export($body, true), $uselog);
             $eventInfo = $body['eventInfo'];
             $eventInfo['active'] = 'Y';
             $apiLogger->write(" #391 in addAttend body decoded  = ".var_export($body, true), $uselog);
             $eventOk = $this->addEvent($eventInfo);  // add the event if it doesn't exist
            if (!$eventOk) { /******** For some reason the event doesn't exist and can't be added  *****/
                $apiLogger->write("ERROR #394 in addAttend **** EVENT Cant be added **** = ".var_export($eventInfo, true), $uselog);

                return "ERROR Event Cant be added";
            }

             $persons = $body['persons'];
             $comment = $body['comment'];

             $attendeesOk = $this->addAttendees($persons, $comment, $eventInfo);
             //krumo($attendeesOk);
             /************ Add peopleCount to the event record  eventCurrentCount ***************/
             $apiLogger->write(' #411 in addAttend with attendeesOk response = '.var_export($attendeesOk, true), $uselog);
            //return($attendeesOk['response']); return;
              $eventId = $eventInfo['eventId'];
            if (!$attendeesOk['ok']) {
                return $attendeesOk['response'];
            }
            switch ($attendeesOk['response']) {
                case 'Booked':
                case 'Waitlisted':
                    echo $attendeesOk['response'];
                    break;

                //$event
                break;
                default:
                    return "Attendees Added with response ".var_export($attendeesOk['response'], true);
            }
        }
    /***********************************************************************************************
    *************  before adding check if the number of persons would take the event over its limit
    *************  if so make sure the return message  says "Request in Waitlist" ****************
    *************  Called internally from addAttend which has decoded the message into an array, and as a test point  ****
    ******************  the message format ex addAttendees is an array 'ok' true or false, 'confirmed' a count, 'waitlisted' a count   ************

    ************************************************************************************************/

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @param array $attendees  Parameter description (if any) ...
     * @param array $commentAry Parameter description (if any) ...
     * @param array $eventInfo  Parameter description (if any) ...
     *
     * @return array  Return description (if any) ...
     * @access public
     */
        public function addAttendees($attendees, $commentAry, $eventInfo)
        {
             $f3 = Base::instance();
             $uselog = $f3->get('uselog');
              include_once 'krumo/class.krumo.php';
             $apiLogger = new MyLog('api.log');
             $apiLogger->write('Entering addAttendees #420 attendees ='.var_export($attendees, true), $uselog);
             $comment = $commentAry['comment'];
            $apiLogger->write('Entering addAttendees #414 comment ='.var_export($comment, true), $uselog);
             $attendeeResponses = array();
             $requesterEmail = $attendees[0]['email'];
             $apiLogger->write('In addAttendees #424 attendees ='.var_export($attendees, true), $uselog);
             $requesterId = 0;
             $event = new Event($this->db);
             $event->load(array('eventId = ?', $eventInfo['eventId']));
             //krumo($event->eventId);
             $eventLimit = $event->eventLimit;
             $eventCurrentCount = $event->eventCurrentCount;
             $numberOfAttendees = count($attendees);
             $returnMessage = 'Attendees added OK';
             //krumo('attendees = ');krumo($attendees);krumo('event full = '.$event->eventFull);
            if ($event->eventFull or ((0 < $eventLimit) and (0 > ($eventLimit - $eventCurrentCount)))) {
                $requestOverLimit = true ;
                //krumo('request over limit '.$requestOverLimit);
            }

            foreach ($attendees as $akey => $person) {
                if ($akey == 0) {
                    $person['requester'] = true;
                } else {
                    $person['requester'] = false;
                }
                //krumo($person);
                /****
                * add with the person details together with the event id and event date as keys
                ***/
                /**** check that the person hasn't already been added for that event, if it has just not add again but remember that and email it ***/
                $attendee = new Attendee($this->db);
                $person['email'] = $requesterEmail;
                $apiLogger->write('Adding attendees #435 with requesterId '.$requesterId, $uselog);
                $apiLogger->write('Adding attendees #424 with akey'.$akey, $uselog);
                //die();
                $resp = $attendee->add($person, $comment, $eventInfo, $requesterId, $requestOverLimit);
                //  krumo($resp);
                $apiLogger->write('Adding attendees #437 with response '.var_export($resp, true), $uselog);
                // $resp[0] is of the form Updated, Existed, Added
                $attendeeResponses[] = array('name' => $person['name'], 'response' => $resp[0], 'id' => $resp[1], 'requestStatus' => $resp[2]);
                if ($akey == 0) {
                    //its the requester, the first person so remember the id that has been returned for  any other persons to be inserted into the attendees entries
                    $requesterId = $resp[1];
                }
            }
              // Now update requested count for the requester
            $apiLogger->write('Adding attendees #483 with requester '.var_export($attendees[0], true), $uselog);
            $attendee = new Attendee($this->db);
            $r3 = $attendee->updateCount($attendees[0], $eventInfo);
            $apiLogger->write('Adding attendees #486 with r3 = '.var_export($r3, true), $uselog);
            /***********************************************************************************************************
            **********   At this point we have received an array of array responses 'name' & 'response' ****************
            **********   The overall status of the request would be either 'Booked' or 'Waitlisted'    ****************
            **********
               //***********  Now generate email response to adding names ***/
               $respText = '';
               $peopleCount = 0;
            $waitingCount = 0;
            foreach ($attendeeResponses as $akey => $aResponse) {
                //  krumo($aResponse['response']);
                switch ($aResponse['response']) {
                    case 'existed': // the case where there is no comment change
                    case 'updated': // the case where the comment is updated
                        //was already in the database for this event get the attendee requestStatus
                        if ($aResponse['requestStatus'] == 'Booked') {
                            $peopleCount++;
                        } else {
                            $waitingCount++;
                        }

                            $respText .= $aResponse['name']." was already in the system as attending this event<br>\n";
                        break;
                    case 'added':
                        $eventCurrentCount++;
                        if ($aResponse['requestStatus'] == 'Booked') {
                            $peopleCount++;
                        } else { // add to waiting count

                            $waitingCount++;
                        }

                            $respText .= $aResponse['name']." has been added to the list to attend this event<br>\n";
                        break;
                    default:
                        krumo(' Hit default #490 with case '.var_export($aResponse, true).' in person # '.$akey);
                }
            }
            /***********  Now update the eventCurrentCount in the event table **************/
             $event->load(array('eventId  =? ', $eventInfo['eventId']));
            //  krumo($eventCurrentCount);krumo($event->eventLimit);krumo($event->eventFull);
             $event->eventCurrentCount = $eventCurrentCount;
             //krumo($event->eventLimit - $eventCurrentCount) ;
            if ((0 < $event->eventLimit) && (0 > ($event->eventLimit - $eventCurrentCount ))) {
                $event->eventFull = true;
            }
            //  krumo($event->eventFull);
             $event->save();
             //krumo($waitingCount);
             $apiLogger->write($peopleCount.' attendees and waitlisted '.$waitingCount.' with email text  '.$respText, $uselog);
             $apiLogger->write($peopleCount.' Booked  and waitlisted '.$waitingCount.' with email text  '.$respText, $uselog);
             $resp = array('ok' => true, 'response' => 'Booked', 'added' => $peopleCount, 'waitlisted' => $waitingCount);
            if ($waitingCount > 0) {
                $resp['response'] = 'Waitlisted';
            }

             return $resp;
        }

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return void
     * @access public
     */
        public function getEventInfo()
        {
             $f3 = Base::instance();
             $uselog = $f3->get('uselog');
             $apiLogger = new MyLog('api.log');
             $apiLogger->write('Entering getEventInfo with id = '.var_export($f3->get('PARAMS.id'), true), $uselog);
             /**** Get event details & pass back to wordpress in an array format

            'eventType' 'eventLimit' 'eventCurrentCount'
            ***************************/
             $event = new Event($this->db);
             $event->load(array('eventId = ?', $f3->get('PARAMS.id')));
             $apiLogger->write('getEventInfo #531 with eventType  = '.$event->eventType, $uselog);
             //$apiLogger->write( 'getEventInfo #532 with eventType sub  = '.explode("pods",$event->eventType)[1] ,$uselog  );
             $eventInfo = array('eventType' => $event->eventType,
              'eventLimit' => $event->eventLimit, 'eventCurrentCount' => $event->eventCurrentCount, );
                 $apiLogger->write('getEventInfo #535 with eventInfo  = '.var_export($eventInfo, true), $uselog);
                 $apiLogger->write('getEventInfo #536 with eventInfo encoded  = '.json_encode($eventInfo), $uselog);
                 echo json_encode($eventInfo);
        }

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return void
     * @access public
     */
        public function eventGrid()
        {
            //require_once 'krumo/class.krumo.php';
             $f3 = Base::instance();
             $event = new Event($this->db);
            /******$json1 = '{   "total": "xxx",
              "page": "yyy",
              "records": "zzz",
              "rows" : [
            {"id" :"1", "cell" :["cell11", "cell12", "cell13"]},
            {"id" :"2", "cell":["cell21", "cell22", "cell23"]}  ]}';
            //krumo( $json1);
            //echo '<br>';
             //krumo(json_decode($json1,true));
            $json2 = '{   "totalpages" : 1,
              "currpage" : 1,
              "totalrecords" : 84,
              "eventdata" : [    {"id" : "1","eventName":"cell11", "eventDate" :"cell12", "eventContactEmail" :"cell13"},
            {"id" : "2","eventName":"cell21", "eventDate" :"cell22", "eventContactEmail" :"cell23"} ]}';
              //krumo($json2);
             // echo '<br>';
             // krumo(json_decode($json2,true));
             //$this->event->load(array('eventId=?',$this->eventInfo['eventId']));

             //krumo($this->event->eventName);
             *******/
             $eventCount = $event->count(array('active = "Y"'));
            //  krumo($eventCount);
            $events = $event->find(array( 'active = "Y"'), array('order' => 'eventCurrentCount DESC , eventDate ASC'));

            $eventArray = array('totalpages' => 1, 'currpage' => 1, 'totalrecords' => $eventCount, 'eventdata' => array());
            foreach ($events as $eventnum => $anevent) {
                 //krumo($event);
                  $eventArray['eventdata'][] = array('id' => $anevent->eventId, 'eventName' => $anevent->eventName, 'eventDate' => $anevent->eventDate, 'eventContactEmail' => $anevent-> eventContactEmail,
                   'eventLimit' => $anevent->eventLimit, 'eventCurrentCount' => $anevent->eventCurrentCount, 'eventFull' => $anevent->eventFull, );
                 //  $eventArray['eventdata'][] = array('id' => $event->id,'cell' => array($event->eventName,$event->eventDate,$event-> eventContactEmail));
            }
             //krumo($eventArray);
            //  krumo(json_encode($eventArray));
             echo json_encode($eventArray);
        }
    // return json with requesters only for this event

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return void
     * @access public
     */
        public function requesterGrid()
        {
            //require_once 'krumo/class.krumo.php';
             $f3 = Base::instance();
             $uselog = $f3->get('uselog');
             $attendeeLogger = new MyLog('attendee.log');
             $attendeeLogger->write('in fn requesterGrid #599 PARAMS = '.var_export($f3->get('PARAMS'), true), $uselog);
             $attendee = new Attendee($this->db);
              $eventId = $f3->get('PARAMS.eventid');
             $attendeeLogger->write('in fn requesterGrid #602 PARAMS.eventid = '.var_export($f3->get('PARAMS.eventid'), true), $uselog);

             //$attendeeCount = $attendee->count(array('requesterId=0 and eventId = ?',$eventId));
             $attendeeCount = $attendee->count(array('requesterId=id and eventId = ?', $eventId));
            //  krumo($eventCount);
            //fetch the requester name for each request
            //then fetch the count of each reuqest by grouping on requester id
            //$attendees=$attendee->find(array('requesterId=0 and eventId = ?',$eventId));
            $attendees = $attendee->find(array('requesterId = id and eventId = ?', $eventId));

            $attendeeArray = array('totalpages' => 1, 'currpage' => 1, 'totalrecords' => $attendeeCount, 'attendeedata' => array());
            foreach ($attendees as $attendeenum => $anattendee) {
                //krumo($event);
                $numRequested
                = $attendeeArray['attendeedata'][] = array('id' => $anattendee->id, 'name' => $anattendee->name, 'membnum' => $anattendee->membnum, 'memberPaid' => $anattendee-> memberPaid,
                   'memberGuest' => $anattendee->memberGuest, 'requesterEmail' => $anattendee->requesterEmail, 'requestStatus' => $anattendee->requestStatus,
                   //'numberRequested' => $anattendee->numberRequested,
                   'createdAt' => $anattendee->createdAt, );
                 //  $eventArray['eventdata'][] = array('id' => $event->id,'cell' => array($event->eventName,$event->eventDate,$event-> eventContactEmail));
            }
             //krumo($eventArray);
            //  krumo(json_encode($eventArray));
             echo json_encode($attendeeArray);
        }

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return void
     * @access public
     */
        public function attendeeGrid()
        {
            //require_once 'krumo/class.krumo.php';
             $f3 = Base::instance();
             $uselog = $f3->get('uselog');
             $attendeeLogger = new MyLog('attendee.log');
             $attendeeLogger->write('in fn attendeeGrid #598 PARAMS = '.var_export($f3->get('PARAMS'), true), $uselog);
             $attendee = new Attendee($this->db);
              $eventId = $f3->get('PARAMS.eventid');
             $attendeeLogger->write('in fn attendeeGrid #601 PARAMS.eventid = '.var_export($f3->get('PARAMS.eventid'), true), $uselog);

             $attendeeCount = $attendee->count(array('eventId = ?', $eventId));
            //  krumo($eventCount);
            $attendees = $attendee->find(array('eventId=?', $eventId));

            $attendeeArray = array('totalpages' => 1, 'currpage' => 1, 'totalrecords' => $attendeeCount, 'attendeedata' => array());
            foreach ($attendees as $attendeenum => $anattendee) {
                $attendeeArray['attendeedata'][] = array('id' => $anattendee->id, 'name' => $anattendee->name, 'membnum' => $anattendee->membnum, 'memberPaid' => $anattendee-> memberPaid,
                   'memberGuest' => $anattendee->memberGuest, 'requesterEmail' => $anattendee->requesterEmail, 'requestStatus' => $anattendee->requestStatus,
                   'requestComment' => $anattendee->requestComment, 'createdAt' => $anattendee->createdAt, );
                 //  $eventArray['eventdata'][] = array('id' => $event->id,'cell' => array($event->eventName,$event->eventDate,$event-> eventContactEmail));
            }
             //krumo($eventArray);
            //  krumo(json_encode($eventArray));
             echo json_encode($attendeeArray);
        }

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return void
     * @access public
     */
        public function attendeeGrid2()
        {
            // returns the attendees including the requester
            //require_once 'krumo/class.krumo.php';
             $f3 = Base::instance();
             $uselog = $f3->get('uselog');
             $attendeeLogger = new MyLog('attendee.log');
             $attendeeLogger->write('in fn attendeeGrid3 #657 PARAMS= '.var_export($f3->get('PARAMS'), true), $uselog);
             $attendee = new Attendee($this->db);
              $eventId = $f3->get('PARAMS.eventid');
            //  $attendeeLogger->write('in fn attendeeGrid #660 PARAMS.requesterid = '.var_export($f3->get('PARAMS.requesterid '),true),$uselog);
             $attendeeLogger->write('in fn attendeeGrid #665 PARAMS.event = '.var_export($eventId, true), $uselog);
            $attendeeCount = $attendee->count(array('eventId=?', $eventId));
             // $attendeeCount= $attendee->count(array('requester<> 1 and requesterId=?',$requesterId));
             $attendeeLogger->write('in fn attendeeGrid #668 attendeeCount = '.var_export($attendeeCount, true), $uselog);
            //$attendees=$attendee->find(array('requester<> 1 and requesterId=?',$requesterId));
            $attendees = $attendee->find(array('eventId=?', $eventId), array('order', 'requesterId ASC'));
            $attendeeArray = array('totalpages' => 1, 'currpage' => 1, 'totalrecords' => $attendeeCount, 'attendeedata' => array());
            foreach ($attendees as $attendeenum => $anattendee) {
                 //krumo($event);
                if ($anattendee->id != $anattendee->requesterId) {
                    $aname = '---'.$anattendee->name;
                } else {
                    $aname = $anattendee->name;
                }
                  $attendeeArray['attendeedata'][] = array('id' => $anattendee->id, 'name' => $aname, 'membnum' => $anattendee->membnum, 'memberPaid' => $anattendee-> memberPaid,
                   'memberGuest' => $anattendee->memberGuest, 'requesterEmail' => $anattendee->requesterEmail,
                   'requestStatus' => $anattendee->requestStatus, 'requestCount' => $anattendee->requestCount,
                   'requestComment' => $anattendee->requestComment, 'createdAt' => $anattendee->createdAt, );
                 //  $eventArray['eventdata'][] = array('id' => $event->id,'cell' => array($event->eventName,$event->eventDate,$event-> eventContactEmail));
            }
             //krumo($eventArray);
            //  krumo(json_encode($eventArray));
             echo json_encode($attendeeArray);
        }

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return void
     * @access public
     */
        public function attendeeGrid3()
        {
            // returns the attendees minus the requester
            //require_once 'krumo/class.krumo.php';
             $f3 = Base::instance();
             $uselog = $f3->get('uselog');
             $attendeeLogger = new MyLog('attendee.log');
             $attendeeLogger->write('in fn attendeeGrid3 #657 PARAMS= '.var_export($f3->get('PARAMS'), true), $uselog);
             $attendee = new Attendee($this->db);
              $requesterId = $f3->get('PARAMS.requesterid');
            //  $attendeeLogger->write('in fn attendeeGrid #660 PARAMS.requesterid = '.var_export($f3->get('PARAMS.requesterid '),true),$uselog);
             $attendeeLogger->write('in fn attendeeGrid #661 PARAMS.requesterid = '.var_export($requesterId, true), $uselog);
             //$attendeeCount = $attendee->count(array('requesterId=?',$requesterId));
             $attendeeCount = $attendee->count(array('requester<> 1 and requesterId=?', $requesterId));
             $attendeeLogger->write('in fn attendeeGrid #663 attendeeCount = '.var_export($attendeeCount, true), $uselog);
            $attendees = $attendee->find(array('requester<> 1 and requesterId=?', $requesterId));
            //$attendees=$attendee->find(array('requesterId=?',$requesterId));
            $attendeeArray = array('totalpages' => 1, 'currpage' => 1, 'totalrecords' => $attendeeCount, 'attendeedata' => array());
            foreach ($attendees as $attendeenum => $anattendee) {
                 //krumo($event);
                  $attendeeArray['attendeedata'][] = array('id' => $anattendee->id, 'name' => $anattendee->name, 'membnum' => $anattendee->membnum, 'memberPaid' => $anattendee-> memberPaid,
                   'memberGuest' => $anattendee->memberGuest, 'requesterEmail' => $anattendee->requesterEmail, 'requestStatus' => $anattendee->requestStatus,
                   'requestComment' => $anattendee->requestComment, 'createdAt' => $anattendee->createdAt, );
                 //  $eventArray['eventdata'][] = array('id' => $event->id,'cell' => array($event->eventName,$event->eventDate,$event-> eventContactEmail));
            }
             //krumo($eventArray);
            //  krumo(json_encode($eventArray));
             echo json_encode($attendeeArray);
        }

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return void
     * @access public
     */
        public function fiddle()
        {
             include_once 'krumo/class.krumo.php';
             $empl = '{  "rows":[
    {"empId":"10","name":"Albert","salary":"1000.00","bossId":null,"level":0,"isLeaf":"false","loaded":"true","expanded":"true"},
    {"empId":"11","name":"Bert","salary":"900.00","bossId":"10","level":1,"isLeaf":"true","loaded":"true","expanded":"true"},
    {"empId":"12","name":"Chuck","salary":"900.00","bossId":"10","level":1,"isLeaf":"false","loaded":"true","expanded":"true"},
    {"empId":"13","name":"Donna","salary":"800.00","bossId":"12","level":2,"isLeaf":"true","loaded":"true","expanded":"true"},
    {"empId":"14","name":"Eddie","salary":"700.00","bossId":"12","level":2,"isLeaf":"true","loaded":"true","expanded":"true"},
    {"empId":"15","name":"Fred","salary":"600.00","bossId":"12","level":2,"isLeaf":"true","loaded":"true","expanded":"true"}
  ],
  "total":6,
  "page":1
  }';
             krumo($empl);
             echo '<br>';
             krumo(json_decode($empl, true));
             $emp2 = '{  "rows":[
    {"id":"10","name":"Albert","memberGuest":"M","requesterId":null,"level":0,"isLeaf":"false","loaded":"true","expanded":"true"},
    {"id":"11","name":"Bert","memberGuest":"G","requesterId":"10","level":1,"isLeaf":"true","loaded":"true","expanded":"true"},
    {"id":"12","name":"Chuck","memberGuest":"G","requesterId":"10","level":1,"isLeaf":"false","loaded":"true","expanded":"true"},
    {"id":"13","name":"Donna","memberGuest":"M","requesterId":"12","level":0,"isLeaf":"true","loaded":"true","expanded":"true"},
    {"id":"14","name":"Eddie","memberGuest":"G","requesterId":"13","level":1,"isLeaf":"true","loaded":"true","expanded":"true"},
    {"id":"15","name":"Fred","memberGuest":"G","requesterId":"13","level":1,"isLeaf":"true","loaded":"true","expanded":"true"}
  ],
  "total":6,
  "page":1
  }';
             echo '<br>';
             krumo(json_decode($emp2, true));
             $attendee = new Attendee($this->db);
             krumo($attendee->getTree(99999));
        }

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return void
     * @access public
     */
        public function dataJson2()
        {
            //for treegrid
              $emp2 = '{  "rows":[
    {"id":"10","name":"Albert","memberGuest":"M","requesterId":null,"level":0,"isLeaf":"false","loaded":"true","expanded":"false"},
    {"id":"11","name":"Bert","memberGuest":"G","requesterId":"10","level":1,"isLeaf":"true","loaded":"true","expanded":"true"},
    {"id":"12","name":"Chuck","memberGuest":"G","requesterId":"10","level":1,"isLeaf":"true","loaded":"true","expanded":"true"},
    {"id":"13","name":"Donna","memberGuest":"M","requesterId":null,"level":0,"isLeaf":"false","loaded":"true","expanded":"true"},
    {"id":"14","name":"Eddie","memberGuest":"G","requesterId":"13","level":1,"isLeaf":"true","loaded":"true","expanded":"true"},
    {"id":"15","name":"Fred","memberGuest":"G","requesterId":"13","level":1,"isLeaf":"true","loaded":"true","expanded":"true"}
  ],
  "total":6,
  "page":1
  }';
             $f3 = $this->f3;
             $uselog = $f3->get('uselog');
             $attendeeLogger = new MyLog('attendee.log');
             $attendeeLogger->write('in fn dataJson2 #667 PARAMS= '.var_export($emp2, true), $uselog);
             $eventId = $f3->get('PARAMS.eventid');
            $attendee = new Attendee($this->db);
            $att2 = $attendee->getTree($eventId);
            $attendeeArray = array('pages' => 1, 'currpage' => 1, 'total' => count($att2), 'rows' => array());
            foreach ($att2 as $anattendee) {
                 //krumo($event);
                  $attendeeArray['rows'][] = array('id' => $anattendee->id,
                  'name' => $anattendee->name, 'membnum' => $anattendee->membnum,
                  'memberPaid' => $anattendee-> memberPaid, 'memberGuest' => $anattendee->memberGuest,
                  'requesterId' => (string) $anattendee->requesterId, 'requesterEmail' => $anattendee->requesterEmail,
                  'requestStatus' => $anattendee->requestStatus, 'requestComment' => $anattendee->requestComment,
                  'createdAt' => $anattendee->createdAt, 'level' => intval($anattendee->level),
                  'isLeaf' => $anattendee->isLeaf, "loaded" => "true", "expanded" => "false", );
                 //  $eventArray['eventdata'][] = array('id' => $event->id,'cell' => array($event->eventName,$event->eventDate,$event-> eventContactEmail));
            }
              //  $attendeeLogger->write('in fn dataJson2 #679 PARAMS= '.var_export($attendeeArray,true),$uselog);
            $attendeeLogger->write('in fn dataJson2 #680 PARAMS= '.var_export(json_encode($attendeeArray), true), $uselog);
            echo json_encode($attendeeArray);
            /****
            $emp3 =  '{"total":5,"page":1,  "rows":[{"id":1677,"name":"Laurie Yates","membnum":180,"memberPaid":null,"memberGuest":"M","requesterId":"0","requesterEmail":"laurie@lyates.com","requestStatus":"Booked","requestComment":"ONE","createdAt":"2016-11-15 17:58:47","level":0,"isLeaf":"false","loaded":"true","expanded":"false"},{"id":1678,"name":"Junior 1 Yates","membnum":null,"memberPaid":null,"memberGuest":"G","requesterId":"1677","requesterEmail":"laurie@lyates.com","requestStatus":"Booked","requestComment":"ONE","createdAt":"2016-11-15 17:58:47","level":1,"isLeaf":"true","loaded":"true","expanded":"false"},
            {"id":1679,"name":"Susan Yates","membnum":181,"memberPaid":null,"memberGuest":"M","requesterId":0,"requesterEmail":"laurie@lyates.com","requestStatus":"Waitlisted","requestComment":"ONE","createdAt":"2016-11-15 17:58:47","level":0,"isLeaf":"false","loaded":"true","expanded":"false"},
            {"id":1680,"name":"Junior 2 Yates","membnum":null,"memberPaid":null,"memberGuest":"G","requesterId":"1679","requesterEmail":"laurie@lyates.com","requestStatus":"Waitlisted","requestComment":"ONE","createdAt":"2016-11-15 17:58:47","level":1,"isLeaf":"true","loaded":"true","expanded":"false"},
            {"id":1681,"name":"Susan Elizabeth Yates","membnum":1081,"memberPaid":null,"memberGuest":"M","requesterId":0,"requesterEmail":"laurie@lyates.com","requestStatus":"Waitlisted","requestComment":"ONE","createdAt":"2016-11-15 17:58:47","level":0,"isLeaf":"false","loaded":"true","expanded":"false"}
            ]
            }';
            **/
            //echo $emp3;
        }

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return void
     * @access public
     */
        public function dataJson()
        {
             echo '{  "rows":[
    {"empId":"10","name":"Albert","salary":"1000.00","bossId":null,"level":0,"isLeaf":"false","loaded":"true","expanded":"false"},
    {"empId":"11","name":"Bert","salary":"900.00","bossId":"10","level":1,"isLeaf":"true","loaded":"true","expanded":"true"},
    {"empId":"12","name":"Chuck","salary":"900.00","bossId":"10","level":1,"isLeaf":"false","loaded":"true","expanded":"true"},
    {"empId":"13","name":"Donna","salary":"800.00","bossId":"12","level":2,"isLeaf":"true","loaded":"true","expanded":"true"},
    {"empId":"14","name":"Eddie","salary":"700.00","bossId":"12","level":2,"isLeaf":"true","loaded":"true","expanded":"true"},
    {"empId":"15","name":"Fred","salary":"600.00","bossId":"12","level":2,"isLeaf":"true","loaded":"true","expanded":"true"}
  ],
  "total":6,
  "page":1
  }';
        }

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return void
     * @access public
     */
        public function xmlEvents()
        {

              $f3 = $this->f3;
               $members = new Event($this->db);
              $uselog = $f3->get('uselog');

              $attendeeLogger = new MyLog('attendee.log');
             $attendeeLogger->write('in fn events #559 u3ayear='.$f3->get('SESSION.u3ayear'), $uselog);
              $f3->set('pageHead', 'User List');
            $attendeeLogger->write('in fn events #561 GET.Search='.$f3->get('GET.Search'), $uselog);
            if ($f3->get('GET.Search') == 'true') {
                // set up filters
                $filters = $f3->get('GET.filters');
                $attendeeLogger->write('in fn events filters = '.$filters, $uselog);

                $where = "";

                $attendeeLogger->write('in fn events where = '.$where, $uselog);
                 $f3->set('SESSION.lastseen', time());
                 /**********************  Now get the resulting xml via SWL using this where selection ******/
                 $whh =    $this->getresultWhere($where);

                 $attendeeLogger->write('in fn events #574where result = '.$whh."\n");
                echo $whh;
            } else {
                //$u3ayear = $f3->get('SESSION.u3ayear');
                $attendeeLogger->write("in fn events where result #579 ", $uselog);
                $res1 = $this->getresultWhere("where active='Y'");
                $attendeeLogger->write("in fn events where result #581 , res1=".var_export($res1, true), $uselog);
                echo $res1;
            }
        }

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @param string $whereToUse Parameter description (if any) ...
     *
     * @return string Return description (if any) ...
     * @access public
     */
        public function getresultWhere($whereToUse)
        {

             $f3 = $this->f3;
              $attendeeLogger = new MyLog('attendee.log');
            $uselog = $f3->get('uselog');
              $attendeeLogger->write('in getresultWhere #590 $whereToUse = '.$whereToUse, $uselog);
            header("Content-type: text/xml;charset=utf-8");
             $page = $GET['page'];

             $sidx = $GET['sidx'];
             $sord = $GET['sord'];
              $attendeeLogger->write('in getresultWhere #596 $sidx = '.$sidx.' $sord = '.$sord, $uselog);
             $extrasort = ', eventDate ASC';
             //$fred = $f3->get('dbUser');
             $db = mysqliConnect('localhost', $f3->get('dbUser'), $f3->get('dbPass'), $f3->get('dbName')) or die("Connection Error: ".mysqlError());
            $attendeeLogger->write('in getresultWhere #600 ', $uselog);
             // calculate the number of rows for the query. We need this for paging the result
             $result = mysqliQuery($db, "SELECT COUNT(*) AS count FROM events ".$whereToUse);
             $attendeeLogger->write('in getresultWhere #603 '.var_export($result, true), $uselog);
             $row = mysqliFetchArray($result, MYSQL_ASSOC);
             $count = $row['count'];
             $limit = $count;  // temporary force all rows
            //  $limit = $GET['rows'];  // temporary comment out  to force all rows need this if non-local grid, i.e. loadOnce=false

            // calculate the total pages for the query
            if ($count > 0 && $limit > 0) {
                  $totalPages = ceil($count/$limit);
            } else {
                $totalPages = 0;
            }

                // if for some reasons the requested page is greater than the total
                // set the requested page to total page
            if ($page > $totalPages) {
                $page = $totalPages;
            }

                // calculate the starting position of the rows
                 $start = $limit*$page - $limit;

                // if for some reasons start position is negative set it to 0
                // typical case is that the user type 0 for the requested page
            if ($start < 0) {
                $start = 0;
            }

                 // the actual query for the grid data
                 // Fetch extra columns to allow for the icons columns in the events grid
                // $SQL = "SELECT id,surname ,forename,membnum ,phone,mobile,email,membtype,location,paidthisyear,amtpaidthisyear,feewhere,fyear,u3ayear,3,datejoined FROM members  ".$whereToUse." ORDER BY $sidx $sord ". $extrasort. " LIMIT $start , $limit";
                 $SQL = "SELECT id,eventName,eventDate ,eventContactEmail,eventLimit,eventCurrentCount,eventFull,3 FROM events  ".$whereToUse." ORDER BY $sidx $sord ".$extrasort." LIMIT $start , $limit";

                 $attendeeLogger->write('in getresultWhere SQL = '.$SQL."\n", $uselog);
                 $result = mysqliQuery($db, $SQL) or die("Couldn't execute query.".mysqlError());
                $s = "<?xml version='1.0' encoding='utf-8'?>";
                $s .=  "<rows>";
                $s .= "<page>".$page."</page>";
                $s .= "<total>".$totalPages."</total>";
                $s .= "<records>".$count."</records>";

                $s .= '<userdata name="forename">Total LastFY</userdata>';   // name = target column's name
                $s .= '<userdata name="phone">'.$amtTotalLastfy.'</userdata>';
                $s .= '<userdata name="email">Total ThisFY</userdata>';   // name = target column's name
                $s .= '<userdata name="location">'.$amtTotalThisfy.'</userdata>';

                // be sure to put text data in CDATA
                /*
                while($row = mysqlFetchArray($result,MYSQL_ASSOC)) {
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
            while ($row = mysqliFetchArray($result, MYSQL_ASSOC)) {
                foreach ($row as $key => $value) {
                    if ($key == 'id') {
                        $s .= "<row id='".$value."'>";
                    } else {
                        $s .= "<cell>"."$value"."</cell>";
                    }
                    //$key holds the table column name
                }
                $s .= "</row>";
            }
                $s .= "</rows>";

                //$attendeeLogger->write('in getresultWhere #415 result = '.$s,$uselog);
                 return $s;
        }
    }
}