<?php
/* author webber   web-ber12@yandex.ru */
// version 0.1
// визуальное создание и редактирование простых форм на основе сниппета eForm
// создать сниппет с названием easyForm и кодом 
// return require_once MODX_BASE_PATH."assets/modules/easyForm/snippet.easyForm.php";
// пример вызова на странице
// [!easyForm? &formid=`f1`!] - где цифра после префикса f - это id формы из модуля
// остальные параметры задаются аналогично eForm
// &tpl, &reportTpl и &to задавать не нужно - они формируются автоматом из того, что задано в модуле easyForm

if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}

include_once("functions.easyForm.php");

$out='';
$formid=$params['formid'];
$eid=str_replace('f','',$formid);
$params['tpl']=makeTpl($eid);
$params['report']=makeReportTpl($eid);
$formInfo=getFormInfo($eid);
$params['to']=$formInfo['to'];
$params['subject']='Обратная связь: '.$formInfo['name'];

if($params['tpl']!=''&&$params['report']!=''){
	$out.=$modx->runSnippet("eForm",$params);
}
else{$out.='<p>Не созданы необходимые компоненты для показа формы</p>';}
return $out;
?>