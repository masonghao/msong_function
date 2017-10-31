<?php
/**
 * 一段小代码，显示目标网页图片到浏览器
 */
    $htmlCode = file_get_contents("http://ibaotu.com/s-beijing/beijing/4-91577-0-0-0-1.html");
    preg_match_all('/<img (.+?)>/', $htmlCode, $img_list);
    $img_list_html = implode(" ", $img_list[0]);
    preg_match_all('/data-url="(.+?)"/', $img_list_html, $img_url_list);
    echo '<img src="'.implode('" />'."\n".'<img src="',$img_url_list[1]).'" />'."\n";
