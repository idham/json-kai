<?php
/*
Controller name: Core
Controller description: Basic introspection methods
*/

class JSON_API_Core_Controller {
	
  public function info() {
    global $json_api;
    $php = '';
    if (!empty($json_api->query->controller)) {
      return $json_api->controller_info($json_api->query->controller);
    } else {
      $dir = json_api_dir();
      if (file_exists("$dir/json-api.php")) {
        $php = file_get_contents("$dir/json-api.php");
      } else {
        // Check one directory up, in case json-api.php was moved
        $dir = dirname($dir);
        if (file_exists("$dir/json-api.php")) {
          $php = file_get_contents("$dir/json-api.php");
        }
      }
      if (preg_match('/^\s*Version:\s*(.+)$/m', $php, $matches)) {
        $version = $matches[1];
      } else {
        $version = '(Unknown)';
      }
      $active_controllers = explode(',', get_option('json_api_controllers', 'core'));
      $controllers = array_intersect($json_api->get_controllers(), $active_controllers);
      return array(
        'json_api_version' => $version,
        'controllers' => array_values($controllers)
      );
    }
  }

  public function detailTaxi(){
	global $json_api;
	extract($json_api->query->get(array('nota')));
	//get kobook
	$prints = (strtoupper(get_post_meta($nota, 'print' ,true))==="TRUE"  ? true : false);if ($prints==""){$prints=false;}
	$pramias = (strtoupper(get_post_meta($nota, 'review' ,true))=="PRAMIA" ? true:false);
	$kobook = get_post_meta($nota, 'book_code',true);
	$datanya = get_page_by_title($kobook);
	$hasil = array (
			'nota'=>'T-'.strtoupper(dechex($nota)).'-'.substr(get_post_meta( $nota, 'pickup_time',true), 5),
			'book_code'=>get_post_meta($nota, 'book_code',true),
			'contact'=>get_post_meta($nota, 'contact',true),
			'name'=>get_post_meta($nota, 'name',true),
			'pramia'=> filter_var($pramias, FILTER_VALIDATE_BOOLEAN),
			'print'=>$prints,
			'pickup_time'=>get_post_meta($nota, 'pickup_time',true),
			'rating'=>get_post_meta($nota, 'rating',true),
			'review'=>get_post_meta($nota, 'review',true),
			'stasiun'=>get_post_meta($nota, 'stasiun',true),
			'taxi'=>get_post_meta($nota, 'taxi',true),
			'vendor'=>get_post_meta($nota, 'vendor',true),
			'train_no'=>get_post_meta($datanya->ID, 'train_no',true),
			'train_name'=>get_post_meta($datanya->ID, 'train_name',true),				
		);
	
	return  $hasil;
  }
  
  public function detailPorter(){
	global $json_api;
	extract($json_api->query->get(array('nota')));
	//get kobook
	$prints = false;
	$prints = (strtoupper(get_post_meta($nota, 'print' ,true))==="TRUE"  ? true : false);if ($prints==""){$prints=false;}
	$pramias = (strtoupper(get_post_meta($nota, 'review' ,true))=="PRAMIA" ? true:false);
	$kobook = get_post_meta($nota, 'book_code',true);
	$datanya = get_page_by_title($kobook);	
	$hasil = array (
			'nota'=>'P-'.strtoupper(dechex($nota)).'-'.get_post_meta( $nota, 'pickup_date',true), 
			'book_code'=>get_post_meta($nota, 'book_code',true),
			'pramia'=>get_post_meta($nota, 'pramia',true),
			'seat'=>get_post_meta($nota, 'seat',true),
			'wagon'=>get_post_meta($nota, 'wagon',true),
			'stasiun'=>get_post_meta($nota, 'stasiun',true),
			'vendor'=>get_post_meta($nota, 'vendor',true),
			'porter_qty'=>get_post_meta($nota, 'porter_qty',true),
			'pramia'=> filter_var($pramias, FILTER_VALIDATE_BOOLEAN),
			'print'=>filter_var($prints, FILTER_VALIDATE_BOOLEAN),
			'rating'=>get_post_meta($nota, 'rating',true),
			'review'=>get_post_meta($nota, 'review',true),
			'train_no'=>get_post_meta($datanya->ID, 'train_no',true),
			'train_name'=>get_post_meta($datanya->ID, 'train_name',true),
					
		);
	return  $hasil;
  }
	
