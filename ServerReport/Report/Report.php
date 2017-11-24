<?php

header('Content-Type: text/html; charset=utf-8');
require_once('../include/Core.php');
error_reporting(-1);
ini_set('display_errors', 1);
set_time_limit(0); // Just in case it takes more than your default

class Report extends Core {

	
	/*
	 * AddUrlRSS
    */
	public function AddUrlRSS(){
		if(!isset($this->data->Name , $this->data->Url) ){
            $this->Response(False,'Data Not Resive');
        }
        $data['name'] = $this->data->Name;
        $data['url'] = $this->data->Url;
        $Result =$this->Insert('sites',$data);
        if($Result){
            $class['title'] = $this->data->Title;
            $class['body'] = $this->data->Body;
            $class['date'] = $this->data->Date;
            $class['keywords'] = $this->data->Keywords;
            $class['image'] = $this->data->Image;
            $class['sId'] =  $this->returnid();
            $Result =$this->Insert('site_class',$class);
            if(!$Result){
                $this->Response(False,'Data Not Record');
            }
            $this->Response(true,'Done Successfully');
        }else{
            $this->Response(False,'Site Not Record');
        }
      
	}
	
	
    /*
	 * ListUrlRSS
    */
	public function ListUrlRSS(){
		
		if(!isset($this->data->Start) ){
            $this->Response(False,'Data Not Resive');
        }
        $Result= $this->Selects('sites','','id','DESC');
        if($Result){
            $this->Response(true,$Result);
        }else{
            $this->Response(false,'Do Not');
        }
      
	}
	
	 /*
	 * RemoveUrlRSS
    */
	public function RemoveUrlRSS(){
		if(!isset($this->data->ID) ){
            $this->Response(False,'Data Not Resive');
        }
        $Result= $this->Delete('sites',"`id`={$this->data->ID}");
        if($Result){
            $this->Delete('site_class',"`sId`={$this->data->ID}");
            $this->Response(true,'Do');
        }else{
            $this->Response(false,'Do Not');
        }
      
	}
	
	/*
	 * ReportParsers
    */
	public function ReportParsers(){
		
		if(!isset($this->data->Start) ){
            $this->Response(False,'Data Not Resive');
        }
        $Result= $this->Selects('sites');
        if($Result){
            $count='';
           foreach($Result as $key){

                $status5 = $this->Num_rows('content',"`Sid`={$key['id']}");
                $status4 = $this->Num_rows('htmls',"`status`=4 and `sId`={$key['id']}");
                $notparse = $this->Num_rows('htmls',"`status`=0 and `sId`={$key['id']}");
                $count['Sites'][]= $key['name'];
                $count['Success'][] = (int)$status5;
                $count['Fail'][] = (int)$status4;
                $count['notparse'][] = (int)$notparse;
                
           } 
            $this->Response(true,$count);
        }else{
            $this->Response(false,'DOnot');
        }
	}
	
		/*
	 * ReportTimer
    */
	public function ReportTimer(){
		
		if(!isset($this->data->Start) ){
            $this->Response(False,'Data Not Resive');
        }
        $Result= $this->Selects('sites');
        if($Result){
            $count='';
            $joon='';
           foreach($Result as $key){

                $status5 = $this->Select('timer',"`Sid`={$key['id']}");

                $count['Sites'][]= $key['name'];
                $count['timer'][] = $joon += $status5['time'];

                
           } 
            $this->Response(true,$count);
        }else{
            $this->Response(false,'DOnot');
        }
	}
	
	
			/*
	 * ReportLogs
    */
	public function ReportLogs(){
		
		if(!isset($this->data->Start) ){
            $this->Response(False,'Data Not Resive');
        }
        $Result= $this->Selects('logs','','id','DESC');
        if($Result){
           $this->Response(true,$Result);
            
        }else{
            $this->Response(false,'DOnot');
        }
	}
	
	
	/*
	 * RemoveHTML
	   Add this method corn any 1 min 
    */
	public function RemoveHTML(){
		
		if(!isset($this->data->Start) ){
            $this->Response(False,'Data Not Resive');
        }
        $Result= $this->Delete('htmls',"`status`=5");
        if($Result){
            $this->Response(true,'Do');
        }else{
            $this->Response(false,'Do Not');
        }
	}
	
	
	/*
	 * UrlRSS
    */
	public function UrlRSS(){
	    
		if(!isset($this->data->ID) ){
            $this->Response(False,'Data Not Resive1');
        }

        $Result = $this->Select('sites',"`id`={$this->data->ID}");
        if($Result){
            $view = '';
            $Result2 = $this->Select('site_class',"`sId`={$this->data->ID}");
            if($Result2){
                $view['url'] = $Result;
                $view['class'] = $Result2;
                $this->Response(true,$view);
            }else{
            $this->Response(False,'Site Not Record');
          }
        }else{
            $this->Response(False,'Site Not Record');
        }
      
	}
	
	
	/*
	 * StatusUrlRSS
    */
	public function StatusUrlRSS(){
	    
		if(!isset($this->data->Status,$this->data->ID) ){
            $this->Response(False,'Data Not Resive1');
        }

        $Data['status']= $this->data->Status;
        $Result = $this->Update('sites',$Data,"`id`='" . $this->data->ID . "'");
        if($Result){
            $this->Response(true,'Do');
        }else{
            $this->Response(False,'Site Not Record');
        }
      
	}

	 
}
?>