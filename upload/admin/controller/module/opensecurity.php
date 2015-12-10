<?php
class ControllerModuleOpensecurity extends Controller {
	
	public function index() {
		$this->load->language('module/opensecurity');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			$this->model_setting_setting->editSetting('opensec', $_POST, 0);

			$this->response->redirect($this->url->link('module/opensecurity', 'token=' . $this->session->data['token'], 'SSL'));
			
		}
		
		// load settings
		$settings=$this->model_setting_setting->getSetting('opensec',0);
		
		// ----------------------------
		$data=array();
		$data['settings']=$settings;
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_module_settings'] = $this->language->get('text_module_settings');
		$data['module_status'] = $this->language->get('module_status');
		$data['captcha_status'] = $this->language->get('captcha_status');
		$data['captcha_logins_count'] = $this->language->get('captcha_logins_count');
		$data['alert_email'] = $this->language->get('alert_email');
		$data['text_auto_block_ip'] = $this->language->get('text_auto_block_ip');
		$data['text_send_emails'] = $this->language->get('text_send_emails');
		
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_last_messages'] = $this->language->get('text_last_messages');
		$data['text_Date_Time'] = $this->language->get('text_Date_Time');
		$data['text_Message'] = $this->language->get('text_Message');
		$data['text_version'] = $this->language->get('text_version');
		$data['text_notify_me'] = $this->language->get('text_notify_me');
		
		$data['action'] = $this->url->link('module/opensecurity', 'token=' . $this->session->data['token'], 'SSL');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
		
		// breadcrumbs
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL')
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('module_title'),
			'href' => $this->url->link('module/opensecurity', 'token=' . $this->session->data['token'], 'SSL')
		);
		// breadcrumbs
		
		
		$messages = $this->db->query("SELECT * FROM ". DB_PREFIX ."opsalerts ORDER BY adate desc limit 0, 10")->rows;
		
		$data['messages'] = $messages;
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
	
		$this->response->setOutput($this->load->view('module/opensecurity.tpl', $data));
	}

	public function install() {
	
		$this->load->model('setting/setting');
	  
		$settings=Array ( 
		'opensec-version'=> '0.1',
		'opensec-status' => 1, 
		'opensec-alertemail' => '',
		'opensec-sendalerts' => 1,
		'opensec-captchastatus' => 1,
		'opensec-wlogcount' => 2,
		'opensec-autoblockip' =>1,
		'opensec-autoblockipcount' =>4
		);
	  
		$this->model_setting_setting->editSetting('opensec', $settings, 0);
		
		$this->db->query("CREATE TABLE IF NOT EXISTS ". DB_PREFIX ."opsiplog(id int auto_increment, rdate int, ip varchar(20), authstatus int, accessstatus int, processed int, primary key(id)) DEFAULT CHARSET=utf8");
		
		$this->db->query("CREATE TABLE IF NOT EXISTS ". DB_PREFIX ."opsalerts(id int auto_increment, adate int, alerttype int, alerttext text, mailsent int, primary key(id)) DEFAULT CHARSET=utf8");
		
		$this->db->query("CREATE TABLE IF NOT EXISTS ". DB_PREFIX ."opsblacklist(id int auto_increment, ip varchar(20), expire int, blocktype int, primary key(id)) DEFAULT CHARSET=utf8");
		
		$this->db->query("CREATE TABLE IF NOT EXISTS ". DB_PREFIX ."opswhitelist(id int auto_increment, ip varchar(20), expire int, accesstype int, primary key(id)) DEFAULT CHARSET=utf8");
	}

	public function uninstall() {
		
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('opensec', 0);
		
		$this->db->query("DROP TABLE ". DB_PREFIX ."opsiplog");
		$this->db->query("DROP TABLE ". DB_PREFIX ."opsalerts");		
		$this->db->query("DROP TABLE ". DB_PREFIX ."opsblacklist");
		$this->db->query("DROP TABLE ". DB_PREFIX ."opswhitelist");
	}

	protected function validate() {

	
	return 1;
	}
}
?>