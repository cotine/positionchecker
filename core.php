
<?php

//更新时间 2015年7月12日  更新内容dddddd
//维护到216行
//优化了seo查询 去掉了正则 修改了查询逻辑 提高效率ddddd

//更新时间 2015年7月11日  更新内容
//大大优化了sem分析效率 主要通过去掉以前的正则替换
//大大方便了以后代码维护的效率 重构了split函数 split_first_right 可以按照从要取的字符串左边 或者右边都可以开始截取ddd
//大大优化了sem准确率 主要通过修复了php自带函数tag 的漏洞 重新写了一个remove_html函数
//增加了时间函数 方便以后分析效率
//重来组织了代码库 按照 抓取 存储 分析 展示 来分类 方便找到优化的重点
//下部安排 继续优化代码效率 主要通过时间函数找出执行瓶颈 进一步优化
//下部安排 优化界面 考虑用开源的ui 比如boostrp试试



//****数据抓取****-网络函数**http抓取******
//fetch()//返回html代码 输入项为url
// kwtohtml($kw,$maxcheck=10) //返回html代码 输入url 中文关键词 及最大查询页面数 默认是前10


//****数据分析*****文本字符串处理********
// kwtoseourls($kw,$maxcheck=10) //返回url数组 有maxcheck个元素 每seo url是一个数组元素 输入为关键词
// kwtosemurls($kw) //返回数组 里面是sem广告的url 输入的是关键词**瓶颈
// checkposition($kw,$url,$maxcheck) //返回排名  输入是关键字 网址 以及最多检查名次 返回的排名  百度改变算法 一次查询最多50
// checkpositionsem($kw,$url) //返回sem排名 超过数组格式 为下线
// split_first_right($content,$left_seperator,$right_seperator)/*用单引号 可以不用转义字符 返回数组 包含url 和html，需要再处理的 输入为字符串间隔符 先分右 再分左注意*/
// split_first_left($content,$left_seperator,$right_seperator)/* 先分左边  再分右边*/
// remove_html($html)//输入是带有<> 文本和html标签 返回是尖括号外面的内容 注意只能包含一段文本

// $t1 = microtime(true);带个 true 参数, 返回的将是一个浮点类型.
// ... 执行代码 ...
// $t2 = microtime(true);
// echo '<p>第一步 t2到t1 耗时'.round($t2-$t1,3).'秒<p>';
// echo '<p>第二步 t3到t2 耗时'.round($t3-$t2,3).'秒<p>';


//****数据储存*********txt文件操作**********
// texttokw($textstr)  //获得文本中的关键词及运行频率及检查排名范围  输入的是text字符串 输出是数组  texttokw[0]为url texttokw[1]为运行状态  texttokw[2]查询范围   后面是检查关键词
// checkrunstat($filename) //更新txt文件中第一行中的*5/56* 已经运行天数 并返回还有多少天可以执行 如果为0为执行 同时把原始文本中的存入textstr变量
// storekwsurl($filename,$kws,$url,$runstat="1/1",$maxcheck=100) //更新txt文件，更新里面的关键词url 以及运行状态和检查的排名



//*****数据展示*******发信***图表处理******
//array_sort($arr,$keys,$type='asc'){ //2维数组排序输入是2位关联数组 及排序的主键 默认是升序 降序为des
// kwstosemurlscombination($kws,$maxcheck=10,$maxreturn=10) //输入关键词数组，及检查排名 返回覆盖率前列的结果 以关联数组形势averposition
// urlscombine($urlss,$maxreturn=10) //返回2维度数组 有maxcheck个元素,键名是网址，是2维的一个是平均排名一个是覆盖度 每隔url是一个数组元素 输入为关键词
// filterdatabydateandurl($filename,$website,$checkfreq="daily",$datalen="7") //返回2维数组（指定网址给定时间段历史排名及覆盖率） 出入的是text字符串 输出是数组第一个为url后面为关键词


//send_mail("cotine@163.com","test","S<font color=red>mt</font>p\r认证</br>的");


