<?php
require_once('../include/Core.php');

class GrabSourceComponent extends Core
{
    public $data;

  /*
  * Get Task Service
  */
  public function GetTask()
  {
    if (!isset($this->data->Urls) or !$this->data->Urls){
        $this->Response(false, ' Task Not Recive');
      }
          
        $this->SetLog('GetTask',511);
        foreach($this->data->Urls as $Url){
        $content = $this->GetContent($Url->Url);

        if($content)
        {
           //Data Insert DataInfo in Database
            $DataInfo['id']='null';
            #$DataInfo['primary_port']=$content['primary_port'];
            $DataInfo['redirect_url']=$content['redirect_url'];
            $DataInfo['speed_download']=$content['speed_download'];
            $DataInfo['size_download']=$content['size_download'];
            $DataInfo['connect_time']=$content['connect_time'];
            $DataInfo['namelookup_time']=$content['namelookup_time'];
            $DataInfo['total_time']=$content['total_time'];
            $DataInfo['http_code']=$content['http_code'];
            $DataInfo['linkId']=$Url->Id;
            $DataInfo['sId']=$Url->SId;
            $this->Insert('infocontent',$DataInfo);
            
            if($DataInfo['http_code'] == 200){
                $this->Log($Url->Id,'Done GrapSource ID Links');
            }
            
            if($content['errmsg']){
                $this->Log($Url->Id,$content['errmsg'].' ID Links');
            }

            //var_dump($content['content']);
            // Date Insert with mysql
              $Data['html']=base64_encode($content['content']);
              $Data['sId']=$Url->SId;
              $Data['lId']=$Url->Id;
              $Data['status']=0;
              $this->Insert('htmls',$Data);
              
              $temp[]=array('Id' =>$Url->Id , 'Status'=>1);
        }else{
          $temp[]=array('Id' =>$Url->Id , 'Status'=>0);
        }
    }
          $Reports['Report']=$temp;
          //var_dump($Reports);die();
          // Send Report To Controller . function in sql.php
         $this->RequestToServer($this->data->Controller , $Reports );
      }


  /*
    * Curl For Get Content
    */
    protected function GetContent($url){
    //  var_dump($url);die();
      $this->SetLog('GetContent',512);
      $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
    );

    $ch= curl_init($url);
//var_dump($ch);die();
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    $err     = curl_errno($ch);
    $errmsg  = curl_error($ch);
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = ($content);
  //primary_port
  //redirect_url
  //speed_download
  //size_download
  //connect_time
  //namelookup_time
  //total_time
  //http_code
    //linkId
    //SId

   //var_dump($header);die();
    return $header;

  }

}


?>
