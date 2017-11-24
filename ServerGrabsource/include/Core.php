<?php ob_start(); ?>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('Sql.php');

/**
 * @description Main class of tadbir project
 * Class Core
 */
class Core extends Sql
{
    public $UserId;
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @description Parse requst sent from client and check is API is valid and exist
     * @return null
     */
    public function Request()
    {
        @$Method = $_GET['param'];
		//echo $Method;die();
		var_dump($_POST['data']);
        $alldata = $this->InitParam(@$_POST['data']);
        
        if (isset($Method)) {
            if (method_exists($this, $Method) && !method_exists('Core', $Method) && is_callable(array($this, $Method))) {
              /*  if(!@$this->data->Token){
                    $this->Response(false, "token error");
                }
				if( $this->data->Token=="-1"){
					if($Method=="Login" OR $Method=="Register"){
                        $this->$Method();
                    }else{
                       $this->Response(false, "Dont have Access to use service ");
                    }
				}

                $this->CheckUser($this->data->Token);
                */
				$this->$Method();
            } else {
                $this->Response(false, "This Api Not Found");
            }
        } else {
            $this->Response(false, 'This Api Not Found .. !');
        }

    }

    private function InitParam($json)
    {

        if (isset($json)) {

            $json = json_decode($json);

            if ($json == null) {
                $this->Response(false, 'Json Pattern not valid ..!');
            }
			/*
            //Cheak UserName and Guid
            if (isset($json->Auth)) {

                if (isset($json->Auth->UserName) and isset($json->Auth->Guid)) {
                    if (!empty($json->Auth->UserName) and !empty($json->Auth->Guid)) {
                        $json->Auth->UserName = $this->Validation($json->Auth->UserName);
                        $json->Auth->Guid = $this->Validation($json->Auth->Guid);
                    } else {
                        $this->Response(false, 'Auth Patern Not Valid ..!');
                    }
                } else {
                    $this->Response(false, 'Auth Patern Not Valid ..!');
                }

            }
            if(isset($json->UserName)){
                if(empty($json->UserName)){
                    $this->Response(false, 'UserName Patern Not Valid ..!');
                }
                $json->UserName = $this->Validation($json->UserName);
            }
			*/
            $this->data = $json;
        } else {
            $this->Response(false, 'Data Is Empty ..00!');
        }
    }

    protected function Validation($string)
    {

        return $string;
    }

    /**
     * @description create json and return to client
     * @param bool $Success
     * @param string $ResultMessage
     * @param null $ResultValue
     */
    public function Response($Success = false, $ResultMessage = "", $ResultValue = null)
    {
        if ($ResultValue == null) {
            $json = array('Success' => $Success, 'Message' => $ResultMessage);
        } else {
            $json = array('Success' => $Success, 'Message' => $ResultMessage, 'ResultValue' => $ResultValue);
        }
		header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');

        exit (json_encode($json));
    }

    /*
    * chekc user
    *
    */
    public function CheckUser(){

        $Result = $this->Select('users',"`token` = '" . $this->data->Token. "'");
        if (!$Result) {
            $this->Response(false, 'Token Not Valid ');
        }

        $this->UserId=$Result['id'];

    }


    /**
     * @description Generate Guid code
     * @return mixed
     */
    public function GUID()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = chr(123)// "{"
                . substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12)
                . chr(125);// "}"
            return $uuid;
        }
    }

    /**
     * @description create DateTime
     * @param null $Time
     * @return string
     */
    public function DateTime($Time = null)
    {
        if ($Time) {
            $DateTime = date('Y-m-d H:i:s', $Time);
        } else {
            $DateTime = date('Y-m-d H:i:s');
        }
        return $DateTime;
    }

    /*
    * Checkempty
    */
    public function Checkempty($fields){

        if(! is_array($fields)){
            $this->Response(false , 'Not Array');
        }
        foreach ($fields as $field) {
            if( empty($field)){

			$this->Response(false , "Fields not found");
            }
            $this->Validation($field);

        }
        return ;
    }

	/*
	*create token
	*/
	public function Token(){

	$token=base_convert(rand(10000000000000000000000000000,9999999999999999999999),20,36);

	 return $token;

	}
	
	function Log($id='', $text='')
    {
        $log = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        if(isset($log[1])){
            $LogInsert['class']     =   $log[1]["class"];
            $LogInsert['function']  =   $log[1]["function"];
            $LogInsert['line']      =   $log[1]["line"];
            $LogInsert['text']      =   filter_var($text, FILTER_SANITIZE_STRING);
            $LogInsert['rid']       =   $id;
            $LogInsert['time']       =   time();
            $Insert  = $this->Insert('logs',$LogInsert);
        }
				
    }

}
