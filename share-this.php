<?php
/*
Modified to integrate with wp shopping cart
*/

@define('wpsc_akst_ADDTOCONTENT', true);
// set this to false if you do not want to automatically add the Share This link to your content


@define('wpsc_akst_ADDTOFOOTER', true);
// set this to false if you do not want to automatically add the Share This form to the page in your footer


@define('wpsc_akst_ADDTOFEED', true);
// set this to false if you do not want to automatically add the Share This link to items in your feed


@define('wpsc_akst_SHOWICON', true);
// set this to false if you do not want to show the Share This icon next to the Share This link


// Find more URLs here: 
// http://3spots.blogspot.com/2006/02/30-social-bookmarks-add-to-footer.html

$social_sites = array(
	'facebook' => array(
    'name' => 'Facebook'
    , 'url' => 'http://www.facebook.com/share.php?u={url}'
  )
	,	'delicious' => array(
    'name' => 'del.icio.us'
    , 'url' => 'http://del.icio.us/post?url={url}&title={title}'
  )
  , 'digg' => array(
    'name' => 'Digg'
    , 'url' => 'http://digg.com/submit?phase=2&url={url}&title={title}'
  )
  , 'furl' => array(
    'name' => 'Furl'
    , 'url' => 'http://furl.net/storeIt.jsp?u={url}&t={title}'
  )
  , 'netscape' => array(
    'name' => 'Netscape'
    , 'url' => ' http://www.netscape.com/submit/?U={url}&T={title}'
  )
  , 'yahoo_myweb' => array(
    'name' => 'Yahoo! My Web'
    , 'url' => 'http://myweb2.search.yahoo.com/myresults/bookmarklet?u={url}&t={title}'
  )
  , 'stumbleupon' => array(
    'name' => 'StumbleUpon'
    , 'url' => 'http://www.stumbleupon.com/submit?url={url}&title={title}'
  )
  , 'google_bmarks' => array(
    'name' => 'Google Bookmarks'
    , 'url' => '  http://www.google.com/bookmarks/mark?op=edit&bkmk={url}&title={title}'
  )
  , 'technorati' => array(
    'name' => 'Technorati'
    , 'url' => 'http://www.technorati.com/faves?add={url}'
  )
  , 'blinklist' => array(
    'name' => 'BlinkList'
    , 'url' => 'http://blinklist.com/index.php?Action=Blink/addblink.php&Url={url}&Title={title}'
  )
  , 'newsvine' => array(
    'name' => 'Newsvine'
    , 'url' => 'http://www.newsvine.com/_wine/save?u={url}&h={title}'
  )
  , 'magnolia' => array(
    'name' => 'ma.gnolia'
    , 'url' => 'http://ma.gnolia.com/bookmarklet/add?url={url}&title={title}'
  )
  , 'reddit' => array(
    'name' => 'reddit'
    , 'url' => 'http://reddit.com/submit?url={url}&title={title}'
  )
  , 'windows_live' => array(
    'name' => 'Windows Live'
    , 'url' => 'https://favorites.live.com/quickadd.aspx?marklet=1&mkt=en-us&url={url}&title={title}&top=1'
  )
  , 'tailrank' => array(
    'name' => 'Tailrank'
    , 'url' => 'http://tailrank.com/share/?link_href={url}&title={title}'
  )
);

/*

// Additional sites

  , 'blogmarks' => array(
    'name' => 'Blogmarks'
    , 'url' => 'http://blogmarks.net/my/new.php?mini=1&url={url}&title={title}'
  )

  , 'favoriting' => array(
    'name' => 'Favoriting'
    , 'url' => 'http://www.favoriting.com/nuevoFavorito.asp?qs_origen=3&qs_url={url}&qs_title={title}'
  )

*/


// NO NEED TO EDIT BELOW THIS LINE
// ============================================================

@define('AK_WPROOT', '../../../');
@define('wpsc_akst_FILEPATH', WPSC_URL.'/share-this.php');