function fetch($pageurl) { 
//返回字符串html 输入url
	$ch = curl_init(); 
	$timeout = 5; 
	curl_setopt ($ch, CURLOPT_URL, "$pageurl"); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)"); 
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
	$content = curl_exec($ch);
	if ($content === false)
		exit(' Connection error: ' . curl_error($ch));
	curl_close($ch); 
	return $content;
}

function kwtohtml($kw,$maxcheck=10) {
//返回字符串html代码 输入字符串中文关键词 及最大查询页面数 默认是前10
//需要调用 function fetch 
	$keywords=$kw;	
	$enkeywords=urlencode($keywords);
	$pageURL="http://115.239.211.112/s?word=$enkeywords&rn=$maxcheck"; 
	$contents=fetch($pageURL); /*抓取页面*/
	return 	$contents;	/*返回html代码*/	
}


function remove_html($html){
//返回html中的可见文本 输入带有html<> 各种标签的字符串
	$html=strip_tags($html);
	$ps1=strpos($html, ">");//第一次>出现的位置
	$ps2=strpos($html, "<");//第一次<出现的位置
	if ($ps1!==false) {//如果字符串中包含>标签
        if ($ps2!==false) {//字符串中同时有<   >
	        $html=substr($html,$ps1+1,$ps2-1);
	        return $html;
        }
        else//字符串中只有>标签
			$html=substr($html,$ps1+1);
			return $html;
    }
	elseif ($ps2!==false){ //字符串中只有<
		$html=substr($html,0,$ps2-1);
	    return $html;
	}
	else //本身就是纯字符串
		return $html;//如果字符串不包含html标签 结果为本身
}


function split_first_right($content,$left_seperator,$right_seperator){
//返回数组  按照输入的左右分隔符 先定位右分隔符 如果没有找到返回空数组  输入为字符串
//使用了''单引号输入 html左右分隔符 就再也不用注意转义字符了
	if ( (strpos($content,$left_seperator)) and (strpos($content,$right_seperator))  )	{
		//起码找到一组复合条件的元素
		$split_results=explode($right_seperator,$content);//返回第一个分割后的数组
		array_pop($split_results);//去掉最后一个元素 因为是先右 后左 保留第一个 每一个元素都包含必须要信息	
		foreach ($split_results as $rs)			{	
			$rs1=explode($left_seperator,$rs);//再根据左侧分隔符切分
			if (count($rs1)>0) //如果查找左边分隔符 
				$urls[]=end($rs1);//取最后一个元素
		}
		return @$urls;
	}
	else//如果找不到就返回一个空数组
		return array();
}

function split_first_left($content,$left_seperator,$right_seperator){
//返回数组  按照输入的左右分隔符 先定位右分隔符 如果没有找到返回空数组   输入为字符串
//使用了''单引号输入 html左右分隔符 就再也不用注意转义字符了
	if ( (strpos($content,$left_seperator)) and (strpos($content,$right_seperator))  )	{
		$split_results=explode($left_seperator,$content);//返回数数组
		array_shift($split_results);//去掉第一个元素 每一个元素都包含必须要信息	
		foreach ($split_results as $rs)		{	
				$rs1=explode($right_seperator,$rs);//再根据右侧分隔符切分
				if (count($rs1)>0) //如果查找到
					$urls[]=$rs1[0];//取第一个元素
		}
		return @$urls;
	}
	else//如果找不到就返回一个空数组
		return array();
}
	

function htmltoseourls($contents) { 
//返回数组 里面是所有的seo url 有maxcheck个元素  输入为serp
//需要调用   remove_html 经过优化1000分析耗时0.4秒之前1.1秒
	$splist_results=split_first_right($contents,'<span class=','class="c-tools" id="tools_');
	//seo 结果为<span class="g">www.baidu.com</span><div class="c-tools" id="tools_
	foreach ($splist_results as $rs){
			$rs=remove_html($rs);			//提取html中的文本 有可能包含空格及日期	
			$rs=trim($rs);			//小心www.baidu.com 前后都会有空格 trim去除两段空格 但是url和时间中间的空格不会去掉
			$rs1=explode("/",$rs);			//* 再去掉域名2级目录/后面的部分*/		
			$rs1=explode(" ",$rs1[0]);			/* 再去掉url和时间部分 取前面的部分*/		
			$urls[]=$rs1[0];					 					
	}
	return 	$urls;	/*url排名*/	
}
		
