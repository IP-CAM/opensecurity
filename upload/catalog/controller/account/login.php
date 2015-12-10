<?php

include DIR_SYSTEM.'library/opslib.php';

class ControllerAccountLogin extends Controller {
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
		$this->load->model('account/customer');

		//opensecurity module
		$this->load->language('module/opensecurity');
		$this->ops = new opsLib( $this, $this->config, $this->language );
		//opensecurity module
		
		// Login override for admin users
		if (!empty($this->request->get['token'])) {
			$this->customer->logout();
			$this->cart->clear();

			unset($this->session->data['order_id']);
			unset($this->session->data['payment_address']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['shipping_address']);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['comment']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);

			$customer_info = $this->model_account_customer->getCustomerByToken($this->request->get['token']);

			if ($customer_info && $this->customer->login($customer_info['email'], '', true)) {
				// Default Addresses
				$this->load->model('account/address');

				if ($this->config->get('config_tax_customer') == 'payment') {
					$this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
				}

				if ($this->config->get('config_tax_customer') == 'shipping') {
					$this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
				}


				$this->response->redirect($this->url->link('account/account', '', 'SSL'));
			}
		}

		if ($this->customer->isLogged()) {
			$this->response->redirect($this->url->link('account/account', '', 'SSL'));
		}

		$this->load->language('account/login');

		$this->document->setTitle($this->language->get('heading_title'));

if ($this->validateIp())
	if ($this->validateCaptcha())
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
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

			// Added strpos check to pass McAfee PCI compliance test (http://forum.opencart.com/viewtopic.php?f=10&t=12043&p=151494#p151295)
			if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false || strpos($this->request->post['redirect'], $this->config->get('config_ssl')) !== false)) {
				$this->response->redirect(str_replace('&amp;', '&', $this->request->post['redirect']));
			} else {
				$this->response->redirect($this->url->link('account/account', '', 'SSL'));
			}
		}
		
		// opensecurity
		$data['error_captcha'] = 0;
		$data['text_captcha'] = $this->language->get('text_captcha');
		$this->authLog();
		
		$data['show_captcha'] = $this->ops->isCaptchaNeeded();
		// opensecurity
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_login'),
			'href' => $this->url->link('account/login', '', 'SSL')
		);

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_new_customer'] = $this->language->get('text_new_customer');
		$data['text_register'] = $this->language->get('text_register');
		$data['text_register_account'] = $this->language->get('text_register_account');
		$data['text_returning_customer'] = $this->language->get('text_returning_customer');
		$data['text_i_am_returning_customer'] = $this->language->get('text_i_am_returning_customer');
		$data['text_forgotten'] = $this->language->get('text_forgotten');

		$data['entry_email'] = $this->language->get('entry_email');
		$data['entry_password'] = $this->language->get('entry_password');

		$data['button_continue'] = $this->language->get('button_continue');
		$data['button_login'] = $this->language->get('button_login');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['action'] = $this->url->link('account/login', '', 'SSL');
		$data['register'] = $this->url->link('account/register', '', 'SSL');
		$data['forgotten'] = $this->url->link('account/forgotten', '', 'SSL');

		// Added strpos check to pass McAfee PCI compliance test (http://forum.opencart.com/viewtopic.php?f=10&t=12043&p=151494#p151295)
		if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false || strpos($this->request->post['redirect'], $this->config->get('config_ssl')) !== false)) {
			$data['redirect'] = $this->request->post['redirect'];
		} elseif (isset($this->session->data['redirect'])) {
			$data['redirect'] = $this->session->data['redirect'];

			unset($this->session->data['redirect']);
		} else {
			$data['redirect'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['password'])) {
			$data['password'] = $this->request->post['password'];
		} else {
			$data['password'] = '';
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/login.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/account/login.tpl', $data));
		} else {
			$this->response->setOutput($this->load->view('default/template/account/login.tpl', $data));
		}
	}

	protected function validate() {
		$this->event->trigger('pre.customer.login');

		// Check how many login attempts have been made.
		$login_info = $this->model_account_customer->getLoginAttempts($this->request->post['email']);

		if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
			$this->error['warning'] = $this->language->get('error_attempts');
		}

		// Check if customer has been approved.
		$customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);

		if ($customer_info && !$customer_info['approved']) {
			$this->error['warning'] = $this->language->get('error_approved');
		}

		if (!$this->error) {
			if (!$this->customer->login($this->request->post['email'], $this->request->post['password'])) {
				$this->error['warning'] = $this->language->get('error_login');

				$this->model_account_customer->addLoginAttempt($this->request->post['email']);
			} else {
				$this->model_account_customer->deleteLoginAttempts($this->request->post['email']);
			}
		}

		return !$this->error;
	}
}
