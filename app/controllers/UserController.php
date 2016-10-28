<?php

class UserController extends Controller {

	public function index()
    {$f3=$this->f3;
        $user = new User($this->db);
        $f3->set('users',$user->all());
        $f3->set('page_head','Manage Users');
        $f3->set('message', $f3->get('PARAMS.message'));
        $f3->set('view','admin/list.htm');
		
		$f3->set('page_role',$f3->get('SESSION.user_role'));

	}
	
		
 
public function changeme()
{$f3=$this->f3;
      $user = new User($this->db);
	  $admin_logger = new Log('admin.log');
		$admin_logger->write('in User changeme');
       // $f3->set('users',$user->all());
		$user->getByUser($f3->get('SESSION.user_id'));
        $f3->set('message', $f3->get('PARAMS.message'));
		
		 if($f3->exists('POST.currentPassword'))
        {$admin_logger->write('in User changeme current pwd '.$f3->get('POST.currentPassword'));
		$admin_logger->write('in User changeme current userid '.$f3->get('SESSION.user_id'));
		//$chpw=new MemberController;
		if ($this->checkpwd($f3->get('SESSION.user_id'),$f3->get('POST.currentPassword') ) ){
		
		
		$pwdcrypt=$user->password;
		
		$admin_logger->write('in User changeme after checkpwd succeed');
		$admin_logger->write('in User changeme original pwdcrypt '.$pwdcrypt);
		$pwdcrypt=crypt($f3->get('POST.newPassword'),$pwdcrypt);
		$admin_logger->write('in User changeme new pwdcrypt '.$pwdcrypt);
		$user->password= $pwdcrypt;
		$user->update();
		$f3->reroute('/members');
				}
		else {
		$admin_logger->write('in User changeme after checkpwd failed');
		
		}
		
		}
		elseif($f3->exists('POST.newEmail'))
		{
		//$user->getById();
		$user->email= $f3->get('POST.newEmail');
		$user->update();
		$f3->reroute('/members');
		}
		$admin_logger->write('in User changeme not POST user_id = '.$f3->get('SESSION.user_id'));
        $f3->set('view','admin/changeme.htm'); 
		
		
		//$user->getById($f3->get('SESSION.user_id'));
		$f3->set('username',$user->username); 
		$f3->set('useremail',$user->email);		
		$f3->set('page_head','Change Password or Email for User  '.$user->username);
		$f3->set('page_role',$f3->get('SESSION.user_role'));
}
function checkpwd($thisuserid,$thispassword) { 
	$f3=$this->f3;
	$auth_logger = new Log('auth.log');
			$memuser = new DB\SQL\Mapper($this->db, 'mem_users'); 
			
		//$thisuser=$memuser->load(array('username =:user',array(':user'=> $f3->get('POST.user_id')) ) );
			$thisuser=$memuser->load(array('username =:user',array(':user'=> $thisuserid)));
			//$auth_logger->write( 'the posted password = '.$f3->get('SESSION.password'))	;
			$auth_logger->write( 'the posted userid/name = '.$thisuserid);
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
			
			if ($pwdcrypt!=crypt($thispassword,$pwdcrypt))/*****check Posted  the database ***/
				{$auth_logger->write( 'encrypted password NOT equal to posted password which was = '.$thispassword)	;
				$auth_logger->write( 'encrypted password NOT equal to posted password which was = '.crypt($thispassword,$pwdcrypt))	;
	/*****		$f3->get('POST.user_id')!=$f3->get('user_id') ||
				crypt($f3->get('POST.password'),$crypt)!=$crypt)********/
				$f3->set('message','Invalid original password');
				return false;}
			else {$auth_logger->write( 'encrypted password IS equal to POST.password which was = '.$pwdcrypt)	;
		
				$f3->set('SESSION.lastseen',time());
				
				
				$auth_logger->write( 'Exiting checkpwd SESSION.user_id= '.$f3->get('SESSION.user_id'  ) );
				$auth_logger->write( 'Exiting checkpwd SESSION.user_role= '.$f3->get('SESSION.user_role'  ) );
				$auth_logger->write( 'Exiting checkpwd SESSION.lastseen= '.$f3->get('SESSION.lastseen'  ) );
				return true;
			}
		
	return true;
		}

}