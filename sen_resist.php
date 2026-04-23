<?php
   require_once 'DbMa.php';
   require_once 'Encode.php';
   require_once 'htmlpkg.php';


// Import the necessary classes
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;

// Include the composer autoload file
require 'vendor/autoload.php';
require_once 'sen_cnfg0001.php';
// Setup a new Eloquent Capsule instance





   $title='管理（新規ユーザ作成）';
   $h2="管理（新規ユーザ作成）";
   putHtmlHeader($title,$h2);


   if(isset($_POST['usrac0']) and isset($_POST['ipswd0']) and isset($_POST['mkey0'])){
        if($_POST['mkey0']==$SENKEY){

            $credentials = [
                'login' => $_POST['usrac0'],

                'email'    => $_POST['usrac0'],
                'password' => $_POST['ipswd0'],
                        ];

            if ($user = Sentinel::findByCredentials($credentials)){// user account alreadry exists
                print'<div>error! Its account alreadry exists</div>';

            }else{//Resister new account

                $user = Sentinel::registerAndActivate($credentials);

                print "<div>ユーザアカウント{$_POST['usrac0']}が作成されました</div>";








            }
         }else{
            print'<div>!401 forbidden</div>';
         }
    }else{
        print'<div>!400</div>';
    }
    ?>
    <div id="formcontainer">
        <form method="POST" action="sen_resist.php">
                <div  class="formparts_2">
                <div class="fml"><label class="label">e-mail(new account)</label></div><input name="usrac0" id="usrac0" type="email" size="35" maxlength="50">
                </div>
                <div  class="formparts_2">
                <div class="fml"><label class="label">password</label></div><input name="ipswd0" id="ipswd0" type="password" size="35" minlength="8" maxlength="20" >
                </div>
                <div  class="formparts_2">
                <div class="fml"><label class="label">secret key</label></div><input name="mkey0" id="mkey0" type="text" size="40" maxlength="50" >
                </div>


                <div class="submitbutton">
                <input  type="submit" value="送信／Submit">
                </div>

        </form>
    </div>
    <?php
    putHtmlContainerClose();