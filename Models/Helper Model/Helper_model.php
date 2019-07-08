<?php
class Helper_model extends CI_Model 
{

	/**
	 * the configuration table of website
	 * ### default: 'config'
	 */
	protected $config_table;
	
	/**
	 * If you want to resize image while uploading 
	 * ### default: true
	 */
	protected $resize_image;

	/**
	 * Set the resize height, resize_image must be true
	 * ### default: 1000
	 */
    protected $resize_h;
    
	/**
	 * Set the resize width, resize_image must be true
	 * ### default: 1000
	 */
	protected $resize_w;
	
	/**
	 * array containing settings of website
	 * ### default: array()
	 */
    protected $_settings = array();
	
	/**
	 * mail switch to send emails through aws or default email
	 * ### default: true
	 */
    protected $_mail_switch = true;
	
	function __construct(){
		parent::__construct();

		$this->config_table				=	'config';
		
		$this->resize_image 			=	true;
		$this->resize_h		 			=	1000;
		$this->resize_w 				=	1000;
		
		$this->_settings				=	$this->config_list();

		$this->_mail_switch				=	true;


	}


    /**
     * Checks if a request is made from cli or web, returns not_found() if called from web
	 * @param void
	 * @return void
     */
    function cli_only() {
        $is_cli = $this->input->is_cli_request();
        if (!$is_cli) {
            $msg = "Somebody from IP--> " . $_SERVER['REMOTE_ADDR'] . " Tried to open http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $this->notification->slack_notify($msg);
            not_found();
            exit();
        }
    }

	/**
	 * Fetching data from config table of database	
	 * @param string name of the configuration setting you want to fetch
	 * @param boolean output - if true, function will echo the setting, else return the value
	 * @return string if the second parameter is not passed
	 */
	public function settings($name = null, $output = null ){
		if($name == null){
			log_message('error', "settings called without a value");
			return false;
		}

		$query			=	$this->db->get_where( $this->config_table, array('name' => $name), 1);
		$res			=	$query->result();
		foreach($res as $details){
			if($output==1){
				echo $details->value;
			}else{
				return $details->value;
			}
		}
	}


