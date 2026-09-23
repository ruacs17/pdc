<?php require_once('templ_up.php');?>
<?php
$qUser = $dbMain->select('users','*',array('username'=>$username));
$userInfo=$dbMain->fetch_array($qUser);
$msg='';
$changeP=0;
if( isset($_POST['btnChangePass']) ){
    $changeP=1;
}
if( isset($_POST['btnChange']) ){
    $changePs=( isset($_POST['changeP']) ) ? $_POST['changeP'] : 0;
    $changeP = ($changePs==1) ? 1 : 0;
    $oldPass = ( isset($_POST['txOldPass']) ) ? $_POST['txOldPass'] : "";
    $newPass = ( isset($_POST['txNewPass']) ) ? $_POST['txNewPass'] : "";
    $cnewPass = ( isset($_POST['txCNewPass']) ) ? $_POST['txCNewPass'] : "";
    if($oldPass=="" || $newPass=="" || $cnewPass==""){
        $msg='<div class="alert alert-error"><strong>Failed!</strong> Please fill up the form properly!</div>';
    }
    else if( strlen($newPass) <= 5 ){
        $msg='<div class="alert alert-error"><strong>Failed!</strong> Password must be atleast 6 characters!</div>';
    }
    else if( $newPass != $cnewPass){
        $msg='<div class="alert alert-error"><strong>Failed!</strong> Confirmation Password does not match!</div>';
    }
    else if( functions::encode($oldPass)!=$userInfo['password'] ){
        $msg='<div class="alert alert-error"><strong>Failed!</strong> Old Password does not match!</div>';
    }
    else if( functions::encode($oldPass)===$userInfo['password'] ){
        #$logs->save('change password');
        $dbMain->update('users',array('password'=>functions::encode($newPass)),array('username'=>$username));
        functions::say("Password successfully changed.");
        functions::sendTo($_SERVER['PHP_SELF']);
        die();
    }
}
?>
<!-- body content: start here-->
<form method="post">
    <div class="row-fluid">
        <div class="box span7 ">
            <div class="box-header" data-original-title="">
                <h2>
                    <i class="halflings-icon white list-alt"></i><span class="break"></span>My Information
                </h2>
            </div>
            <div class="box-content">
                <table class="table">
                    <tr>
                        <td width="40%"><i>Username</i></td>
                        <td width="60%" class="center"><?php echo $userInfo['username']?></td>
                    </tr>
                    <tr>
                        <td><i>First Name</i></td>
                        <td class="center"><?php echo strtoupper($userInfo['fname'])?></td>
                    </tr>
                    <tr>
                        <td><i>Middle Name</i></td>
                        <td class="center"><?php echo strtoupper($userInfo['mname'])?></td>
                    </tr>
                    <tr>
                        <td><i>Last Name</i></td>
                        <td class="center"><?php echo strtoupper($userInfo['lname'])?></td>
                    </tr>
                    <tr>
                        <td><i>E-mail Address</i></td>
                        <td class="center"><?php echo $userInfo['email']?></td>
                    </tr>
                    <tr>
                        <td><i>Mobile Number</i></td>
                        <td class="center"><?php echo $userInfo['mobile']?></td>
                    </tr>                 
                </table>
                <div align="right"><input type="submit" name="btnChangePass" id="btnChangePass" class="btn btn-primary" value="Change Password"></div>
            </div>
        </div>
  <!--/span-->
<?php if($changeP){?>
    <div class="box span5">
        <div class="box-header" data-original-title="">
            <h2>
                <i class="icon-lock white list-alt"></i><span class="break"></span>Change Password
            </h2>
            <div class="box-icon">
                <a href="#" class="btn-minimize">
                    <i class="halflings-icon white chevron-up"></i>
                </a>
            </div>
        </div>
        <div class="box-content">
            <table class="table">
                <tr>
                    <td width="40%"><i>Old Password</i></td>
                    <td width="60%" class="center"><input type="password" id="txOldPass" name="txOldPass" value=""></td>
                </tr>
                <tr>
                    <td><i>New Password</i></td>
                    <td class="center"><input type="password" id="txNewPass" name="txNewPass" value=""></td>
                </tr>
                <tr>
                    <td><i>Confirm Password</i></td>
                    <td class="center"><input type="password" id="txCNewPass" name="txCNewPass" value=""></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td class="center"><input type="submit" class="btn btn-primary" name="btnChange" id="btnChange" value="Save"></td>
                </tr>         
            </table>
        </div>
          <?php echo $msg;?>
    </div><!--/span-->
    <?php }?>
</div>
<!--/row-->
<input type="hidden" name="changeP" id="changeP" value="<?php echo $changeP;?>">
</form>
<!-- body content: end here-->
<?php require_once('templ_down.php');?>