// if (function_exists('load_plugin_textdomain')) {
//   load_plugin_textdomain('alexking.org');
// }


$wpsc_akst_action = '';

if (!function_exists('ak_check_email_address')) {
  function ak_check_email_address($email) {
  return true;
// From: http://www.ilovejackdaniels.com/php/email-address-validation/
// First, we check that there's one @ symbol, and that the lengths are right
    if (!preg_match("/^[a-zA-Z0-9.+_-]+@[a-zA-Z0-9-.]+\.[a-zA-Z]{2,5}$/", $email)) {
      // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
      return false;
    }
// Split it into sections to make life easier
    $email_array = explode("@", $email);
    $local_array = explode(".", $email_array[0]);
    for ($i = 0; $i < sizeof($local_array); $i++) {
       if (!preg_match("/^[a-zA-Z0-9.+_-]+@[a-zA-Z0-9-.]+\.[a-zA-Z]{2,5}$/", $local_array[$i])) {
        return false;
      }
    } 
    if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
      $domain_array = explode(".", $email_array[1]);
      if (sizeof($domain_array) < 2) {
          return false; // Not enough parts to domain
      }
      for ($i = 0; $i < sizeof($domain_array); $i++) {
        if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
          return false;
        }
      }
    }
    return true;
  }
}

if (!function_exists('ak_decode_entities')) {
  function ak_decode_entities($text, $quote_style = ENT_COMPAT) {
// From: http://us2.php.net/manual/en/function.html-entity-decode.php#68536
    if (function_exists('html_entity_decode')) {
      $text = html_entity_decode($text, $quote_style, 'ISO-8859-1'); // NOTE: UTF-8 does not work!
    }
    else { 
      $trans_tbl = get_html_translation_table(HTML_ENTITIES, $quote_style);
      $trans_tbl = array_flip($trans_tbl);
      $text = strtr($text, $trans_tbl);
    }
    $text = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $text); 
    $text = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $text);
    return $text;
  }
}

