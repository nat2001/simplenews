<?php
/*
	simplenews.php - V1.01
	Nathan J. Dane, 2020.
	Returns a BBC News page as an array
	
	Layout (TBC):
	Short Title
	Description
	URL
	Area (e.g. UK)
	Summary
	paragraphs(tab seperated)
	
*/

function getNews($url,$limit)
{
	$html = file_get_contents($url);
	$html = str_get_html($html);	// Under NO circumstances should $html be overwritten. It's here to stay.
	if ($html===false) return false;
	
	$URL=$html->find("meta[property=og:url]",0);	// URL. The BBC try to hide the AV URL behind a legitamite one, 
	$URL=$URL->content;								// So we have to take drastic measures to remove them
	$URL=htmlspecialchars_decode($URL);
	if(!strncmp($URL,"https://www.bbc.com/news/av/",28)) // Don't even try AV pages
	{
		echo "Skipped: AV Story\r\n";
		return false;
	}
	
	$stitle=$html->find("meta[property=og:title]",0);	// Short title
	$stitle=$stitle->content;
	$stitle=htmlspecialchars_decode($stitle);
	
	$ltitle=$html->find("title",0)->plaintext;	// Long title
	$ltitle=htmlspecialchars_decode($ltitle);
	
	$desc=$html->find('meta[property=og:description]',0);	// Description
	$desc=$desc->content;
	$desc=htmlspecialchars_decode($desc);
	
	$area=$html->find('meta[property=article:section]',0);	// Area
	$area=$area->content;
	$area=htmlspecialchars_decode($area);
	
	$intro=$html->find('p[class=story-body__introduction]',0);	// Summary
	if($intro!==false)
		$intro=htmlspecialchars_decode($intro->plaintext);
	
	$i=0;
	$found=false;
	$other=false;
	foreach ($html->find('p') as $para)
	{
		if (strpos($para,"<strong>"))
		{
			$found=true;
			$other=true;
		}
		if($i<$limit && $found==true)
		{
			if(($intro===false && $i==0) || ($other && $i==0))
				$intro=$para->plaintext;
			else
				$paragraph[]=$para->plaintext;
			$i++;
		}
		if (strpos($para,"introduction"))
			$found=true;
	}
	
	$stitle=fix_text($stitle);
	$ltitle=fix_text($ltitle);
	$desc=fix_text($desc);
	$intro=fix_text($intro);
	
	if (!strncmp($stitle,"In pictures:",12)) return false;
	if (!isset($paragraph)) return false;
	if ($paragraph=='') return false;
	if (!$paragraph) return false;
	
	return array($stitle,$ltitle,$desc,$url,$area,$intro,$paragraph);
	//				1		2		3	4	5		6		7
}
?>