  public function listOrder(){
	global $json_api;
	extract($json_api->query->get(array('kobook')));
	//get kobook
	$datanya = get_page_by_title($kobook);
	$rating = array();
	if($datanya){	 
	$orders = get_post_meta( $datanya->ID, 'orders' , true);
	for ($i=0;$i<sizeof($orders);$i++){
		$kategori = get_the_category($orders[$i]);
		$katx = $kategori[0]->slug;
		$ratex = (get_post_meta( $orders[$i], 'rating', true) ? get_post_meta( $orders[$i], 'rating',true) : 0);
		//$prints = (get_post_meta( $orders[$i], 'print', true) ? get_post_meta( $orders[$i], 'print',true) : false);
		$prints = (strtoupper(get_post_meta($orders[$i], 'print' ,true))==="TRUE"  ? true : false);if ($prints==""){$prints=false;}
		$pramias = (strtoupper(get_post_meta($orders[$i], 'review' ,true))=="PRAMIA" ? true:false);
		$direction = ($katx=='porter') ? get_post_meta($orders[$i], 'porter_type' ,true) : 'jemput';
		$pfix = ($katx=='porter') ? get_post_meta($orders[$i], 'pickup_date' ,true) : substr(get_post_meta( $orders[$i], 'pickup_time',true), 5);
		$rating[$i] = array ('id_nota'=>intval($orders[$i]),
							'nota'=>strtoupper($katx[0]).'-'.strtoupper(dechex($orders[$i])).'-'.$pfix,
							'rating'=>intval($ratex),
							'review'=>get_post_meta($orders[$i], 'review' ,true),
							'kategori'=>$kategori[0]->slug,
							'direction'=> $direction,
							'print'=>$prints,
							'pramia'=>$pramias,
							);
		}
	}
	$hasil['book_code'] = $kobook;
	$hasil['order'] = $rating;
	
	return $hasil;
  }
 