if (!function_exists('ak_prototype')) {
  function ak_prototype() {
    if (!function_exists('wp_enqueue_script')) {
      global $ak_prototype;
      if (!isset($ak_prototype) || !$ak_prototype) {
        print('
    <script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-includes/js/prototype.js"></script>
        ');
      }
      $ak_prototype = true;
    }
  }
}

if (!empty($_REQUEST['wpsc_akst_action'])) {
  switch ($_REQUEST['wpsc_akst_action']) {
    case 'js':
      header("Content-type: text/javascript");
?>
function wpsc_akst_share(id, url, title) {
  if ((jQuery('#wpsc_akst_form').css("display") == 'block') && (jQuery('#wpsc_akst_post_id').attr("value") == id)) {  
    jQuery('#wpsc_akst_form').css("display", "none");
    return;
    }
  
  var link = jQuery('#wpsc_akst_link_' + id);
  var options = {
    margin: 1 ,
    border: 1 ,
    padding: 1 ,
    scroll: 1 
  };
  
  var offset = {};
  new_container_offset = jQuery('#wpsc_akst_link_' + id).offset(options, offset);
  
  if(offset['left'] == null) {
		offset['left'] = new_container_offset.left;
		offset['top'] = new_container_offset.top;
	}

<?php
  foreach ($social_sites as $key => $data) {
    print(' jQuery("#wpsc_akst_'.$key.'").attr("href", wpsc_akst_share_url("'.$data['url'].'", url, title));');
  }
?>

  jQuery('#wpsc_akst_post_id').value = id;
  jQuery('#wpsc_akst_form').css("left", offset['left'] + 'px');
  jQuery('#wpsc_akst_form').css("top", (offset['top']+ 14 + 3) + 'px');
  jQuery('#wpsc_akst_form').css("display", 'block');
}

function wpsc_akst_share_url(base, url, title) {
  base = base.replace('{url}', url);
  return base.replace('{title}', title);
}

function wpsc_akst_share_tab(tab) {
  var tab1 = document.getElementById('wpsc_akst_tab1');
  var tab2 = document.getElementById('wpsc_akst_tab2');
  var body1 = document.getElementById('wpsc_akst_social');
  var body2 = document.getElementById('wpsc_akst_email');
  
  switch (tab) {
    case '1':
      tab2.className = '';
      tab1.className = 'selected';
      body2.style.display = 'none';
      body1.style.display = 'block';
      break;
    case '2':
      tab1.className = '';
      tab2.className = 'selected';
      body1.style.display = 'none';
      body2.style.display = 'block';
      break;
  }
}

function wpsc_akst_xy(id) {
  var element = jQuery(id);
  var x = 0;
  var y = 0;
}
<?php
      die();
      break;
    case 'css':
      header("Content-type: text/css");
?>
#wpsc_akst_form {
  background: #999;
  border: 1px solid #ddd;
  display: none;
  position: absolute;
  width: 350px;
  z-index: 999;
}
#wpsc_akst_form a.wpsc_akst_close {
  color: #fff;
  float: right;
  margin: 5px;
}
#wpsc_akst_form ul.tabs {
  border: 1px solid #999;
  list-style: none;
  margin: 10px 10px 0 10px;
  padding: 0;
}
#wpsc_akst_form ul.tabs li {
  background: #ccc;
  border-bottom: 1px solid #999;
  cursor: pointer;
  float: left;
  margin: 0 3px 0 0;
  padding: 3px 5px 2px 5px;
}
#wpsc_akst_form ul.tabs li.selected {
  background: #fff;
  border-bottom: 1px solid #fff;
  cursor: default;
  padding: 4px 5px 1px 5px;
}
#wpsc_akst_form div.clear {
  clear: both;
  float: none;
}
#wpsc_akst_social, #wpsc_akst_email {
  background: #fff;
  border: 1px solid #fff;
  padding: 10px;
}
#wpsc_akst_social ul {
  list-style: none;
  margin: 0;
  padding: 0;
}
#wpsc_akst_social ul li {
  float: left;
  margin: 0;
  padding: 0;
  width: 45%;
}
#wpsc_akst_social ul li a {
  background-position: 0px 2px;
  background-repeat: no-repeat;
  display: block;
  float: left;
  height: 24px;
  padding: 4px 0 0 22px;
  vertical-align: middle;
}
<?php
foreach ($social_sites as $key => $data) {
  print(
'#wpsc_akst_'.$key.' {
  background-image: url(images/social_networking/'.$key.'.gif);
}
');
}
?>
#wpsc_akst_email {
  display: none;
  text-align: left;
}
#wpsc_akst_email form, #wpsc_akst_email fieldset {
  border: 0;
  margin: 0;
  padding: 0;
}
#wpsc_akst_email fieldset legend {
  display: none;
}
#wpsc_akst_email ul {
  list-style: none;
  margin: 0;
  padding: 0;
}
#wpsc_akst_email ul li {
  margin: 0 0 7px 0;
  padding: 0;
}
#wpsc_akst_email ul li label {
  color: #555;
  display: block;
  margin-bottom: 3px;
}
#wpsc_akst_email ul li input {
  padding: 3px 10px;
}
#wpsc_akst_email ul li input.wpsc_akst_text {
  padding: 3px;
  width: 280px;
}
<?php
if (wpsc_akst_SHOWICON) {
?>
.wpsc_akst_share_link {
 display: block;
 margin: 0px 0px 6px 0px;
 width: 119px;
}
<?php
}
      die();
      break;
  }
}

function wpsc_akst_request_handler() {
  if (!empty($_REQUEST['wpsc_akst_action'])) {
    switch ($_REQUEST['wpsc_akst_action']) {
      case 'share-this':
        wpsc_akst_page();
        break;
      case 'send_mail':
        wpsc_akst_send_mail();     
        break;
    }
  }
}
add_action('init', 'wpsc_akst_request_handler', 9999);     