function kwtoseourls($kw,$maxcheck=10) { 
//返回数组 里面是所有的seo url 有maxcheck个元素  输入为字符串 seo关键词
//需要调用 kwtohtml    htmltoseourls经过优化1000分析耗时0.4秒之前1.1秒
	$contents=kwtohtml($kw,$maxcheck);
	$urls=htmltoseourls($contents);
	return 	$urls;	/*url排名*/	
}



function kwtosemurls($kw) { 
//返回数组 里面是sem广告的url 输入的是关键词
//需要引用 function kwtohtml fetch  remove_html split_first_right
	$contents=kwtohtml($kw);
	//处理白色背景广告 分隔符  <div id=\"tools_30" 先右    后左 
	$left_urls_white=split_first_right($contents,'"><span class="','</span><div id="tools_30');

	 //处理左边蓝色背景广告 分隔符  </span></a><div id="tools_40先右 后左 
	$left_urls_blue=split_first_right($contents,'"><span class="','</span></a><div id="tools_40');

	 //处理右边广告 分隔符 先右 <div id=\"tools_" 后左 
	 $right_urls=split_first_right($contents,'<font size="-1"','</font></a><div id="tools_');

	$urlss=array_merge($left_urls_blue,$left_urls_white,$right_urls);//合并3个数组
	$urlss=array_filter($urlss);//删除空数组
	foreach ($urlss as $url)	{
		 $url=remove_html($url);//去除html
		 $url1=explode("&",$url);//去除时间 取&nbps 前面的部分 如果不存在返回当前变量
		 $urls[]=$url1[0];
	}
	return @$urls;  //返回urls 有可能没有任何结果 素以要用@
}


function checkposition($kw,$url,$maxcheck=10) {
//返回排名  输入为 关键词 查询url 及最大排名 maxcheck为10整数倍 最大50 百度改变算法 一次查询最多50
//调用 kwtohtml    	
	if ($maxcheck>50)	{   // 百度改变算法 一次查询最多50 如果输入的值大于50 则自动变为50 		
		$maxcheck=50; 
	}
	sleep(mt_rand(0,1));/*随机1到3秒查一次*/
	$contents=kwtohtml($kw,$maxcheck);
	if (stripos( $contents,$url)==false){//如果源代码中找不到查询的url
		$pm=$maxcheck."+";
		return  $pm;	/*url排名*/	
	}
	$urls=htmltoseourls($contents);/*抓取页面提取所有seo url*/	
	$ii=0;
	foreach ($urls as $seourl)	{	 
		if (stripos( $seourl,$url)!==false) 
			break;     /*如果找到了就 终止循环 注意stripos找到 有可能等于0 所以要用!==false 确定他找到了*/
		$ii++;
	}
	if ($ii<$maxcheck and $ii<count($urls))  {
	 //如果查询到的排名 比最大查询范围小 同时比返回结果小
		$pm=$ii;
	}
	else	//结果中找不到目标url
		$pm=$maxcheck."+";
	return  $pm;	/*url排名*/		
}


function checkpositionsem($kw,$url) //返回排名sem 输入关键词和url
	{ 
		sleep(mt_rand(0,1));/*随机1到3秒查一次*/
		$urls=kwtosemurls($kw);/*//返回数组 里面是sem广告的url 输入的是关键词*/	
		$i=1;
		$maxsemurl=count($urls);
		if (is_array($urls))
			{
				foreach ($urls as $semurl)
					{	 
					$semurl=strip_tags($semurl);			/*已经抓到 直接使用之前先消毒 去掉url中加粗的字体*/					
					$semurl=explode("/",$semurl);			/* 再去掉\后面的部分*/			
				
					if (stripos( $semurl[0],$url)!==false) break;          /*如果找到了就 终止循环 注意stripos找到 有可能等于0 所以要用!==false 确定他找到了*/
				   /*echo $seourl."<br>";
					fwrite($fp,$semurl . "\r\n");*/
					$i++;
					}
					
				if ($i<$maxsemurl)   /*$ii<$maxcheck 是查询的结果*/
					$pm=$i;
				else	//echo "could not find your url in ".$maxcheck."<br>" ;/*url排名*/	
					$pm="Keywords offline";

				  
			}
		else
			$pm="Keywords offline";
	
		return 	$pm;	/*url排名*/	

	}




