<?php
class bsiInstallStart
{	
	private $bsiCoreRoot  = '';
	private $bsiHostPath  = '';
	private $bsiDBCONFile = '/includes/db.conn.php';
	private $bsiGallery   = 'gallery/';
	
	public $installinfo = array('php_version'=>false, 'gd_version'=>false, 'config_file'=>false, 'gallery_path'=>false);
	public $installerror = array('session_disabled'=>false, 'config_notwritable'=>false, 'gallery_notwritable'=>false, 'gd_notinstalled'=>false, 'gd_versionnotpermit'=>false, 'mysql_notavailable'=>false);
	
	function __construct(){
		$this->getPathInfo();
		$this->getInstallInfo(); 
	}
	private function getPathInfo(){
		$path_info = pathinfo($_SERVER["SCRIPT_FILENAME"]);
		preg_match("/(.*[\/\\\])/",$path_info['dirname'],$tmpvar);
		$this->bsiCoreRoot = $tmpvar[1];		
		$host_info = pathinfo($_SERVER["PHP_SELF"]);
		$this->bsiHostPath = "http://".$_SERVER['HTTP_HOST'].$host_info['dirname']."/";		
	}
	public function getInstallInfo(){
		$this->installinfo['php_version'] = phpversion();
		
		if (!session_id()) $this->installerror['session_disabled'] = true;
		
		$this->installinfo['config_file'] = $this->bsiCoreRoot.$this->bsiDBCONFile;
		
		// check writable settings file
		if (!is_writable($this->installinfo['config_file'])) $this->installerror['config_notwritable'] = true; 
		
		$this->installinfo['gallery_path'] = $this->bsiCoreRoot.$this->bsiGallery;
		if (!$this->checkFolder($this->installinfo['gallery_path'])) $this->installerror['gallery_notwritable'] = true;
						
		if (!in_array("gd",get_loaded_extensions())) {
			$this->installerror['gd_notinstalled'] = true;
			$this->installerror['gd_versionnotpermit'] = true;
		}
		
		if (!$this->installerror['gd_notinstalled'] && function_exists('gd_info')){
			$info = gd_info();
			$this->installinfo['gd_version'] = preg_replace("/[^\d\.]/","",$info['GD Version']);	
			if ($this->installinfo['gd_version'] < 2) $this->installerror['gd_versionnotpermit'] = true;
		}
		
		if (!in_array("mysqli",get_loaded_extensions())) $this->installerror['mysql_notavailable'] = true;			
	}
	
	private function checkFolder($folderPath){
		if ( !($fileHandler=@fopen($folderPath."sample_bsi_dir_test.php","a+"))) return false;
		if (!@fwrite($fileHandler,"test")) return false;
		if (!@fclose($fileHandler)) return false;
		if (!@unlink($folderPath."sample_bsi_dir_test.php")) return false;
		
		return true;
	}	
} 
class bsiInstallFinish
{	
	public $adminUserName  = '';
	public $adminUserPass  = '';
	public $adminFirstName = '';
	public $adminLastName  = '';
	public $adminemail     = '';
	public $userSitePath   = '';
	public $adminSitePath  = '';
	
	private $encAdminPass = '';
	private $hotelName = '';
	private $hotelEmail = '';
		
	function __construct(){
		$this->getAuthParams();		
		$this->updateConfigData();
		$this->getHostPaths();
	}
	private function getAuthParams(){
		if(trim($_POST["admin_login"])){
			$this->adminUserName = trim($_POST["admin_login"]);
		}else{
			$this->adminUserName = "admin@".$_SERVER['HTTP_HOST'];
		}
		
		if(trim($_POST["admin_firstname"])){
			$this->adminFirstName = trim($_POST["admin_firstname"]);
		}else{
			$this->adminFirstName = "";
		}
		
		if(trim($_POST["admin_lastname"])){
			$this->adminLastName = trim($_POST["admin_lastname"]);
		}else{
			$this->adminLastName = "";
		}
		
		if(trim($_POST["admin_email"])){
			$this->adminemail = trim($_POST["admin_email"]);
		}else{
			$this->adminemail = "admin@".$_SERVER['HTTP_HOST'];
		}		
		
		if(trim($_POST["admin_password"])){
			$this->adminUserPass = trim($_POST["admin_password"]);
		}else{
			$this->adminUserPass = $this->autoGeneratePassword(8,8);
		}
		$this->encAdminPass = md5($this->adminUserPass);		
		
		if(trim($_POST["hotel_name"])){
			$this->hotelName = trim($_POST["hotel_name"]);
		}else{
			$this->hotelName = false;
		}
		
		if(trim($_POST["hotel_email"])){
			$this->hotelEmail = trim($_POST["hotel_email"]);
		}else{
			$this->hotelEmail = false;
		}				
	}
	
