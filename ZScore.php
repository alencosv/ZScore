<?php


class ZScore {
  private $weight;
  private $height;
  private $length;
  private $head_circumference;
  private $gender;
  private $age;
  private $index;
  private $ageunit;
  private $age_group;
  private $bmi;
  private $base_path;

  private $lms =array('L'=>'','M'=>'','S'=>'');

  //Constants
  const WEIGHT_FOR_HEIGHT = "wfh";
  const WEIGHT_FOR_LENGTH = "wfl";
  const LENGTH_HEIGHT_FOR_AGE = "lhfa";
  const LENGTH_FOR_AGE = "lfa";
  const HC_FOR_AGE = "hcfa"; //Head Circumference for Age
  const HEIGHT_FOR_AGE = "hfa";
  const BMI_FOR_AGE = "bfa";
  const WEIGHT_FOR_AGE = "wfa";
  const AGE_DAYS = "days";
  const AGE_MONTH = "month";
  const AGE_YEAR = "year";
  const GENDER_BOY = "boys";
  const GENDER_GIRL = "girls";

  function reset_lms(){
    $this->lms =array('L'=>'','M'=>'','S'=>'');
  }

  function get_weight(){
    return $this->weight;
  }

  function get_ageunit(){
    return $this->ageunit;
  }

  function set_weight($weight){
    $this->weight=$weight;
  }

  function get_height(){
    return $this->height;
  }

  function set_height($height){
    $this->height=$height;
  }

  function get_length(){
    if(!is_numeric($this->length)){
      $this->length = $this->height;
    }
    return $this->length;
  }

  function set_length($length){
    $this->length=$length;
  }

  function get_gender(){
    return $this->gender;
  }

  function set_gender($gender){
    $this->gender=$gender;
  }

  function set_bmi($bmi){
    $this->bmi=$bmi;
  }

  function get_age(){
    return $this->age;
  }

  function get_head_circumference(){
    return $this->head_circumference;
  }

  function set_head_circumference($head_circumference){
    $this->head_circumference = $head_circumference;
  }

  function set_base_path($path){
    $this->base_path = $path;
  }

  function get_base_path(){
    return $this->base_path;
  }

  function get_age_group(){
    return $this->age_group;
  }


  function set_age($age,$unit){
    $this->age=$age;
    $this->age_group= "0_5";
    if($unit==ZScore::AGE_DAYS){
      if(($age/30.4375) > 60){
        $this->age = round(($age/30.4375),0);
        $this->age_group= "5_19";
      }
    }else if($unit==ZScore::AGE_YEAR){
      $this->age=round($age*12,0);
      if($this->age < 60){
        $this->age = round(($age*12*30.4375),0);
      }else{
    	 $this->age_group= "5_19";
      }
    }else if($unit==ZScore::AGE_MONTH){
      if($this->age < 60){
        $this->age = round(($age*30.4375),0);
      }else{
        $this->age_group= "5_19";
      }
    }
    $this->ageunit=$unit;
  }

  function get_index(){
    return $this->index;
  }

  function set_index($index){
    $this->index=$index;
    if($index == ZScore::WEIGHT_FOR_HEIGHT){
      if(is_numeric($this->age) && $this->age <= 731){
         $this->index= ZScore::WEIGHT_FOR_LENGTH;
      }else if(!is_numeric($this->age)){
        if($this->height < 87){
           $this->index= ZScore::WEIGHT_FOR_LENGTH;
        }
      }
    }
  }

  function get_bmi(){
    if(!is_numeric($this->bmi)) {
      $this->bmi= $this->weight/(($this->height/100)*($this->height/100));
    }
    return $this->bmi;
  }

   function get_filePath(){
    $tables="lms_".$this->age_group;
    $index = $this->get_index();
    $path=  $this->get_base_path()."/".$tables."/".$this->get_index()."_".$this->get_gender()."_p_exp.txt";
    return $path;
  }

  function get_lms(){
    $filepath =$this->get_filePath();
    if(!file_exists($filepath)){
      drupal_set_message(" - - Lookup Table Not Found  for: ".$this->get_index()."- - $filepath");
      return -1;
    }
    $handle = fopen($filepath, "r");
    $labels = preg_split("/[\s]+/",fgets($handle));
    //$selected = preg_split("/[\s]+/",fgets($handle));
    $indicator = $this->get_indicator();
    while (!feof($handle)){
      $new = fgets($handle);
      $line = preg_split("/[\s]+/",$new);
      if($line[0] == $indicator){
        $this->lms['L']=$line[1];
        $this->lms['M']=$line[2];
        $this->lms['S']=$line[3];
      }
    }
    fclose($handle);
    //drupal_set_message(print_r($this->lms,1));
    return $this->lms;
  }

  function get_indicator(){
    $indicator = $this->get_age();
    if($this->get_index()== ZScore::WEIGHT_FOR_HEIGHT){
      $indicator = $this->get_height();
    }else if($this->get_index()== ZScore::WEIGHT_FOR_LENGTH){
      $indicator = $this->get_length();
    }
    return $indicator;
  }

  function get_yvalue(){
    $yvalue = $this->get_weight();
    if($this->get_index()== ZScore::HEIGHT_FOR_AGE){
      $yvalue = $this->get_height();
    }else if($this->get_index()== ZScore::LENGTH_HEIGHT_FOR_AGE){
      $yvalue = $this->get_length();
    }else if($this->get_index()== ZScore::BMI_FOR_AGE){
      $yvalue = $this->get_bmi();
    }else if($this->get_index()== ZScore::WEIGHT_FOR_HEIGHT){
      $yvalue = $this->get_bmi();
    }else if($this->get_index()== ZScore::HC_FOR_AGE){
      $yvalue = $this->get_head_circumference();
    }

    //HC_FOR_AGE
    return $yvalue;
  }


  function get_zind($lms){
    $yvalue = $this->get_yvalue();
    $zind = (pow(($yvalue/$lms['M']), $lms['L'])  - 1)/($lms['S']*$lms['L']) ;
    if($zind > 3){
      $sd3pos= $this->lms['M']* pow((1+$lms['L']*$lms['S']*3),(1/$lms['L']));
      $sd2pos= $this->lms['M']* pow((1+$lms['L']*$lms['S']*2),(1/$lms['L']));
      $sd23pos=$sd3pos - $sd2pos;
      if($sd23pos==0){
        $zind = 3;
      }else{
        $zind = 3 + (($yvalue - $sd3pos)/$sd23pos);
      }
    }else if($zind < -3){
      $sd2neg= $this->lms['M']* pow((1+$lms['L']*$lms['S']*(-2)),(1/$lms['L'])) ;
      $sd3neg= $this->lms['M']* pow((1+$lms['L']*$lms['S']*(-3)),(1/$lms['L']));
      $sd23neg=$sd2neg - $sd3neg;
      if($sd23neg==0){
        $zind = -3;
      }else{
        $zind = (-3) + (($yvalue - $sd3neg)/$sd23neg);
      }
    }
    return round($zind,3);
  }


}

?>