	/**
	 * returns the configuration table from the database as an associative array
	 * @param void
	 * @return array of configuration settings from the config_table
	 */
	public function config_list(){
		$query			=	$this->db->select('*')->from( $this->config_table )->get();
		$config			=	$query->result();
		foreach($config as $row){
			$conf[$row->name]	=	$row->value;
		}
		$list_config	=	$conf;
		return $list_config;
	}
	
	
	/**
	 * uploads a file to amazon s3 bucket
	 * @param string path where the file is to be uploaded
	 * @param string name of the file
	 * @param string key if there are more than 1 files with the same name to be uploaded, i.e. if file is an array
	 * @param boolean image if true, will resize the original image to 1000x1000
	 * @return string path of the uploaded image
	 * @return false if the file was not uploaded
	 */
	public function upload_file_s3($path, $name, $key = null, $image = true){
	
		$this->load->helper('string');
		if(!isset($path)){
			log_message('error','Image upload path not defined');
			return false;
		}
		if(!isset($name)){
			log_message('error','File name not defined');
			return false;
		}

		if(!$_FILES[$name]['name'] OR $_FILES[$name]['name']==''){
			log_message('error','$_FILES array undefined or not set while uploading file '.current_url());
			return false;
		}

		$target_dir =	$path;
		
		if(!is_dir($path)){
			mkdir($path, 777, true);
		}

		$time			=	md5(base64_encode(time().random_string('alnum',5)));

		if($key === null){
			$origin_path		=	$_FILES[$name]["tmp_name"];
			$target_file		=	$target_dir.$time."_".rand(0,10).".".strtolower(get_file_ext($_FILES[$name]["name"]));
		}else{
			$origin_path		=	$_FILES[$name]["tmp_name"][$key];
			$target_file		=	$target_dir.$time."_".rand(0,10).".".strtolower(get_file_ext($_FILES[$name]["name"][$key]));
		}
		
		if(!move_uploaded_file($origin_path, $target_file)){
			log_message('error','Directory does not exist, please create directory or defined valid path in first argument');
			return false;
		}
		
		if($image){
			$resized_file	=	$this->image_resize($target_file,1000,1000);
			if(!$resized_file){
				$resized_file	=	$target_file;
			}
		}else{
			$resized_file	=	$target_file;
		}

		if($_FILES[$name]){
			if($key===null){
				$response	= s3_upload_file($target_file,$resized_file);
			}else{
				$response	= s3_upload_file($target_file,$resized_file);
			}
			
			if(!$response['status']){
				log_message('error','File Upload failed to s3 with error '.$response['value']);
				return false;
			}
			
			if(!file_exists($resized_file)){
				log_message('error','File not found on local location');
				return false;
			}
		
			gc_collect_cycles();

			unlink($resized_file);

			return $response['value'];
		
		}else{
			log_message('error','File not found, please check if form has attribute enctype=multipart/form-data');
			return false;
		}

	}

	
	/**
	 * uploads a file to server
	 * @param string path where the file is to be uploaded
	 * @param string name of the file
	 * @param string key if there are more than 1 files with the same name to be uploaded, i.e. if file is an array
	 * @return string path of the uploaded image
	 * @return false if the file was not uploaded
	 */
	public function upload_file($path, $name, $key = null){

		$this->load->helper('string');	
		/*
		Upload file helper function	
		*/
		if(!isset($path)){
			/*
			Need to set path like /directory/image/	
			*/
			log_message('error','Image upload path not defined');
			return false;
		}
		if(!isset($name)){
			/*
			name of input type file is missing
			*/
			
			log_message('error','File name not defined');
			return false;
		}

		if(!$_FILES[$name]['name'] OR $_FILES[$name]['name']==''){
			log_message('error','$_FILES array undefined or not set while uploading file '.current_url());
			return false;
		}

		$target_dir =	$path;

		$time			=	md5(base64_encode(time().random_string('alnum',5)));

		if($key===null){
			$target_file	=	$target_dir.$time."_".rand(0,10).".".strtolower(get_file_ext($_FILES[$name]["name"]));
			// $target_file	=	$target_dir.$time."_".rand(0,10).".jpg";
		}else{
			$target_file	=	$target_dir.$time."_".rand(0,10).".".strtolower(get_file_ext($_FILES[$name]["name"][$key]));
			// $target_file	=	$target_dir.$time."_".rand(0,10).".jpg";
		}
		
		$uploadOk = 1;
		// $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
	
		if($_FILES[$name]){
			if($key===null){
				$origin_path 	=	$_FILES[$name]["tmp_name"];
			}else{
				$origin_path 	=	$_FILES[$name]["tmp_name"][$key];
			}
			
			if (move_uploaded_file($origin_path, $target_file)) {
				return $target_file;
			}else {
				log_message('error','Directory does not exist, please create directory or defined valid path in first argument');
				return false;
			}
		}else{
			log_message('error','File not found, please check if form has attribute enctype=multipart/form-data');
			return false;
		}
	}

	
	/**
	 * to display alert if available in the flashdata, place this function where you want to show alert messages,
	 * is used while redirecting with a message
	 * @param void uses the flashdata available
	 * @return void
	 */
	public function alert(){
		if($this->session->userdata('msg')){
			$type 	=	$this->session->userdata('type');
			if($type=='danger'){
				$this->output->set_header('Feedback: error');
			}
			?>
			<div id="fading_div" class="animated flash delay-3s alert alert-<?php echo $type;?>">
				<i class="fa fa-info-circle fa-lg"></i> <?php echo $this->session->userdata('msg');?>
				<span onclick="$('#fading_div').fadeOut('slow')" class="pull-right" style="cursor:pointer"><i class="fa fa-times"></i></span>
			</div>
			<?php
		}
	}
	
	
	/**
	 * to display alert with the parameters passed, uses bootstrap for styling
	 * @param string msg the message to be displayed
	 * @param string type can be info, danger, success, warning (default: info )
	 * @param string icon can be any fa-icon class without 'fa' (default: info-circle)
	 * @return void
	 */
	public function display_alert( $msg, $type=null, $icon=null){
		if(!$msg){
				return false;
				exit();
		}
		if($type==null){
			$type='info';
		}
		
		if($icon==null){
				$icon='info-circle';
		}
		?>
		<div class="alert alert-<?php echo $type;?>" align="center">
			<i class="fa fa-<?php echo $icon;?> fa-lg"></i> <?php echo $msg;?>
		</div>
		<?php
	}
	
	
	/**
	 * redirect to a 'url' with a alert with the parameters passed
	 * @param string msg the message to be displayed
	 * @param string type can be info, danger, success, warning (default: info )
	 * @param string url where the redirect should be after setting flashdata
	 * @return void
	 */
	public function redirect_msg( $msg, $type = 'info', $url){
		if(!$msg OR !$url){
			return false;	
		}

		$this->session->set_flashdata(array( 'msg'=>$msg, 'type'=>$type));
		redirect(base_url($url));
		exit();
	}
	
	
	/**
	 * private function to send aws ses mail, called from send_fromatted mail
	 * @param array data containing required data to send mail
	 * @param array attachments array, paths of files
	 * @return boolean true or false
	 */
	protected function send_ses_mail($data, $attachment=null){
		$mail = send_ses($data, $attachment);
		if($mail=='sent') {
			$log_message 	=	'INFO: Message sent to :'.$data['to'].' via AWS | Subject : "'.$data['subject'].'"';
			log_message('error', $log_message);
			return true;
		} else {
			$log_message 	=	'Message not sent to :'.$data['to'].' via AWS | Subject : "'.$data['subject'].'" Email Debugger info # '.$mail;
			log_message('error', $log_message);
			return false;
			exit();
		}
	}

	
	/**
	 * public function to send mail, called from send_fromatted mail
	 * @param string msg the message to be displayed
	 * @param string type can be info, danger, success, warning (default: info )
	 * @param string url where the redirect should be after setting flashdata
	 * @return void
	 */
	public function send_formatted_mail( $data, $attachment=null){
		
		$this->load->library('email');
		$this->email->clear();
		
		if(empty($data['name'])
			OR empty($data['subject'])
			OR empty($data['to'])
			OR empty($data['message']) ){
			log_message('error','Empty paraters for email');
			return false;
		}

		if($this->_mail_switch == true){
			return $this->send_ses_mail($data,$attachment);
		}

		$config['useragent']        =   'Website';
		$config['mailpath']         =   '/usr/sbin/sendmail'; // or "/usr/sbin/sendmail"
		$config['protocol']         =   'smtp';
		$config['smtp_host']        =   'localhost';
		$config['smtp_port']        =   '25';
		
		$config['mailtype']			=	'html';
		$config['charset']			=	'utf-8';
		$config['dsn']				=	true;
		$config['wordwrap']			=	true;
		$config['newline']			=	"\r\n";
		$config['crlf'] 			= 	"\r\n";
			
		
		ini_set('sendmail_from', $data['from']);



		$this->email->initialize($config);
		$this->email->to($data['to']);
		$this->email->from($data['from'], $data['name']);
		$this->email->subject($data['subject']);
		$this->email->message($data['message']);
		$this->email->reply_to($this->_settings['verification_mail'],'Website');
		

		if($attachment!=null){
			if(!is_array($attachment)){
				$this->email->attach(FCPATH.$attachment);
			}else{
				foreach($attachment as $key => $attach){
					$this->email->attach(FCPATH.$attach);
				}
			}
		}

		if($mail = $this->email->send()){
			if($mail){
				log_message('error','Message sent to :'.$data['to'].' | Subject : "'.$data['subject'].'"');
				return true;
			}else{
				log_message('error','message not sent to :'.$data['to'].' Email Debugger info # '.implode(' | ',$this->email->print_debugger()));
				return false;
				exit();
			}
			
		}
		// $mail = $this->email->send();

	}
	
