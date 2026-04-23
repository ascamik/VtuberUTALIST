<?php


require_once 'htmlpkg.php';

// Import the necessary classes
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;

// Include the composer autoload file
require 'vendor/autoload.php';

// Setup a new Eloquent Capsule instance
require_once 'sen_cnfg0001.php';



$title='管理';
$h2="管理";
putHtmlHeader($title,$h2);
putHtmltextarea();


if ($user = Sentinel::check()) {

    $re = Sentinel::logout();

    if ($re) {
        //
    
    print'<div>ログアウトしました</div>';
    }
    else {
        print'<div>fail</div>';
        // 
    }
}else{
    print'<div>ログインしていません</div>';
}
putHtmltextarea_close();
putHtmlContainerClose();
