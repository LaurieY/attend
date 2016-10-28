<?php
//trim.php
$SQL="SELECT id,surname ,forename,membnum ,phone,mobile,email,membtype,location,paidthisyear,amtpaidthisyear,datejoined FROM members   where surname LIKE ''Yat'%' ORDER BY membnum asc LIMIT 0 , 10
";
echo $SQL."\n";
var_dump(str_replace("'", "",$SQL ));

?>