	/**
	 * public function to resize image
	 * @param string upload_path the path of original file
	 * @param integer width (default = 1000)
	 * @param integer height (default = null) to keep aspect ratio
	 * @param string target default = '', to keep upload path & target path same
	 * @return string path of file resized
	 * @return boolean false, if file was not resized
	 */
	public function image_resize( $upload_path, $width=1000, $height=null, $target=''){

		if(!file_exists($upload_path)){
			log_message('error','Failed to resize image as image not exist # '.$upload_path);
			return false;
		}

		if($target==''){
			$target	=	$upload_path;
		}

		// Just need path to grab image and resize followed by overwriting
		$this->load->library('image_lib');
		ini_set('memory_limit', '-1');	// this will prevent memory overload by PHP, 
		
		$config['source_image']		=	$upload_path;
		$config['new_image']		=	$target;
	
		$config['image_library'] 	=	'gd2';
		
		$img_prop 					=	getimagesize($upload_path);

		if($img_prop[0]>$width){

			$config['width']=$width;
		
		}else{
			return false;
		}
		
		$this->image_lib->initialize($config); 

		$confirm = $this->image_lib->resize();

		if($confirm){
			return $config['new_image'];
		}else{
			log_message('error','Error in image resizing');
			return false;
		}
			
		/*
		Reference
		http://stackoverflow.com/questions/11193346/image-resizing-codeigniter
		*/
	}
	