  public function cekBook(){
	global $json_api;
	extract($json_api->query->get(array('kobook')));
	//get kobook
	$datanya = get_page_by_title($kobook);
	$rating = array();
	if($datanya){ 
	$orders = get_post_meta( $datanya->ID, 'orders' , true);
	for ($i=0;$i<sizeof($orders);$i++){
		$kategori = get_the_category($orders[$i]);
		$katx = $kategori[0]->slug;
		$ratex = (get_post_meta( $orders[$i], 'rating') ? get_post_meta( $orders[$i], 'rating',true) : 0);
		$direction = ($katx=='porter') ? get_post_meta($orders[$i], 'porter_type' ,true) : 'jemput';
		$prints = (strtoupper(get_post_meta($orders[$i], 'print' ,true))==="TRUE"  ? true : false);if ($prints==""){$prints=false;}
		//$prints = (get_post_meta( $orders[$i], 'print', true) ? get_post_meta( $orders[$i], 'print',true) : false);
		$pramias = (strtoupper(get_post_meta($orders[$i], 'review' ,true))=="PRAMIA" ? true:false);
		$pfix = ($katx=='porter') ? get_post_meta($orders[$i], 'pickup_date' ,true) : substr(get_post_meta( $orders[$i], 'pickup_time',true), 5);
		$rating[$i] = array ('id_nota'=>intval($orders[$i]),
							'nota'=>strtoupper($katx[0]).'-'.strtoupper(dechex($orders[$i])).'-'.$pfix,
							'rating'=>intval($ratex),
							'review'=>get_post_meta($orders[$i], 'review' ,true),
							'kategori'=>$kategori[0]->slug,
							'direction'=> $direction,
							'print'=>filter_var($prints, FILTER_VALIDATE_BOOLEAN),
							'pramia'=>filter_var($pramias, FILTER_VALIDATE_BOOLEAN),
							);
		}
		$rating = ($rating[0]['id_nota']==0? array():$rating);
	}
	ini_set("user_agent","RailTicket-B2B");
	$context = stream_context_create(array(
    'http' => array(
        'method' => 'GET',
        'header' => "USER AGENT: Railticket-B2B\r\n" .
                    "Cookie: vendor=skywise\r\n"
    	)
	));
	$url = 'http://ws.railticket.kereta-api.co.id/?rqid=6ABF8992-BDFF-11E6-845A-9152DBFCD93A&app=information&action=get_book_info_ibook&book_code='.strtoupper($kobook);
	$content = file_get_contents($url, false , $context);
	$json = json_decode($content, true);
	if ($json !=null){
		$no_telp = intval($json[caller]);
		$json[caller] = $no_telp;
		$train_no = $json[train_no];
		$train_name = $json[train_name];
		$tgl = date_create_from_format('d-M-y', $json[dep_date]);
		$skrg = date('Ymd');
		if ($skrg <= date_format($tgl, 'Ymd')) {
			 $json['order']=true;
			//Bikin Page Kobook
			$postarr = array (
			'post_title'=>$kobook,
			'post_type'=>'page',
			'post_status'=>'draft',
			'meta_input'=> array (
					'order'=>array(),
					'train_no'=>$train_no,
					'train_name'=>$train_name,
					),
			);
			$datanya = get_page_by_title($kobook);
			if (!$datanya){
					$datanya = wp_insert_post( $postarr,  false );
					$json['list'] = $datanya;		
					}else{
					$json['list'] = $datanya->ID;	
					}
			}else{
			 $json['order']=false;
			}
		//get relation 
		$url = 'http://ws.railticket.kereta-api.co.id/?rqid=6ABF8992-BDFF-11E6-845A-9152DBFCD93A&app=information&action=get_train_relation&train_no='.$train_no.'&route_date='.date_format($tgl, 'Ymd');
		$content = file_get_contents($url, false, $context);
		$hasil = preg_replace("/\":\s*([a-zA-Z0-9_]+)/" , "\":\"$1\"",$content);
		$relasi = json_decode($hasil, true);
		$json['relation'] = $relasi['relation'];
		  for ($i=0;$i<sizeof($relasi['relation']);$i++){
		   if (intval($relasi['relation'][$i][1]) - intval($json[dep_time]) < 0) {
			$json['relation'][$i][2] = $json[arv_date];
			}else{
			$json['relation'][$i][2] = $json[dep_date];
			} 
		   $id_st = $relasi['relation'][$i][0];
		   //get taxi list 
		   $taxi_option[$id_st]= array("GT"=>"Generic Taxi non Argo $id_st","BB"=>"Burung Biru");
		  }
		$json['taxi_option'] = $taxi_option;
		$json['orders'] = $rating;
		return $json;
	}else{
		$hasil = preg_replace("/\":\s*([a-zA-Z0-9_]+)/" , "\":\"$1\"",$content);
		$json = json_decode($hasil, true);
		$tgl = date_parse_from_format('d-M-Y', $json['dep_date']);
		$url = 'http://ws.railticket.kereta-api.co.id/?rqid=6ABF8992-BDFF-11E6-845A-9152DBFCD93A&app=information&action=get_train_relation&train_no='.$json[train_no].'&route_date='.$tgl;	
		$content = file_get_contents($url, false, $context);
		$hasil = preg_replace("/\":\s*([a-zA-Z0-9_]+)/" , "\":\"$1\"",$content);
		$relasi = json_decode($hasil, true);
		$json['relation'] = $relasi['relation'];
		  for ($i=0;$i<sizeof($relasi['relation']);$i++){
		  	$json['relation'][$i][3] = namaStasiun($json['relation'][$i][0]);
		if (intval($relasi['relation'][$i][1]) - intval($json[dep_time]) < 0) {
			$json['relation'][$i][2] = $json[arv_date];
			}else{
			$json['relation'][$i][2] = $json[dep_date];
			} 
		   $id_st = $relasi['relation'][$i][0];
		   $taxi_option[$id_st]= array("GT"=>"Generic Taxi non Argo $id_st","BB"=>"Burung Biru");
		  }
		$json['taxi_option'] = $taxi_option;		
		return $json;
		}
  	
  }
  
