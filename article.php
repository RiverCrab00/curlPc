<?php
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
$base='http://ddh.bucea.edu.cn/djzs/';
$url=$base.'index.htm';
$html=request($url,false);
$doc=phpQuery::newDocumentHTML($html); 
//$row=pq(".articleList2 ul li")->html();
$proxyArray = array();
foreach (pq('.articleList2 ul li', $doc) as $liOne) {
	$proxyOne = array();
	foreach (pq('a', $liOne) as $aOne) {
	  $a = pq($aOne)->text();
	  $href=$aOne->getAttribute('href');
	  $proxyOne['href'] = $base.$href;
	  $proxyOne['content']=getContent($proxyOne['href']);
	  $proxyOne['title'] = trim($a);
	}
	foreach (pq('span', $liOne) as $spanOne) {
		$span = pq($spanOne)->text();
		$proxyOne['time'] = strtotime(trim($span,'[]'));
	}
$proxyArray[] = $proxyOne;
}
//return $proxyArray;

//var_dump($proxyArray[0]);
$db=new PDO('mysql:host=139.159.217.83;charset=utf8;dbname=test;','root','950612zyl');
for($i=0;$i<count($proxyArray);$i++){
	$time=time();
	$sql="insert into article(title,content,publish_time,created_time,type) value('{$proxyArray[$i]['title']}','{$proxyArray[$i]['content']}',{$proxyArray[$i]['time']},$time,2)";
	$res=$db->query($sql);
	if(!$res){
		die;
		echo '错误';
	}
}
	
	//echo $sql;die();
	//$sql2='select title from article';
	
	//$data=$res->fetchAll();
	

/*$url='http://ddh.bucea.edu.cn/fzxl/108502.htm';
$html=request($url,false);
$doc=phpQuery::newDocumentHTML($html);
$content='';
foreach (pq('p',$doc) as $one) {
 	$content.='<p>'.pq($one)->text().'</p>';
} 
echo $content;
*/