	/**
	 * to update config table
	 * @param string $key_name
	 * @param string $value
	 */
	public function update_config( $var ,$val){

		log_message('error', 'Variable loaded'.$var);
		log_message('error', 'Value loaded'.$val);

		$data = array(
				'value' => $val
		);

		$query=$this->db->where('var', $var);
		$query=$this->db->update( $this->config_table, $data);
		
		if($query){
		log_message('error', 'query success');
			return true;
		}else{
			log_message('error', 'query failed');
			return false;
		}
	}

	/**
	 * function to delete entry from table
	 * @param string table table name
	 * @param string where column name for column to be deleted
	 * @param string  the value of where column
	 * @return boolean true/false
	 */
	public function del($table, $where_column, $where_value){
	
		if(($table==null) OR ($where_column==null) OR ($where_value==null)){
			log_message('error','Missing table, where_column, or where_value');
			return false;
		}
		
		$query		=	$this->db->where($where_column, $where_value);
		$query		=	$this->db->delete($table);

		if($query){
			return true;
		}else{
			log_message('error','Unable to delete from '.$table." given attribute ".$col." with value ".$val);
			return false;
		}
	}

	/**
	 * function to delete entry from table
	 * @param string table table name
	 * @param array data to be updated in the table
	 * @param string where column name for column to be deleted
	 * @param string  the value of where column
	 * @return boolean true/false
	 */
	public function update($table, $data, $col, $val){

		if(($data==null) OR ($col==null) OR ($val==null) OR ($table==null)){
			log_message('error','Missing table, col, or value, called from function: '.debug_backtrace()[1]['function']);
			return false;
			exit();
		}
		
		$query	=	$this->db->where($col, $val);
		$query	=	$this->db->update($table, $data);
		
		if($query){
			return true;
		}else{
			return false;
		}
	}