function texttokw($textstr)  //获得文本中的关键词及运行频率及检查排名范围  输入的是text字符串 输出是数组  texttokw[0]为url texttokw[1]为运行状态  texttokw[2]查询范围   后面是检查关键词
	{	

	$arr=explode("\r\n",$textstr);//检查的时候就已经把text放入分行存入数组
	preg_match("/(\d*\/\d*)\*(.*?)\|(\d{1,3})/",$arr[0],$matches);//取出 运行状态 $matches[1]=21/2   网址 $matches[2]=www.sephora.cn  查询范围 $matches[3]=3 	
	$matches[2]=str_replace("http://","",$matches[2]);
	$matches[2]=str_replace("www.","",$matches[2]);
	$arr[0]=$matches[3];//查询范围放数组第2个
	array_unshift($arr,$matches[1]);//运行状态
	array_unshift($arr,$matches[2]);//url放数组最前面
	return $arr;
	}


	

function checkrunstat($filename)//更新txt文件中第一行中的*5/56* 已经运行天数 并返回还有多少天可以执行 如果为0为执行 同时把原始文本中的存入textstr变量
	{
		$textstr=file_get_contents($filename);
		$arr=explode("\r\n",$textstr);//检查的时候就已经把text放入分行存入数组
		preg_match("/(\d*\/\d*)\*(.*?)\|(\d{1,3})/",$arr[0],$matches);//取出 运行状态 $matches[1]=21/2   网址 $matches[2]=www.sephora.cn  查询范围 $matches[3]=3 
		$runstat=explode("/",$matches[1]);//$matches[1]=1/3,保存运行时间到runstat
		$rundays=(int)$runstat[0];//已经运行的天数保存在$runstat[0] 分割出来字符串后要变成整数
		$runfrequence=(int)$runstat[1]; //运行间隔天数 $runstat[1]分割出来字符串后要变成整数
		$runstat[0]++;//运行天数加一 字符串可以加1变成整数
		//$runstat[1]=$runfrequenceset;每隔多少天允许一次
		$newrunstat=$runstat[0]."/".$runstat[1];//新运行状态3/6 已经运行3天 每6天运行一次
		$oldrunstat=$matches[1];
		$newtextstr=str_replace($oldrunstat,$newrunstat,$textstr);
		file_put_contents($filename,$newtextstr);//更新txt中允许状态
		if ($rundays%$runfrequence==0) $leftdays=0;
		else
		$leftdays=$runfrequence-$rundays%$runfrequence;
		return $leftdays;//还差几天可以运行
	}


function storekwsurl($filename,$kws,$url,$runstat="1/1",$maxcheck=100) //更新txt文件，更新里面的关键词url 以及运行状态和检查的排名
	{
		$firstline="*$runstat*$url|$maxcheck";
		$restlines=implode("\r\n",$kws);
		$textst=$firstline."\r\n".$restlines;
		file_put_contents($filename,$textst);//更新txt中允许状态

	}



function array_sort($arr,$keys,$type='asc')
	{ //2维数组排序输入是2位关联数组 及排序的主键 默认是升序 降序为des
		$keysvalue = $new_array = array();
		foreach ($arr as $k=>$v){
			$keysvalue[$k] = $v[$keys];
		}
		if($type == 'asc'){
			asort($keysvalue);
		}else{
			arsort($keysvalue);
		}
		reset($keysvalue);
		foreach ($keysvalue as $k=>$v){
			$new_array[$k] = $arr[$k];
		}
		return $new_array; 
	}


