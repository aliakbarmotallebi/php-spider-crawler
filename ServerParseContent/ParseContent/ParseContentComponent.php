<?php
require_once('../include/Core.php');


class ParseContentComponent extends Core
{
  public $ControllerPars = "http://www.niyazmand.com/spider/SpiderCrawler/ServerController/Controller/ReportParseContent";
          public $data;
          public $Finder;
          public $Patern;
          public $Temp;
          public $Title ='';
          public $Date ='';
          public $Body ='';
          public $Keywords ='';
          public $Image ='';
          public $NameImage ='';
          public $itemid = '';

  
            /*
            * Get Task Service
            */
        public function GetTask(){

                if(!isset($this->data->Htmls) AND $this->data->Htmls){
                   $this->Response(false, ' Task Not Recive');
               }

               $this->SetLog('GetTask',711);
                $temp='';
                foreach($this->data->Htmls as $Item){
                    $temp[] =$this->ParseContent($Item);
                    $Reports['Report']=$temp;
                    $this->RequestToServer($this->ControllerPars,$Reports);

                }

        }
        
      



           /*
            * Parse Html
            */
           protected function ParseContent($Item){
            
            $starttime = microtime(true);
            $this->itemid = $Item->Id;

            $dom = new domDocument('1.0', 'utf-8');
            libxml_use_internal_errors(true);
            $dom->preserveWhiteSpace = false;

            $this->SetLog('ParseContent',712);
                // a new dom object
            $dom->validateOnParse = false;
            $dom->recover = true;
            $dom->strictErrorChecking = false;
            $uft8Decode = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
            $d = @$dom->loadHTML($uft8Decode.base64_decode($Item->Html));
                // discard white space
            $this->Finder = new DomXPath($dom);
            $this->GenerateQuery($Item->Class);
            $this->FindTitle();
            $this->FindDate();
            $this->FindBody();
            $this->FindKeywords();
            $this->FindImage();

            if(!empty($this->Title))
                $Data['title']=$this->Title;

            if(!empty($this->Date))
                $Data['date']=$this->Date;
                
            if($this->Body)
                $Data['body']=filter_var($this->Body, FILTER_SANITIZE_STRING); 

            if(!empty($this->Keywords))
                $Data['keywords']=$this->Keywords;
            
                
            $Data['image']=filter_var($this->Image, FILTER_SANITIZE_STRING);
            $Data['nameImage']=$this->NameImage;
            $Data['lId']=$Item->LId;
            $Data['Sid'] = $Item->Sid;
            
          
            $endtime = microtime(true);
            $this->gettime($endtime,$starttime,$Item->Sid);
            
            if(!empty($this->Title)){
                $Result = $this->Insert('content',$Data);
                if($Result){
                     $this->Log($this->itemid,"Parse Content By ID Htmls");
                   return array('Id' =>$Item->Id , 'Status'=>'5');
                }else{
                    $this->Log($this->itemid,"Not Parse Content By ID Htmls");
                   return array('Id' =>$Item->Id , 'Status'=>'4');
                   
                }
            }else{
                $this->Log($this->itemid,"Empty Title By ID Htmls");
                $this->Log($this->itemid,"Not Parse Content By ID Htmls");
                return array('Id' =>$Item->Id , 'Status'=>'4');
            }

                        
 
       }


            /*
            * Find Title
            */
            public function FindTitle(){
                $nodes = @$this->Finder->query($this->Title);
                if($nodes)
                    if(!$nodes->length==0){
                        $node =@$nodes->item(0)->nodeValue;
                    }else{
                        $this->Log($this->itemid,"Not Found Title By ID Htmls");
                    }
                    //return $this->Title;
                    return $this->Title = (isset($node))? $node : '';
            }

            /*
            * Find Date
            */
            public function FindDate(){
                $nodes = @$this->Finder->query($this->Date);
                if($nodes)
                   if(!$nodes->length==0){
                        $node=@$nodes->item(0)->nodeValue;
                    }
                    else{
                        $this->Log($this->itemid,"Not Found Date By ID Htmls");
                    }
                    return $this->Date = (isset($node))? $node :'' ;
            }


            /*
            * Find Body
            */
            public function FindBody(){
                $nodes = @$this->Finder->query($this->Body);
                if($nodes)
                    if(!$nodes->length==0){
                        $node=@$nodes->item(0)->nodeValue;
                    }else{
                        $this->Log($this->itemid,"Not Found Body By ID Htmls");
                    }
                     return $this->Body = (isset($node))? $node : '' ;
            }

            /*
            * Find Keywords
            */
            public function FindKeywords(){
                 
                $nodes = @$this->Finder->query($this->Keywords);
                
                if($nodes)
                   if(!$nodes->length==0){
                        $nodes = @$this->Finder->query($this->Keywords)->item(0);
                        $node=@$nodes->getElementsByTagName("a");
                        if(!$node->length==0){
                            $tag ='';
                            for($q =0;$q < $node->length;$q++){
                                 $tag .= $node->item($q)->nodeValue.' ,';
                            }
                        }
                    }
                    return $this->Keywords = (isset($tag))? $tag : '';
                
            }
            
            
            public function FindImage(){
                $nodes = @$this->Finder->query($this->Image);
                if($nodes)
                    if(!$nodes->length==0){
                         $nodes = @$this->Finder->query($this->Image)->item(0);
                         $im = @$nodes->getElementsByTagName("img");
                                                     
                        if(!$im->length==0){
                             $node = $im->item(0)->getAttribute('src');
                            if($node){
                              $base64 = base64_encode(file_get_contents($node));
                              $name = time().".".pathinfo($node, PATHINFO_EXTENSION);
                            }
                        }else{
                            $this->Log($this->itemid,"Not Found Image By ID Htmls");
                        }
                       
                    }else{
                        $this->Log($this->itemid,"Not Found Image By ID Htmls");
                    }
                $this->Image = (isset($base64))? $base64 : '' ;
                $this->NameImage = (isset($name))? $name : '' ;
                    
              
            }
            
           
            /*
            * Genarate Find Query
            */
            public function GenerateQuery($class){
                                  
                $this->SetLog('GenerateQuery',713);
                foreach ($class as $keys => $values) {
                    $query = "//";
                    if($values){
                        foreach (json_decode($values) as $key => $value) {
                            switch($key){
                                case "Tag":
                                $query .= $value;
                                break;
                                case "Attribute":
                                $query .= "[@$value=";
                                break;
                                case "Value":
                                $query .= "'".$value."']";
                                break;
                            }
                            if(json_last_error()){
                                $this->Log($this->itemid,"Class decode_json  By ID site_class and ID json_last_error ".json_last_error());
                            }

                        }
                    }
                        //var_dump($query);
                        switch ($keys)
                        {
                            case 'Title':
                            $this->Title = $query;
                            break;
                            case 'Date':
                            $this->Date = $query;
                            break;
                            case 'Keywords':
                            $this->Keywords = $query;
                            break;
                            case 'Body':
                            $this->Body = $query;
                            break;
                            case 'Image':
                            $this->Image = $query;
                            break;
                            default:
                            $this->Response(false);
                            break;
                        }
                    }
                }
                
        public function gettime($time1,$time2,$sid){
            
            $time=  $time1 - $time2 ;
            $Data['time']=$time;
            $Data['Sid'] = $sid;
            $Result = $this->Insert('timer',$Data);
            
        }
        
       
}



        ?>