	private function autoGeneratePassword($length=10, $strength=0) {
		$vowels = 'aeuy';
		$consonants = 'bdghjmnpqrstvz';
		if ($strength & 1) {
			$consonants .= 'BDGHJLMNPQRSTVWXZ';
		}
		if ($strength & 2) {
			$vowels .= "AEUY";
		}
		if ($strength & 4) {
			$consonants .= '23456789';
		}
		if ($strength & 8) {
			$consonants .= '@#$%~';
		}
	 
		$password = '';
		$alt = time() % 2;
		for ($i = 0; $i < $length; $i++) {
			if ($alt == 1) {
				$password .= $consonants[(rand() % strlen($consonants))];
				$alt = 0;
			} else {
				$password .= $vowels[(rand() % strlen($vowels))];
				$alt = 1;
			}
		}
		return $password;
	}	
	
	private function updateConfigData(){
		mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE bsi_admin SET username = '".$this->adminUserName."', pass = '".$this->encAdminPass."', f_name = '".$this->adminFirstName."', l_name = '".$this->adminLastName."', email = '".$this->adminemail."' WHERE id = 1");
				
		if($this->hotelName){
			mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE bsi_configure SET conf_value = '".$this->hotelName."' WHERE conf_key = 'conf_portal_name'");
		}
		if($this->hotelEmail){
			mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE bsi_configure SET conf_value = '".$this->hotelEmail."' WHERE conf_key = 'conf_portal_email'");
		}
	}
	private function getHostPaths(){
		$host_info = pathinfo($_SERVER["PHP_SELF"]);		
		$bsiHostPath = "http://".$_SERVER['HTTP_HOST'].substr($host_info['dirname'], 0, strrpos($host_info['dirname'], '/'))."/";
		$this->adminSitePath = $bsiHostPath."cp/index.php";
		$this->userSitePath = $bsiHostPath."index.php";	
	}
}
class bsiInstallScript
{	
	private $bsiCoreRoot = '';
	private $bsiDBCONFile = '/includes/db.conn.php';
	public  $installerror = array('save_conn'=>false, 'mysql_conn'=>false, 'create_db'=>false, 'create_table'=>false);
	
	function __construct(){
		$this->setConfigPath();
		$this->doInstallScript();
	}
	private function setConfigPath(){
		$path_info = pathinfo($_SERVER["SCRIPT_FILENAME"]);
		preg_match("/(.*[\/\\\])/",$path_info['dirname'],$tmpvar);
		$this->bsiCoreRoot = $tmpvar[1];			
	}
	
	private function cleanString($string){	
		$string = preg_replace("/[\'\/\\\]/","",stripslashes($string));
		return $string;
	}
	
	public function writeFile($filestring){		
		$this->bsiDBCONFile = $this->bsiCoreRoot.$this->bsiDBCONFile;		
		$fhandle = fopen($this->bsiDBCONFile,"w");
		if (!$fhandle) {
			return false;
		}	
		if (fwrite($fhandle, $filestring) === FALSE) {
			return false;
		}
		fclose ($fhandle);
		return true;
	}		
		
