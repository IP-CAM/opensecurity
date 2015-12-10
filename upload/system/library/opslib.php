<?php 
/*
message types:
  1: Ip ban
  2: dDos alert
  3: 
  4: Need self update
  5: Need update opencart
  6: Need update any module

*/
class opsLib {
  
  function __construct($obj, $config, $lang) {
  
  $this->_this = $obj;
  $this->settings = $this->loadSettings();
  $this->db = $obj->db;
  $this->config = $config;
  $this->lang = $lang;
  
  }
  
  public function loadSettings() {
  $this->_this->load->model('setting/setting');
  $settings = $this->_this->model_setting_setting->getSetting('opensec',0);
  if( empty($settings) ) {
      $settings['opensec-status'] = 0;     
      }
  return $settings;
  }
  
  public function isCaptchaNeeded() {
      
  if ($this->settings['opensec-status'] == 0) return 0;
  
  if( isset( $this->settings['ip_baned'] ) ) return 0;
	      
  if ( ($this->settings['opensec-captchastatus'] == 1 )&&( $this->settings['opensec-wlogcount'] == 0) ) return 1;
  
  if ( $this->settings['opensec-captchastatus'] == 1 ) {
	      
      $period_start = time()-60*60;
      $period_end = time();
      $ip = $_SERVER['REMOTE_ADDR'];

      $result = $this->db->query("select count(id) cnt from ". DB_PREFIX ."opsiplog where (ip = '$ip') and (rdate between $period_start and $period_end)");

      if ($result->rows[0]['cnt'] >= $this->settings['opensec-wlogcount']) return 1;
    }
  }

  public function addIpBan() {
  $ip = $_SERVER['REMOTE_ADDR'];
  $expire = time()+60*60;
  $curtime = time();
  
  $result = $this->db->query("SELECT id FROM ". DB_PREFIX ."opsblacklist WHERE (ip = '$ip') and (expire > $curtime)");
  
  if ($result->num_rows == 0) {
      $this->db->query("INSERT INTO ". DB_PREFIX ."opsblacklist values('', '$ip', '$expire', '1')");
      }
  $this->db->query("DELETE FROM ". DB_PREFIX ."opsiplog where ip = '$ip'");
  
  $shortmsg = $ip.' '.$this->lang->get('msg_ip_was_blocked');
  
  $msg=$this->tmpProcess($this->lang->get('email_ip_was_blocked'), $replace = Array('ip' => $ip, 'website' => $_SERVER['SERVER_NAME']));
  
  $this->saveAlert($msg, 1, $shortmsg);
  }
  
  public function saveAlert($message, $messagetype, $shortmsg) {

  $date = time();
  $email = 0;
  
  $theme = $this->lang->get('msg_default_theme');
  
  if($messagetype == 1) $theme = $this->lang->get('msg_id_lockout');;
  
  if( $this->settings['opensec-sendalerts'] == 1 ) {
    $this->sendEmail($message, $theme);
    $email = 1;
  }
  
  $this->db->query("INSERT INTO ". DB_PREFIX ."opsalerts VALUES('', '$date', '$messagetype', '$shortmsg', $email)");
  }

  
  public function tmpProcess($template, $replace) {
  
  $tmp=$template;
  
  foreach($replace as $key=>$rpl)
    $tmp = str_replace('{'.$key.'}', $rpl, $tmp);

  return $tmp;
  }
  
  public function getEmailText($message) {
  
  $template = "<html>
  <head>
    <meta charset='UTF-8' />
  </head>
  <body>
  $message
  </body>
  </html>
  ";
  
  return $template;
  }

  public function sendEmail($message, $theme) {
  
  if( $this->settings['opensec-alertemail'] == '') return 0;
  
  $mail = new Mail();
  $mail->protocol = $this->config->get('config_mail_protocol');
  $mail->parameter = $this->config->get('config_mail_parameter');
  $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
  $mail->smtp_username = $this->config->get('config_mail_smtp_username');
  $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
  $mail->smtp_port = $this->config->get('config_mail_smtp_port');
  $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
  $mail->setFrom($this->config->get('config_email'));
  $mail->setTo($this->settings['opensec-alertemail']);
  $mail->setSender('OPS Alerts');
  $mail->setSubject($theme);
  
  $text = $this->getEmailText($message);
  
  $mail->setHtml($text);
  $mail->setText($text);
  $mail->send();

  }

}
?>