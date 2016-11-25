<?php
class LoginController extends Controller{
function startup() {
	$f3=$this->f3;
	 $f3->set('message','');
	$login_logger = new Log('login.log');
	$login_logger->write( 'Entering LoginController startup URI= '.$f3->get('URI'  ) );
	if (!$f3->exists('COOKIE.PHPSESSID')){
	$login_logger->write( 'In LoginController No COOKIE.PHPSESSID ');
	}
	$f3->reroute('/login');
}
function auth() {
	$f3=$this->f3;
	$f3->clear('message');
	
//$f3->set('page_head','Login');
		$auth_logger = new Log('auth.log');
		$auth_logger->write( 'In auth ');
		//if (!$f3->get('COOKIE.sent'))
		if (!$f3->get('COOKIE.PHPSESSID'))
			{$f3->set('message','Cookies must be enabled to enter this area');
			$auth_logger->write( 'In auth Cookies must be enabled to enter this area');
			$auth_logger->write( ' COOKIE contents = '.var_export($f3->get('COOKIE'), true));
			$auth_logger->write( ' SESSION contents = '.var_export($f3->get('SESSION'), true));
		//	echo var_export($f3->get('COOKIE'),true);
			//echo var_export($f3->get('SESSION'), true);
			$f3->reroute('/nocookie');
			}
		else {/***********
	****/
	$auth_logger->write( 'In auth Cookies ARE enabled');
			$auth_logger->write( ' COOKIE contents = '.var_export($f3->get('COOKIE'), true));
			$auth_logger->write( ' SESSION contents = '.var_export($f3->get('SESSION'), true));
	$thisuserid= $f3->get('POST.user_id');
	$thispassword = $f3->get('SESSION.password') ;
		if ($this->checkpwd($thisuserid,$thispassword) ){
		
		$f3->reroute('/attend2');
		
		}
		else 
		$this->login($f3); 
		//$f3->reroute('/login');
		}
	}	
function checkpwd($thisuserid,$thispassword) { 
	$f3=$this->f3;
	$auth_logger = new Log('auth.log');
			$memuser = new DB\SQL\Mapper($this->db, 'attend_users'); 
			
		//$thisuser=$memuser->load(array('username =:user',array(':user'=> $f3->get('POST.user_id')) ) );
			$thisuser=$memuser->load(array('username =:user',array(':user'=> $thisuserid)));
			//$auth_logger->write( 'the posted password = '.$f3->get('SESSION.password'))	;
			$auth_logger->write( 'checkpwd the posted userid/name = '.$thisuserid);
			//$auth_logger->write( 'the posted username = '.$thisuser);
			$auth_logger->write( 'the posted password = '.$thispassword);
			if($memuser->loaded() ){
			$auth_logger->write( 'thisusers loaded count = '.$memuser->loaded())	;
			$auth_logger->write( 'thisuser = '.$thisuser->username)	;
			}
			else 
			return false;
			$pwdcrypt=$thisuser->password;
			$auth_logger->write( 'this encrypted password = '.$pwdcrypt)	;
			$magiccaptcha='FE7O1';
			$captcha= $f3->get('SESSION.captcha');
			$auth_logger->write( 'captcha received = '.$f3->get('POST.captcha'));
			$auth_logger->write( 'magic captcha  = '.$magiccaptcha);
			$auth_logger->write( 'IP start  = '.substr($f3->get('IP'),0,9));
			$failpost=$captcha && strtoupper($f3->get('POST.captcha'))!=$captcha;
			$failmagic =false;
			
			if ($thisuser->username!='admin') {$failmagic = ($captcha && strtoupper($f3->get('POST.captcha'))!=$magiccaptcha);
			$failip = (substr($f3->get('IP'),0,9)!='192.168.1');
			}
			
			$auth_logger->write( ' fail failpost='.$failpost.' fail failmagic='.$failmagic.' fail failip='.$failip);
			if ($failpost && ($failmagic ||$failip))
			{$f3->set('message','Invalid CAPTCHA code');
				return false;}
			elseif ($pwdcrypt!=crypt($f3->get('POST.password'),$pwdcrypt))/*****check Posted  the database ***/
				{$auth_logger->write( 'encrypted password NOT equal to POST.password which was = '.$f3->get('POST.password'))	;
	/*****		$f3->get('POST.user_id')!=$f3->get('user_id') ||
				crypt($f3->get('POST.password'),$crypt)!=$crypt)********/
				$f3->set('message','Invalid user ID or password');
				return false;}
			else {$auth_logger->write( 'encrypted password IS equal to POST.password which was = '.$f3->get('POST.password'))	;
				//$f3->clear('COOKIE.sent');
				
				
				$f3->clear('SESSION.captcha');
				$f3->set('SESSION.user_id',$f3->get('POST.user_id'));
				$f3->set('SESSION.crypt',$pwdcrypt);
				$f3->set('SESSION.user_role',$thisuser->role);
				$f3->set('SESSION.lastseen',time());
			
				
				$auth_logger->write( 'Exiting checkpwd SESSION.user_id= '.$f3->get('SESSION.user_id'  ) );
				$auth_logger->write( 'Exiting checkpwd SESSION.user_role= '.$f3->get('SESSION.user_role'  ) );
				$auth_logger->write( 'Exiting checkpwd SESSION.lastseen= '.$f3->get('SESSION.lastseen'  ) );
				return true;
			}
		
	return true;
		}

function login() {
	$f3=$this->f3;
		$login_logger = new Log('login.log');
		//$login_logger->erase();
	$login_logger->write( 'Entering login'  );
/*	$login_logger->write( 'Root = '.$f3->get('ROOT')   );
	$login_logger->write( 'Base = '.$f3->get('BASE')   );
	$login_logger->write( 'Ui = '.$f3->get('PATH')   );
	$login_logger->write( 'Path = '.$f3->get('UI')   );
	$login_logger->write( 'Logs = '.$f3->get('LOGS')   ); */
	//$mysession = http_build_query($f3->get('SESSION'));
	//$f3->dump($mysession   );
		//$f3->clear('SESSION');
		if($f3->exists('SESSION.u3ayear'  ) ) {$f3->clear('SESSION.u3ayear');
		$f3->clear('SESSION.lastu3ayear');}
		if ($f3->get('eurocookie')) {
		$login_logger->write( 'IN login IN Eurocookie'  );
		/*	$loc=Web\Geo::instance()->location(); // innecessary because we ARE in the EU
			$f3->set('message','Cookies Set');
			if (isset($loc['continent_code']) && $loc['continent_code']=='EU')
			*/
				
			$f3->set('message',
					'The administrator pages of this Web site uses cookies '.
					'for identification and security. Without these '.
					'cookies, these pages would simply be inaccessible. By '.
					'using these pages you agree to this safety measure.');
$login_logger->write( 'In login in continent==EU'  );
		}
		F3::set('FONTS','ui/fonts/');
	/*	$fontdir=http_build_query(scandir('ui'));
		$login_logger->write( 'Fonts = '.$f3->get('FONTS')   )	;
		$login_logger->write( 'UI dir contains= '.$fontdir   )	;
		$login_logger->write( 'Session.captcha = '.get_class($f3-> get( 'SESSION.captcha' )))	;
		$login_logger->write( 'Session.captcha = '.$f3-> get( 'SESSION.captcha' ))	;
		****/
		
		//$f3->set('COOKIE.sent',TRUE);
		$img = new Image();
		//$fred=$img->captcha('ui/fonts/thunder.ttf',16,5);
		$login_logger->write( 'message contains= '.$f3->get('message'))	;
		if ($f3->get('message')) {
			$img=new Image;
			// $finfo = finfo_open(FILEINFO_MIME_TYPE);
			//$finfofile=  finfo_file($finfo, 'ui/fonts/thunder.ttf') ;
		/*	$login_logger->write( 'file details = '.$finfofile)	;
			$capt = $img->captcha('ui/fonts/thunder.ttf',18,5,'SESSION.captcha');
			$login_logger->write( 'image class is = '.get_class($img   ))	;
			$login_logger->write( 'captcha contains= '.get_class($capt   ))	;
			***/
			$f3->set('captcha',$f3->base64(
				$img->captcha('ui/fonts/thunder.ttf',18,5,'SESSION.captcha')->
					dump(),'image/png'));
		}
		//$mysession = http_build_query($f3->get('SESSION'));
		//$f3->dump($mysession   );
	$login_logger->write( 'In  login setting page_head'  );
	if ($f3->get('COOKIE.PHPSESSID'))
	$login_logger->write( ' COOKIE PHPSESSID exists contents = '.var_export($f3->get('COOKIE'), true));
	else {
	$login_logger->write( ' COOKIE PHPSESSID NOT exists contents = '.var_export($f3->get('COOKIE'), true));
			$this->f3->reroute('/nocookie');}
			$f3->set('page_head','Login');
		$f3->set('page_role','');
		$f3->set('view','member/login.htm');
		$f3->set('SESSION.lastseen',time()); 

	}
	

	//! Terminate session
function logout() {
	//$f3=$this->f3;
		$this->f3->clear('SESSION');
		
		$this->f3->reroute('/login');
	}		
	
}
