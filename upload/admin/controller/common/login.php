<?php

include DIR_SYSTEM.'library/opslib.php';

class ControllerCommonLogin extends Controller {
	private $error = array();

	public function validateIp() {
	      if( $this->ops->settings['opensec-status'] == 0 ) return 1;
	      
	      if ( $this->request->server['REQUEST_METHOD'] != 'POST' ) return 1;
	      
	      $ip = $_SERVER['REMOTE_ADDR'];
	      $expire = time();

	      $result = $this->db->query( "SELECT id FROM ". DB_PREFIX ."opsblacklist WHERE (ip='$ip') and (expire > $expire)" );

	      if ($result->num_rows == 0) return 1;
		else
		  {
		  $this->error['warning'] = $this->language->get( 'text_ip_ban' );
		  $this->error['ip_ban'] = 1;
		  $this->ops->settings['ip_baned'] = 1;
		  return 0;
		  }
	}
	
	public function validateCaptcha() {
	      if( $this->ops->settings['opensec-status'] ==0 ) return 1;
	
	      if ( $this->request->server['REQUEST_METHOD'] != 'POST' ) return 1;
	
	      if ( !$this->ops->isCaptchaNeeded() )return 1;
	
	      if ( !isset($this->request->post['input-captcha']) )
		{
		$this->error['warning'] = $this->language->get('text_wrong_captcha');
		$this->error['captcha_error'] = 1;
		return 0;
		}
	
	      if ( !isset($this->session->data['captcha']) )
		{
		$this->error['warning'] = $this->language->get('text_wrong_captcha');
		$this->error['captcha_error'] = 1;
		return 0;
		}
	
	      if ( strtoupper($this->session->data['captcha']) == strtoupper($this->request->post['input-captcha']) ) return 1;
	
	      $this->error['warning']=$this->language->get('text_wrong_captcha');
	      $this->error['captcha_error'] = 1;
	      return 0;
	}
	
	public function authLog() {
		if( $this->ops->settings['opensec-status'] ==0 ) return 1;
		
		if( isset($this->ops->settings['ip_baned']) ) return 1;
		
		if( $this->tokenerror == 1 ) return 1;
		
		$rdate = time();
		$ip = $_SERVER['REMOTE_ADDR'];
		$processed = 0;
	
		if ( isset($this->error['warning']) )  {
		
		    if ( isset($this->error['captcha_error']) ) $authstatus = 1;
		      else
			$authstatus = 2;
		
		    $accessstatus = 0;
		    $this->db->query( "INSERT INTO ". DB_PREFIX ."opsiplog values('', '$rdate', '$ip', '$authstatus', '$accessstatus', '$processed')" );
		
		    } elseif ( isset($this->session->data['success']) ) {
		    $authstatus = 0;
		    $accessstatus = 1;
		
		    $this->db->query( "INSERT INTO ". DB_PREFIX ."opsiplog values('', '$rdate', '$ip', '$authstatus', '$accessstatus', '$processed')" );
		    }
		
		$expire = time()-(60*60);
		
		$result = $this->db->query( "SELECT count(id) cnt from ". DB_PREFIX ."opsiplog where ((ip = '$ip') and (rdate > $expire))" );
		
		if ($result->rows[0]['cnt'] >= $this->ops->settings['opensec-autoblockipcount']) $this->ops->addIpBan();
	}
	
	public function index() {

		//opensecurity module
		$this->load->language('module/opensecurity');
		$this->ops = new opsLib( $this, $this->config, $this->language );
		$this->tokenerror = 0;
		//opensecurity module
		
		$this->load->language('common/login');
		
		$this->document->setTitle($this->language->get('heading_title'));

		if ($this->user->isLogged() && isset($this->request->get['token']) && ($this->request->get['token'] == $this->session->data['token'])) {
			$this->response->redirect($this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'));
		}

if ($this->validateIp())
	if ($this->validateCaptcha())
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->session->data['token'] = token(32);

			if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], HTTP_SERVER) === 0 || strpos($this->request->post['redirect'], HTTPS_SERVER) === 0 )) {
				$this->response->redirect($this->request->post['redirect'] . '&token=' . $this->session->data['token']);
			} else {
				$this->response->redirect($this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'));
			}
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_login'] = $this->language->get('text_login');
		$data['text_forgotten'] = $this->language->get('text_forgotten');

		$data['entry_username'] = $this->language->get('entry_username');
		$data['entry_password'] = $this->language->get('entry_password');

		$data['button_login'] = $this->language->get('button_login');

		if ((isset($this->session->data['token']) && !isset($this->request->get['token'])) || ((isset($this->request->get['token']) && (isset($this->session->data['token']) && ($this->request->get['token'] != $this->session->data['token']))))) {
			$this->error['warning'] = $this->language->get('error_token');
			$this->tokenerror = 1;
		}
		// opensecurity
		$data['error_captcha'] = 0;
		$data['text_captcha'] = $this->language->get('text_captcha');
		$this->authLog();
		$data['show_captcha'] = $this->ops->isCaptchaNeeded();
		
		$data['show_captcha'] = $this->ops->isCaptchaNeeded();
		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
		$data['base'] = $server;
		
		// opensecurity
		
		if (isset($this->error['warning'])) {
			
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['action'] = $this->url->link('common/login', '', 'SSL');

		if (isset($this->request->post['username'])) {
			$data['username'] = $this->request->post['username'];
		} else {
			$data['username'] = '';
		}

		if (isset($this->request->post['password'])) {
			$data['password'] = $this->request->post['password'];
		} else {
			$data['password'] = '';
		}

		if (isset($this->request->get['route'])) {
			$route = $this->request->get['route'];

			unset($this->request->get['route']);
			unset($this->request->get['token']);

			$url = '';

			if ($this->request->get) {
				$url .= http_build_query($this->request->get);
			}

			$data['redirect'] = $this->url->link($route, $url, 'SSL');
		} else {
			$data['redirect'] = '';
		}

		if ($this->config->get('config_password')) {
			$data['forgotten'] = $this->url->link('common/forgotten', '', 'SSL');
		} else {
			$data['forgotten'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('common/login.tpl', $data));
	}

	protected function validate() {
		if (!isset($this->request->post['username']) || !isset($this->request->post['password']) || !$this->user->login($this->request->post['username'], html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8'))) {
			$this->error['warning'] = $this->language->get('error_login');
		}

		return !$this->error;
	}

	public function check() {
		$route = isset($this->request->get['route']) ? $this->request->get['route'] : '';

		$ignore = array(
			'common/login',
			'common/forgotten',
			'common/reset'
		);

		if (!$this->user->isLogged() && !in_array($route, $ignore)) {
			return new Action('common/login');
		}

		if (isset($this->request->get['route'])) {
			$ignore = array(
				'common/login',
				'common/logout',
				'common/forgotten',
				'common/reset',
				'error/not_found',
				'error/permission'
			);

			if (!in_array($route, $ignore) && (!isset($this->request->get['token']) || !isset($this->session->data['token']) || ($this->request->get['token'] != $this->session->data['token']))) {
				return new Action('common/login');
			}
		} else {
			if (!isset($this->request->get['token']) || !isset($this->session->data['token']) || ($this->request->get['token'] != $this->session->data['token'])) {
				return new Action('common/login');
			}
		}
	}
}