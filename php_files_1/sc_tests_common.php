<?php
class sc_tests_common extends testCollection {
	public $master;
	public $backup;
	public $relay; //one of the relays
	public $relay_array; //array with all the relays (0,1,2...)
	public $cookies;
	public $version;
	protected $servers = array ();
	public $test_file = 'count.php';
	public $test_file2 = 'sess_size.php';
	public $session_life_time = 1440;
	public $count;
	
	protected function should_setup($method_string) {
		if (gutils::should_refresh_config ( __CLASS__ ) === true) {
			return true;
		}
		
		if ($this->get_property ( 'restart_happened', 'sc' ) !== 1 && ! gutils::was_method_performed ( $method_string )) {
			return true;
		}
		
		return false;
	}
	
	public function setup($number_of_servers) {
		$this->servers = gutils::assign_servers ( $number_of_servers );
		$allowed_hosts = "";
		$method_string = 'global';
		foreach ( $this->servers as $idx => $server ) {
			$server_id = $server ['server_id'];
			$staf_handle = $server ['staf_handle'];
			sc_daemon::get_instance ( $staf_handle )->stop ();
		}
		//if ($this->should_setup($method_string)) {	
		foreach ( $this->servers as $idx => $server ) {
			$server_id = $server ['server_id'];
			$staf_handle = $server ['staf_handle'];
			Logger::get_instance ( gutils::get_suite_id () )->set_prefix_txt ( $server_id );
			Webserver::get_instance ( $staf_handle )->copy_to_docroot ( Utils::make_path ( gutils::get_gonzo_dir (), 'res', 'sc', $this->test_file ) );
			Webserver::get_instance ( $staf_handle )->copy_to_docroot ( Utils::make_path ( gutils::get_gonzo_dir (), 'res', 'sc', $this->test_file2 ) );
			Webserver::get_instance ( $staf_handle )->stop ();
			sc::get_instance ( $staf_handle )->ini_init ( false );
			sc::get_instance ( $staf_handle )->ini_set ( 'zend_sc.scd_port', 10162 );
			$internal_ip = sc_daemon::get_instance ( $staf_handle )->get_network_hostname ();
			$allowed_hosts .= $internal_ip . ',';
			DBG3 ( "server_id [$idx] has external ip of [{$server["server_id"]}] and internal ip of [$internal_ip]" );
		}
		
		$allowed_hosts = rtrim ( $allowed_hosts, ',' );
		
		foreach ( $this->servers as $idx => $server ) {
			$server_id = $server ['server_id'];
			$staf_handle = $server ['staf_handle'];
			Logger::get_instance ( gutils::get_suite_id () )->set_prefix_txt ( $server_id );
			sc_daemon::get_instance ( $staf_handle )->set_allowed_hosts ( $allowed_hosts );
			INFO ( "setting allowed_hosts: [$allowed_hosts]" );
			sc_daemon::get_instance ( $staf_handle )->ini_init ( false ); // writing, not copying to remote
			sc_daemon::get_instance ( $staf_handle )->ini_set ( 'zend_sc.network.tcp_port_remote', 10158, false, false ); // not refreshing
			sc_daemon::get_instance ( $staf_handle )->ini_set ( 'zend_sc.network.tcp_port_local', 10162, false, false );
			sc_daemon::get_instance ( $staf_handle )->ini_set ( 'zend_sc.log_verbosity_level', 5, false, false );
			sc_daemon::get_instance ( $staf_handle )->ini_set ( 'zend_sc.ha.broadcast_delta', 1, false, false );
			sc_daemon::get_instance ( $staf_handle )->ini_set ( 'zend_sc.session_lifetime', $this->session_life_time, false, false );
			sc::get_instance($staf_handle)->ini_set('zend_sc.log_verbosity_level',3,false);
			if (isset ( $this->transfer_max_size )) {
				sc_daemon::get_instance ( $staf_handle )->ini_set ( 'zend_sc.graceful_transfer_max_size', $this->transfer_max_size, false, false );
			} else
				sc_daemon::get_instance ( $staf_handle )->ini_set ( 'zend_sc.graceful_transfer_max_size', 10, false, false );
			if (isset ( $this->graceful_max_time )) {
				sc_daemon::get_instance ( $staf_handle )->ini_set ( 'zend_sc.graceful_max_time', $this->graceful_max_time, false, false );
			} else
				sc_daemon::get_instance ( $staf_handle )->ini_set ( 'zend_sc.graceful_max_time', 60, false, false );
			sc_daemon::get_instance ( $staf_handle )->php_ini_set ( 'session.save_handler', 'cluster', 'Session' );
			sc_daemon::get_instance ( $staf_handle )->ini_set ( 'zend_sc.graceful_primary_replacement', "", false , false );
			sc_daemon::get_instance ( $staf_handle )->ini_set ( 'zend_sc.graceful_secondary_replacement', "", false );
		}
		
		DBG3 ( "the allowed hosts are " . print_r ( $allowed_hosts, true ) );
		$this->set_property ( 'restart_happened', 1, 'sc' );
		//	}	
		

		foreach ( $this->servers as $idx => $server ) {
			$server_id = $server ['server_id'];
			$staf_handle = $server ['staf_handle'];
			Logger::get_instance ( gutils::get_suite_id () )->set_prefix_txt ( $server_id );
			$os = Webserver::get_instance ( $staf_handle )->get_os (); //finding the os and tmp dir to clear old sc_last_gracful files
			$zend_install_dir = Webserver::get_instance ( $staf_handle )->get_zend_install_dir ();
			$tmp_dir = Utils::make_os_path ( $os, $zend_install_dir, 'tmp', 'sc_last_graceful' );
			Utils::delete ( $tmp_dir, $staf_handle );
			if (isset ( $this->broadcast ) && isset ( $this->disk_storage )) {
				sc_daemon::get_instance ( $staf_handle )->ini_set ( 'zend_sc.ha.use_broadcast', $this->broadcast, false, false );
				sc_daemon::get_instance ( $staf_handle )->ini_set ( 'zend_sc.storage.use_permanent_storage', $this->disk_storage, false ); // writing+copying
			}
			Webserver::get_instance ( $staf_handle )->start ();
			sc_daemon::get_instance ( $staf_handle )->start ();
			$this->servers [$idx] ['webserver'] = Webserver::get_instance ( $staf_handle );
			$internal_ip = sc_daemon::get_instance ( $staf_handle )->get_network_hostname ();
			$this->servers [$idx] ["internal_ip"] = $internal_ip;
		}
		
		sleep ( 5 );
		gutils::register_method ( $method_string );
	}
	