    public function addTaxi2(){
	global $json_api;
	extract($json_api->query->get(array('kobook', 'book_code', 'stasiun','pickup_date', 'pickup_time', 'taxi', 'taxi_qty', 'name', 'contact', 'pramia')));
   	//get param 
   	$datanya = get_page_by_title($kobook);
	$postarr = array (
			'post_title'=>$kobook,
			'post_type'=>'post',
			'post_status'=>'draft',
			'post_category'=>array(1),
			'meta_input'=> array (
					'book_code'=>$kobook,
					'stasiun'=>$stasiun,
					'pickup_time'=>$pickup_time,
					'taxi'=>$taxi,
					'taxi_qty'=>$taxi_qty,
					'name'=>$name,
					'contact'=>$contact,
					'print'=>false,
					'vendor'=>''
					),
			);
	   $nota = wp_insert_post( $postarr,  false );
	   $orderlist = get_post_meta($datanya->ID, 'orders',true);
	   $orderlist[] = $nota;
	   update_post_meta( $datanya->ID, 'orders', $orderlist);
	   if(strtoupper($pramia!="FALSE")){
	   	update_post_meta( $nota, 'rating', 5);
		update_post_meta( $nota, 'review', 'Pramia');   
	   }	 
	$rating= array ('id_nota'=>$nota,
							'nota'=>'T-'.strtoupper(dechex($nota)).'-'.substr(get_post_meta( $nota, 'pickup_time',true), 5),
							);
		
	return $rating;
   }
   
   public function addPorter2(){
	global $json_api;
	extract($json_api->query->get(array('kobook', 'stasiun', 'porter_qty', 'wagon','seat','pramia')));
	ini_set("user_agent","RailTicket-B2B");
	$context = stream_context_create(array(
    'http' => array(
        'method' => 'GET',
        'header' => "USER AGENT: Railticket-B2B\r\n" .
                    "Cookie: foo=bar\r\n"
    	)
	));
	$url = 'http://ws.railticket.kereta-api.co.id/?rqid=6ABF8992-BDFF-11E6-845A-9152DBFCD93A&app=information&action=get_book_info_ibook&book_code='.strtoupper($kobook);
	$content = file_get_contents($url, false , $context);
	$json = json_decode($content, true);
	$origin = explode(" ", $json[org]);
	$porter_type = ($stasiun==$origin[0]) ? 'antar' :'jemput';		
	$tgl = date_create_from_format('d-M-y', $json[dep_date]);
	
    $datanya = get_page_by_title($kobook);
	$postarr = array (
			'post_title'=>$kobook,
			'post_type'=>'post',
			'post_status'=>'draft',
			'post_category'=>array(2),
			'meta_input'=> array (
					'book_code'=>$kobook,
					'stasiun'=>$stasiun,
					'porter_qty'=>$porter_qty,
					'wagon'=>$wagon,
					'seat'=>$seat,
					'pickup_date'=>date_format($tgl, 'Ymd'),
					'porter_type'=>$porter_type,
					'print'=>false,  
					'vendor'=>''
					),
			);
	   $nota = wp_insert_post( $postarr,  false );
	   $orderlist = get_post_meta($datanya->ID, 'orders',true);
	   $orderlist[] = $nota;
	   update_post_meta( $datanya->ID, 'orders', $orderlist);
	     if(strtoupper($pramia!="FALSE")){
		   	update_post_meta( $nota, 'rating', 5);
			update_post_meta( $nota, 'review', 'Pramia');   
	   	}
	   $rating= array ('id_nota'=>$nota,
							'nota'=>'P-'.strtoupper(dechex($nota)).'-'.get_post_meta( $nota, 'pickup_date',true),
							);
		
	return $rating;
   }
   public function addRating(){
	global $json_api;
	extract($json_api->query->get(array('id_nota', 'rating', 'review')));
	update_post_meta( $id_nota, 'rating', $rating);
	update_post_meta( $id_nota, 'review', $review);
	$hasil['data'] = get_post($id_nota);
	$hasil['rating'] = get_post_meta($id_nota , 'rating');
	$hasil['review'] = get_post_meta($nota , 'review');
	
	return $hasil;
}
 