function filterdatabydateandurl($filename,$website,$checkfreq="daily",$datalen="7")//返回2维数组（指定网址给定时间段历史排名及覆盖率） 出入的是text字符串 输出是数组第一个为url后面为关键词
	{	//hourly///monthly
		//		$finallines1[$i]["checkfreq"]=$finalline["checkfreq"];
				//	$finallines1[$i]["url"]=$finalline["url"];
				//	$finallines1[$i]["visibility"]=$finalline["visib"];
				//	$finallines1[$i]["averposition"]=$finalline["avgpo"];
		//$filename='urlresult.txt';
		$textstr=file_get_contents($filename);
		$eachrequests=explode("*\r\n",$textstr);//检查的时候就已经把text放入分行存入数组
		$m=0;
		//$url="sephora";
		//$checkfreq="daily";
		array_pop($eachrequests);//切割后的最后一个元素是空
		//print_r($eachrequests);

		foreach ($eachrequests as $eachrequest)  {				
				$eachlines=explode("\r\n",$eachrequest);
				foreach($eachlines as $eachline){
					if (is_numeric(strpos($eachline,$website))){							
						$resultline=$eachline;
					}
				}
				$dateinfo=end($eachlines);		
				$r=explode(",",$resultline);
				$url=$r[0];
				$visib=$r[2];
				$avgpo=$r[1];
				$filterlines[$m]["dateinfo"]=$dateinfo;
				preg_match("/Position Checked on (\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/",$dateinfo,$matches);
				$filterlines[$m]["url"]=$url;
				$filterlines[$m]["visib"]=$visib;
				$filterlines[$m]["avgpo"]=$avgpo;
				$filterlines[$m]["year"]=$matches[1];
				$filterlines[$m]["month"]=$matches[2];
				$filterlines[$m]["date"]=$matches[3];
				$filterlines[$m]["hour"]=$matches[4];
				$filterlines[$m]["min"]=$matches[5];			
				$m++;	
		}
		if ($checkfreq=="daily")	$time="date";
			elseif ($checkfreq=="hourly")	$time="hour";
				elseif ($checkfreq=="monthly")	$time="month";
		array_sort($filterlines,$time,$type='asc');
		$m=0;
		foreach ($filterlines as $filterline){         //先排序
			$finallines[$m]["checkfreq0"]=$filterline[$time];
			$finallines[$m]["url"]=$filterline["url"];
			$finallines[$m]["year"]=$filterline["year"];
			$finallines[$m]["month"]=$filterline["month"];
			$finallines[$m]["hour"]=$filterline["hour"];
			$finallines[$m]["visib"]=$filterline["visib"];
			$finallines[$m]["avgpo"]=$filterline["avgpo"];
			$m++;
		}
		$i=0;
		$finallines1=array();
		foreach ($finallines as $finalline){

			if (@$finallines1[$i-1]["checkfreq0"]<>$finalline["checkfreq0"])   //去掉重复的日期
			{
				$finallines1[$i]["checkfreq0"]=$finalline["checkfreq0"];
				$finallines1[$i]["checkfreq"]=$finalline["year"]."-".$finalline["month"]."-".$finalline["checkfreq0"];
				$finallines1[$i]["month"]=$finalline["month"];
				$finallines1[$i]["year"]=$finalline["year"];
				$finallines1[$i]["hour"]=$finalline["hour"];
				$finallines1[$i]["url"]=$finalline["url"];
				$finallines1[$i]["visibility"]=$finalline["visib"];
				$finallines1[$i]["averposition"]=$finalline["avgpo"];
				$i++;				
			}			
		}
		//调整时间格式
		return $finallines1;
}














