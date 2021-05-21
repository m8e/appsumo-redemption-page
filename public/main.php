<?php
session_start();

if(isset($_GET['GENERATE_CODES'])){
  generate_random_codes();
}else{
  check_post_request();
}

// Initialize CSRF protection
if (empty($_SESSION['csrf_token'])) {
  if (function_exists('mcrypt_create_iv')) {
    $_SESSION['csrf_token'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
  } else {
    $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
  }
}

// Initialize brute-force prevention 
if (empty($_SESSION['attempts'])) {
  reset_attempts();
}

function check_post_request(){
  global $configs;

  if (!empty($_POST['csrf_token'])) {
    $response=process_request();
    $response_status= $response ? "success" : "error";

    echo '<script>window.onload = function(e){ swal("'.$configs["page_alerts"][$response_status]["title"].'", "'.$configs["page_alerts"][$response_status]["messsage"].'", "'.$response_status.'");}</script>';
  }
}

function sanitize_fields(){
  if(!empty($_POST['f_name']) && !empty($_POST['f_email']) && !empty($_POST['f_appsumo_code'])){
    $fields=array();
    $fields["f_email"]=filter_var($_POST['f_email'], FILTER_VALIDATE_EMAIL);

    foreach($_POST as $field_key => $field_value) {
      $field_prefix=substr($field_key, 0, 2);
      if($field_prefix === "f_" && !in_array($field_key, array("f_email"))) {
        $fields[$field_key]=filter_var($_POST[$field_key], FILTER_SANITIZE_STRING);
      }
    }

    if ($fields["f_name"] && $fields["f_email"] && $fields["f_appsumo_code"]) {
      return $fields;
    }
  }

  return false;
}

function validate_code($fields){
  global $configs;

  if (($handle_codes = fopen($configs["appsumo_codes"]["path"], "r")) !== FALSE) {
    while (($data_codes = fgetcsv($handle_codes, 1000, ",")) !== FALSE) {
      if($data_codes[0]==$fields['f_appsumo_code']){
        if (!file_exists($configs["appsumo_activations_path"])) {
          if (!file_exists(dirname($configs["appsumo_activations_path"]))) {
            mkdir(dirname($configs["appsumo_activations_path"]), 0755, true);
          }
          touch($configs["appsumo_activations_path"]);
        }

        if (($handle_activations = fopen($configs["appsumo_activations_path"], "r")) !== FALSE) {
          $code_index = array_search('f_appsumo_code', array_keys($fields));

          while (($data_activations = fgetcsv($handle_activations, 1000, ",")) !== FALSE) {
            if($data_activations[$code_index]==$fields['f_appsumo_code']){
              fclose($handle_codes);
              fclose($handle_activations);
              return false; // code already activated
            }
          }

          fclose($handle_codes);
          fclose($handle_activations);

          $fields[]=$_SERVER['REMOTE_ADDR'];
          $fields[]=date('Y-m-d H:i:s');

          $csv_file = fopen($configs["appsumo_activations_path"],'a'); 
          fputcsv($csv_file, $fields);
          fclose($csv_file);

          return true;
        }
      }
    }

    fclose($handle_codes);
  }

  return false;
}

function match_variables($fields, $data){
  foreach($fields as $field_key => $field_value) {
    $snippet='{'.$field_key.'}';
    if (strpos($data, $snippet) !== false){
       $data=str_replace($snippet, $field_value, $data);
    }
  }

  return $data;
}

function send_email($fields, $code_status){
  global $configs;

  if($configs["email"]["notifications"][$code_status]["enabled"]){
    $subject= match_variables($fields, $configs["email"]["notifications"][$code_status]["subject"]);
    $messsage= match_variables($fields, $configs["email"]["notifications"][$code_status]["messsage"]);

    if($configs["email"]["smtp"]["enabled"]){
      require './libs/PHPMailer/PHPMailer.php';
      require './libs/PHPMailer/SMTP.php';

      try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->SMTPDebug=0;
        $mail->isSMTP();
        $mail->Port=$configs["email"]["smtp"]["port"];
        $mail->SMTPAuth=true;
        $mail->Host=$configs["email"]["smtp"]["host"];
        $mail->SMTPSecure="tls";
        $mail->Username=$configs["email"]["smtp"]["username"];
        $mail->Password=$configs["email"]["smtp"]["password"];

        $mail->setFrom($configs["email"]["smtp"]["from_email"], $configs["email"]["smtp"]["from_name"]);
        $mail->addAddress($fields["f_email"]);

        $mail->isHTML(true);
        $mail->Subject=$subject;
        $mail->Body=$messsage;

        $mail->send();
      }catch (Exception $e) {
        return null;
      }
    }else{
      mail($fields["f_email"], $subject, $messsage);
    }
  }
}

