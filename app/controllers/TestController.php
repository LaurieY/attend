<?php
namespace
{

    class TestController extends Controller
    {

        private $u3ayear;

        private $attend;

        private $persons;

        private $attendee1;

        private $attendee2;

        private $attendee3;

        private $commentAry;

        private $eventInfo;

        private $event;

        private $attendee;

        private $baseUrl;

        public function afterroute()
        {
            // allows simple non views activities
        }

        public function beforeroute()
        {
            // $f3->set('message', '');
            $f3 = $this->f3;
            $this->baseUrl = 'http://testattend.u3a.world';
            $testLogger = new MyLog('Test.log');
            $testLogger->write('Entering TestController beforeroute URI = ' . $f3->get('URI'));
            $options = new Option($this->db);
            $this->u3ayear = $f3->get('SESSION.u3ayear');
            if ($this->u3ayear == '') {
                $options->initu3ayear();
                $this->u3ayear = $f3->get('SESSION.u3ayear');
            }
            // echo $f3->get('AUTOLOAD');
            $this->attend = new AttendanceAjaxController();
            $this->attendee1 = array(
                'name' => 'Laurie Yates',
                'number' => 180,
                'email' => 'laurie@lyates.com',
                'memberGuest' => 'M'
            );
            $this->attendee2 = array(
                'name' => 'Junior 1 Yates',
                'number' => null,
                'email' => null,
                'memberGuest' => 'G'
            );
            $this->attendee3 = array(
                'name' => 'Junior 2 Yates',
                'number' => null,
                'email' => null,
                'memberGuest' => 'G'
            );
            $this->attendee4 = array(
                'name' => 'Susan Yates',
                'number' => 181,
                'email' => 'laurie@lyates.com',
                'memberGuest' => 'M'
            );
            $this->attendee5 = array(
                'name' => 'Susan Elizabeth Yates',
                'number' => 1081,
                'email' => 'laurie@lyates.com',
                'memberGuest' => 'M'
            );

            $this->commentAry = array(
                'comment' => 'Ello'
            );
            $this->eventInfo = array(
                'numberOfNames' => 1,
                'eventId' => 99999,
                'eventName' => 'Test Event',
                'eventDate' => '2016-12-07',
                'eventContactEmail' => 'laurie@lyates.com',
                'eventType' => 'event',
                'eventLimit' => 2,
                'active' => 'Y'
            );
            $this->event = new \Event($this->db);
            $this->attendee = new Attendee($this->db);
        }

        public function unitattend()
        {
            $f3 = Base::instance();

            $testnum = $f3->get('PARAMS.test');
            $test = new Test();
            // krumo($test);
            if ($testnum != 0) {
                $this->unitattend1($test, $testnum);
                // krumo($test); //LEY
            } else {
                for ($i = 1; $i < 11; $i ++) {
                    $this->unitattend1($test, $i);
                    // krumo($test);
                    // krumo($testnum);
                    // krumo($test->results()); //LEY
                }
            }
            foreach ($test->results() as $key => $result) {
                echo $result['text'] . ' :- <b>';
                if ($result['status']) {
                    echo 'Pass';
                } else {
                    echo 'Fail (' . $result['source'] . ')';
                }
                echo '<p></b>';
            }
        }

