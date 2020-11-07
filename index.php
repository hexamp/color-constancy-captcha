<?php
  ini_set('display_errors',1);
  error_reporting(E_ALL);
  session_start();
  unset($_SESSION['image']);
  unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html>
<head>

  <meta charset="UTF-8">
  <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/i18n/jquery.spectrum-ja.min.js"></script>
  <script type="text/javascript" src ="../js/canvasimage.js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.css">
  <style type="text/css">
  canvas {border: 1px solid #000;}
  </style>

  <title>Color Constancy CAPTCHA</title>
</head>

<body>
<?php
  $image_array = array("test.jpg");
  if(!isset($_SESSION['image'])){
    $_SESSION['image'] = $image_array[array_rand($image_array)];
  }

  if(!isset($_SESSION['success'])){
    $_SESSION['success'] = 0;
  }

  captcha_main();

  if($_SESSION['success'] == 1){
    echo "<p>SUCCESS</p>";
    $_SESSION['image'] = $image_array[array_rand($image_array)];
  }

  printBody("gradientsss");
   
?>

</body>
</html>

<?php

define("LAB",0);
define("HSV",1);

function captcha_main(){
  if(!isset($_POST["color"])){
    return;
  }
  if($_POST["color"] != ""){
    $selected_color = getColorVal($_POST["color"]);
  }
  else{
    $selected_color = array(0,0,0);
  }

  if( (isset($_POST["objX"]) && $_POST["objX"] > 0) && (isset($_POST["objY"]) && $_POST["objY"] > 0) && (isset($_POST["objW"]) && $_POST["objW"] > 0) && (isset($_POST["objH"]) && $_POST["objH"] > 0)){
    $answer_color = checkColor($_POST["objX"],$_POST["objY"],$_POST["objW"],$_POST["objH"],$_SESSION['image']);
    list($hsv_euc, $lab_euc, $lab_delta) = calcResult($selected_color,$answer_color);
    echo $lab_delta;
    if($lab_delta < 30) {
      $_SESSION['success'] = 1;
    }
    else{
      $_SESSION['success'] = 0;
    }
  }
}

function printBody($captcha_type="shape"){
  echo "<div id = captcha>
  <canvas id=\"canvas\" width = \"300\" height =\"300\"></canvas>
  <img id = \"captcha_img\" src=\" " . $_SESSION['image'] . " \" style=\"display:none\">
  <img id = \"task_img\" src=\"\" style=\"display:none\">
  <p>選択した箇所の<font  color=\"#ff0000\">元の色を推定</font>し，スライダーを用いて選択して下さい。(<font  color=\"#ff0000\">画像をクリックすると位置が選べます</font>)</p>
  <form name =\"captcha_form\" action=\"index.php\" method=\"post\">
  <input type=\"text\" id=\"picker\" name=\"color\">
  <input type=\"hidden\" name=\"objX\" value=''>
  <input type=\"hidden\" name=\"objY\" value=''>
  <input type=\"hidden\" name=\"objW\" value=''>
  <input type=\"hidden\" name=\"objH\" value=''>
  <input type=\"hidden\" name=\"canvas_image\" value=''>
  <input type=\"submit\" value=\"Submit\">
  </form>
  </div>";
  if(strcmp($captcha_type,"shape") == 0) {
    echo "<script type=\"text/javascript\" src=\"../js/shape.js\"></script>";
    return;
  } 
  echo "<script type=\"text/javascript\" src=\"../js/gradient.js\"></script>";
}

function calcResult($color1,$color2){
  $hsv1 = rgb2hsv($color1);
  $hsv2 = rgb2hsv($color2);

  $hsv_euc = calcEuc($hsv1,$hsv2,"HSV");

  $lab1 = rgb2lab($color1);
  $lab2 = rgb2lab($color2);

  $lab_euc = calcEuc($lab1,$lab2,"LAB");
  $lab_del = calcDelta2000($color1,$color2);

  return array($hsv_euc,$lab_euc,$lab_del);
}

function selectColor($color,$color_chart){
  $min_dist = 1000;
  $min_idx = 0;
  for ($i=0 ; $i < count($color_chart) ; $i++){
    $distance = calcResult($color,$color_chart[$i])[2];
    if ($distance <= $min_dist){
        $min_dist = $distance;
        $min_idx = $i;
    }
  }
  return $color_chart[$min_idx];
}

function checkColor($objX,$objY,$objW,$objH,$image_name){
  $img = imagecreatefromjpeg($image_name);
  $r_array = array();
  $g_array = array();
  $b_array = array();
  for($j = $objY; $j < $objH ; $j++){
    for($i = $objX; $i < $objW; $i++){
      $rgb = imagecolorat($img,$i,$j);
      $r_array[] = ($rgb >> 16) & 0xFF;
      $g_array[] = ($rgb >> 8) & 0xFF;
      $b_array[] = $rgb & 0xFF;
    }
  }

  $r = calcMedian($r_array);
  $g = calcMedian($g_array);
  $b = calcMedian($b_array);

  unset($r_array);
  unset($g_array);
  unset($b_array);

  return array($r,$g,$b);
}


function calcDelta2000($color1,$color2){

  $color1 = rgb2lab($color1);
  $color2 = rgb2lab($color2);

  $l1 = $color1[0];
  $a1 = $color1[1];
  $b1 = $color1[2];
  $l2 = $color2[0];
  $a2 = $color2[1];
  $b2 = $color2[2];

  $l_del_d = $l2 - $l1;
  $l_bar = ($l1 + $l2) / 2;
  $c_ast1 = sqrt(pow($a1,2)+pow($b1,2));
  $c_ast2 = sqrt(pow($a2,2)+pow($b2,2));
  $c_bar = ($c_ast1 + $c_ast2)/2;


  $g = (1 - sqrt(pow($c_bar,7)/(pow($c_bar,7) + pow(25,7))))*0.5;

  $a_d1 = $a1*(1+$g);
  $a_d2 = $a2*(1+$g);
  $c_d1 = sqrt(pow($a_d1,2)+pow($b1,2));
  $c_d2 = sqrt(pow($a_d2,2)+pow($b2,2));
  $c_bar_d = ($c_d1 + $c_d2)/2;
  $c_del_d = $c_d2 - $c_d1;

  if($b1 == $a_d1){
    $h_d1 = 0;
  }
  else{
    $ang_h1 = rad2ang(atan2($b1,$a_d1))+360;
    $int_h1 = fmod($ang_h1,360.0);
    $sm_h1 = $ang_h1 - floor($ang_h1);
    $h_d1 = $int_h1 + $sm_h1;
  }

  if($b2 == $a_d2){
    $h_d2 = 0;
  }
  else{
    $ang_h2 = rad2ang(atan2($b2,$a_d2))+360;
    $int_h2 = fmod($ang_h2,360.0);
    $sm_h2 = $ang_h2 - floor($ang_h2);
    $h_d2 = $int_h2 + $sm_h2;
  }
  $diff = $h_d2 - $h_d1;

  $h_del_d = 0;
  if(abs($diff) <= 180){
    $h_del_d = $diff;
  }
  else if($h_d2 <= $h_d1){
    $h_del_d = $diff + 360;
  }
  else{
    $h_del_d = $diff - 360;
  }

  $H_del_d = 2*sqrt($c_d1*$c_d2)*sin(ang2rad($h_del_d/2));
  $H_bar_d = $h_d1 + $h_d2;
  if(abs($diff)<=180){
    $H_bar_d /= 2;
  }
  else if($H_bar_d < 360){
    $H_bar_d = ($H_bar_d + 360)/2;
  }
  else{
    $H_bar_d = ($H_bar_d - 360)/2;
  }

  $t = 1 - 0.17*cos(ang2rad($H_bar_d - 30)) + 0.24*cos(ang2rad(2*$H_bar_d))
  + 0.32*cos(ang2rad(3*$H_bar_d+6))  - 0.20*cos(ang2rad(4*$H_bar_d-63));

  $sl = 1 + 0.015*pow($l_bar-50,2)/sqrt(20+pow($l_bar-50,2));
  $sc = 1 + 0.045*$c_bar_d;
  $sh = 1 + 0.015*$c_bar_d*$t;

  $del_the = 30*(exp(-1*pow(($H_bar_d - 275)/25,2)));
  $rc = 2 * sqrt(pow($c_bar_d,7)/(pow($c_bar_d,7)+pow(25,7)));
  $rt = -1 * sin(ang2rad(2 * $del_the)) * $rc;

  $delta2000 = sqrt(pow($l_del_d/$sl,2) + pow($c_del_d/$sc,2) + pow($H_del_d/$sh,2) + $rt*$c_del_d/$sc*$H_del_d/$sh);

  return $delta2000;

}

function ang2rad($ang){
  return $ang * M_PI /180;
}

function rad2ang($rad){
  return $rad * 180 / M_PI;
}

function calcMedian($list){
  sort($list);
  if(count($list)%2==0){
    return floor(( $list[count($list)/2-1] + $list[count($list)/2] ) /2);
  }
  else{
    return $list[floor(count($list)/2)];
  }
}

function calcAngle($color1,$color2,$type){

  if($type == HSV){
    hsv2urV($color1);
    hsv2urV($color2);
  }

  $lenA = calcNorm($color1);
  $lenB = calcNorm($color2);

  if($lenA == 0){
    $lenA = 1;
  }

  if($lenB == 0){
    $lenB = 1;
  }

  $dot = calcDot($color1,$color2);
  $cos_sita = $dot / ($lenA * $lenB);

  return (acos($cos_sita) * 180 / M_PI);

}

function calcEuc($color1,$color2,$type){
  $sum=0;

  if($type=="HSV"){
    hsv2urV($color1);
    hsv2urV($color2);
  }

  for($i=0 ; $i<3; $i++){
    $sum += pow($color1[$i]-$color2[$i],2);
  }
  return sqrt($sum/2);
}

function hsv2urV(&$color){

  $h = $color[0]*M_PI/180;
  $s = $color[1];

  $color[0] = $s * cos($h);
  $color[1] = $s * sin($h);

}

function calcNorm($vec){
  $norm = 0;
  $len = 0;
  for($i = 0; $i < 3; $i++){
    $len += $vec[$i]*$vec[$i];
  }
  return sqrt($len);
}

function calcDot($vec1,$vec2){
  $result = 0;
  for($i = 0; $i < 3; $i++){
    $result += $vec1[$i] * $vec2[$i];
  }
  return $result;
}

function getColorVal($color_str){

  list($val1,$val2,$val3) = sscanf($color_str, "rgb(%d,%d,%d)");
  return array($val1,$val2,$val3);
}

function rgb2hsv($color){
  $r = $color[0]/255;
  $g = $color[1]/255;
  $b = $color[2]/255;
  $min = min($r,$g,$b);
  $max = max($r,$g,$b);
  $v = $max;
  if($max == $r){
    if($max-$min == 0){
      $h = 0;
    }
    else{
      $h = 60 * (($g - $b) / ($max - $min));
    }
  }
  else if($max == $g){
    if($max-$min == 0){
      $h = 0;
    }
    else{
      $h = 60 * (($b - $r) / ($max - $min)) + 120;
    }
  }
  else if($max ==$b){
    if($max-$min == 0){
      $h = 0;
    }
    else{
      $h = 60 * (($r - $g) / ($max - $min)) + 240;
    }
  }
  $h + 360;
  $h %= 360;
  if($v != 0){
    $s = ($max - $min) / $max;
  }
  else{
    $s = 0;
  }
  return array($h,$s,$v);
}


function rgb2lab($color){

  $r = $color[0]/255;
  $g = $color[1]/255;
  $b = $color[2]/255;

  $color = array($r,$g,$b);

  linear_exchange($color);

  $xyz = rgb2xyz($color);

  $xn = $xyz[0] / 0.95047;
  $yn = $xyz[1] / 1.00000;
  $zn = $xyz[2] / 1.08883;

  $l = 116 * func_lab($yn) -16;
  $a = 500 * ( func_lab($xn) - func_lab($yn) );
  $b = 200 * ( func_lab($yn) - func_lab($zn) );

  return array($l,$a,$b);
}

function rgb2xyz($color){
  $matrix = array(
    array(0.4124, 0.3576, 0.1805),
    array(0.2126, 0.7152, 0.0722),
    array(0.0193, 0.1192, 0.9505)
  );

  $x = $matrix[0][0]*$color[0] + $matrix[0][1]*$color[1] + $matrix[0][2]*$color[2];
  $y = $matrix[1][0]*$color[0] + $matrix[1][1]*$color[1] + $matrix[1][2]*$color[2];
  $z = $matrix[2][0]*$color[0] + $matrix[2][1]*$color[1] + $matrix[2][2]*$color[2];

  return array($x,$y,$z);
}

function linear_exchange(&$array){
  for($i = 0 ; $i < 3 ; $i++){
    if($array[$i] <= 0.04045){
      $array[$i] /= 12.92;
    }
    else{
      $array[$i] = pow( ($array[$i] + 0.055) / 1.055, 2.4);
    }
  }
}

function func_lab($val){
  $thres = pow(6/29,3);
  $coef = pow(29/3,3);
  if($val > $thres){
    $result= pow($val,1/3);
  }
  else{
    $result = ($coef * $val + 16) / 116;
  }
  return $result;
}
