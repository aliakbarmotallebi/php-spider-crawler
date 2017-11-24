<?php ob_start(); ?>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 999999999999999999999999);
set_time_limit(0);
error_reporting(E_ALL);


require_once('../include/Core.php');
class ControllerComponent extends Core {  
  public $limit=2;
  public $data;
  public $IpParseContent    = "http://niyazmand.com/spider/SpiderCrawler/ServerParseContent/ParseContent/GetTask";
  public $IpGrabSource      = "http://niyazmand.com/spider/SpiderCrawler/ServerGrabsource/Grabsource/GetTask";
  public $Controller        = "http://niyazmand.com/spider/SpiderCrawler/ServerController/Controller/ReportGrabSource";
  public $ControllerPars    = "http://niyazmand.com/spider/SpiderCrawler/ServerController/Controller/ReportParseContent";
  public $ParsingFeed       = "http://niyazmand.com/spider/SpiderCrawler/ServerRssReader/RssReader/ParsingFeed";
  public $GrabSource        = "http://niyazmand.com/spider/SpiderCrawler/ServerController/Controller/Call_GrabSource_GetTask";

  /*
  * Call_ParsingFeed_GetTask
  */
  public function Call_ParsingFeed_GetTask(){
    if(!isset($this->data->Task) OR !$this->data->Task){
      $this->Response(False,'Task Not Resive');
    }
	
    $Result=$this->Selects('sites' ,'`status`=1');

    if ($Result) {
      $content['Sites']= $Result;
     
      var_dump($this->RequestToServer($this->ParsingFeed ,$content));

     
      $this->data->Task = "start";
      $this->Call_GrabSource_GetTask();
    }
  }
  
  /*
  * Call_DetactionLink_GetTask
  */
  public function Call_GrabSource_GetTask(){
    if(!isset($this->data->Task) OR !$this->data->Task){
     $this->Response(False,'Task Not Resive');
    }
    
    $this->SetLog('Start Grab',412);
    $Result=$this->Selects('links' ,'`status`= 0');
    if ($Result) {
      foreach($Result as $Urls){
        $tmp[]=array('Id'=>$Urls['Id'],'Url' =>$Urls['url'] ,'SId'=>$Urls['sId']);
        $Data['status']=2;
        $this->Update('links',$Data,"`id`='" . $Urls['Id']. "'");
      }


      $content['Urls']=$tmp;
      $content['Controller']=$this->Controller;
     
      $this->RequestToServer($this->IpGrabSource ,$content);
        
    }else{
      $this->Response(False,'Data Not found GrabSource');

    }
  }

  /*
  * ReportGrabSource
  */
  public function ReportGrabSource(){
    if(!isset($this->data->Report) OR !$this->data->Report){
      $this->Response(False,'Task Not Resive');
    }
    $this->SetLog('ReportGrabSource',414);
    foreach ($this->data->Report as $Item) {
      if ($Item->Status==1) {
        $Data['status']=1;
      }else {
        $Data['status']=3;
      }
      $Result = $this->Update('links',$Data,"`id`='" . $Item->Id . "'");
    }
    $this->data->Task = "start";
    $this->Call_GrabSource_GetTask();

  }



  /*
  * Call_ParseContent_GetTask
  */

  public function Call_ParseContent_GetTask(){
    if (!isset($this->data->Task) OR !$this->data->Task) {
      $this->Response(False,'Task Not Resive');
    }
    
    $this->SetLog('Call_ParseContent_GetTask',415);
    $Result=$this->Selects('htmls','`status` = 0','id','','LIMIT 2');
    if ($Result) {
      foreach ($Result as $Item) {
        $tmp[]=array(
          'Id'=>$Item['id'],
          'Html'=>substr(stripslashes($Item['html']),0,650000),
          'LId'=>$Item['lId'],
          'Sid'=>$Item['sId'],
          'Class'=>$this->GetClass_ParseContent($Item['sId'],$Item['id'])
          );

      }
      //TODO : write SID
      //$content['Class']=$this->GetClass_ParseContent($Item['sId']);
      $content['Controller']=$this->Controller;
      $content['Htmls']=$tmp;
      //var_dump($content);die('fuck you');
      var_dump($this->RequestToServer($this->IpParseContent ,$content));
    }else {
      $this->Response(False,'Data Not Resive dd');
    }
  }

  /*
  * GetClass_ParseContent
  */
  public function GetClass_ParseContent($id,$idhtml){
    if(!$id){
      $this->Log($idhtml,"Not Get Class ParseContent By ID Htmls");
      $Data['status']=3;
      $Result = $this->Update('htmls',$Data,"`id`='" . $idhtml . "'");
      $this->Response($id);
      
    }
    $Result=$this->Select('site_class',"`sId`='". $id."'");
    if($Result){
      return array(
        'Title' =>$Result['title'] ,
        'Date' =>$Result['date'] ,
        'Body' =>$Result['body'] ,
        'Keywords' =>$Result['keywords'] ,
        'Image' =>$Result['image']
        );
    }

  }

  /*
  * ReportGrabSource
  */
  public function ReportParseContent(){
      //var_dump($this->data);

    if(!isset($this->data->Report) OR !$this->data->Report){
      $this->Response(False,'Task Not Resive');
    }
    $this->SetLog('ReportParseContent',416);
    foreach ($this->data->Report as $Item) {
      if (isset($Item->Status)) {
        $Data['status']= $Item->Status;
        $Result = $this->Update('htmls',$Data,"`id`='" . $Item->Id . "'");
      }
    }

  }







}



?>
