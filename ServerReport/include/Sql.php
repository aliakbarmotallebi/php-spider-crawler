<?php
class Sql
{
	public $query;
	var	$vars = array();
	public function salam(){

		echo 'hi';

	}
	public function __construct()
	{
		$db['host']	= 'localhost';  // location
		$db['database']	= 'httpniya_SpiderCrawler';	// DataBase Name
		$db['username']	= 'httpniya_spider';	// Mysql User Name	: root
		$db['password']	= 'prTaQsC9~e0#';	// Mysql Password

		@$this -> db = new mysqli ($db['host'],$db['username'],$db['password'],$db['database']);
			if (mysqli_connect_errno())
				exit ('No Connect To DataBases');
			else
				$this -> Query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
	}
    public function returnid(){
		return($this->db->insert_id);
	}

		function inner($query){
		$return = array();
		if($query){
				$result = $this -> Query($query);

				$num_result = $result->num_rows;
				if($num_result){
					for ($i=0 ; $i < $num_result ; $i++){
						$return[] = $result->fetch_assoc();
					}
				}
		}
		return $return;

	}

	function Insert($Table,$Columns)
	{

			$CName 	= '';
			$CValue = '';
			foreach($Columns as $Key => $Value)
			{
				$CName 	.= "`$Key`,";
				if($Value == 'null')
					$CValue .= "$Value,";
				else
					$CValue .= "'$Value',";
			}
			$Query = "INSERT INTO `$Table` ($CName) VALUES ($CValue);";
			$Query = str_replace(',)',')',$Query);
			$Query = str_replace('`,)','`)',$Query);
			$Query = str_replace("',)","')",$Query);
			//echo $Query;
			if($this -> Query($Query))
				return true;
			else
				return false;

	}
	public function DibugQuery($param)
	{
		switch ($param) {
			case 'query':
			echo $this->query;
				break;
		}
	}

	function Query($query)
	{
		$this->query=$query;

		$Result =$this -> db -> query($query);

		if(isset($Result->error)){
			echo $Result->error ;
		}

		return $Result ;
	}

	function Selects($Table,$where = '',$orderby = '',$DESC = 'ASC',$Limit = '',$Column = '*')
	{
		$return = array();
			if($orderby){
				$orderby = "order by `$orderby` $DESC";

			}
			if($where){
				$query = "SELECT $Column FROM `$Table` WHERE $where $orderby $Limit;";
				

			}else{
				$query = "SELECT $Column FROM `$Table` $orderby $Limit; ";

			}
				//$this->SetLog($query  , 'query');
				 //echo $query.'<br>';

					$result = $this -> Query($query);

						@$num_result = $result->num_rows;
						if(@$num_result == 1)
						{
							$return[] = $result->fetch_assoc();

						}else{
							for ($i=0 ; $i < $num_result ; $i++)
								{
									$return[] = $result->fetch_assoc();

								}
						}
					//	var_dump($result);die('hi');

			return $return;

	}

	function Select($Table,$where = '',$orderby = '',$DESC = 'ASC',$Limit = '',$Column = '*')
	{
		$return = array();
			if($orderby){
				$orderby = "order by `$orderby` $DESC";
			}
			if($where){
				$query = "SELECT $Column FROM `$Table` WHERE $where $orderby $Limit;";
			}else{
				$query = "SELECT $Column FROM `$Table` $orderby $Limit; ";
			}
				// echo $query.'<br>';

			$result = $this -> Query($query);
			if(! $result)
				return false ;

			$return=$result->fetch_assoc();
			return $return;

	}
	function Update($Table,$Columns,$where = '')
	{
			$CName 	= '';

			foreach($Columns as $Key => $Value)
			{
				if($Value == 'null')
					$CValue = "$Value,";
				else
					$CValue = "'$Value',";

				$CName 	.= "`$Key`	=	$CValue";

			}
			$Query = "UPDATE `$Table` SET $CName WHERE $where;";
			$Query = str_replace(', WHERE',' WHERE',$Query);

			$Query = str_replace(',)',')',$Query);
			$Query = str_replace('`,)','`)',$Query);
			$Query = str_replace("',)","')",$Query);


		//echo $Query;
		$result = $this -> Query($Query);
		//return $result->num_rows;
		return $result;
	}

	function Delete($Table,$where = '')
	{
		$query = "DELETE FROM `$Table` WHERE $where;";
		$result = $this -> Query($query);
		//return $result->num_rows;
		return $result;
	}

	function Find($Config)
	{
		return var_dump($Config);
	}

	function Num_rows($Table,$where = '')
	{
			if($where){
				$query = "SELECT count(*) FROM `$Table` WHERE $where;";
			}else{
				$query = "SELECT count(*) FROM `$Table`;";
			}

		$result = $this -> Query($query);
		$return = $result->fetch_assoc();
		return $return['count(*)'];
	}
	function GetSP($SP,$Column)
	{
		$return = array();

				$query = "CALL $SP($Column);";
				 // echo $query.'<br>';

					$result = $this -> Query($query);
						@$num_result = $result->num_rows;
						if(@$num_result == 1)
						{
							$return = $result->fetch_assoc();
						}else{
							for ($i=0 ; $i < $num_result ; $i++)
								{
									$return[] = $result->fetch_assoc();
								}
						}

			return $return;
	}
    function SetLog($log,$type = null)
    {
        $LogInsert['id']          =   'null';
        $LogInsert['log']    =   $log;
        $LogInsert['type']      =   $type;
        $Insert  = $this->Insert('log',$LogInsert);
    }
        function SendSMS($Number,$Text){
        include_once 'SMS_Sender.php';
        $result = Send_SMS('najva', '771414', '10009123094804', $Number, $Text, 0, false);

        // if ( $result === '0')
        //     {
        //         // echo 'true';
        //     }
        //     else if ($result !== '')
        //     {
        //     	$this->SetLog('16',$Number);
        //         // echo "Error No: $result";
        //     }
    }
    public function loginToDrupal() {
        $ch = curl_init('http://niyazmand.com/rss/rss/user/login/');
        $post_data = array(
            'username' => 'hamid',
            'password' => 'hamid',
        );
        $post = json_encode($post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Accept: application/json",
            "Content-type: application/json"
        ));
        $response = json_decode(curl_exec($ch));
        curl_close($ch);
        $session = @$response->session_name . '=' . $response->sessid;
		$token = @$response->token;
		return array($session,$token);
    }
    public function RequestToServer($url , $data , $session, $token,  $bg=false ,$json=false){

		$curl = curl_init($url);
		$curl_post_data = array(
			"data" => json_encode($data) ,
		);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POST, true);
			if($bg){
				curl_setopt($curl, CURLOPT_TIMEOUT, 1);
			}
			@curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
			if($json){
		       $dta = http_build_query($data);
			   curl_setopt($curl, CURLOPT_HTTPHEADER,array(
                "Accept: application/json",
                "Cookie: $session",
    			"X-CSRF-Token: $token"
                )); // Accept JSON response
			   @curl_setopt($curl, CURLOPT_POSTFIELDS, $dta);

			}

			$curl_response = curl_exec($curl);
			curl_close($curl);
			var_dump($curl_response);
			$result=json_decode($curl_response);
			return $result ;
	}


	public function RequestToServerServer($url , $data){
		$curl = curl_init($url);
		$curl_post_data = array(
			"data" => json_encode($data) ,
		);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POST, true);
			@curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
			$curl_response = curl_exec($curl);
			curl_close($curl);
			$result=json_decode($curl_response);
			return $result;
	}

}
?>