	public function destruct_test() {
		foreach ( $this->servers as $idx => $server ) {
			$server_id = $server ['server_id'];
			$staf_handle = $server ['staf_handle'];
			sc_daemon::get_instance ( $staf_handle )->stop ();
			$this->restore_current_snapshot ();
		}
	}
public function request($server_array, $change_cookies = 0,$reset=0, $instance = 1) {
		if (! (isset ( $server_array ['server_id'] ))) {
			throw new Exception ( "No server supplied so no request could be done, received server_array: " . print_r ( $server_array, true ) );
		}
		if ($reset == 1) { //Counter to follow manually the number of requests and derive from it the expected result
			$this->count = 0;
			//$this->count[$instance] = 0;
		}
		if (isset ( $this->cookies [$instance] )) { //doing the request with 2 options - first request(no sess cookie to send) and all other
			$zend_res = Webserver::get_instance ( $server_array ['staf_handle'] )->exec_file ( $this->test_file, array (), FALSE, array (), $this->cookies [$instance] );
			DBG3 ( "using cookies " . print_r ( $this->cookies [$instance], 1 ) );
		} else {
			$zend_res = Webserver::get_instance ( $server_array ['staf_handle'] )->exec_file ( $this->test_file, array (), FALSE );
			$this->count = 0; // since it's the first request we start the counter
			//$this->count[$instance] = 0;
		}
		DBG2 ( "The internal IP is " . $server_array ['internal_ip'] );
		$this->count ++;
		//$this->count[$instance] ++;
		
		if (! ($zend_res instanceof Zend_Http_Response)) {
			throw new Exception ( "There was an error in the request - proper response was not received" );
		}
		
		/* @var $res Zend_Http_Response */
		$res ['request'] = $zend_res;
		$res ['body'] = $zend_res->getBody ();
		$headers = $zend_res->getHeaders ();
		$res ['sess_details'] = sc::parse_session_cookie ( $headers );
		DBG3 ( "the parse_session_response return value= " . print_r ( $res ['sess_details'], true ) );
		if ($res ['sess_details'] === FALSE) {
			if ($change_cookies === 1) {
				DBG3 ( print_r ( '$change_cookies=' . $change_cookies . '  $this->cookies[PHPSESSID]=' . $this->cookies [$instance] ['PHPSESSID'] . '  $res[sess_details][session_string]=' . $res ['sess_details'] ['session_string'] ) );
				throw new Exception ( "Session Cookie should change but it is the same. Headers are are\n" . print_r ( $headers, true ) );
			}
		} else {
			if ($change_cookies === 0) {
				throw new Exception ( "Cookies should not change but the current session cookie is different then the last.
								Sesion details are" . print_r ( $res ['sess_details'], 1 ) );
			}
			//DBG3("the session details=" . print_r($res ['sess_details'], 1));
			self::set_roles ( $res ['sess_details'], $instance );
			$this->cookies [$instance] = array ('PHPSESSID' => $res ['sess_details'] ['session_string'] );
			$this->version [$instance] = $res ['sess_details'] ['version'];
			if ($res ['sess_details'] ['backup_ip'] == 0) {
				WARNING ( 'the backup ip is 0.' );
			}
			//DBG3("the cookie array: " . print_r($this->cookies . ' The version: ' . $this->version, 1));
		}
		
		//DBG3("the count is " . $this->count . ", the cookie array: " . print_r($this->cookies,1) . ', The version: ' . $this->version, 1);
		return $res;
	
	}
	private function set_roles($sess_details_array, $instance) {
		//$old_master[$instance] = $this->master[$instance];
		//$old_backup[$instance] = $this->backup[$instance];
		//$old_relay[$instance] = $this->relay[$instance];
		unset ( $this->master [$instance] );
		$this->backup [$instance] = 0;
		$this->relay [$instance] = 0;
		foreach ( $this->servers as $idx => &$server ) {
			$server ['external_id'] = $idx;
			if ($server ['internal_ip'] == $sess_details_array ['master_ip']) {
				$this->master [$instance] = $server;
			} elseif ($server ['internal_ip'] == $sess_details_array ['backup_ip']) {
				$this->backup [$instance] = $server;
			} else {
				$server_status = sc::get_instance ( $server ['staf_handle'] )->get_daemon_status ();
				$this->un_verified_relay [$instance] = $server;
				if ($server_status === "ZEND_SC_STATUS_OK") {
					$this->relay [$instance] = $server;
					$this->relay_array [$instance] [] = $server;
				}
			}
		}
		if ($this->relay [$instance] == 0)
			$this->relay [$instance] = $this->un_verified_relay; //keep compatibility with older state machine tests and minimize the influence of bug 29969
		if ((! isset ( $this->master [$instance] )) || ! $this->backup [$instance]) {
			WARNING ( "either master  or backup field are empty in the cookie empty" );
		}
		
		if ($this->master [$instance] ['server_id'] == $this->master [$instance] ["internal_ip"]) { // then short msg enough
			$msg = "roles: master - " . $this->master [$instance] ['server_id'] . ", backup - " . $this->backup [$instance] ['server_id'] . ", relay - " . $this->relay [$instance] ['server_id'];
		} else {
			$msg = "roles: master - " . $this->master [$instance] ['server_id'] . ':' . $this->master [$instance] ["internal_ip"] . ", backup - " . $this->backup [$instance] ['server_id'] . ':' . $this->backup [$instance] ["internal_ip"];
		}
		
		INFO ( $msg );
	}

//	private function set_roles($sess_details_array) {
//		$old_master = $this->master;
//		$old_backup = $this->backup;
//		$old_relay = $this->relay;
//		unset ( $this->master );
//		$this->backup = 0;
//		$this->relay = 0;
//		foreach ( $this->servers as $idx => $server ) {
//			if ($server ['internal_ip'] == $sess_details_array ['master_ip']) {
//				$this->master = $this->servers [$idx];
//			} elseif ($server ['internal_ip'] == $sess_details_array ['backup_ip']) {
//				$this->backup = $this->servers [$idx];
//			} else {
//				$server_status = sc::get_instance ( $server ['staf_handle'] )->get_daemon_status ();
//				$this->un_verified_relay = $this->servers [$idx];
//				if ($server_status === "ZEND_SC_STATUS_OK") {
//					$this->relay = $this->servers [$idx];
//					$this->relay_array [] = $this->servers [$idx];
//				}
//			}
//		}
//		if ($this->relay == 0)
//			$this->relay = $this->un_verified_relay; //keep compatibility with older state machine tests and minimize the influence of bug 29969
//		if ((! isset ( $this->master )) || ! $this->backup) {
//			WARNING ( "either master  or backup field are empty in the cookie empty" );
//		}
//		
//		if ($this->count > 1 && $old_backup != $this->backup && $old_master != $this->master) {
//			WARNING ( 'Both master and backup fields changed in the last request' );
//		}
//		
//		if ($this->master ['server_id'] == $this->master ["internal_ip"]) { // then short msg enough
//			$msg = "roles: master - " . $this->master ['server_id'] . ", backup - " . $this->backup ['server_id'] . ", relay - " . $this->relay ['server_id'];
//		} else {
//			$msg = "roles: master - " . $this->master ['server_id'] . ':' . $this->master ["internal_ip"] . ", backup - " . $this->backup ['server_id'] . ':' . $this->backup ["internal_ip"] . ", relay - " . $this->relay ['server_id'] . ':' . $this->relay ["internal_ip"];
//		}
//		
//		INFO ( $msg );
//	}
	
//	public function request($server_array, $change_cookies = 0, $reset = 0) {
//		if (! (isset ( $server_array ['server_id'] ))) {
//			throw new Exception ( "No server supplied so no request could be done, received server_array: " . print_r ( $server_array, true ) );
//		}
//		if ($reset == 1) { //Counter to follow manually the number of requests and derive from it the expected result
//			$this->count = 0;
//		}
//		if (isset ( $this->cookies )) { //doing the request with 2 options - first request(no sess cookie to send) and all other
//			$zend_res = Webserver::get_instance ( $server_array ['staf_handle'] )->exec_file ( $this->test_file, array (), FALSE, array (), $this->cookies );
//			DBG3 ( "using cookies " . print_r ( $this->cookies, 1 ) );
//		} else {
//			$zend_res = Webserver::get_instance ( $server_array ['staf_handle'] )->exec_file ( $this->test_file, array (), FALSE );
//			$this->count = 0; // since it's the first request we start the counter
//		}
//		DBG2 ( "The internal IP is " . $server_array ['internal_ip'] );
//		$this->count ++;
//		
//		if (! ($zend_res instanceof Zend_Http_Response)) {
//			throw new Exception ( "There was an error in the request - proper response was not received" );
//		}
//		
//		/* @var $res Zend_Http_Response */
//		$res ['request'] = $zend_res;
//		$res ['body'] = $zend_res->getBody ();
//		$headers = $zend_res->getHeaders ();
//		$res ['sess_details'] = sc::parse_session_cookie ( $headers );
//		DBG3 ( "the parse_session_response return value= " . print_r ( $res ['sess_details'], true ) );
//		if ($res ['sess_details'] === FALSE) {
//			if ($change_cookies === 1) {
//				DBG3 ( print_r ( '$change_cookies=' . $change_cookies . '  $this->cookies[PHPSESSID]=' . $this->cookies ['PHPSESSID'] . '  $res[sess_details][session_string]=' . $res ['sess_details'] ['session_string'] ) );
//				throw new Exception ( "Session Cookie should change but it is the same. Headers are are\n" . print_r ( $headers, true ) );
//			}
//		} else {
//			if ($change_cookies === 0) {
//				throw new Exception ( "Cookies should not change but the current session cookie is different then the last.
//								Sesion details are" . print_r ( $res ['sess_details'], 1 ) );
//			}
//			//DBG3("the session details=" . print_r($res ['sess_details'], 1));
//			self::set_roles ( $res ['sess_details'] );
//			$this->cookies = array ('PHPSESSID' => $res ['sess_details'] ['session_string'] );
//			$this->version = $res ['sess_details'] ['version'];
//			if ($res ['sess_details'] ['backup_ip'] == 0) {
//				WARNING ( 'the backup ip is 0.' );
//			}
//			//DBG3("the cookie array: " . print_r($this->cookies . ' The version: ' . $this->version, 1));
//		}
//		
//		//DBG3("the count is " . $this->count . ", the cookie array: " . print_r($this->cookies,1) . ', The version: ' . $this->version, 1);
//		return $res;
//	
//	}
	
	public function graceful_shutdown($server_array, $only_shutdown = 0, $no_fail_exception = 0) {
		$graceful_success = 0;
		$graceful_return = sc::get_instance ( $server_array ['staf_handle'] )->graceful_shutdown ();
		$t1 = time ();
		if ($graceful_return != "TRUE") {
			throw new Exception ( "graceful shutdown failed. response was" . $graceful_return );
			$graceful_success = 0;
		}
		if ($only_shutdown == 1)
			return TRUE;
		sleep ( 4 );
		for($i = 0; $i < 300; $i ++) {
			$d_status = sc::get_instance ( $server_array ['staf_handle'] )->get_daemon_status ();
			$d_status = trim ( $d_status );
			if ($d_status == "ZEND_SC_STATUS_STANDBY") {
				INFO ( "daemon status is " . $d_status );
				$graceful_success = 1;
				break;
			}
			if ($d_status !== "ZEND_SC_STATUS_SHUTDOWN_IN_PROCESS") {
				$last_error = sc::get_instance ( $server_array ['staf_handle'] )->get_last_error ();
				$msg = "After graceful shutdown daemon changed it's status to {$d_status} instead of standby. last error is: " . print_r ( $last_error, 1 );
				INFO ( $msg );
				break;
			}
			sleep ( 1 );
		}
		$t2 = time ();
		if ($graceful_success !== 1) {
			$msg = "After graceful shutdown daemon did not change it's status to STANDBY for" . $i . "seconds";
			if ($no_fail_exception == 0) {
				throw new Exception ( $msg );
			}
			print ($msg) ;
		}
		$this->get_replacement_list ();
		$this->time_to_graceful = $t2 - $t1;
		INFO ( "Graceful shutdown took ~ {$this->time_to_graceful} seconds" );
		$replacement_ip = $this->replacement_ip_list [$server_array ['internal_ip']] ['primary-replacement'];
		return $replacement_ip;
	}
	public function get_replacement_list($server = 0) {
		if ($server !== 0) {
			$server_to_ask_for_replacement_list = $server;
		} else {
			foreach ( $this->servers as $server ) { //which server can we ask for the new replacement list. we can ask the replaced server or any other standby server.
				$server_status = sc::get_instance ( $server ['staf_handle'] )->get_daemon_status ();
				if ($server_status == "ZEND_SC_STATUS_OK") {
					$server_to_ask_for_replacement_list = $server;
					//break;
				}
			}
			if (! isset ( $server_to_ask_for_replacement_list )) {
				throw new Exception ( "Cannot find replacement list since none of the servers were in status ZEND_SC_STATUS_OK" );
			}
		}
		$this->replacement_ip_list = sc::get_instance ( $server_to_ask_for_replacement_list ['staf_handle'] )->get_replacements_list ();
		DBG2 ( "The replacements list \n" . print_r ( $this->replacement_ip_list, 1 ) );
		$this->ip_array_to_servers_ids ();
	}
	public function reload_configuration($server_array, $restart = 0) {
		$reload_success = 0;
		if ($restart === 1) {
			sc_daemon::get_instance ( $server_array ['staf_handle'] )->stop;
			sleep ( 6 );
			sc_daemon::get_instance ( $server_array ['staf_handle'] )->start;
		} else {
			$reload_return = sc::get_instance ( $server_array ['staf_handle'] )->reload_configuration ();
			if (! $reload_return === "TRUE") {
				throw new Exception ( "reload configuration failed. response was" . $reload_return );
				$reload_success = 0;
			}
		}
		$t1 = time ();
		for($i = 0; $i < 300; $i ++) {
			$d_status = sc::get_instance ( $server_array ['staf_handle'] )->get_daemon_status ();
			$d_status = trim ( $d_status );
			if ($d_status == "ZEND_SC_STATUS_OK") {
				$t2 = time ();
				INFO ( "daemon status is " . $d_status );
				$reload_success = 1;
				break;
			}
			sleep ( 1 );
		}
		if ($reload_success !== 1) {
			throw new Exception ( "After reload configuration, daemon did not change it's status to OK for" . $i . "seconds" );
		}
		$this->get_replacement_list ( $server_array );
		$this->time_to_reload = $t2 - $t1;
		INFO ( "reload configuration took ~ {$this->time_to_reload} seconds" );
	}
	public function get_replacement_during_graceful($server_array) {
		$this->replacement_ip_list = sc::get_instance ( $server_array ['staf_handle'] )->get_replacements_list ();
		DBG2 ( "The replacements list \n" . print_r ( $this->replacement_ip_list, 1 ) );
		$replacement_ip = $this->replacement_ip_list [$server_array ['internal_ip']] ['ip'];
		INFO ( 'the replacement server IP is ' . $replacement_ip );
		$this->ip_array_to_servers_ids ();
		return $replacement_ip;
	}
	protected function ip_array_to_servers_ids() { //creating the server replacement list with server ids instead of server ips
		unset ( $this->replacement_ids );
		unset ( $this->replacement_ids_full);
		foreach ( $this->replacement_ip_list as $replaced_ip => $replacement_array ) {
			foreach ( $this->servers as $id => $server ) {
				if ($server ['internal_ip'] === $replaced_ip) {
					$replaced_id = $id;
				} elseif ($server ['internal_ip'] == $replacement_array ['primary-replacement']) {
					$primary_replacement_id = $id;
				} elseif ($server ['internal_ip'] == $replacement_array ['secondary-replacement']) {
					$secondary_replacement_id = $id;
				}
			}
			
			
			$this->replacement_ids [$replaced_id] = $primary_replacement_id; //left to keep compatibility with pre 5.03 tests before changing all the tests
			$this->replacement_ids_full [$replaced_id] ['primary-replacement'] = $primary_replacement_id;
			if (isset($secondary_replacement_id)) {
				$this->replacement_ids_full [$replaced_id] ['secondary-replacement'] = $secondary_replacement_id;
				if ($primary_replacement_id === $secondary_replacement_id) {
					throw new Exception ( "Primary and secondary replacement servers are the same" );
				}
			}else INFO ("The secondary replacement is empty");
		}
		INFO ( "the replacement id array is " . print_r ( $this->replacement_ids ), 1 );
	}
	protected function ip_array_to_servers_ids_pre_ZS5_03() { //creating the server replacement list with server ids instead of server ips
		unset ( $this->replacement_ids );
		foreach ( $this->replacement_ip_list as $replaced_ip => $replacement_array ) {
			foreach ( $this->servers as $id => $server ) {
				if ($server ['internal_ip'] === $replaced_ip) {
					$replaced_id = $id;
				}
				if ($server ['internal_ip'] == $replacement_array ['ip']) {
					$replacement_id = $id;
				}
			}
			$this->replacement_ids [$replaced_id] = $replacement_id;
		}
		INFO ( "the replacement id array is " . print_r ( $this->replacement_ids ), 1 );
	}
	static public function is_session_changed($headers) {
		DBG2 ( "headers: " . print_r ( $headers, true ) );
		foreach ( $headers ['Set-cookie'] as $cookie ) {
			if (strncmp ( $cookie, 'PHPSESSID', 9 ) == 0) {
				return TRUE;
			}
		}
		return FALSE;
	}
	
	protected function reset() {
		$this->count = 0;
		unset ( $this->cookies );
		unset ( $this->version );
	}
	public function untraced_request($server_array) {
		if (! (isset ( $server_array ['server_id'] ))) {
			throw new Exception ( "No server supplied so no request could be done, received server_array: " . print_r ( $server_array, true ) );
		}
		$zend_res = Webserver::get_instance ( $server_array ['staf_handle'] )->exec_file ( $this->test_file, array (), FALSE );
	}
	
	public function gen_session_size($server_array, $string_len = 1000) {
		if (! (isset ( $server_array ['server_id'] ))) {
			throw new Exception ( "No server supplied so no request could be done, received server_array: " . print_r ( $server_array, true ) );
		}
		$zend_res = Webserver::get_instance ( $server_array ['staf_handle'] )->exec_file ( $this->test_file2, array ('size' => $string_len ) );
	}
	public function bench($server_array, $url, $number = 10, $concurrency = 1, $throw_exception = 1) {
		$ret = Webserver::get_instance ( $server_array ['staf_handle'] )->bench_exec ( $number, $concurrency, $url );
		if (! $ret && $throw_exception)
			throw new exception ( "ab failed to finish all requests returned value was " . print_r ( $ret, 1 ) );
	}
	
	public function status_ok_servers() {
		foreach ( $this->servers as $server ) {
			$server_status = sc::get_instance ( $server ['staf_handle'] )->get_daemon_status ();
			if ($server_status == "ZEND_SC_STATUS_OK") {
				$ok_servers [] = $server ['external_id'];
				//break;
			}
		}
		return $ok_servers;
	}
	public function set_primary_graceful_ip($server,$ip){
		$staf_handle = $server ['staf_handle'];
		sc_daemon::get_instance ( $staf_handle )->ini_set ( 'zend_sc.graceful_primary_replacement', $ip, false );
		$this->reload_configuration ( $server );
	}
	public function set_secondary_graceful_ip($server,$ip) {
		$staf_handle = $server ['staf_handle'];
		sc_daemon::get_instance ( $staf_handle )->ini_set ( 'zend_sc.graceful_secondary_replacement', $ip, false );
		$this->reload_configuration ( $server );
	}
}