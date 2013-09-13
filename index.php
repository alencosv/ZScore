<?php

include 'ZScore.php';

$weight = 10.5;
$height = 90; 
$length = 75;
$age = 3;
$hc = 45;
$bmi=18.8;

$score = new ZScore();
$score->set_index(ZScore::WEIGHT_FOR_AGE);
$score->set_age($age,ZScore::AGE_YEAR);
$score->set_gender(ZScore::GENDER_BOY);
$score->set_weight($weight);
$score->set_height($height);
$score->set_length($length);
$lms = $score->get_lms();
$zscore = $score->get_zind($lms);
print " Age is:  ".$score->get_age().". ZScore is : \n".$zscore."\n";  
?>
