DROP TABLE IF EXISTS events;
CREATE TABLE events(
  id int(11) NOT NULL AUTO_INCREMENT,
  eventId int(11) NOT NULL,
  eventName varchar(128) NOT NULL,
  eventDate date NOT NULL,
  eventType varchar(16) NOT NULL,
  eventContactEmail varchar(128) NOT NULL,
  eventLimit int(3) NOT NULL,
  eventCurrentCount int(3) DEFAULT 0,
  active char(1) DEFAULT 'Y',
  eventBooked int(3) DEFAULT 0,
  eventWaitlisted int(3) DEFAULT 0,
  eventFull boolean default FALSE,
  createdAt datetime NOT NULL,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_date` (`eventId`,eventDate)

 
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1581 ;

DROP TABLE IF EXISTS events_archive;
CREATE TABLE events_archive(
  id int(11) NOT NULL AUTO_INCREMENT,
  eventId int(11) NOT NULL,
  event_name varchar(128) NOT NULL,
  eventDate date NOT NULL,
  event_type varchar(16) NOT NULL,
  event_contact_email varchar(128) NOT NULL,
  event_limit int(3) NOT NULL,
  event_current_count int(3) DEFAULT 0,
  active char(1) DEFAULT 'N',
  createdAt datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
  
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1581 ;

DROP TABLE IF EXISTS attendees;
CREATE TABLE attendees(
  id int(11) NOT NULL AUTO_INCREMENT,
  eventId int(11) NOT NULL,
  eventDate date NOT NULL,
  eventType varchar(8) NOT NULL,
  name varchar(128) NOT NULL,
  membnum int (6) DEFAULT NULL,
  memberPaid varchar(1) DEFAULT NULL,
  memberGuest varchar(1) NOT NULL,
  requesterEmail varchar(128) NOT NULL,
  requesterId int(11) ,
  requester boolean default NULL, 
  requestCount int(3) default 0,
  requestStatus  varchar(32) DEFAULT 'Requested',
  requestComment varchar(1024) DEFAULT NULL,
  createdAt datetime NOT NULL,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
   UNIQUE KEY `id` (`id`),
   KEY `eventId_date_name` (`eventId`,`eventDate`,name),
  KEY index_attendees_on_eventId (eventId),
  KEY index_attendees_on_requester (requester)

) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1581 ;

DROP TABLE IF EXISTS attendee_comments;
CREATE TABLE attendee_comments(
	id int(11) NOT NULL AUTO_INCREMENT,
	eventId int(11) NOT NULL,
	eventDate date NOT NULL,
	name varchar(128) NOT NULL,
	membnum int (6) DEFAULT NULL,
	requestComment varchar(1024) DEFAULT NULL,
	createdAt datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
  
	  UNIQUE KEY index_attendee_comments_on_eventId_name (eventId,name)

) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1581 ;

DROP TABLE IF EXISTS events_trail;
CREATE TABLE events_trail(
  id int(11) NOT NULL AUTO_INCREMENT,
  eventId int(11) NOT NULL,
	body_json varchar(1024) NOT NULL,
  createdAt datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY index_events_trail_on_eventId(eventId)

) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1581 ;


DROP TABLE IF EXISTS `attend_users`;
CREATE TABLE IF NOT EXISTS `attend_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(40) NOT NULL,
  `password` varchar(128) NOT NULL,
  `email` varchar(64) NOT NULL,
  `role` varchar(10) NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=40 ;

--
-- Dumping data for table `mem_users`
--

INSERT INTO `attend_users` (`id`, `username`, `password`, `email`, `role`) VALUES
(14, 'laurie2', '$2y$12$PKE3PyWG5qtp7lhIx43Qx.va1q9ccFBwd5I3Kc02dal8R4m1alvgy', 'laurie2@lyates.com', 'user'),
(6, 'laurie', '$2y$12$PKE3PyWG5qtp7lhIx43Qx.va1q9ccFBwd5I3Kc02dal8R4m1alvgy', 'laurie@lyates.com', 'editor'),
(23, 'admin', '$2y$12$PKE3PyWG5qtp7lhIx43Qx.va1q9ccFBwd5I3Kc02dal8R4m1alvgy', 'fred@lyates.com', 'admin'),
(30, 'brian', '$2y$12$WzAaKjMNixk4J2Nw3qeUeejlz/lAEcNdfhWKgzpXWtJWzMe0aghq6', 'monda@mercuryin.es', 'editor'),
(31, 'allan', '$2y$12$WzAaKjMNixk4J2Nw3qeUeezqOVFGqyTljyRo9qeSByMz60UC8qa/G', 'aledmarbella@gmail.com', 'admin'),
(34, 'christine', '$2y$12$WzAaKjMNixk4J2Nw3qeUeeL3rfGzMMpUQaWAgZ6F3Z6GLvm8W.Uom', 'cbkemzura@msn.com', 'user'),
(32, 'aine', '$2y$12$WzAaKjMNixk4J2Nw3qeUeeIeN8ZRgTNEe8L82jMla.aYUn0C1Hq7m', 'aine_niorain@hotmail.com', 'user'),
(33, 'francis', '$2y$12$PKE3PyWG5qtp7lhIx43Qx.oHUIYz22pXF0cw6h742XZROTUVRAqKe', 'francis@mantarota.com', 'user'),
(35, 'peter', '$2y$12$WzAaKjMNixk4J2Nw3qeUee9LEO54Ar4m0gHm4tGxKlSrPbNPDJRRS', '2307pas@gmail.com', 'user'),
(36, 'keith', '$2y$12$WzAaKjMNixk4J2Nw3qeUeemzkJjCPo50SO5v0DnU0SxocHCe16Jqa', 'barthorpe@hotmail.co.uk', 'editor'),
(38, 'clive', '$2y$12$WzAaKjMNixk4J2Nw3qeUeeKKz2E79ndl8uUMsaEUa/OjpZeNVbimS', 'cliveper@btinternet.com', 'user'),
(39, 'laurier', '$2y$12$WzAaKjMNixk4J2Nw3qeUeeJqyxZaBgRXgK/3euAjZQitZaA6HJXUW', 'laurier@lyates.com', 'register');


DROP TABLE IF EXISTS `optionsu3a`;
CREATE TABLE IF NOT EXISTS `optionsu3a` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `optionname` varchar(255) NOT NULL,
  `optionvalue` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=29 ;



INSERT INTO `optionsu3a` (`id`, `optionname`, `optionvalue`) VALUES
(1, 'u3a_year_start_day', '1'),
(2, 'u3a_year_start_month', '7'),
(5, 'use_log', 'true'),
(6, 'weeklyemail', 'president@u3a.international'),
(7, 'weeklyemail', 'membership@u3a.es'),
(8, 'weeklyemail', 'laurieu3a@lyates.com'),
(9, 'allowwelcomeemail', 'TRUE'),
(10, 'welcomemail_fromaddress', 'president@u3a.international'),
(11, 'weeklyemail', 'barthorpe@hotmail.co.uk'),
(12, 'emailbcc', 'laurie@u3a.es'),
(13, 'weekly_members_email', 'cliveper@btinternet.com'),
(14, 'glemail', 'laurie.lyates@gmail.com'),
(15, 'allemail', 'laurie.lyates@gmail.com'),
(16, 'payersemail', 'laurie.lyates@gmail.com'),
(17, 'paidemail', 'laurie.lyates@gmail.com'),
(18, 'paidemail', 'president@u3a.es'),
(19, 'payersemail', 'president@u3a.es'),
(20, 'payersemail', 'membership@u3a.es'),
(21, 'payersemail', 'barthorpe@hotmail.co.uk'),
(22, 'paidemail', 'Registrar@u3a.es'),
(23, 'mailchimp_api', '10471974a9dd7affb5a18c3c6e5e4b3d-us2'),
(24, 'mcreportemail', 'laurie@u3a.es'),
(26, 'updatemailchimp', 'TRUE'),
(27, 'membnumcol', '2'),
(28, 'mjl1_start_month', '1');