function wpsc_akst_init() {
  if (function_exists('wp_enqueue_script')) {
    wp_enqueue_script('prototype');
  }
}
add_action('init', 'wpsc_akst_init');      

function wpsc_akst_head() {
  $wp = get_bloginfo('wpurl');
  $url = wpsc_akst_FILEPATH;
  ak_prototype();
  print('
  <script type="text/javascript" src="'.$url.'?wpsc_akst_action=js"></script>
  <link rel="stylesheet" type="text/css" href="'.$url.'?wpsc_akst_action=css" />
  ');
}
add_action('wp_head', 'wpsc_akst_head');

function wpsc_akst_share_link($action = 'print') {
  global $wpdb, $wpsc_akst_action, $post, $wp_query;
  if (in_array($wpsc_akst_action, array('page'))) {
    return '';
  }
  if (is_feed() || (function_exists('akm_check_mobile') && akm_check_mobile())) {
    $onclick = '';
  } else {
		$permalink = get_permalink($post->ID);
		if($wp_query->query_vars['product_url_name'] != null){
			$product_id = $wpdb->get_var("SELECT `product_id` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN ( 'url_name' ) AND `meta_value` IN ( '".$wp_query->query_vars['product_url_name']."' ) ORDER BY `product_id` DESC LIMIT 1");
			$product_link = wpsc_product_url($product_id);
		} else {
			if(strstr($permalink, "?") !== false) {
				$product_link = $permalink."&product_id=".$_REQUEST['product_id'];
			} else {
				$product_link = wpsc_product_url((int)$_REQUEST['product_id']);
			}
		}
// 		exit("<pre>".print_r($product_link,true)."</pre>");
	$onclick = 'onclick="wpsc_akst_share(\''.$post->ID.'\', \''.urlencode($product_link).'\', \''.urlencode(get_the_title()).'\'); return false;"';
	}
  global $post;
  ob_start();
 /*<?php bloginfo('siteurl'); ?>/?p=<?php print($post->ID); ?>&amp;wpsc_akst_action=share-this */
?>
<a href="#" <?php print($onclick); ?> title="<?php _e('E-mail this, post to del.icio.us, etc.','wpsc'); ?>" id="wpsc_akst_link_<?php print($post->ID); ?>" class="wpsc_akst_share_link" rel="nofollow"><img src='<?php echo WPSC_URL; ?>/images/social_networking/share-this-product.gif' title='Share This' alt='Share This' /></a>
<?php
  $link = ob_get_contents();
  ob_end_clean();
  switch ($action) {
    case 'print':
      print($link);
      break;
    case 'return':
      return $link;
      break;
  }
}

function wpsc_akst_add_share_link_to_content($content) {
  $doit = false;
  if (is_feed() && wpsc_akst_ADDTOFEED) {
    $doit = true;
  }
  else if (wpsc_akst_ADDTOCONTENT) {
    $doit = true;
  }
  if ($doit) {
    $content .= '<p class="wpsc_akst_link">'.wpsc_akst_share_link('return').'</p>';
  }
  return $content;
}
//add_action('the_content', 'wpsc_akst_add_share_link_to_content');
//add_action('the_content_rss', 'wpsc_akst_add_share_link_to_content');

function wpsc_akst_share_form() {
  global $post, $social_sites, $current_user, $wp_query, $wpdb;

  if (!empty($_GET['p'])) {
    $id = intval($_GET['p']);
    } else if(!empty($_GET['page_id']))
      {
      $id = $_GET['page_id'];
      }
    
  if (isset($current_user)) {
    $user = get_currentuserinfo();
    $name = $current_user->user_nicename;
    $email = $current_user->user_email;
  }
  else {
    $user = wp_get_current_commenter();
    $name = $user['comment_author'];
    $email = $user['comment_author_email'];
  }
?>
  <!-- Share This BEGIN -->
  <div id="wpsc_akst_form">
    <a href="#" onclick="jQuery('#wpsc_akst_form').css('display','none'); return false;" class="wpsc_akst_close"><?php _e('Close', 'wpsc'); ?></a>
    <ul class="tabs">
      <li id="wpsc_akst_tab1" class="selected" onclick="wpsc_akst_share_tab('1');"><?php _e('Social Web', 'wpsc'); ?></li>
      <li id="wpsc_akst_tab2" onclick="wpsc_akst_share_tab('2');"><?php _e('E-mail', 'wpsc'); ?></li>
    </ul>
    <div class="clear"></div>
    <div id="wpsc_akst_social">
      <ul>
<?php
  foreach ($social_sites as $key => $data) {
    print('       <li><a href="#" id="wpsc_akst_'.$key.'">'.$data['name'].'</a></li>'."\n");
  }
?>
      </ul>
      <div class="clear"></div>
    </div>
    <div id="wpsc_akst_email">
      <form action="#" method="post">
			<!-- "<?php echo WPSC_URL; ?>/share-this.php -->
        <fieldset>
          <legend><?php _e('E-mail It', 'wpsc'); ?></legend>
          <ul>
            <li>
              <label><?php _e('To Address:','wpsc'); ?></label>
              <input type="text" name="wpsc_akst_to" value="" class="wpsc_akst_text" />
            </li>
            <li>
              <label><?php _e('Your Name:','wpsc'); ?></label>
              <input type="text" name="wpsc_akst_name" value="<?php print(htmlspecialchars($name)); ?>" class="wpsc_akst_text" />
            </li>
            <li>
              <label><?php _e('Your Address:','wpsc'); ?></label>
              <input type="text" name="wpsc_akst_email" value="<?php print(htmlspecialchars($email)); ?>" class="wpsc_akst_text" />
            </li>
            <li>
              <input type="submit" name="wpsc_akst_submit" value="<?php _e('Send It','wpsc'); ?>" />
            </li>
          </ul>
          <input type="hidden" name="wpsc_akst_action" value="send_mail" />
          <input type="hidden" name="wpsc_akst_post_id" id="wpsc_akst_post_id" value="<?php print($id); ?>" />
          <?php
					if($wp_query->query_vars['product_url_name'] != '') {
						$product_id = $wpdb->get_var("SELECT `product_id` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN ( 'url_name' ) AND `meta_value` IN ( '".$wp_query->query_vars['product_url_name']."' ) ORDER BY `product_id` DESC LIMIT 1");
					} else if(is_numeric($_GET['product_id'])){
					  $product_id = (int)$_GET['product_id'];
					}
					echo "<input type=\"hidden\" name=\"wpsc_akst_product_id\" id=\"wpsc_akst_product_id\" value=\"".$product_id."\" />\n\r";
          ?>
        </fieldset>
      </form>
    </div>
  </div>
  <!-- Share This END -->
<?php
}
if (wpsc_akst_ADDTOFOOTER) {
  add_action('wp_footer', 'wpsc_akst_share_form');
}

function wpsc_akst_send_mail() {
  global $wpdb, $wp_query;
  //exit("<pre>".print_r($_REQUEST,true)."</pre>");
  $post_id = '';
  $to = '';
  $name = '';
  $email = '';
  
  if (!empty($_REQUEST['wpsc_akst_to'])) {
    $to = stripslashes($_REQUEST['wpsc_akst_to']);
    $to = strip_tags($to);
    $to = str_replace(
      array(
        ','
        ,"\n"
        ,"\t"
        ,"\r"
      )
      , array()
      , $to
    );
  }
  
  if (!empty($_REQUEST['wpsc_akst_name'])) {
    $name = stripslashes($_REQUEST['wpsc_akst_name']);
    $name = strip_tags($name);
    $name = str_replace(
      array(
        '"'
        ,"\n"
        ,"\t"
        ,"\r"
      )
      , array()
      , $name
    );
  }

  if (!empty($_REQUEST['wpsc_akst_email'])) {
    $email = stripslashes($_REQUEST['wpsc_akst_email']);
    $email = strip_tags($email);
    $email = str_replace(
      array(
        ','
        ,"\n"
        ,"\t"
        ,"\r"
      )
      , array()
      , $email
    );
  }
  
  if (!empty($_REQUEST['wpsc_akst_post_id'])) {
    $post_id = intval($_REQUEST['wpsc_akst_post_id']);
  }

  if (empty($to) || !ak_check_email_address($to) || empty($email) || !ak_check_email_address($email)) {
    wp_die(__('Click your <strong>back button</strong> and make sure those e-mail addresses are valid then try again.', 'wpsc'));
  }
  
//  $post = &get_post($post_id);
  $headers = "MIME-Version: 1.0\n" .
    'From: "'.$name.'" <'.$email.'>'."\n"
    .'Reply-To: "'.$name.'" <'.$email.'>'."\n"
    .'Return-Path: "'.$name.'" <'.$email.'>'."\n"
    ."Content-Type: text/plain; charset=\"" . get_option('blog_charset') ."\"\n";
  
  $subject = __('Check out this product on ', 'wpsc').get_bloginfo('name');
  
  if(is_numeric($_REQUEST['wpsc_akst_product_id']))
    {
    $permalink = get_option('product_list_url');    
    $message .= __('Greetings--', 'wpsc')."\n\n";
    $message .= $name.__(' thinks this will be of interest to you:','wpsc')."\n\n";
    //$message .= ak_decode_entities(get_the_title($post_id))."\n\n";
    if($wp_query->query_vars['product_url_name'] != '') {
			$product_id = $wpdb->get_var("SELECT `product_id` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN ( 'url_name' ) AND `meta_value` IN ( '".$wp_query->query_vars['product_url_name']."' ) ORDER BY `product_id` DESC LIMIT 1");
			$message .= wpsc_product_url($product_id);
    } else {
			if(strstr($permalink, "?") !== false) {
				$message .= $permalink."&product_id=".$_REQUEST['wpsc_akst_product_id']."\n\n";
			} else {
				// $message .= $permalink."?product_id=".$_REQUEST['wpsc_akst_product_id']."\n\n";
				$message .= wpsc_product_url((int)$_REQUEST['wpsc_akst_product_id'])."\n\n";
			}
    }
    $message .= __('Enjoy.')."\n\n";
    $message .= '--'."\n";
    $message .= get_bloginfo('home')."\n";
    }
    else
      {  
      $message = __('Greetings--','wpsc')."\n\n"
        .$name.__(' thinks this will be of interest to you:','wpsc')."\n\n"
        .ak_decode_entities(get_the_title($post_id))."\n\n"
        .get_permalink($post_id)."\n\n"
        .__('Enjoy.')."\n\n"
        .'--'."\n"
        .get_bloginfo('home')."\n";
      }
  @wp_mail($to, $subject, $message, $headers);
  
  if (!empty($_SERVER['HTTP_REFERER'])) {
    $url = $_SERVER['HTTP_REFERER'];
  }
  
  header("Location: $url");
  status_header('302');
  die();
}

function wpsc_akst_hide_pop() {
  return false;
}

function wpsc_akst_page() {
  global $social_sites, $wpsc_akst_action, $current_user, $post, $wp_query, $wpdb;
  
  $wpsc_akst_action = 'page';
  
  add_action('akpc_display_popularity', 'wpsc_akst_hide_pop');
  
  $id = 0;
  if (!empty($_GET['p'])) {
    $id = intval($_GET['p']);
  } else if(!empty($_GET['page_id']))
    {
    $id = $_GET['page_id'];
    }
  
  if ($id <= 0) {
    header("Location: ".get_bloginfo('siteurl'));
    die();
  }
  if (isset($current_user)) {
    $user = get_currentuserinfo();
    $name = $current_user->user_nicename;
    $email = $current_user->user_email;
  }
  else {
    $user = wp_get_current_commenter();
    $name = $user['comment_author'];
    $email = $user['comment_author_email'];
  }
  query_posts('p='.$id);
  if (have_posts()) : 
    while (have_posts()) : 
      the_post();
      header('Content-Type: '.get_bloginfo('html_type').'; charset='.get_bloginfo('charset'));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
        "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?php _e('Share This : ', 'wpsc'); the_title(); ?></title>
  <meta name="robots" content="noindex, noarchive" />
  <link rel="stylesheet" type="text/css" href="<?php print(wpsc_akst_FILEPATH); ?>?wpsc_akst_action=css" />
  <style type="text/css">
  
  #wpsc_akst_social ul li {
    width: 48%;
  }
  #wpsc_akst_social ul li a {
    background-position: 0px 4px;
  }
  #wpsc_akst_email {
    display: block;
  }
  #wpsc_akst_email ul li {
    margin-bottom: 10px;
  }
  #wpsc_akst_email ul li input.wpsc_akst_text {
    width: 220px;
  }
  
  body {
    background: #fff url(<?php bloginfo('wpurl'); ?>/wp-content/plugins/share-this/page_back.gif) repeat-x;
    font: 11px Verdana, sans-serif;
    padding: 20px;
    text-align: center;
  }
  #body {
    background: #fff;
    border: 1px solid #ccc;
    border-width: 5px 1px 2px 1px;
    margin: 0 auto;
    text-align: left;
    width: 700px;
  }
  #info {
    border-bottom: 1px solid #ddd;
    line-height: 150%;
    padding: 10px;
  }
  #info p {
    margin: 0;
    padding: 0;
  }
  #social {
    float: left;
    padding: 10px 0 10px 10px;
    width: 350px;
  }
  #email {
    float: left;
    padding: 10px;
    width: 300px;
  }
  #content {
    border-top: 1px solid #ddd;
    padding: 20px 50px;
  }
  #content .wpsc_akst_date {
    color: #666;
    float: right;
    padding-top: 4px;
  }
  #content .wpsc_akst_title {
    font: bold 18px "Lucida Sans Unicode", "Lucida Grande", "Trebuchet MS", sans-serif;
    margin: 0 150px 10px 0;
    padding: 0;
  }
  #content .wpsc_akst_category {
    color: #333;
  }
  #content .wpsc_akst_entry {
    font-size: 12px;
    line-height: 150%;
    margin-bottom: 20px;
  }
  #content .wpsc_akst_entry p, #content .wpsc_akst_entry li, #content .wpsc_akst_entry dt, #content .wpsc_akst_entry dd, #content .wpsc_akst_entry div, #content .wpsc_akst_entry blockquote {
    margin-bottom: 10px;
    padding: 0;
  }
  #content .wpsc_akst_entry blockquote {
    background: #eee;
    border-left: 2px solid #ccc;
    padding: 10px;
  }
  #content .wpsc_akst_entry blockquote p {
    margin: 0 0 10px 0;
  }
  #content .wpsc_akst_entry p, #content .wpsc_akst_entry li, #content .wpsc_akst_entry dt, #content .wpsc_akst_entry dd, #content .wpsc_akst_entry td, #content .wpsc_akst_entry blockquote, #content .wpsc_akst_entry blockquote p {
    line-height: 150%;
  }
  #content .wpsc_akst_return {
    font-size: 11px;
    margin: 0;
    padding: 20px;
    text-align: center;
  }
  #footer {
    background: #eee;
    border-top: 1px solid #ddd;
    padding: 10px;
  }
  #footer p {
    color: #555;
    margin: 0;
    padding: 0;
    text-align: center;
  }
  #footer p a, #footer p a:visited {
    color: #444;
  }
  h2 {
    color: #333;
    font: bold 14px "Lucida Sans Unicode", "Lucida Grande", "Trebuchet MS", sans-serif;
    margin: 0 0;
    padding: 0;
  }
  div.clear {
    float: none;
    clear: both;
  }
  hr {
    border: 0;
    border-bottom: 1px solid #ccc;
  }
  
  </style>

