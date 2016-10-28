<?php

class Option extends DB\SQL\Mapper {

    public function __construct(DB\SQL $db) {
        parent::__construct($db,'optionsu3a');
			
    }
	public function initmjl1start () { //sets the start month for when we offer MJL1 for new joiners, saved in SESSION.mjl1startmonth
	//and also the end from SESSION.u3astartmonth (saved in initlastu3ayear() called from member controller index
	$fw=Base::instance();
	if(!$fw->exists('SESSION.mjl1startmonth')) {
		$this->load('optionname="mjl1_start_month"');
		$fw->set('SESSION.mjl1startmonth', $this->optionvalue);		}
	}
	public   function initu3ayear(){
	$fw=Base::instance();

	//var_export($fw->get('SESSION',false)); //LEY
	if(!$fw->exists('SESSION.u3ayear')) {
  $today = getdate();
	  $thismon= $today['mon'];
	  $thisyear = (string) $today['year'];
	  $lastyear = (string) $today['year'] -1;
	  $nextyear = (string) $today['year'] +1;
	  $this->load('optionname="u3a_year_start_month"');
	$whichmonth = $this->optionvalue;
	$whichmonth = $this->optionvalue;
	$fw->set('SESSION.fyear', $thisyear);
	$fw->set('SESSION.u3astartmonth', $whichmonth);
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
}	
public function initemailsettings ()

{	$fw=Base::instance();
	if(!$fw->exists('SESSION.allowwelcomeemail')) {
		
		$this->load('optionname="allowwelcomeemail"');
		$allowwelcomeemail = FALSE;
		
		if($this->optionvalue =='TRUE') $allowwelcomeemail = TRUE; ;
		$fw->set('SESSION.allowwelcomeemail',  $allowwelcomeemail);
		
		$this->load('optionname="welcomemail_fromaddress"');
		$welcomemail_fromaddress = $this->optionvalue;
		$fw->set('SESSION.welcomemail_fromaddress',  $welcomemail_fromaddress);
		
		
	}
	
	
}

}
