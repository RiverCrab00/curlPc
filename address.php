<?php
set_time_limit(300);
//curl获得页面
function request($url,$https=true,$proxy=false,$method='get',$data=null){
    //1.初始化
    $ch = curl_init($url);
    //2.设置curl
    //返回数据不输出
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //开启支持gzip
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    //设置超时限制
    // curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    //根据url设置referer
    $host = parse_url($url);
    $host = $host['host'];
    curl_setopt($ch, CURLOPT_REFERER, 'http://'.$host);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.152 Safari/537.36');
    //确认是否开启代理
    if($proxy === true){
      // $proxyArray = $this->getProxy();
      // $proxyOne = $proxyArray[rand(1,(count($proxyArray)-1))];
      // // file_put_contents('./dbug',json_encode($proxyOne));
      // //开启代理
      // curl_setopt($ch, CURLOPT_PROXY, $proxyOne[0]);
      // curl_setopt($ch, CURLOPT_PROXYPORT,$proxyOne[1]);
      curl_setopt($ch, CURLOPT_PROXY, '61.191.41.130');
      curl_setopt($ch, CURLOPT_PROXYPORT,80);
    }
    //满足https
    if($https === true){
      //绕过ssl验证
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    //满足post
    if($method === 'post'){
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    //3.发送请求
    $content = curl_exec($ch);
    //4.关闭资源
    curl_close($ch);
    return $content;
  }
//引入phpquery
require './phpQuery/phpQuery.php';
//引入配置文件
//require './conf/config.php';
function getContent($url){
	$html=request($url,false);
	$doc=phpQuery::newDocumentHTML($html);
	$content='';
	foreach (pq('.article p',$doc) as $one) {
	 	$content.='<p>'.pq($one)->text().'</p>';
	}
	return $content;
}
function getC($str){
  preg_match_all('/[\x{4e00}-\x{9fff}]+/u', $str, $matches);
  return $str = join('', $matches[0]);
}
$base='http://www.cnu.edu.cn/fwzn/bgdh/jgsw/';
$url=$base.'index.htm';
$html=request($url,false);
$doc=phpQuery::newDocumentHTML($html); 
//$row=pq(".articleList2 ul li")->html();
echo '<pre>';
$hrefs=[];
foreach (pq('.jxjy_pyfx ul li a', $doc) as $key => $value) {
  $hrefs[]=$value->getAttribute('href');
}
$db=new PDO('mysql:host=139.159.217.83;charset=utf8;dbname=test;','root','950612zyl');
$new=array_slice($hrefs,0,4);
$old=array_slice($hrefs,4);
/*foreach($new as $v){
  $depart=$base.$v;
  $html=request($depart,false);
  $doc=phpQuery::newDocumentHTML($html);
  foreach (pq('tbody tr:gt(0)',$doc) as $key => $value) {
    foreach (pq('td div',$value) as $key=>$v) {
      $info['category']=getC(pq('.artic_t_1 h2',$doc)->text());
      if($key==0){
        $info['depart']=pq($v)->text();
      }elseif($key==1){
        $info['address']=pq($v)->text();
      }else{
        $info['tel']=pq($v)->text();
      }
    }
    $sql="insert into address_book(depart,address,tel,category) value('{$info['depart']}','{$info['address']}','{$info['tel']}','{$info['category']}')";
      $res=$db->query($sql);
    if(!$res){
      echo '错误';
      die;
    }
  }
}*/
foreach ($old as $url) {
  $depart=$base.$url;
  $html=request($depart,false);
  $doc=phpQuery::newDocumentHTML($html);
  foreach (pq('tbody tr:gt(0)',$doc) as $value) {
    foreach(pq('td',$value) as $key=>$v){
      $info['category']=getC(pq('.artic_t_1 h2',$doc)->text());
      if($key==0){
        $info['depart']=pq($v)->text();
      }elseif($key==1){
        $info['address']=pq($v)->text();
      }else{
        $info['tel']=pq($v)->text();
      }
    }
    $sql="insert into address_book(depart,address,tel,category) value('{$info['depart']}','{$info['address']}','{$info['tel']}','{$info['category']}')";
    $res=$db->query($sql);
    if(!$res){
      echo '错误';
      die;
    }
  }
}
die('全部完成');


