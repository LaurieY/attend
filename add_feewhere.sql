#ALTER TABLE  `members` ADD  `feewhere` VARCHAR( 32 ) NOT NULL DEFAULT  'Treasurer' AFTER  `datepaid`;
#ALTER TABLE  `trail` ADD  `feewhere` VARCHAR( 32 )  NULL AFTER  `datepaid`;

ALTER TABLE  `members` ADD  `fyear` VARCHAR( 4 ) NULL AFTER  `feeewhere` ,ADD  `u3ayear` VARCHAR( 10 ) NULL AFTER  `fyear`;
UPDATE `members` SET `u3ayear` ='2014-2015' WHERE 1;
UPDATE `members` SET `fyear` ='2015' WHERE `membtype` ='MJL1';
UPDATE `members` SET `fyear` ='2014' WHERE `membtype` ='MJL2';
UPDATE `members` SET `fyear` ='2014' WHERE `fyear` is NULL  ;
UPDATE `members` SET `fyear` ='2015' WHERE  membnum in (57,85,174,177,179,267,287,315,316,359,419,420,425,426,434,435,441,448,449,473,488);

SELECT count(*), sum(`amtpaidthisyear`) FROM `members` WHERE `fyear` ='2014';
SELECT count(*), sum(`amtpaidthisyear`) FROM `members` WHERE `fyear` ='2015';

SELECT `membtype`,count(*), sum(`amtpaidthisyear`) FROM `members` WHERE `fyear` ='2014' group by `membtype`;

SELECT `membtype`,count(*), sum(`amtpaidthisyear`) FROM `members` WHERE `fyear` ='2015' group by `membtype`;


