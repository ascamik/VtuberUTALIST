<?php






function relclass($releasedate){
    $dateParts = explode('-', $releasedate);
    if (count($dateParts) === 3 && checkdate($dateParts[1], $dateParts[2], $dateParts[0])) {
        $unixtimerelsd=strtotime($releasedate);
        if($unixtimerelsd < strtotime('1989-01-01')){
            return 'syo'; //syouwa

        }elseif($unixtimerelsd < strtotime('2001-01-01')){
            return 'her'; //heisei early

        }elseif($unixtimerelsd < strtotime('2019-05-01')){
            return 'mil'; //heisei late millenium

        }elseif($unixtimerelsd < strtotime('now')-60*60*24*365){
            return 'rei'; //reiwa

        }else{
            return 'new'; // new
        }

    
    
    
    }else{
        return'invalid';

    }
}