	/**
	 * function to delete entry from table
	 * @param string table table name
	 * @param string where column name for column to be deleted
	 * @param string  the value of where column
	 * @param integer limit
	 * @param array order by array ['column1'=>'ASC/DESC'];
	 * @param array columns to fetch array ['column1', 'column2']
	 * @return object of result
	 * @return boolean true/false
	 */
	public function get_by_id( $table, $col, $value, $limit=null, $order=null, $col_get=null){

		if($col_get != null){
			$col_get 	= implode(',', $col_get);
			$query 		= $this->db->select($col_get);
		}

		if($order!=null){
			foreach($order as $key => $value1){
				$query	= $this->db->order_by($key, $value1);	
			}
		}	
		$query 			=	$this->db->get_where($table, array($col => $value));
		
		if($query->num_rows()>0){
			return $query->result();
		}else{
			return false;
		}
	}

	
	/**
	 * function to delete entry from table
	 * @param string table table name
	 * @param array where associative array
	 * @param mixed array or integer limit
	 * @param array order by array ['column1'=>'ASC/DESC'];
	 * @param array columns to fetch array ['column1', 'column2']
	 * @return object of result
	 * @return boolean true/false
	 */
	public function get_multi_where($table, $where, $limit=null, $count=false, $order=null, $group=null){
		
		if($order!=null){
			foreach ($order as $key => $value){
				$this->db->order_by($key, $value);	
			}
		}

		if(!empty($group)){
			$this->db->group_by($group);
		}

		$query =	$this->db->get_where($table,$where,$limit);
		if($query->num_rows()>0){
			if($count){
				return $query->num_rows();
			}else{
				return $query->result();
			}
		}
		return false;
	}
	
	
	/**
	 * function to get a single row entry from table
	 * @param string table table name
	 * @param array where associative array
	 * @param array order by array ['column1'=>'ASC/DESC'];
	 * @param integer array or integer limit
	 * @return object of result
	 * @return boolean false, if no result was found
	 */
	public function get_row_array($table, $where, $order=null, $limit=1)
	{
		/*
			$this->helper_model->get_row('user', array('user_id' => 1));
			will return the email in string format
			jazeabby@gmail.com
		*/	
		if(empty($table)){
			return false;
		}

		if(!empty($order) && is_array($order)){
			foreach ($order as $key => $value1){
			$query=$this->db->order_by($key, $value1);	
			}
		}	

		$query =	$this->db->get_where($table, $where,$limit);

		if(empty($query->num_rows()) || $query->num_rows() == 0){
			return false;
		}
		else if($query->num_rows() > 0){
			$row = $query->row();
			if(isset($row)){
				return $row;	
			}
		}	
	}
	
	
	/**
	 * function to get a single value from a row in a table
	 * @param string table table name
	 * @param string where_column name
	 * @param mixed value of the where_column
	 * @param string value of the column to get
	 * @return mixed data which was to be fetched of result row
	 * @return boolean false if the row wasn't found
	 */
	public function get_row($table, $col, $value, $col_get=null)
	{
		/*
			$this->helper_model->get_row('user', 'user_id', 25, 'email');
			will return the email in string format
			jazeabby@gmail.com
		*/	
		if(!$table OR !$col OR !$value){
			return false;	
		}
		$query =	$this->db->get_where($table, array($col => $value),1);
		if($query->num_rows()>0){
			$data = $query->row_array();
			if($col_get){
				return $data[$col_get];
			}else{
				return $data;
			}
		}else{
			return false;
		}
	}
	
	public function get_table($table, $order=null, $group=null, $limit=null, $col_get=null){
		/*
			$this->helper_model->get_table('user',array('id'=>'asc'));
			will produce
			SELECT  * FROM `USER` ORDER BY `ID` ASC;
		*/	
		if($col_get!=null){
			$col_get=implode(',', $col_get);
			$query	=	$this->db->select($col_get);
		}	

		if(!empty($order)){
			foreach ($order as $key => $value){
				$query	=	$this->db->order_by($key, $value);	
			}
		}
		
		if(!empty($group)){
			$this->db->group_by($group);
		}
		
		if(!empty($limit)){
			$this->db->limit($limit);
		}
		
		$query = $this->db->get($table);
		if($query->num_rows()>0){
			return $query->result();
		}else{
			return false;
		}	
	}
	
	public function count_data($table,$condition=null,$value=null){
		if($condition!=null && $value!=null){
			$query = $this->db->get_where($table, array($condition => $value));
			if($query){
				return $query->num_rows();
			}else{
				return false;
			}	
		}else{
			$query = $this->db->get($table);
			if($query){
				echo  $query->num_rows();
			}
		}

	}
	
	public function count_multiple($table,$condition=null){
		if($condition!=null){
			$query = $this->db->get_where($table,$condition);
			if($query){
				return $query->num_rows();
			}else{
				return false;
			}	
		}else{
			$query = $this->db->get($table);
			if($query){
				return $query->num_rows();
			}
		}	
	
	}
	
	public function search($table,$keyword,$attribute,$condition=null){
		$this->db->from($table);
		foreach($attribute as $key=>$values){
			$this->db->or_like($values,$keyword);
		}
		if($condition!=null){
			$this->db->where($condition);
		}
		$query	=	$this->db->get();
		if($query){
			return $query->result();
		}
	}
	
	/**
	 * To check whether the input string is of json or not
	 * @param string string to check
	 */
	public function isJson($string=null) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
	}
	

	/**
	 * Generate PDF
	 * @param string html
	 * @param string filename
	 * @param string filepath
	 * 
	 */
	public function generatePDF($html, $filename, $filepath){
		$this->load->library('pdfgenerator');

		$filepath	=	FCPATH.$filepath;
		//generate($html, $filename='', $filepath=false, $stream=true, $paper = 'A4', $orientation = "portrait")
		$this->pdfgenerator->generate($html, $filename, $filepath, false, $paper = 'A4', $orientation = "portrait");
	}

}