        public function unitattend1(&$test, $testnum)
        {
            // return test object
            $f3 = Base::instance();
            // $testnum = $f3->get('PARAMS.test');
            require_once 'krumo/class.krumo.php';
            $uselog = $f3->get('uselog');
            $apiLogger = new MyLog('api.log');
            $apiLogger->write('Entering unitattend1 #69', $uselog);
            // Set up
            // $test = new Test;
            // krumo($test);

            // This is where the tests begin
            /*
             * $test->expect(
             * isNull($f3->get('ERROR')),
             * 'No errors expected at this point'
             * );
             */
            /*
             *
             * test can add an attendee to an existing event
             *
             */
            $this->persons = array();
            $this->persons[] = $this->attendee1;

            switch ($testnum) {
                case 1:
                    $this->db->exec('delete from events where eventId = :id', array(
                        ':id' => $this->eventInfo['eventId']
                    ));
                    $this->db->exec('delete from events where eventId = :id', array(
                        ':id' => $this->eventInfo['eventId']
                    ));
                    $this->db->exec('delete from events where eventId = :id', array(
                        ':id' => 0
                    )); // Needed to allow it to be repeatable as I don't create the event in a direct call to addAttendees
                                                                                                  // $this->event->add($eventInfo);
                                                                                                  // krumo($this->eventInfo);
                    $attendResp = $this->attend->addAttendees($this->persons, $this->commentAry, $this->eventInfo);
                    // check the attendee added
                    $test->expect($attendResp != array(), 'Test 1a:- Not Blank Array from addAttendees');
                    // check the event wasn't added
                    $eventResp = $this->event->exists($this->eventInfo);
                    $test->expect(! $eventResp, 'Test 1b:- False for event now exists from  addAttendees');

                    $this->event->add($this->eventInfo);
                    $eventResp = $this->event->exists($this->eventInfo);
                    $test->expect($eventResp, 'Test 1c:- true for event now exists from  add an event');

                    $this->db->exec('delete from events where eventId = :id', array(
                        ':id' => 0
                    )); // Needed to allow it to be repeatable as I don't create the event in a direct call to addAttendees
                    break;
                case 2:
                    $this->event->add($this->eventInfo);
                    $this->db->exec('delete from events where eventId = :id', array(
                        ':id' => $this->eventInfo['eventId']
                    ));
                    $this->db->exec('delete from attendees where eventId = :id', array(
                        ':id' => $this->eventInfo['eventId']
                    ));
                    $this->db->exec('delete from events where eventId = :id', array(
                        ':id' => 0
                    )); // Needed to allow it to be repeatable as I don't create the event in a direct call to addAttendees

                    $attendResp = $this->attend->addAttendees($this->persons, $this->commentAry, $this->eventInfo);
                    // krumo($attendResp);
                    $test->expect($attendResp['ok'], 'Test 2:- ok => true in array from addAttendees for 1 person ');

                    $test->expect(($attendResp['added'] == 1), 'Test 2:- added = 1 in array from addAttendees for 1 person received ' . $attendResp['added']);
                    $this->db->exec('delete from events where eventId = :id', array(
                        ':id' => 0
                    )); // Needed to allow it to be repeatable as I don't create the event in a direct call to addAttendees
                    break;
                case 3:
           /*
            *
            * now try via a POST to addAttend
            *
            */
               //	krumo(array(':id' => $this->eventInfo['eventId']));
                    $this->db->exec('delete from events where eventId = :id', array(
                        ':id' => $this->eventInfo['eventId']
                    ));
                    $this->db->exec('delete from attendees where eventId = :id', array(
                        ':id' => $this->eventInfo['eventId']
                    ));
                    // break;
                    $bodyAll = array(
                        'eventInfo' => $this->eventInfo,
                        'persons' => $this->persons,
                        'comment' => $this->commentAry
                    );
                    $bodyAllJson = json_encode($bodyAll); // krumo($bodyAllJson);
                                                          // $attendeesPost = json_encode();
                    $testResp = $this->myiMock('addAttend', null, null, $bodyAllJson);
                    // krumo($testResp);
                    $test->expect($testResp == 'Booked', 'Test 3:- POST to addAttend expect "Booked" ,received ' . var_export($testResp, true));
                    break;
                /**
                 * **** clear relevant attendees set limit to 2*******
                 */

                case 4: // Ensure event limit is 2
                    $this->eventInfo['eventLimit'] = 1;
                    $eventId = $this->eventInfo['eventId'];

                    $this->db->exec('delete from events where eventId = :id', array(
                        ':id' => $this->eventInfo['eventId']
                    ));
                    $this->db->exec('delete from attendees where eventId = :id', array(
                        ':id' => $this->eventInfo['eventId']
                    ));
                    $this->event->load(array(
                        'eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    /**
                     * ******* The event has been deleted it should be dry *****
                     */
                    $test->expect($this->event->dry(), 'Test 4a:- delete Event check eventId not exists, received ' . $this->event->dry());
                    $testResp = $this->event->add($this->eventInfo);
                    // krumo($testResp);
                    /**
                     * ******* The event has been created it should be NOT dry *****
                     */
                    $this->event->load(array(
                        'eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect(! $this->event->dry(), 'Test 4b:- delete Event check eventId does exists, received ' . $this->event->dry());
                    /**
                     * ******* The event has been created it should have the correct eventId *****
                     */
                    $test->expect($testResp->eventId == $this->eventInfo['eventId'], 'Test 4ca:- Create Event check eventId, received ' . var_export($testResp->eventId, true));

                    $test->expect(! $this->event->eventFull, 'Test 4cb:- Create Event check eventFull false, received ' . var_export($this->event->eventFull, true));

                    /**
                     * ******* The event has been created try to add 3 people should return Waiting *****
                     */
                    $this->persons = array();
                    $this->persons[] = $this->attendee1;
                    $this->persons[] = $this->attendee2;
                    $this->persons[] = $this->attendee3;
                    $this->eventInfo['numberOfNames'] = 3;
                    $bodyAll = array(
                        'eventInfo' => $this->eventInfo,
                        'persons' => $this->persons,
                        'comment' => $this->commentAry
                    );
                    $bodyAllJson = json_encode($bodyAll); // krumo($bodyAllJson);
                                                          // $attendeesPost = json_encode();
                    $url = 'http://testattend.u3a.world/addAttend';
                    // $testResp = $this->myiMock('addAttend', NULL, NULL, $bodyAllJson);

                    $options = array(
                        'method' => 'POST',
                        'content' => $bodyAllJson
                    );
                    // $testResp =\Web::instance()->request($url, $options); //('POST /addAttend [sync]', NULL, NULL, $bodyAllJson);
                    $testResp = $this->myiMock('addAttend', null, null, $bodyAllJson);
                    // krumo($testResp);
                    $test->expect($testResp == 'Booked', 'Test 4d:- POST to addAttend expect "Booked" ,received ' . var_export($testResp, true));
                    $this->event->load(array(
                        'eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($this->event->eventCurrentCount == 3, 'Test 4e:- POST to addAttend expect eventCurrentCount == 3 ,received ' . $this->event->eventCurrentCount); // ;krumo($this->event->cast());
                          // die();
                    $this->attendee->load(array(
                        'requestStatus = "Waitlisted" and eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($this->attendee->dry(), 'Test 4f:- POST to addAttend expect attendees as Waitlisted to be  dry, received ' . $this->attendee->dry());

                    // $attendeesCount = $this->attendee->count(array('requestStatus = "Waitlisted" and eventId =?', $this->eventInfo['eventId']));
                    $attendeesCount = $this->attendee->count();
                    // krumo($attendeesCount);

                    $test->expect($attendeesCount == 3, 'Test 4g:- POST to addAttend expect only 3 attendees, received ' . $attendeesCount);

                    $this->attendee->load(array(
                        'requestStatus = "Waitlisted" and eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($this->attendee->dry(), 'Test 4h:- POST to addAttend expect attendees as Waitlisted to be  dry, received ' . $this->attendee->dry());
                    $apiLogger->write('Entering unitattend1 4I #226', $uselog);
                    // krumo("4I");
                    // Now try to add same attendees again check not duplicated attendees and they are added ok
                    // krumo($bodyAllJson);
                    $testResp = $this->myiMock('addAttend', null, null, $bodyAllJson);
                    $attendeesCount = $this->attendee->count();
                    $test->expect($attendeesCount == 3, 'Test 4i:- POST to addAttend expect only 3 attendees, received ' . $attendeesCount);
                    // krumo("4J");
                    $apiLogger->write('Entering unitattend1  4J #236', $uselog);
                    $test->expect($testResp == 'Booked', 'Test 4j:- POST to addAttend expect "Booked" ,received ' . var_export($testResp, true));
                    // Now set attendess to be just the 1st name, expect it to be NOT booked as per policy-1-B

                    $this->persons = array();
                    $this->persons[] = $this->attendee4;
                    $this->eventInfo['numberOfNames'] = 2;
                    $bodyAll = array(
                        'eventInfo' => $this->eventInfo,
                        'persons' => $this->persons,
                        'comment' => $this->commentAry
                    );
                    $bodyAllJson = json_encode($bodyAll); // krumo($bodyAllJson);

                    $testResp = $this->myiMock('addAttend', null, null, $bodyAllJson);
                    // krumo($testResp);
                    $test->expect($testResp != 'Booked', 'Test 4k:- POST to addAttend expect NOT  "Booked" ,received ' . var_export($testResp, true));
                    // Now try adding #1 and #4 should get #1 booked #4 waitlisted
                    $this->persons = array();
                    $this->persons[] = $this->attendee1;
                    $this->persons[] = $this->attendee5;
                    $this->eventInfo['numberOfNames'] = 2;

                    $bodyAll = array(
                        'eventInfo' => $this->eventInfo,
                        'persons' => $this->persons,
                        'comment' => $this->commentAry
                    );
                    $bodyAllJson = json_encode($bodyAll); // krumo($bodyAllJson);
                                                          // krumo("4L");
                    $testResp = $this->myiMock('addAttend', null, null, $bodyAllJson);
                    $test->expect($testResp != 'Booked', 'Test 4l:- POST to addAttend expect NOT  "Booked" ,received ' . var_export($testResp, true));
                    $this->event->load(array(
                        'eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($this->event->eventCurrentCount == 5, 'Test 4m:- POST to addAttend expect eventCurrentCount == 5 ,received ' . $this->event->eventCurrentCount);

                    $waitlistedCount = $this->attendee->count(array(
                        'requestStatus = "Waitlisted" and eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($waitlistedCount == 2, 'Test 4n:- POST to addAttend expect count attendees as Waitlisted to be 2, received ' . $waitlistedCount);

                    break;
                case 5:
                    $this->db->exec('delete from events where eventId = :id', array(
                        ':id' => $this->eventInfo['eventId']
                    ));
                    $this->db->exec('delete from attendees where eventId = :id', array(
                        ':id' => $this->eventInfo['eventId']
                    ));
                    $this->persons = array();
                    $this->persons[] = $this->attendee1;
                    $this->eventInfo['numberOfNames'] = 1;
                    $this->eventInfo['eventLimit'] = 3;
                    $testResp = $this->event->add($this->eventInfo);
                    $this->commentAry = array(
                        'comment' => "ONE"
                    );
                    $bodyAll = array(
                        'eventInfo' => $this->eventInfo,
                        'persons' => $this->persons,
                        'comment' => $this->commentAry
                    );
                    $bodyAllJson = json_encode($bodyAll);
                    // krumo("5A"); die();
                    $testResp = $this->myiMock('addAttend', null, null, $bodyAllJson);
                    $test->expect($testResp == 'Booked', 'Test 5a:- POST to addAttend expect   "Booked" ,received ' . var_export($testResp, true));
                    $this->event->load(array(
                        'eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($this->event->eventCurrentCount == 1, 'Test 5b:- POST to addAttend expect eventCurrentCount == 1 ,received ' . $this->event->eventCurrentCount);
                    // krumo($this->event->eventId);
                    // krumo("5B"); die();
                    $this->attendee->load(array(
                        'eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($this->attendee->count() == 1, 'Test 5c:- POST to addAttend expect eventCurrentCount == 1 ,received ' . $this->attendee->count());

                    $test->expect($this->attendee->requestComment == 'ONE', 'Test 5d:- POST to addAttend expect requestComment == ONE ,received ' . $this->attendee->requestComment);
                    $this->persons = array();
                    $this->persons[] = $this->attendee1;
                    $this->persons[] = $this->attendee2;
                    $this->eventInfo['numberOfNames'] = 2;
                    $this->commentAry = array(
                        'comment' => " TWO"
                    );
                    $bodyAll = array(
                        'eventInfo' => $this->eventInfo,
                        'persons' => $this->persons,
                        'comment' => $this->commentAry
                    );
                    $bodyAllJson = json_encode($bodyAll);
                    // krumo("5A");
                    $testResp = $this->myiMock('addAttend', null, null, $bodyAllJson);
                    $test->expect($testResp == 'Booked', 'Test 5e:- POST to addAttend expect   "Booked" ,received ' . var_export($testResp, true));
                    $this->event->load(array(
                        'eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($this->event->eventCurrentCount == 2, 'Test 5f:- POST to addAttend expect eventCurrentCount == 2 ,received ' . $this->event->eventCurrentCount);
                    $attendeeCount = $this->attendee->count(array(
                        'requestStatus ="Booked" and eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($attendeeCount == 2, 'Test 5g:- POST to addAttend expect Booked count  == 2 ,received ' . $attendeeCount);
                    // maybe use sql eqivalent SELECT * FROM `attendees` group by `requesterId`
                    $this->attendee->load(array(
                        'eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $requesterId = $this->attendee->requesterId;
                    $this->attendee->load(array(
                        'id =? ',
                        $requesterId
                    ));
                    $test->expect($this->attendee->requestComment == 'ONE/ TWO', 'Test 5g:- POST to addAttend expect requestComment == ONE/  TWO ,received ' . $this->attendee->requestComment);
                    break;
                case 6:
                    $this->db->exec('delete from events where eventId = :id', array(
                        ':id' => $this->eventInfo['eventId']
                    ));
                    $this->db->exec('delete from attendees', array());
                    $this->persons = array();
                    $this->persons[] = $this->attendee1;
                    $this->persons[] = $this->attendee2;
                    $this->eventInfo['numberOfNames'] = 2;
                    $this->eventInfo['eventLimit'] = 1;
                    $testResp = $this->event->add($this->eventInfo);
                    $this->commentAry = array(
                        'comment' => "ONE"
                    );
                    $bodyAll = array(
                        'eventInfo' => $this->eventInfo,
                        'persons' => $this->persons,
                        'comment' => $this->commentAry
                    );
                    $bodyAllJson = json_encode($bodyAll);

                    $testResp = $this->myiMock('addAttend', null, null, $bodyAllJson);
                    $attendeeCount = $this->attendee->count(array(
                        'requestStatus ="Booked" and eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($attendeeCount == 2, 'Test 6a:- POST to addAttend expect Booked count  == 2 ,received ' . $attendeeCount);
                    $this->persons = array();
                    $this->persons[] = $this->attendee4;
                    $this->persons[] = $this->attendee3;
                    $bodyAll = array(
                        'eventInfo' => $this->eventInfo,
                        'persons' => $this->persons,
                        'comment' => $this->commentAry
                    );
                    $bodyAllJson = json_encode($bodyAll);
                    $testResp = $this->myiMock('addAttend', null, null, $bodyAllJson);
                    $attendeeCount = $this->attendee->count(array(
                        'requestStatus ="Booked" and eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($attendeeCount == 2, 'Test 6b:- POST to addAttend expect Booked count  == 2 ,received ' . $attendeeCount);
                    $attendeeCount = $this->attendee->count(array(
                        'requestStatus ="Waitlisted" and eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($attendeeCount == 2, 'Test 6c:- POST to addAttend expect Waitlisted count  == 2 ,received ' . $attendeeCount);
                    $this->event->load(array(
                        'eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($this->event->eventCurrentCount == 4, 'Test 6d:- POST to addAttend expect eventCurrentCount == 4 ,received ' . $this->event->eventCurrentCount);
                    // increase event capacity
                    $this->eventInfo['eventLimit'] = 4;
                    $testResp = $this->event->add($this->eventInfo);
                    // ensure no more attendees marked booked
                    $testResp = $this->myiMock('addAttend', null, null, $bodyAllJson);
                    $attendeeCount = $this->attendee->count(array(
                        'requestStatus ="Booked" and eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($attendeeCount == 2, 'Test 6e:- POST to addAttend expect Booked count  == 2 ,received ' . $attendeeCount);
                    $attendeeCount = $this->attendee->count(array(
                        'requestStatus ="Waitlisted" and eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($attendeeCount == 2, 'Test 6f:- POST to addAttend expect Waitlisted count  == 2 ,received ' . $attendeeCount);
                    $this->event->load(array(
                        'eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($this->event->eventCurrentCount == 4, 'Test 6g:- POST to addAttend expect eventCurrentCount == 4 ,received ' . $this->event->eventCurrentCount);

                    $this->persons = array();
                    $this->persons[] = $this->attendee5;
                    $this->persons[] = $this->attendee4;
                    $bodyAll = array(
                        'eventInfo' => $this->eventInfo,
                        'persons' => $this->persons,
                        'comment' => $this->commentAry
                    );
                    $bodyAllJson = json_encode($bodyAll);
                    // krumo("6H");
                    $testResp = $this->myiMock('addAttend', null, null, $bodyAllJson);
                    $attendeeCount = $this->attendee->count(array(
                        'requestStatus ="Booked" and eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($attendeeCount == 2, 'Test 6h:- POST to addAttend expect Booked count  == 2 ,received ' . $attendeeCount);
                    $attendeeCount = $this->attendee->count(array(
                        'requestStatus ="Waitlisted" and eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($attendeeCount == 3, 'Test 6i:- POST to addAttend expect Waitlisted count  == 3 ,received ' . $attendeeCount);
                    $this->event->load(array(
                        'eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($this->event->eventCurrentCount == 5, 'Test 6j:- POST to addAttend expect eventCurrentCount == 5 ,received ' . $this->event->eventCurrentCount);

                    break;
                case 7:
                    $this->db->exec('delete from events where eventId = :id', array(
                        ':id' => $this->eventInfo['eventId']
                    ));
                    $this->db->exec('delete from attendees', array());
                    $this->persons = array();
                    $this->persons[] = $this->attendee1;
                    $this->persons[] = $this->attendee2;
                    $this->eventInfo['numberOfNames'] = 2;
                    $this->eventInfo['eventLimit'] = 0;
                    $testResp = $this->event->add($this->eventInfo);
                    $bodyAll = array(
                        'eventInfo' => $this->eventInfo,
                        'persons' => $this->persons,
                        'comment' => $this->commentAry
                    );
                    $bodyAllJson = json_encode($bodyAll);

                    $testResp = $this->myiMock('addAttend', null, null, $bodyAllJson);
                    $attendeeCount = $this->attendee->count(array(
                        'requestStatus ="Booked" and eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($attendeeCount == 2, 'Test 7a:- POST to addAttend expect Booked count  == 2 ,received ' . $attendeeCount);
                    // krumo($testResp);
                    $test->expect($testResp == 'Booked', 'Test 7b:- POST to addAttend expect response Booked  ,received ' . $testResp);
                    $numBooked = $this->event->eventBooked;
                    $test->expect($numBooked == 2, 'Test 7c:- POST to addAttend expect eventBooked  == 2 ,received ' . $numBooked);
                    // krumo("7D "); 
                    //krumo($this->eventInfo);
                    $this->persons[] = $this->attendee1;
                    $this->persons[] = $this->attendee3;

                    $bodyAll = array(
                        'eventInfo' => $this->eventInfo,
                        'persons' => $this->persons,
                        'comment' => $this->commentAry
                    );
                    $bodyAllJson = json_encode($bodyAll);
                    $testResp = $this->myiMock('addAttend', null, null, $bodyAllJson);
                    $attendeeCount = $this->attendee->count(array(
                        'requestStatus ="Booked" and eventId =?',
                        $this->eventInfo['eventId']
                    ));
                    $test->expect($attendeeCount == 3, 'Test 7d:- POST to addAttend expect Booked count  == 3 ,received ' . $attendeeCount);
                    // krumo($testResp);
                    $test->expect($testResp == 'Booked', 'Test 7e:- POST to addAttend expect response Booked  ,received ' . $testResp);

                    break;
            }
            // krumo($test->results());
            // return;
            return $test;

        /**
         * *$f3->set('QUIET', TRUE); // do not show output of the active route
         * $this->myiMock('addAttend'); // set the route that f3 will run
         * // mocking test here
         * $f3->set('QUIET', FALSE); // allow test results to be shown later
         * $f3->clear('ERROR'); // clear any errors **
         */
            // Display the results; not MVC but let's keep it simple
        }

        /**
         * *************** Unused TESTS ******************
         */
        public function testattend2()
        {
            /**
             * **** various test public functions *
             */
            $f3 = Base::instance();
            $uselog = $f3->get('uselog');
            $apiLogger = new MyLog('api.log');
            $apiLogger->write('Entering testattend2', $uselog);
            require_once 'krumo/class.krumo.php';
            $web = Web::instance();
            $url = 'http://testattend.u3a.world/addeventpost';
            // $eventInfo = array('eventId' => 1001);
            $eventInfo = array(
                'eventId' => 2574,
                'eventName' => 'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
                'eventDate' => '2016-11-29',
                'eventType' => 'event',
                'eventLimit' => 55,
                'eventCurrentCount' => 11,
                'eventContactEmail' => 'laurie29.lyates@gmail.com',
                'active' => 'Y'
            );
            krumo($eventInfo);
            $bodyAll = array(
                'eventInfo' => $eventInfo
            );
            $bodyAllJson = json_encode($bodyAll);
            $options = array(
                'method' => 'POST',
                // 'content' => httpBuildQuery($bodyAllJson));
                'content' => $bodyAllJson
            );
            $resp = Web::instance()->request($url, $options);
            $apiLogger->write('testattend2 resp #463 = ' . var_export($resp, true), $uselog);
            krumo($resp);
        }

        public function testattend()
        {
            /**
             * **** various test public functions *
             */
            $f3 = Base::instance();
            require_once 'krumo/class.krumo.php';
            $event = new Event($this->db);

            /**
             * ******* Now test change of date to 23rd ****
             */
            $eventInfo = array(
                'eventId' => 2574,
                'eventName' => 'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
                'eventDate' => '2016-11-29',
                'eventType' => 'event',
                'eventLimit' => 55,
                'eventCurrentCount' => 11,
                'eventContactEmail' => 'laurie29.lyates@gmail.com',
                'active' => 'Y'
            );
            // krumo("Event ".$eventInfo['eventDate']);
            $eventInfoJson = json_encode($eventInfo);
            $event->reset();
            $this->addEvent($eventInfoJson);
            // return 0;

            $event->load(array(
                'eventId =?',
                2574
            ));
            $event->erase();
            $eventInfo = array(
                'eventId' => 2574,
                'eventName' => 'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
                'eventDate' => '2016-10-09',
                'eventType' => 'event',
                'eventLimit' => 55,
                'eventCurrentCount' => 9,
                'eventContactEmail' => 'laurie9.lyates@gmail.com',
                'active' => 'Y'
            );
            $eventInfoJson = json_encode($eventInfo);
            // krumo("Brand new Event ".$eventInfo['eventDate']);
            $this->addEvent($eventInfoJson);
            $test1 = $event->load();

            /**
             * ******* Now test change of date to 10th ****
             */
            $eventInfo = array(
                'eventId' => 2574,
                'eventName' => 'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
                'eventDate' => '2016-10-10',
                'eventType' => 'event',
                'eventLimit' => 55,
                'eventCurrentCount' => 10,
                'eventContactEmail' => 'laurie10.lyates@gmail.com',
                'active' => 'Y'
            );
            $event->reset();
            $eventInfoJson = json_encode($eventInfo);
            // krumo(" Event ".$eventInfo['eventDate']);
            $this->addEvent($eventInfoJson);
            // return 0;

            /**
             * ******* Now test change of date to 23rd ****
             */
            $eventInfo = array(
                'eventId' => 2574,
                'eventName' => 'fiddler-on-the-roof-the-musical-at-the-salon-theatre-in-fuengirola',
                'eventDate' => '2016-10-29',
                'eventType' => 'event',
                'eventLimit' => 55,
                'eventCurrentCount' => 29,
                'eventContactEmail' => 'laurie29.lyates@gmail.com',
                'active' => 'Y'
            );
            // krumo("Event ".$eventInfo['eventDate']);
            $event->reset();
            $eventInfoJson = json_encode($eventInfo);
            $this->addEvent($eventInfoJson);
            $test2 = $event->load();
        }

        public function testattend3()
        {
            // test of remote delete
            $url = 'http://testattend.u3a.world/event';
            $options = array(
                'method' => 'POST',
                'content' => array(
                    'id' => 3002,
                    'action' => 'trash'
                )
            );

            $resp = Web::instance()->request($url, $options);
            // krumo($resp);
        }

        public function myiMock($pattern, $args, $headers, $body)
        {
            $url = $this->baseUrl . '/' . $pattern;
            $options = array(
                'method' => 'POST',
                'content' => $body
            );

            return Web::instance()->request($url, $options)['body'];
        }

        private function clearAttendees($eventId)
        {
            // $attendee->load(array('eventId =?', $eventId)));
            $this->db->exec('delete from attendees where eventId = :id', array(
                ':id' => $eventId
            ));
        }

        private function clearEvent($eventId)
        {
            $this->db->exec('delete from events where eventId = :id', array(
                ':id' => $eventId
            ));
        }

        private function fiddle()
        {
            require_once 'krumo/class.krumo.php';
            $f3 = Base::instance();

            $json1 = '{   "total": "xxx",
  "page": "yyy",
  "records": "zzz",
  "rows" : [
    {"id" :"1", "cell" :["cell11", "cell12", "cell13"]},
    {"id" :"2", "cell":["cell21", "cell22", "cell23"]}  ]}';
            krumo($json1);
            echo '<br>';
            krumo(json_decode($json1, true));
            $json2 = '{   "totalpages" : 1,
  "currpage" : 1,
  "totalrecords" : 84,
  "eventdata" : [    {"id" : "1", "eventName":"cell11", "eventDate" :"cell12", "eventContactEmail" :"cell13"},
    {"id" : "2", "eventName":"cell21", "eventDate" :"cell22", "eventContactEmail" :"cell23"} ]}';
            krumo($json2);
            echo '<br>';
            krumo(json_decode($json2, true));
            $this->event->load(array(
                'eventId =?',
                $this->eventInfo['eventId']
            ));

            krumo($this->event->eventName);

            $eventCount = $this->event->count(array(
                'active = "Y"'
            ));
            krumo($eventCount);
            $events = $this->event->find(array(
                'active = "Y"'
            ));

            $eventArray = array(
                'totalpages' => 1,
                'currpage' => 1,
                'totalrecords' => $eventCount,
                'eventdata' => array()
            );
            foreach ($events as $eventnum => $event) {
                // krumo($event);
                $eventArray['eventdata'][] = array(
                    'id' => $event->id,
                    'eventName' => $event->eventName,
                    'eventDate' => $event->eventDate,
                    'eventContactEmail' => $event->eventContactEmail,
                    'eventLimit' => $event->eventLimit,
                    'eventCurrentCount' => $event->eventCurrentCount,
                    'eventFull' => $event->eventFull
                );
                // $eventArray['eventdata'][] = array('id' => $event->id, 'cell' => array($event->eventName, $event->eventDate, $event-> eventContactEmail));
            }
            krumo($eventArray);
            krumo(json_encode($eventArray));
        }
    }
}
