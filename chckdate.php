<?php
//validate year and month is bitween current year and mounth from 2020-07 july,2020
//


function chkYearMonth($y,$m){
//$y='2020';
//$m='01';

if(preg_match('/^20[23]\d$/',$y,$match)){ //2020-2039 match
    //print_r($match);
    $ans_y=$match[0];
}
else{
    $ans_y=date('y');
    //print'no match';
}

if(preg_match('/^(0[1-9]|1[0-2])$/',$m,$match)){ //match 01-12
    //print_r($match);
    $ans_m=$match[0];
}
else{
    //print'no match';
    $ans_m=date('m');
}

$int_y=intval($ans_y);
$int_m=intval($ans_m);



//print $int_y;print $int_m;
if( $int_y >= intval(date('Y'))){ // if int_y is current year or after the year
    $ans_y=date('Y');
    
    if($int_m>intval(date('m'))){
        $ans_m=date('m');
    }
}

if( $int_y == 2020){
    $ans_y='2020';
    if($int_m<7){
        $ans_m='07';
    }
}
if( $int_y < 2020){
    $ans_y='2020';
    $ans_m='07';
    
}
//print $ans_y;
//print $ans_m;
$ans=[$ans_y,$ans_m];
return  $ans;
}
//print_r( chkYearMonth('2021','07'));
//print_r( chkYearMonth('2023','10'));
//print_r( chkYearMonth('2019','10'));



function chkMysqlDate($datestring){
    if(preg_match('/^((19|20)\d{2})(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])$/',$datestring,$match)){
        if(checkdate($match[3], $match[4],$match[1])){
            return $match[0]; //yyyyMMdd
        }else{
            return ''; //invalid date
        }

    }elseif(preg_match('/^((19|20)\d{2})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/',$datestring,$match)){
        if(checkdate($match[3], $match[4],$match[1])){
            return $match[0]; //yyyy-MM-dd
        }else{
            return ''; //invaild date
        }
    }elseif(preg_match('/^((19|20)\d{2})\/(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])$/',$datestring,$match)){
        if(checkdate($match[3], $match[4],$match[1])){
            return $match[0]; //yyyy/MM/dd
        }else{
            return ''; //invaild date
        }
    }else{
        return '';

    }
}