function urlscombine($urlss,$maxreturn=10){ //返回2维度数组 有maxcheck个元素,键名是网址，是2维的一个是平均排名一个是覆盖度 每隔url是一个数组元素 输入为关键词

	//$urlss[0]=array("www.baidu.com","www.163.com,"www.sohu.com"5);
	//$urlss[1]=array("www.qq.com","www.baidu.com","www.sohu.com"2);
	//$urlss[2]=array("www.qq.com","www.jd.com","www.sohu.com"7);
	$i=0;//关键词个数
	$totalposition=array();
	$rankedtimes=array();
	foreach ($urlss as $urls)
	{
		$j=1;//排名名次
		foreach ($urls as $url)
			{		
				@$totalposition[$url]+=$j;//排名总和
				@$rankedtimes[$url]+=1;//出现次数总和
				$j++;

			}	
		$i++;
	}
	$m=0;
	foreach ($totalposition as $key=>$value)
		{		
			$averposition[$key]=round($value/$rankedtimes[$key], 1);//平均排名
			$visilibty[$key]=round($rankedtimes[$key]/$i, 2);//覆盖度
			$combineresults[$m]=array("url"=>$key,"averposition"=>$averposition[$key],"visibility"=>$visilibty[$key]);
			$m++;
		}	
	$combineresults = array_sort($combineresults,'visibility',"des");
	$combineresults=array_slice($combineresults,0,$maxreturn);
	return $combineresults;
	// [2] => Array ( [url] => www.sohu.com [averposition] => 4.67 [visibility] => 1 ) 
	//[3] => Array ( [url] => www.qq.com [averposition] => 1 [visibility] => 0.67 )
	// [4] => Array ( [url] => www.jd.com [averposition] => 2 [visibility] => 0.33 )
	//[0] => Array ( [url] => //www.baidu.com [averposition] => 2 [visibility] => 0.67 ) 
	//[1] => Array ( [url] => www.163.com [averposition] => 7 //[visibility] => 0.33 ) )

}




function kwstourlscombination($kws,$maxcheck=10,$maxreturn=10){//输入关键词数组，及检查排名 返回覆盖率前列的结果 以关联数组形势averposition  visibility
	$i=0;
	foreach ($kws as $kw){
		$urlss[$i]=kwtoseourls($kw,$maxcheck);
		$i++;
	}

	$rs=urlscombine($urlss,$maxreturn);
	$k=0;
	foreach ($rs as $r){
		$rs[$k]=$r;
		$k++;
	
	}
	return $rs;
}
function kwstosemurlscombination($kws,$maxcheck=10,$maxreturn=10)
{//输入关键词数组，及检查排名 返回覆盖率前列的结果 以关联数组形势averposition  visibility
	$i=0;
	foreach ($kws as $kw){
		if(count(kwtosemurls($kw))>0) {
			$urlss[$i]=kwtosemurls($kw);
			$i++;
		}
	}
	$rs=urlscombine($urlss,$maxreturn);
	$k=0;
	foreach ($rs as $r){
		$rs[$k]=$r;
		$k++;	
	}
	return $rs;
}

function benchmarkhistorysummary($filename,$maxreturn=10){//输入benmarkhistory文件名，返回以关联数组 url avgposition  avgvisibility 以avgvisibility降序
	$textstr=file_get_contents($filename);
	//$filename="./data/seobenchmark.txt";
	$eachrequests=explode("*\r\n",$textstr);//检查的时候就已经把text放入分行存入数组
	array_pop($eachrequests);//切割后的最后一个元素是空
	$i=0;
	//对每行的url数据先合并  求平均 再排序
	foreach ($eachrequests as $eachrequest)  {				
		$eachlines=explode("\r\n",$eachrequest);
		array_pop($eachlines);//切割后的最后一个元素是时间
		foreach($eachlines as $eachline){
			$urlinfo=explode(",",$eachline);//$urlinfo[0]=url $urlinfo[1]=aveposition  $urlinfo[2]=visibility
			@$totalurlfindtimes[$urlinfo[0]]+=1;
			@$totalposition[$urlinfo[0]]+=$urlinfo[1];
			@$totalvisibility[$urlinfo[0]]+=$urlinfo[2];
		}
	}
	$m=0;
	foreach ($totalurlfindtimes as $key=>$value){
		$rs[$m]["url"]=$key;
		//$rs[$m]["totalposition"]=$totalposition[$key];
		//$rs[$m]["totalvisibility"]=$totalvisibility[$key];
		//$rs[$m]["totalurlfindtimes"]=$value;
		$rs[$m]["avgposition"]=round($totalposition[$key]/$value,1);//取1位小数
		$rs[$m]["avgvisibility"]=round($totalvisibility[$key]/$value,1);
		$m++;
	}
	array_sort($rs,"avgvisibility",$type='des');
	$r=array_slice($rs,0,$maxreturn);
	return $r;

}