	public function doInstallScript(){		
		$mysql_host = $this->cleanString($_POST['mysql_host']);
		$mysql_host = !$mysql_host?"localhost":$mysql_host;	
		
		$mysql_user = $this->cleanString($_POST['mysql_login']);
		$mysql_pass = $this->cleanString($_POST['mysql_password']);
		$mysql_db   = $this->cleanString($_POST['mysql_db']);
				
		$filestring = "<?php\ndefine(\"MYSQL_SERVER\", \"".$mysql_host."\");\ndefine(\"MYSQL_USER\", \"".$mysql_user."\");\ndefine(\"MYSQL_PASSWORD\", \"".$mysql_pass."\");\ndefine(\"MYSQL_DATABASE\", \"".$mysql_db."\");\n\n(\$GLOBALS[\"___mysqli_ston\"] = mysqli_connect(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD)) or die ('I cannot connect to the database because 1: ' . mysqli_error(\$GLOBALS[\"___mysqli_ston\"]));
mysqli_select_db(\$GLOBALS[\"___mysqli_ston\"], constant('MYSQL_DATABASE')) or die ('I cannot connect to the database because 2: ' . mysqli_error(\$GLOBALS[\"___mysqli_ston\"]));\n?>";		
						
		if(!$this->writeFile($filestring)){  // save settings
			$this->installerror['save_conn'] = true;
		}
		$mysql_link = @($GLOBALS["___mysqli_ston"] = mysqli_connect($mysql_host, $mysql_user, $mysql_pass));	
	
		
		if ($mysql_link){		
			if(!mysqli_select_db($mysql_link, $mysql_db)){
				// attempt to create db when doesn't exists
				if(!mysqli_query( $mysql_link, "create database ".$mysql_db)) {
					$this->installerror['create_db'] = true; 
				}else{
					mysqli_select_db( $mysql_link, $mysql_db);
				}
			}
		}else{			
			$this->installerror['mysql_conn'] = true;
			$this->installerror['create_db'] = true; 
		}
		
		// no errors if mysql connection successful and db is exists or was created		
		if (!$this->installerror['mysql_conn'] && !$this->installerror['create_db']){
			//install dbscripts
			$this->installDBScripts();
			
			// check if all tables was created correctly
             $allowed_tables = array(1=>"bsi_admin", "bsi_adminmenu", "bsi_bookings", "bsi_car_extras", "bsi_car_features", "bsi_car_master", "bsi_car_type", "bsi_car_vendor", "bsi_clients", "bsi_configure", "bsi_deposit_duration", "bsi_discount_duration", "bsi_email_contents", "bsi_invoice", "bsi_payment_gateway", "bsi_selected_features", "bsi_res_data", "bsi_car_location", "bsi_all_location", "bsi_close_date", "bsi_language", "bsi_car_priceplan");
			 
             $res = mysqli_query($GLOBALS["___mysqli_ston"], "show tables");			
             while ($row =@mysqli_fetch_row($res)){				 
                  $table = preg_replace("/(.*)/","$1",$row[0]); 
                  if ($key = array_search($table,$allowed_tables)) {
                      unset ($allowed_tables[$key]);
                  }
             }
             if (count($allowed_tables)>0) $this->installerror['create_table'] = true;  // not all tables was created			
		}else{
			$this->installerror['create_table'] = true;
		}		
	}	
	
	private function installDBScripts(){
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_admin`");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_admin` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `pass` varchar(255) CHARACTER SET latin1 NOT NULL,
					  `username` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT 'admin',
					  `access_id` int(1) NOT NULL DEFAULT '0',
					  `f_name` varchar(255) CHARACTER SET latin1 NOT NULL,
					  `l_name` varchar(255) CHARACTER SET latin1 NOT NULL,
					  `email` varchar(255) CHARACTER SET latin1 NOT NULL,
					  `designation` varchar(255) CHARACTER SET latin1 NOT NULL,
					  `last_login` timestamp NULL DEFAULT NULL,
					  `status` tinyint(1) NOT NULL DEFAULT '1',
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;");
					
		mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `bsi_admin` (`pass`, `username`, `access_id`, `f_name`, `l_name`, `email`, `designation`, `status`) VALUES 
					('aaa', 'admin', '1', 'aa', 'aa', 'aa', 'Administrator', '1');");		
									
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_adminmenu`");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_adminmenu` (
					  `id` int(4) NOT NULL AUTO_INCREMENT,
					  `name` varchar(200) NOT NULL DEFAULT '',
					  `url` varchar(200) DEFAULT NULL,
					  `menu_desc` varchar(200) NOT NULL DEFAULT '',
					  `parent_id` int(4) DEFAULT '0',
					  `status` enum('Y','N') DEFAULT 'Y',
					  `ord` int(5) NOT NULL DEFAULT '0',
					  `privileges` int(11) NOT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `kid` (`name`,`parent_id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
					
		mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `bsi_adminmenu` (`id`, `name`, `url`, `menu_desc`, `parent_id`, `status`, `ord`, `privileges`) VALUES
					(1, 'CAR MANAGER', '#', '', 0, 'Y', 1, 1),
					(2, 'PRICE MANAGER', '#', '', 0, 'Y', 2, 1),
					(3, 'BOOKING MANAGER', '#', '', 0, 'Y', 3, 1),
					(4, 'SETTING', '#', '', 0, 'Y', 7, 1),
					(5, ' Car List', 'car-list.php', '', 1, 'Y', 1, 1),
					(6, 'Car Vendors', 'car-vendors.php', '', 1, 'Y', 2, 1),
					(7, ' Car Types', 'car-types.php', '', 1, 'Y', 3, 1),
					(8, 'Car Features', 'car-features.php', '', 1, 'Y', 4, 1),
					(9, 'Prepaid Amount Setting ', 'deposit-duration.php', '', 2, 'Y', 2, 1),
					(10, 'Car Extras', 'car-extras.php', '', 2, 'Y', 4, 1),
					(11, 'Booking List', 'booking-list.php', '', 3, 'Y', 1, 1),
					(12, 'Customer Lookup', 'customer-lookup.php', '', 3, 'Y', 2, 1),
					(13, 'Car Blocking', 'car-blocking.php', '', 3, 'Y', 3, 1),
					(14, 'Global Setting', 'global-setting.php', '', 4, 'Y', 1, 1),
					(15, 'Payment Gateway', 'payment-gateway.php', '', 4, 'Y', 2, 1),
					(16, 'Email Contents', 'email-contents.php', '', 4, 'Y', 3, 1),
					(17, 'Discount upon duration', 'discount-duration.php', '', 2, 'Y', 3, 1),
					(18, 'Price Calculation Setup', 'price-calculation.php', '', 2, 'Y', 0, 1),
					(19, 'Location Manager', 'location_list.php', '', 1, 'Y', 6, 1),
					(20, 'Close Day Setting', 'close_day.php', '', 4, 'Y', 5, 1),
					(72, 'Admin Menu Manager', 'adminmenu.list.php', '', 4, 'Y', 6, 0),
					(73, 'LANGUAGE MANAGER', '#', '', 0, 'Y', 6, 0),
					(74, 'Manage Languages', 'manage_langauge.php', '', 73, 'Y', 1, 0),
					(75, 'Car Price Plan', 'priceplan_list.php', '', 2, 'Y', 1, 0);");
		
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_bookings` (
						  `booking_id` int(10) NOT NULL,
						  `booking_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
						  `pickup_datetime` datetime NOT NULL,
						  `dropoff_datetime` datetime NOT NULL,
						  `client_id` int(11) unsigned DEFAULT NULL,
						  `discount_coupon` varchar(50) DEFAULT NULL,
						  `total_cost` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
						  `payment_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
						  `payment_type` varchar(255) NOT NULL,
						  `payment_success` tinyint(1) NOT NULL DEFAULT '0',
						  `payment_txnid` varchar(100) DEFAULT NULL,
						  `paypal_email` varchar(500) DEFAULT NULL,
						  `special_id` int(10) unsigned NOT NULL DEFAULT '0',
						  `special_requests` text,
						  `is_block` tinyint(4) NOT NULL DEFAULT '0',
						  `is_deleted` tinyint(4) NOT NULL DEFAULT '0',
						  `block_name` varchar(255) DEFAULT NULL,
						  `pick_loc` varchar(255) DEFAULT NULL,
						  `drop_loc` varchar(255) DEFAULT NULL,
						  PRIMARY KEY (`booking_id`),
						  KEY `start_date` (`pickup_datetime`),
						  KEY `end_date` (`dropoff_datetime`),
						  KEY `booking_time` (`discount_coupon`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_car_extras`");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_car_extras` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `car_extras` varchar(255) NOT NULL,
					  `price` decimal(10,2) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
							
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_car_features`");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_car_features` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `features_title` varchar(255) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");					
							
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_car_master`");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_car_master` (
					  `car_id` int(11) NOT NULL AUTO_INCREMENT,
					  `car_type_id` int(11) NOT NULL,
					  `car_vendor_id` int(11) NOT NULL,
					  `car_model` varchar(255) NOT NULL,
					  `car_img` varchar(255) NOT NULL,
					  `mileage` varchar(50) NOT NULL,
					  `fuel_type` varchar(255) NOT NULL,
					  `total_car` int(11) NOT NULL,
					  `status` tinyint(1) NOT NULL,
					  PRIMARY KEY (`car_id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
		
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_car_priceplan`");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_car_priceplan` (
					  `pp_id` int(10) NOT NULL auto_increment,
					  `car_id` int(10) NOT NULL,
					  `start_date` date NOT NULL,
					  `end_date` date NOT NULL,
					  `price_type` int(1) NOT NULL,
					  `mon` decimal(10,2) NOT NULL,
					  `tue` decimal(10,2) NOT NULL,
					  `wed` decimal(10,2) NOT NULL,
					  `thu` decimal(10,2) NOT NULL,
					  `fri` decimal(10,2) NOT NULL,
					  `sat` decimal(10,2) NOT NULL,
					  `sun` decimal(10,2) NOT NULL,
					  `default_price` tinyint(4) NOT NULL default '1',
					  PRIMARY KEY  (`pp_id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
		
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_car_type`");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_car_type` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `type_title` varchar(255) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");			
		
									
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_car_vendor`");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_car_vendor` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `vendor_title` varchar(255) NOT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
											
		
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_clients`");			
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_clients` (
					  `client_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					  `first_name` varchar(64) DEFAULT NULL,
					  `surname` varchar(64) DEFAULT NULL,
					  `title` varchar(16) DEFAULT NULL,
					  `street_addr` text,
					  `city` varchar(64) DEFAULT NULL,
					  `province` varchar(128) DEFAULT NULL,
					  `zip` varchar(64) DEFAULT NULL,
					  `country` varchar(64) DEFAULT NULL,
					  `phone` varchar(64) DEFAULT NULL,
					  `fax` varchar(64) DEFAULT NULL,
					  `email` varchar(128) DEFAULT NULL,
					  `additional_comments` text,
					  `ip` varchar(32) DEFAULT NULL,
					  `existing_client` tinyint(1) NOT NULL DEFAULT '0',
					  PRIMARY KEY (`client_id`),
					  KEY `email` (`email`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
					
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_close_date`");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_close_date` (
						  `closedt` varchar(50) NOT NULL
						) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
							
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_configure`");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_configure` (
					  `conf_id` int(11) NOT NULL AUTO_INCREMENT,
					  `conf_key` varchar(100) NOT NULL,
					  `conf_value` varchar(500) DEFAULT NULL,
					  PRIMARY KEY (`conf_id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
		mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `bsi_configure` (`conf_id`, `conf_key`, `conf_value`) VALUES
						(1, 'conf_portal_name', 'Car Rental System'),
						(2, 'conf_portal_streetaddr', '99 xxxxx Road'),
						(3, 'conf_portal_city', 'Your City'),
						(4, 'conf_portal_state', 'Your State'),
						(5, 'conf_portal_country', 'Your Country'),
						(6, 'conf_portal_zipcode', '1111112'),
						(7, 'conf_portal_phone', '999999999'),
						(8, 'conf_portal_fax', '222'),
						(9, 'conf_portal_email', 'info@bestsoftinc.com'),
						(13, 'conf_currency_symbol', '$'),
						(14, 'conf_currency_code', 'USD'),
						(20, 'conf_tax_amount', '10'),
						(21, 'conf_dateformat', 'dd/mm/yy'),
						(22, 'conf_booking_exptime', '500'),
						(24, 'conf_enabled_discount', '1'),
						(25, 'conf_enabled_deposit', '1'),
						(26, 'conf_portal_timezone', 'America/New_York'),
						(27, 'conf_booking_turn_off', '0'),
						(28, 'conf_min_night_booking', ''),
						(30, 'conf_notification_email', 'sales@bestsoftinc.com'),
						(38, 'conf_pickup_location', '147 West 83rd Street, New York'),
						(39, 'conf_dropoff_location', 'same location'),
						(40, 'conf_interval_between_rent', '01:59:59'),
						(41, 'conf_price_calculation_type', '3'),
						(42, 'conf_booking_start', '0'),
						(43, 'conf_mesurment_unit', 'Km');");
					
					
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_deposit_duration`");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_deposit_duration` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `day_from` int(11) NOT NULL,
						  `day_to` int(11) NOT NULL,
						  `deposit_percent` decimal(10,2) NOT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
		
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_discount_duration`");			
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_discount_duration` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `day_from` int(11) NOT NULL,
					  `day_to` int(11) NOT NULL,
					  `discount_percent` decimal(10,2) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");		
							
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_email_contents`");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_email_contents` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `email_name` varchar(500) NOT NULL,
					  `email_subject` varchar(500) NOT NULL,
					  `email_text` longtext NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
		mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `bsi_email_contents` (`id`, `email_name`, `email_subject`, `email_text`) VALUES
					(1, 'Confirmation Email', 'Confirmation of your successfull booking for that Apartment.', 'Text can be chnage in admin Panel.'),
					(2, 'Cancellation Email ', 'Cancellation Email subject.', 'Text Can be changed from admin panel');");
								
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_invoice`");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_invoice` (
					  `booking_id` int(10) NOT NULL,
					  `client_name` varchar(500) NOT NULL,
					  `client_email` varchar(500) NOT NULL,
					  `invoice` longtext NOT NULL,
					  PRIMARY KEY (`booking_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
					
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_language`;");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_language` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `lang_title` varchar(255) NOT NULL,
					  `lang_code` varchar(10) NOT NULL,
					  `lang_file` varchar(255) NOT NULL,
					  `lang_default` tinyint(1) NOT NULL DEFAULT '0',
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");


		mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `bsi_language` (`id`, `lang_title`, `lang_code`, `lang_file`, `lang_default`) VALUES
						(1, 'English', 'en', 'english.php', 1),
						(2, 'French', 'fr', 'french.php', 0),
						(3, 'German', 'de', 'german.php', 0),
						(4, 'Greek', 'el', 'greek.php', 0),
						(5, 'Spanish', 'es', 'espanol.php', 0),
						(6, 'Italian', 'it', 'italian.php', 0),
						(7, 'Dutch', 'de', 'dutch.php', 0),
						(8, 'Polish', 'pl', 'polish.php', 0),
						(9, 'Portuguese', 'pt', 'portuguese.php', 0),
						(10, 'Russian', 'ru', 'russian.php', 0),
						(11, 'Turkish', 'tr', 'turkish.php', 0),
						(12, 'Thai', 'th', 'thai.php', 0),
						(13, 'Chinese', 'zh-CN', 'chinese.php', 0),
						(14, 'Indonesian', 'id', 'indonesian.php', 0),
						(15, 'Romanian', 'ro', 'romanian.php', 0),
						(17, 'Japanese', 'ja', 'japanese.php', 0);");

					
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_payment_gateway`");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_payment_gateway` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `gateway_name` varchar(255) NOT NULL,
					  `gateway_code` varchar(50) NOT NULL,
					  `account` varchar(255) DEFAULT NULL,
					  `enabled` tinyint(1) NOT NULL DEFAULT '0',
					  `ord` int(11) NOT NULL DEFAULT '0',
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
		
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_res_data` (
					  `booking_id` int(11) NOT NULL,
					  `car_id` int(11) NOT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
					
		mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `bsi_payment_gateway` (`id`, `gateway_name`, `gateway_code`, `account`, `enabled`, `ord`) VALUES
					(4, 'PayPal', 'pp', 'phpdev_1330251667_biz@aol.com', 1, 1),
					(7, ' Call : 1800 000 000 for Payment', 'poa', NULL, 1, 3);");
		
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_selected_features`");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_selected_features` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `car_id` int(11) NOT NULL,
					  `features_id` int(11) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
					
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_car_location`");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_car_location` (
					  `car_id` int(11) NOT NULL,
					  `loc_id` int(11) NOT NULL,
					  `loc_type` int(11) NOT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
					
		mysqli_query($GLOBALS["___mysqli_ston"], "DROP TABLE IF EXISTS `bsi_all_location`");
		mysqli_query($GLOBALS["___mysqli_ston"], "CREATE TABLE `bsi_all_location` (
					  `loc_id` int(11) NOT NULL AUTO_INCREMENT,
					  `location_title` varchar(500) NOT NULL,
					  PRIMARY KEY (`loc_id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
		
	}	
}
?>