   public function addPorter(){
	global $json_api;
	extract($json_api->query->get(array('kobook', 'stasiun', 'porter_qty', 'wagon','seat')));
	ini_set("user_agent","RailTicket-B2B");
	$context = stream_context_create(array(
    'http' => array(
        'method' => 'GET',
        'header' => "USER AGENT: Railticket-B2B\r\n" .
                    "Cookie: foo=bar\r\n"
    	)
	));
	$url = 'http://ws.railticket.kereta-api.co.id/?rqid=6ABF8992-BDFF-11E6-845A-9152DBFCD93A&app=information&action=get_book_info_ibook&book_code='.strtoupper($kobook);
	$content = file_get_contents($url, false , $context);
	$json = json_decode($content, true);
	$tgl = date_create_from_format('d-M-y', $json[dep_date]);
	if ($stasiun==$json[org]){$porter_type="antar";}else{$porter_type="jemput";}
    $datanya = get_page_by_title($kobook);
	$postarr = array (
			'post_title'=>$kobook,
			'post_type'=>'post',
			'post_status'=>'draft',
			'post_category'=>array(2),
			'meta_input'=> array (
					'book_code'=>$kobook,
					'stasiun'=>$stasiun,
					'porter_qty'=>$porter_qty,
					'wagon'=>$wagon,
					'seat'=>$seat,
					'pickup_date'=>date_format($tgl, 'Ymd'),
					'porter_type'=>$porter_type,
					),
			);
	   $nota = wp_insert_post( $postarr,  false );
	   $orderlist = get_post_meta($datanya->ID, 'orders',true);
	   $orderlist[] = $nota;
	   update_post_meta( $datanya->ID, 'orders', $orderlist);
	  for ($i=0;$i<sizeof($orderlist);$i++){
		$kategori = get_the_category($orderlist[$i]);
		$katx = $kategori[0]->slug;
		$ratex = (get_post_meta( $orderlist[$i], 'rating') ? get_post_meta( $orderlist[$i], 'rating',true) : 0);
		$rating[$i] = array ('id_nota'=>$orderlist[$i],
							'nota'=>strtoupper($katx[0]).'-'.$orderlist[$i].'-'.get_post_meta( $orderlist[$i], 'pickup_date',true),
							'stasiun'=>get_post_meta($orderlist[$i], 'stasiun' ,true),
							'pickup_time'=>get_post_meta($orderlist[$i], 'pickup_time' ,true),
							);
		}
	$hasil['book_code'] = $kobook;
	$hasil['order'] = $rating;
	return $hasil;
   }
  
  public function cekStatus(){
	global $json_api;
	extract($json_api->query->get(array('host')));
  	$hasil= snmpget($host, "public", "system.SysContact.0");
	return $hasil;
  }  
  
  public function get_recent_posts() {
    global $json_api;
    $posts = $json_api->introspector->get_posts();
    return $this->posts_result($posts);
  }
  
  public function get_posts() {
    global $json_api;
    $url = parse_url($_SERVER['REQUEST_URI']);
    $defaults = array(
      'ignore_sticky_posts' => true
    );
    $query = wp_parse_args($url['query']);
    unset($query['json']);
    unset($query['post_status']);
    $query = array_merge($defaults, $query);
    $posts = $json_api->introspector->get_posts($query);
    $result = $this->posts_result($posts);
    $result['query'] = $query;
    return $result;
  }
  
