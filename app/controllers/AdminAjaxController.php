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
 * @package   AdminAjaxController
 * @author    Author's name <author@mail.com>
 * @copyright 2016 Author's name
 * @license   http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version   CVS: $Id:$
 * @link      http://pear.php.net/package/AdminAjaxController
 * @see       References to other sections (if any)...
 */
 
 
/**
 * Short description for class
 * 
 * Long description (if any) ...
 * 
 * @category  CategoryName
 * @package   AdminAjaxController
 * @author    Author's name <author@mail.com>
 * @copyright 2016 Author's name
 * @license   http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/AdminAjaxController
 * @see       References to other sections (if any)...
 */
class AdminAjaxController extends Controller
{
    

    function afterroute() 
    {

    }
    /**
     * Short description for function
     * 
     * Long description (if any) ...
     * 
     * @return void  
     * @access public
     */    

    function feesgrid() 
    {
        $f3=$this->f3;
        $fees =    new Fees($this->db);
        $uselog=$f3->get('uselog');
        $admin_logger = new MyLog('admin.log');
        $admin_logger->write('in feesgrid ', $uselog);
        //$f3->set('page_head','Fees List');  
        header("Content-type: text/xml;charset=utf-8");
        $page = $_GET['page']; 
        $limit = $_GET['rows']; 
        $sidx = $_GET['sidx']; 
        $sord = $_GET['sord']; 
        // get count of records
        $u3ayear=$f3->get('SESSION.u3ayear');
        $admin_logger->write('in fn feesgrid u3ayear= '.$u3ayear, $uselog);
        $count=$fees->count("acyear = '".$u3ayear."'");
        if ($count > 0 && $limit > 0) {
              $total_pages = ceil($count/$limit); 
        } else { 
              $total_pages = 0; 
        }
        $admin_logger->write('in fn feesgrid count= '.$count, $uselog);
        if ($page > $total_pages) {
            $page=$total_pages;

        }
        // calculate the starting position of the rows 
        $start = $limit*$page - $limit;
        $response = new stdClass();
        // if for some reasons start position is negative set it to 0 
        // typical case is that the user type 0 for the requested page   
        if ($start <0) {
            $start = 0;
        } 
        $response->page = 1;
        $response->page = $page;
        $response->total = $total_pages;
        $response->records = $count;    

    

        $fees->load("acyear = '".$u3ayear."'");
        $i=0;
        while ( !$fees->dry() ) {  // gets dry when we passed the last record
            //$admin_logger->write('in fn feesgrid with i='.$i,$uselog);
            $response->rows[$i]['id']=$fees['id'];
            $response->rows[$i]['cell']=array($fees['membtype'],$fees['feetopay'],$fees['firstyearfee'],$fees['acyear']);
            // moves forward even when the internal pointer is on last record
            $i++;
            $fees->next();
        }
        echo json_encode($response);
    } // end of feesgrid

    public function editfees() 
    {
          $f3=$this->f3; 
           $admin_logger = new MyLog('admin.log');
          $uselog=$f3->get('uselog');
          $admin_logger->write('in editfees');    

          $fees =    new Fees($this->db);
 
          switch ($f3->get('POST.oper')) {
        case "add":  //**********************   ADD an fees
    

            $fees->copyfrom('POST');
    

            $admin_logger->write('in editfees membtype '.$fees->membtype, $uselog);
            //$admin_logger->write('in editfees feesvalue '.$feess->feesvalue,$uselog);
        

            $fees->save();
        

            break;
        case "edit":
          

          

            // $f3->get('mem_user')->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ) );
            $fees->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ));
            $admin_logger->write('in editfees membtype '.$fees->membtype, $uselog);
        

            $fees->copyfrom('POST');
    

            $fees->update();
            // do mysql update statement here
            //  /
            break;
        case "del":
            $fees->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ));
    

        

            $admin_logger->write('in delfees membtype '.$fees->membtype, $uselog);
        

        

            $fees->erase();
        

    

            // do mysql delete statement here
            break;
}        

    

    }
    function editmailinglist() 
    {
           $f3=$this->f3; 
           $admin_logger = new MyLog('admin.log');
          $uselog=$f3->get('uselog');
          $admin_logger->write('in editmailinglist');    

          $mailinglist =    new Mailinglist($this->db);
 
          switch ($f3->get('POST.oper')) {
        case "add":  //**********************   ADD an mailinglist
    

            $mailinglist->copyfrom('POST');
    

            $admin_logger->write('in editmailinglist membtype '.$mailinglist->memberselection, $uselog);
            //$admin_logger->write('in editmailinglist feesvalue '.$feess->feesvalue,$uselog);
        

            $mailinglist->save();
        

            break;
        case "edit":
          

          

            // $f3->get('mem_user')->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ) );
            $mailinglist->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ));
            $admin_logger->write('in editmailinglist membselection '.$mailinglist->memberselection, $uselog);
        

            $mailinglist->copyfrom('POST');
    

            $mailinglist->update();
            // do mysql update statement here
            //  /
            break;
        case "del":
            $mailinglist->load(array('id =:id',array(':id'=> $f3->get('POST.id')) ));
    

        

            $admin_logger->write('in delmailinglist memberselection '.$mailinglist->memberselection, $uselog);
        

        

            $mailinglist->erase();
        

    

            // do mysql delete statement here
            break;
}
    }    

    function mailinglistgrid() 
    {
         $f3=$this->f3;
         $mailinglist =    new Mailinglist($this->db);
         $uselog=$f3->get('uselog');
         $admin_logger = new MyLog('admin.log');
         $admin_logger->write('in Mailinglist ', $uselog);
    

         header("Content-type: text/xml;charset=utf-8");
         $page = $_GET['page']; 
         $limit = $_GET['rows']; 
         $sidx = $_GET['sidx']; 
         $sord = $_GET['sord']; 
         // get count of records
         //$u3ayear=$f3->get('SESSION.u3ayear');
         $admin_logger->write('in fn Mailinglistgrid ', $uselog);
         //$count=$fees->count("acyear = '".$u3ayear."'");
         $count=$mailinglist->count();
        if ($count > 0 && $limit > 0) {
             $total_pages = ceil($count/$limit); 
        } else { 
                 $total_pages = 0; 
        }
          $admin_logger->write('in fn Mailinglistgrid count= '.$count, $uselog);
        if ($page > $total_pages) {
            $page=$total_pages;
        }
        // calculate the starting position of the rows 
         $start = $limit*$page - $limit;
         $response = new stdClass();
        // if for some reasons start position is negative set it to 0 
        // typical case is that the user type 0 for the requested page 
        if ($start <0) {
            $start = 0;
        } 
         $response->page = 1;
         $response->page = $page;
         $response->total = $total_pages;
         $response->records = $count;    

    

         //$fees->load("acyear = '".$u3ayear."'");
         $mailinglist->load();
         $i=0;
        while ( !$mailinglist->dry() ) {  // gets dry when we passed the last record
            //$admin_logger->write('in fn mailinglistgrid with i='.$i,$uselog);
            $response->rows[$i]['id']=$mailinglist['id'];
            $response->rows[$i]['cell']=array($mailinglist['memberselection'],$mailinglist['mmurl'],$mailinglist['mmlist'],$mailinglist['mmpwd'],$mailinglist['memberquery']);
            // moves forward even when the internal pointer is on last record
            $i++;
            $mailinglist->next();
        }
         echo json_encode($response);
    } // end of mailinglistgrid
    

}