<?php do_action('wpsc_akst_head'); ?>

</head>
<body>

<div id="body">

  <div id="info">
    <p><?php printf(__('<strong>What is this?</strong> From this page you can use the <em>Social Web</em> links to save %s to a social bookmarking site, or the <em>E-mail</em> form to send a link via e-mail.', bloginfo('wpurl')), '<a href="'.get_permalink($id).'">'.get_the_title().'</a>'); ?></p>
  </div>

  <div id="social">
    <h2><?php _e('Social Web', 'wpsc'); ?></h2>
    <div id="wpsc_akst_social">
      <ul>
<?php
  foreach ($social_sites as $key => $data) {
    $link = str_replace(
      array(
        '{url}'
        , '{title}'
      )
      , array(
        urlencode(get_permalink($id))
        , urlencode(get_the_title())
      )
      , $data['url']
    );
    print('       <li><a href="'.$link.'" id="wpsc_akst_'.$key.'">'.$data['name'].'</a></li>'."\n");
  }
?>
      </ul>
      <div class="clear"></div>
    </div>
  </div>
  
  <div id="email">
    <h2><?php _e('E-mail', 'wpsc'); ?></h2>
    <div id="wpsc_akst_email">
      <form action="#" method="post">
        <fieldset>
          <legend><?php _e('E-mail It','wpsc'); ?></legend>
          <ul>
            <li>
              <label><?php _e('To Address:','wpsc'); ?></label>
              <input type="text" name="wpsc_akst_to" value="" class="wpsc_akst_text" />
            </li>
            <li>
              <label><?php _e('Your Name:','wpsc'); ?></label>
              <input type="text" name="wpsc_akst_name" value="<?php print(htmlspecialchars($name)); ?>" class="wpsc_akst_text" />
            </li>
            <li>
              <label><?php _e('Your Address:','wpsc'); ?></label>
              <input type="text" name="wpsc_akst_email" value="<?php print(htmlspecialchars($email)); ?>" class="wpsc_akst_text" />
            </li>
            <li>
              <input type="submit" name="wpsc_akst_submit" value="<?php _e('Send It','wpsc'); ?>" />
            </li>
          </ul>
          <input type="hidden" name="wpsc_akst_action" value="send_mail" />
          <input type="hidden" name="wpsc_akst_post_id" id="wpsc_akst_post_id" value="<?php print($id); ?>" />
          <?php
						if($wp_query->query_vars['product_url_name'] != '') {
							$product_id = $wpdb->get_var("SELECT `product_id` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN ( 'url_name' ) AND `meta_value` IN ( '".$wp_query->query_vars['product_url_name']."' ) ORDER BY `product_id` DESC LIMIT 1");
						} else if(is_numeric($_GET['product_id'])){
							$product_id = (int)$_GET['product_id'];
						}
					echo "<input type=\"hidden\" name=\"wpsc_akst_product_id\" id=\"wpsc_akst_product_id\" value=\"".$product_id."\" />\n\r";
          ?>
        </fieldset>
      </form>
    </div>
  </div>
  
  <div class="clear"></div>
  
  <div id="content">
    <span class="wpsc_akst_date"><?php the_time('F d, Y'); ?></span>
    <h1 class="wpsc_akst_title"><?php the_title(); ?></h1>
    <p class="wpsc_akst_category"><?php _e('Posted in: ', 'wpsc'); the_category(','); ?></p>
    <div class="wpsc_akst_entry"><?php the_content(); ?></div>
    <hr />
    <p class="wpsc_akst_return"><?php _e('Return to:', 'wpsc'); ?> <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></p>
    <div class="clear"></div>
  </div>
  
</div>

</body>
</html>
<?php
    endwhile;
  endif;
  die();
}
?>