  public function get_post() {
    global $json_api, $post;
    $post = $json_api->introspector->get_current_post();
    if ($post) {
      $previous = get_adjacent_post(false, '', true);
      $next = get_adjacent_post(false, '', false);
      $response = array(
        'post' => new JSON_API_Post($post)
      );
      if ($previous) {
        $response['previous_url'] = get_permalink($previous->ID);
      }
      if ($next) {
        $response['next_url'] = get_permalink($next->ID);
      }
      return $response;
    } else {
      $json_api->error("Not found.");
    }
  }

  public function get_page() {
    global $json_api;
    extract($json_api->query->get(array('id', 'slug', 'page_id', 'page_slug', 'children')));
    if ($id || $page_id) {
      if (!$id) {
        $id = $page_id;
      }
      $posts = $json_api->introspector->get_posts(array(
        'page_id' => $id
      ));
    } else if ($slug || $page_slug) {
      if (!$slug) {
        $slug = $page_slug;
      }
      $posts = $json_api->introspector->get_posts(array(
        'pagename' => $slug
      ));
    } else {
      $json_api->error("Include 'id' or 'slug' var in your request.");
    }
    
    // Workaround for https://core.trac.wordpress.org/ticket/12647
    if (empty($posts)) {
      $url = $_SERVER['REQUEST_URI'];
      $parsed_url = parse_url($url);
      $path = $parsed_url['path'];
      if (preg_match('#^http://[^/]+(/.+)$#', get_bloginfo('url'), $matches)) {
        $blog_root = $matches[1];
        $path = preg_replace("#^$blog_root#", '', $path);
      }
      if (substr($path, 0, 1) == '/') {
        $path = substr($path, 1);
      }
      $posts = $json_api->introspector->get_posts(array('pagename' => $path));
    }
    
    if (count($posts) == 1) {
      if (!empty($children)) {
        $json_api->introspector->attach_child_posts($posts[0]);
      }
      return array(
        'page' => $posts[0]
      );
    } else {
      $json_api->error("Not found.");
    }
  }
  
  public function get_date_posts() {
    global $json_api;
    if ($json_api->query->date) {
      $date = preg_replace('/\D/', '', $json_api->query->date);
      if (!preg_match('/^\d{4}(\d{2})?(\d{2})?$/', $date)) {
        $json_api->error("Specify a date var in one of 'YYYY' or 'YYYY-MM' or 'YYYY-MM-DD' formats.");
      }
      $request = array('year' => substr($date, 0, 4));
      if (strlen($date) > 4) {
        $request['monthnum'] = (int) substr($date, 4, 2);
      }
      if (strlen($date) > 6) {
        $request['day'] = (int) substr($date, 6, 2);
      }
      $posts = $json_api->introspector->get_posts($request);
    } else {
      $json_api->error("Include 'date' var in your request.");
    }
    return $this->posts_result($posts);
  }
  
  public function get_category_posts() {
    global $json_api;
    $category = $json_api->introspector->get_current_category();
    if (!$category) {
      $json_api->error("Not found.");
    }
    $posts = $json_api->introspector->get_posts(array(
      'cat' => $category->id
    ));
    return $this->posts_object_result($posts, $category);
  }
  
  public function get_tag_posts() {
    global $json_api;
    $tag = $json_api->introspector->get_current_tag();
    if (!$tag) {
      $json_api->error("Not found.");
    }
    $posts = $json_api->introspector->get_posts(array(
      'tag' => $tag->slug
    ));
    return $this->posts_object_result($posts, $tag);
  }
  
  public function get_author_posts() {
    global $json_api;
    $author = $json_api->introspector->get_current_author();
    if (!$author) {
      $json_api->error("Not found.");
    }
    $posts = $json_api->introspector->get_posts(array(
      'author' => $author->id
    ));
    return $this->posts_object_result($posts, $author);
  }
  
  public function get_search_results() {
    global $json_api;
    if ($json_api->query->search) {
      $posts = $json_api->introspector->get_posts(array(
        's' => $json_api->query->search
      ));
    } else {
      $json_api->error("Include 'search' var in your request.");
    }
    return $this->posts_result($posts);
  }
  