function webhook_callback($fields){
  global $configs;

  if($configs["webhook"]["enabled"] && filter_var($configs["webhook"]["url"], FILTER_VALIDATE_URL)){
    if($configs["webhook"]["method"]=="POST"){
      $data = array();

      foreach($fields as $field_key => $field_value) {
        $data[$field_key]= $field_value;
      }

      file_get_contents($configs["webhook"]["url"], false, stream_context_create(array('http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
      ))));
    }else if($configs["webhook"]["method"]=="GET"){
      $parsed_url=parse_url($configs["webhook"]["url"]);
      if(!$parsed_url['query']){
        $parsed_url['query']="";
      }
      foreach($fields as $field_key => $field_value) {
        $parsed_url['query'].="&".$field_key."=".$field_value;
      }

      file_get_contents($parsed_url['scheme']."://".$parsed_url['host'].$parsed_url['path'].'?'.$parsed_url['query']);
    }
  }
}

function check_attempts(){
  global $configs;

  if($configs["limit_attempts"]["tries"] && $configs["limit_attempts"]["interval"]){ // if those values are empty the verifcation will be skipped
    if($_SESSION['attempts']["count"] <= $configs["limit_attempts"]["tries"]){
      return true;
    }else{
      $datetime_difference=round(abs(strtotime(date('Y-m-d H:i:s'))-strtotime($_SESSION['attempts']["datetime"])) / 60,2);
      if($datetime_difference > $configs["limit_attempts"]["interval"]){
        reset_attempts();
        return true;
      }
    }
    
    return false;
  }

  return true;
}

function process_request(){
  global $configs;

  if(check_attempts()){
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
      if (file_exists($configs['appsumo_codes']['path'])) {
        $fields=sanitize_fields();
        if($fields!==false){
          if(validate_code($fields)){
            send_email($fields, 'valid_code');
            webhook_callback($fields);
            return true;
          }else{
            $_SESSION['attempts']["count"]++;
            send_email($fields, 'invalid_code');
          }
        }
      }
    }
  }

  return false;
}

function generate_random_codes(){
  global $configs;

  if (!file_exists($configs["appsumo_codes"]["path"])) {
    // Generate unique random codes
    if (!file_exists(dirname($configs["appsumo_codes"]["path"]))) {
      mkdir(dirname($configs["appsumo_codes"]["path"]), 0755, true);
    }

    function crypto_rand_secure() {
      $log = ceil(log(61, 2));
      $bytes = (int) ($log / 8) + 1;
      $bits = (int) $log + 1;
      $filter = (int) (1 << $bits) - 1;

      do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter;
      } while ($rnd > 61);

      return $rnd;
    }

    function get_token($length) {
      $token = "";
      $code_alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

      for ($index=0; $index < $length; $index++) {
        $token .= $code_alphabet[crypto_rand_secure(0, 61)];
      }

      return $token;
    }

    $csv_file = fopen($configs["appsumo_codes"]["path"],'a'); 

    for ($index=1; $index <= $configs["appsumo_codes"]["count"]; $index++) { 
      fputcsv($csv_file, [get_token($configs["appsumo_codes"]["length"])]);
    }

    fclose($csv_file);

    $appsumo_codes = file_get_contents($configs["appsumo_codes"]["path"]);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="appsumo_codes.csv"');
    header("Content-Length: " . strlen($appsumo_codes));

    echo $appsumo_codes;
    exit;
  }
}

function reset_attempts(){
  $_SESSION['attempts']=array(
    "count"=> 1,
    "datetime"=> date('Y-m-d H:i:s'),
  );
}
?>