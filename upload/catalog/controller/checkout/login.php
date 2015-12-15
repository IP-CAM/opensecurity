<?php

$login_error =array();

include DIR_SYSTEM.'library/opslib.php';

class ControllerCheckoutLogin extends Controller {

	public function validateIp() {
            global $login_error;
	      if( $this->ops->settings['opensec-status'] == 0 ) return 1;
	      
	      if ( $this->request->server['REQUEST_METHOD'] != 'POST' ) return 1;
	      
	      $ip = $_SERVER['REMOTE_ADDR'];
	      $expire = time();

	      $result = $this->db->query( "SELECT id FROM ". DB_PREFIX ."opsblacklist WHERE (ip='$ip') and (expire > $expire)" );

	      if ($result->num_rows == 0) return 1;
		else
		  {
		  $login_error['warning'] = $this->language->get( 'text_ip_ban' );
		  $login_error['ip_ban'] = 1;
		  $this->ops->settings['ip_baned'] = 1;
		  return 0;
		  }
	}
	
	public function validateCaptcha() {
              global $login_error;

	      if( $this->ops->settings['opensec-status'] ==0 ) return 1;
	
	      if ( $this->request->server['REQUEST_METHOD'] != 'POST' ) return 1;
	
	      if ( !$this->ops->isCaptchaNeeded() )return 1;
	
	      if ( !isset($this->request->post['input-captcha']) )
		{
		$login_error['warning'] = $this->language->get('text_wrong_captcha');
		$login_error['captcha_error'] = 1;
		return 0;
		}
	
	      if ( !isset($this->session->data['captcha']) )
		{
		$login_error['warning'] = $this->language->get('text_wrong_captcha');
		$login_error['captcha_error'] = 1;
		return 0;
		}
	
	      if ( strtoupper($this->session->data['captcha']) == strtoupper($this->request->post['input-captcha']) ) return 1;
	
	      $login_error['warning']=$this->language->get('text_wrong_captcha');
	      $login_error['captcha_error'] = 1;
	      return 0;
	}
	
	public function authLog($json) {
                global $login_error;
                
		if( $this->ops->settings['opensec-status'] ==0 ) return 1;
		
		if( isset($this->ops->settings['ip_baned']) ) return 1;
		
		$rdate = time();
		$ip = $_SERVER['REMOTE_ADDR'];
		$processed = 0;
	
		if ( isset($json['error']['warning']) )  {
		
		    if ( isset($login_error['captcha_error']) ) $authstatus = 1;
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
		$this->load->language('checkout/checkout');
		//opensecurity module
		$this->load->language('module/opensecurity');
		$this->ops = new opsLib( $this, $this->config, $this->language );
		//opensecurity module

		$data['text_checkout_account'] = $this->language->get('text_checkout_account');
		$data['text_checkout_payment_address'] = $this->language->get('text_checkout_payment_address');
		$data['text_new_customer'] = $this->language->get('text_new_customer');
		$data['text_returning_customer'] = $this->language->get('text_returning_customer');
		$data['text_checkout'] = $this->language->get('text_checkout');
		$data['text_register'] = $this->language->get('text_register');
		$data['text_guest'] = $this->language->get('text_guest');
		$data['text_i_am_returning_customer'] = $this->language->get('text_i_am_returning_customer');
		$data['text_register_account'] = $this->language->get('text_register_account');
		$data['text_forgotten'] = $this->language->get('text_forgotten');
		$data['text_loading'] = $this->language->get('text_loading');

		$data['entry_email'] = $this->language->get('entry_email');
		$data['entry_password'] = $this->language->get('entry_password');

		$data['button_continue'] = $this->language->get('button_continue');
		$data['button_login'] = $this->language->get('button_login');

		$data['checkout_guest'] = ($this->config->get('config_checkout_guest') && !$this->config->get('config_customer_price') && !$this->cart->hasDownload());

		if (isset($this->session->data['account'])) {
			$data['account'] = $this->session->data['account'];
		} else {
			$data['account'] = 'register';
		}
		
		// opensecurity
		$data['error_captcha'] = 0;
		$data['text_captcha'] = $this->language->get('text_captcha');
		$data['show_captcha'] = $this->ops->isCaptchaNeeded();
		
		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
		$data['base'] = $server;
		
		// opensecurity
		
		$data['forgotten'] = $this->url->link('account/forgotten', '', 'SSL');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/login.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/checkout/login.tpl', $data));
		} else {
			$this->response->setOutput($this->load->view('default/template/checkout/login.tpl', $data));
		}
	}

	public function save() {
                global $login_error;
                
		$this->load->language('checkout/checkout');
		
		// opensecurity
		$this->load->language('module/opensecurity');
                $this->ops = new opsLib( $this, $this->config, $this->language );
                $login_error['warning'] = '';
                $login_error['captcha_error'] = 0;
                
                // opensecurity
                
		$json = array();

		if ($this->customer->isLogged()) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', 'SSL');
		}
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart');
		}

		if (!$json) {
			$this->load->model('account/customer');

			// Check how many login attempts have been made.
			$login_info = $this->model_account_customer->getLoginAttempts($this->request->post['email']);

			if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
				$json['error']['warning'] = $this->language->get('error_attempts');
			}

			// Check if customer has been approved.
			$customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);

			if ($customer_info && !$customer_info['approved']) {
				$json['error']['warning'] = $this->language->get('error_approved');
			}
			

			if (!isset($json['error'])) {
			
if ($this->validateIp())
	if ($this->validateCaptcha())
				if (!$this->customer->login($this->request->post['email'], $this->request->post['password'])) {
					$json['error']['warning'] = $this->language->get('error_login');

					$this->model_account_customer->addLoginAttempt($this->request->post['email']);
				} else {
					$this->model_account_customer->deleteLoginAttempts($this->request->post['email']);
				}
			}
		}
		
                // opensecurity
                $this->authLog($json);
                
                if($login_error['warning'] != '') $json['error']['warning'] = $login_error['warning'];
                
                if($this->ops->isCaptchaNeeded() && isset($json['error']['warning'])) $json['redirect'] = $this->url->link('checkout/checkout', '', 'SSL');
                // opensecurity
                
		if (!$json) {
			// Trigger customer pre login event
			$this->event->trigger('pre.customer.login');

			// Unset guest
			unset($this->session->data['guest']);

			// Default Shipping Address
			$this->load->model('account/address');

			if ($this->config->get('config_tax_customer') == 'payment') {
				$this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
			}

			if ($this->config->get('config_tax_customer') == 'shipping') {
				$this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
			}

			// Wishlist
			if (isset($this->session->data['wishlist']) && is_array($this->session->data['wishlist'])) {
				$this->load->model('account/wishlist');

				foreach ($this->session->data['wishlist'] as $key => $product_id) {
					$this->model_account_wishlist->addWishlist($product_id);

					unset($this->session->data['wishlist'][$key]);
				}
			}

			// Add to activity log
			$this->load->model('account/activity');

			$activity_data = array(
				'customer_id' => $this->customer->getId(),
				'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
			);

			$this->model_account_activity->addActivity('login', $activity_data);

			// Trigger customer post login event
			$this->event->trigger('post.customer.login');

			$json['redirect'] = $this->url->link('checkout/checkout', '', 'SSL');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