  public function get_date_index() {
    global $json_api;
    $permalinks = $json_api->introspector->get_date_archive_permalinks();
    $tree = $json_api->introspector->get_date_archive_tree($permalinks);
    return array(
      'permalinks' => $permalinks,
      'tree' => $tree
    );
  }
  
  public function get_category_index() {
    global $json_api;
    $args = null;
    if (!empty($json_api->query->parent)) {
      $args = array(
        'parent' => $json_api->query->parent
      );
    }
    $categories = $json_api->introspector->get_categories($args);
    return array(
      'count' => count($categories),
      'categories' => $categories
    );
  }
  
  public function get_tag_index() {
    global $json_api;
    $tags = $json_api->introspector->get_tags();
    return array(
      'count' => count($tags),
      'tags' => $tags
    );
  }
  
  public function get_author_index() {
    global $json_api;
    $authors = $json_api->introspector->get_authors();
    return array(
      'count' => count($authors),
      'authors' => array_values($authors)
    );
  }
  
  public function get_page_index() {
    global $json_api;
    $pages = array();
    $post_type = $json_api->query->post_type ? $json_api->query->post_type : 'page';
    
    // Thanks to blinder for the fix!
    $numberposts = empty($json_api->query->count) ? -1 : $json_api->query->count;
    $wp_posts = get_posts(array(
      'post_type' => $post_type,
      'post_parent' => 0,
      'order' => 'ASC',
      'orderby' => 'menu_order',
      'numberposts' => $numberposts
    ));
    foreach ($wp_posts as $wp_post) {
      $pages[] = new JSON_API_Post($wp_post);
    }
    foreach ($pages as $page) {
      $json_api->introspector->attach_child_posts($page);
    }
    return array(
      'pages' => $pages
    );
  }
  
  public function get_nonce() {
    global $json_api;
    extract($json_api->query->get(array('controller', 'method')));
    if ($controller && $method) {
      $controller = strtolower($controller);
      if (!in_array($controller, $json_api->get_controllers())) {
        $json_api->error("Unknown controller '$controller'.");
      }
      require_once $json_api->controller_path($controller);
      if (!method_exists($json_api->controller_class($controller), $method)) {
        $json_api->error("Unknown method '$method'.");
      }
      $nonce_id = $json_api->get_nonce_id($controller, $method);
      return array(
        'controller' => $controller,
        'method' => $method,
        'nonce' => wp_create_nonce($nonce_id)
      );
    } else {
      $json_api->error("Include 'controller' and 'method' vars in your request.");
    }
  }
  
  protected function get_object_posts($object, $id_var, $slug_var) {
    global $json_api;
    $object_id = "{$type}_id";
    $object_slug = "{$type}_slug";
    extract($json_api->query->get(array('id', 'slug', $object_id, $object_slug)));
    if ($id || $$object_id) {
      if (!$id) {
        $id = $$object_id;
      }
      $posts = $json_api->introspector->get_posts(array(
        $id_var => $id
      ));
    } else if ($slug || $$object_slug) {
      if (!$slug) {
        $slug = $$object_slug;
      }
      $posts = $json_api->introspector->get_posts(array(
        $slug_var => $slug
      ));
    } else {
      $json_api->error("No $type specified. Include 'id' or 'slug' var in your request.");
    }
    return $posts;
  }
  
  protected function posts_result($posts) {
    global $wp_query;
    return array(
      'count' => count($posts),
      'count_total' => (int) $wp_query->found_posts,
      'pages' => $wp_query->max_num_pages,
      'posts' => $posts
    );
  }
  
  protected function posts_object_result($posts, $object) {
    global $wp_query;
    // Convert something like "JSON_API_Category" into "category"
    $object_key = strtolower(substr(get_class($object), 9));
    return array(
      'count' => count($posts),
      'pages' => (int) $wp_query->max_num_pages,
      $object_key => $object,
      'posts' => $posts
    );
  }
  
}

?>