function send_mail($to, $subject = 'No subject', $body) //send_mail("cotine@163.com","test","S<font color=red>mt</font>p\r认证</br>的");
	{
        $loc_host = "test";            //发信计算机名，可随意
        $smtp_acc = "shou@netboosterasia.cn"; //Smtp认证的用户名，类似fuweng@im286.com，或者fuweng
        $smtp_pass="net123";          //Smtp认证的密码，一般等同pop3密码
        $smtp_host="smtp.exmail.qq.com";    //SMTP服务器地址，类似 smtp.tom.com
        $from="shou@netboosterasia.cn";       //发信人Email地址，你的发信信箱地址
		$headers = "Content-Type: text/html; charset=\"utf-8\"\r\nContent-Transfer-Encoding: base64";
		$lb="\r\n";                    //linebreak
            
        $hdr = explode($lb,$headers);     //解析后的hdr
	    if($body) {$bdy = preg_replace("/^\./","..",explode($lb,$body));}//解析后的Body
				$smtp = array(
						//1、EHLO，期待返回220或者250
						array("EHLO ".$loc_host.$lb,"220,250","HELO error: "),
						//2、发送Auth Login，期待返回334
						array("AUTH LOGIN".$lb,"334","AUTH error:"),
						//3、发送经过Base64编码的用户名，期待返回334
						array(base64_encode($smtp_acc).$lb,"334","AUTHENTIFICATION error : "),
						//4、发送经过Base64编码的密码，期待返回235
						array(base64_encode($smtp_pass).$lb,"235","AUTHENTIFICATION error : "));
				//5、发送Mail From，期待返回250
				$smtp[] = array("MAIL FROM: <".$from.">".$lb,"250","MAIL FROM error: ");
				//6、发送Rcpt To。期待返回250
				$smtp[] = array("RCPT TO: <".$to.">".$lb,"250","RCPT TO error: ");
				//7、发送DATA，期待返回354
				$smtp[] = array("DATA".$lb,"354","DATA error: ");
				//8.0、发送From
				$smtp[] = array("From: ".$from.$lb,"","");
				//8.2、发送To
				$smtp[] = array("To: ".$to.$lb,"","");
				//8.1、发送标题
				$smtp[] = array("Subject: ".$subject.$lb,"","");
				//8.3、发送其他Header内容
				foreach($hdr as $h) {$smtp[] = array($h.$lb,"","");}
				//8.4、发送一个空行，结束Header发送
				$smtp[] = array($lb,"","");
				//8.5、发送信件主体
				if($bdy) {foreach($bdy as $b) {$smtp[] = array(base64_encode($b.$lb).$lb,"","");}}
				//9、发送“.”表示信件结束，期待返回250
				$smtp[] = array(".".$lb,"250","DATA(end)error: ");
				//10、发送Quit，退出，期待返回221
				$smtp[] = array("QUIT".$lb,"221","QUIT error: ");
				//打开smtp服务器端口
				$fp = @fsockopen($smtp_host, 25);
				if (!$fp) echo "Error: Cannot conect to ".$smtp_host."
		";
				while($result = @fgets($fp, 1024)){if(substr($result,3,1) == " ") { break; }}
        
        $result_str="";
        //发送smtp数组中的命令/数据
        foreach($smtp as $req){
                //发送信息
                @fputs($fp, $req[0]);
                //如果需要接收服务器返回信息，则
                if($req[1]){
                        //接收信息
                        while($result = @fgets($fp, 1024)){
                                if(substr($result,3,1) == " ") { break; }
                        };
                        if (!strstr($req[1],substr($result,0,3))){
                                $result_str.=$req[2].$result."
		";
                        }
                }
        }
        //关闭连接
        @fclose($fp);
        return $result_str;
    }



// $t1 = microtime(true);带个 true 参数, 返回的将是一个浮点类型.
// ... 执行代码 ...
// $t2 = microtime(true);
// echo '<p>第一步 t2到t1 耗时'.round($t2-$t1,3).'秒<p>';
// echo '<p>第二步 t3到t2 耗时'.round($t3-$t1,2).'秒<p>';




    ?>
