<?php

header('Content-Type: text/html; charset=utf-8');
require_once('../include/Core.php');
error_reporting(-1);
ini_set('display_errors', 1);
set_time_limit(0); // Just in case it takes more than your default
class RssReader extends Core {
	public $rss;
	public $data;
	
	//Url WebService Drupal
	public $SendContents = 'http://niyazmand.com/rss/rss/node';
	public $SendImge ="http://niyazmand.com/rss/rss/file";
	
	public function __construct()
    {
        parent::__construct();
		$this->rss = new DOMDocument('1.0','UTF-8');
    }
	

	
	/*
	 * List_Urls_RSS
    */
	public function ListUrlsRSS(){
		
		if(!isset($this->data->Start) ){
            $this->Response(False,'Data Not Resive');
        }
        $Result= $this->Selects('sites','`status`= 1 ');
			if ($Result) {
				$this->Response(true,$Result);
			}else{
			    $this->Response(false,'Do Not');			
			}
	}
	
		/*
	 * GetContentsBot
    */
	public function GetContentsBot(){
		
		if(!isset($this->data->Sid , $this->data->Limit) ){
            $this->Response(False,'Data Not Resive');
        }
        $Result= $this->Selects('content',"`Sid`={$this->data->Sid}",'id','DESC',"LIMIT {$this->data->Limit}");
			if ($Result) {
				$this->Response(true,$Result);
			}else{
			    $this->Response(false,'Do Not');			
			}
	}
	
	
	  /*
      **  GetContentsAndroid
      */
      public function GetContentsAndroid()
      {
          
        if(!isset($this->data->Start))
        {
          $this->Response(false,'Data Not Resive'); 
        }
        $content = $this->Selects('content',' `status`= 0','id','','LIMIT 10');
                   
           	if ($content) {
				$this->Response(true,$content);
			}else{
			    $this->Response(false,'Do Not');			
			}
      }
	
    /*
      **  GetContents
      */
      public function GetContents()
      {

        if(!isset( $this->data->Cid, $this->data->Sid))
        {
          $this->Response(false,'Data Not Resive'); 
        }
        $content = $this->Selects('content','`status` = 0 and `id` > '.$this->data->Cid.' and `Sid`='.$this->data->Sid);
        
        foreach ($content as $key)
          {
              
            if(!empty($key['image'])){
                
                 $img = array("file"=>array("file"=>$key['image'],"filename"=>$key['nameImage'],"filepath"=>"public://".$key['nameImage']));
                 $loginToDrupal = $this->loginToDrupal();
                 $send = $this->RequestToServer($this->SendImge,$img,$loginToDrupal[0],$loginToDrupal[1],true,true);
                 $fid = $send->fid;

             }else{
                 //defualt fid image 
                 $fid = '1';
             }
            
             $site = $this->Select('sites',' `id` = "'.$key['Sid']. '"');
             $link = $this->Select('links',' `id` = "'.$key['lId']. '"');
             
             $newData = array(
              "title"=> $key['title'],
              "type"=> "rss",
              "language"=> "und",
              'body' => 
                array (
                'und' => 
                array (
                  0 => 
                  array (
                  'value' => $key['body'],
                  'summary' => '',
                  'format' => 'filtered_html',
                  'safe_value' =>'',
                  'safe_summary' => $key['body'],
                  ),
                ),
                ),
                   "field_data"=>
                    array (
                    'und' => array(
                          array (
                          'value' => $key['date'],
                        ),
                      ),
                    ),
                   "field_link_filed"=>
                      array (
                        'und' => array(
                          array (
                          'value' => $site['url'],
                           ),
                         ),
                       ),
                   "field_cid"=>
                      array (
                        'und' => array(
                          array (
                          'value' => $key['id'],
                           ),
                         ),
                       ),
                  "field_url"=>
                     array (
                    'und' => $site['name'],
                   ),

                 "field_field_image_rss"=>
                  array (
                    'und' => array(
                      array (
                      'fid' => $fid,
                       ),
                     ),
                   ),
                    "field_key_words_rss"=>
                      array (
                        'und' => $key['keywords'],
                      ),
                 "field_idsite"=>
                   array (
                        'und' => array(
                          array (
                          'value' => $site['id'],
                           ),
                         ),
                      ),
                  "field_link_content"=>
                   array (
                        'und' => array(
                          array (
                          'value' => $link['url'],
                           ),
                         ),
                      ),
            );
            $loginToDrupal = $this->loginToDrupal();
            $send = $this->RequestToServer($this->SendContents,$newData,$loginToDrupal[0],$loginToDrupal[1],true,true);

              if(isset($send->uri)){
                $Data['status']= 2 ;
                $this->Update('content',$Data,"`id`='" .  $key['id'] . "'");
                $this->Log($key['id'],"Record Content In Drupal By Content");
              }else{
                $Data['status']= 1 ;
                $this->Update('content',$Data,"`id`='" .  $key['id'] . "'");
                $this->SetLog('GetContents FeedReader Siteid'.$key['Sid'],71);
                $this->Log($key['id'],"Not Record Content In Drupal By Content ");
              }
    
          }
    
      }


	/*
	 * Get_Parsing_Feed
    */
	public function ParsingFeed(){
		if(!isset($this->data->Sites)){
            $this->Response(false,'Data Not Resive');
        }
        include_once('Feed.php');
        
        foreach ($this->data->Sites as $site) {
            
            $rss = Feed::loadRss($site->url);
            
            if(!$rss){
                $this->Log($site->id,"Problem Rss Url By Sites");
            }


             foreach ($rss->item as $item) {
                $data['url'] =  $this->safe_urlencode($item->link);
    			$data['sId'] = $site->id;
    			$data['hash']= md5($this->safe_urlencode($item->link));
    			
                $Result =$this->Insert('links',$data);

            }
             
        }
	}

	

	/*
	 * Get_Value
    */
	private function GetValue($object){
		if($object->length == 0 ){
			return NULL;
		}else{
			return $object->item(0)->nodeValue;
	    }
    }
  
    private function safe_urlencode($txt){
      $result = preg_replace_callback("/[^-\._~:\/\?#\\[\\]@!\$&'\(\)\*\+,;=]+/",
        function ($match) {
          return rawurlencode($match[0]);
        }, $txt);
      return ($result);
    }
  
}
?>