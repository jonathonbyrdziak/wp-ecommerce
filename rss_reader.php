<?php
?>
<div class="wrap">
<?php
$target = "http://apps.instinct.co.nz/wordpress_development/index.php?rss=true&action=product_list&type=rss&product_id=1";
//$target = "http://apps.instinct.co.nz/wordpress_development/index.php?rss=true&action=product_list&type=rss";

if(function_exists("curl_init"))
  {    
  //send request using CURL
  $useragent = 'WP e-Commerce plugin';
  ob_start();
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
  curl_setopt($ch, CURLOPT_URL,$target);
  curl_exec($ch);
  $returned_value = ob_get_contents();
  ob_end_clean();
  if((curl_errno($ch) > 0) && (ini_get('allow_url_fopen') == 1))
    {
    // fallback in case of CURL failure, but this will probably fail too
    $returned_value =  file_get_contents($target);
    }
  curl_close($ch);
  }
  else if(ini_get('allow_url_fopen') == 1)
    {
    // If there is no CURL, but we can use file_get_contents, do so
    $returned_value =  file_get_contents($target);
    }
$rss_data= Array();
$xml_parse = xml_parser_create();
xml_parser_set_option($xml_parse, XML_OPTION_CASE_FOLDING, 0);
xml_parser_set_option($xml_parse, XML_OPTION_SKIP_WHITE, 1);
xml_parse_into_struct($xml_parse,$returned_value,$values, $index);
xml_parser_free($xml_parse);
foreach($index as $key=>$val)
  {
  if($key == 'item')
    {
    $row=0;
    for($i=0; $i < count($val); $i+=2)
      {
      $row++;
      $offset = $val[$i] + 1;
      $len = $val[$i + 1] - $offset;      
      $rss_data = array_slice($values, $offset, $len);       
      foreach($rss_data as $rss_tag)
        {
        switch($rss_tag['tag'])
          {
          case 'title':
          $output_data[$row]['title'] = $rss_tag['value'];
          break;      
          
          case 'link':
          $output_data[$row]['link'] = $rss_tag['value'];
          break;
          
          case 'description':
          $output_data[$row]['description'] = $rss_tag['value'];
          break;
          
          case 'enclosure':
          $output_data[$row]['image'] = $rss_tag['attributes']['url'];
          break;
                
          default:
          break;
          }
        }
      }
    }
  }
   

foreach($output_data as $rss_item)
  { 
  echo "<pre>".print_r($rss_item,true)."</pre>";
  }
?>
</div>