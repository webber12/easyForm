<?php
/* author webber   web-ber12@yandex.ru */
// version 0.1
// визуальное создание и редактирование простых форм на основе сниппета eForm
// создать модуль с названием easyForm и кодом 
// require_once MODX_BASE_PATH."assets/modules/easyForm/module.easyForm.php";

if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}

$moduleid=(int)$_GET['id'];
$theme=$modx->config['manager_theme'];

$forms_table=$modx->getFullTableName('forms');
$flds_table=$modx->getFullTableName('form_fields');

//создаем таблицу форм, если ее нет
$sql="
CREATE TABLE IF NOT EXISTS ".$forms_table." (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `sort` int(5) NOT NULL DEFAULT '0',
  `title` text NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
$q=$modx->db->query($sql);

//создаем таблицу полей форм, если ее нет
$sql="
CREATE TABLE IF NOT EXISTS ".$flds_table." (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `parent` int(5) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `type` int(2) NOT NULL DEFAULT '0',
  `value` text NOT NULL DEFAULT '',
  `sort` int(5) NOT NULL DEFAULT '0',
  `required` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
$q=$modx->db->query($sql);



$type=array(//доступные типы полей в форме
	"1"=>"Строка",
	"2"=>"Текст",
	"3"=>"Email",
	"5"=>"Список (select)",
	"6" =>"Флажок (radio)",
	"7"=>"Переключатель (checkbox)",
	"8"=>"Файл",
	"9"=>"Мультиселект",
	"10"=>"Скрытое поле hidden"
);


$info='';

$out.='
<!doctype html>
<html lang="ru">
<head>
	<title>Управление заявками</title>
	<link rel="stylesheet" type="text/css" href="media/style/'.$theme.'/style.css" />
<style>
table{width:100%;}
table td{padding:2px 5px !important;border:solid 1px white;height:38px;vertical-align:middle !important;}
table thead td{color:white;height:25px;
	border: 1px solid #658f1a;
	background: none repeat scroll 0 0 #66901b;
	text-shadow: 0px -1px 0px #2B5F0C;
		border-radius:5px 5px 0 0;
		-moz-border-radius:5px 5px 0 0;
		-webkit-border-radius:5px 5px 0 0;
		-ms-border-radius:0;
		background:-moz-linear-gradient(#8aae4b, #66901b);
		background:-webkit-gradient(linear, 0 0, 0 100%, from(#8aae4b), to(#66901b));
		background:-o-linear-gradient(#8aae4b, #66901b);
}
input[type="text"]{width:300px;margin-bottom:5px !important;}
select{width:307px;margin-bottom:5px !important;}
input[type="text"].small{width:35px;}
p.info{color:#008000;}
</style>
</head>
<body>
';

$out.='<h1>Управление формами</h1>';
$zagol='Список доступных форм';
$info_type=1;

function escape($a){
	global $modx;
	return $modx->db->escape($a);
}

if(isset($_POST['delform1'])){//удаление формы
	$query=$modx->db->query("DELETE FROM ".$forms_table." WHERE id=".(int)$_POST['delform1']);
	if($query){$info='<p class="info">Форма успешно удалена</p>';}
}
if(isset($_POST['delpole1'])){//удаление поля
	$query=$modx->db->query("DELETE FROM ".$flds_table." WHERE id=".(int)$_POST['delpole1']);
	if($query){$info='<p class="info">Поле успешно удалено</p>';}
}

if(isset($_POST['newformname'])&&isset($_POST['newformtitle'])){//добавляем новую форму
	$newformname=escape($_POST['newformname']);
	$newformtitle=escape($_POST['newformtitle']);
	$newformemail=escape($_POST['newformemail']);
	$newformsort=1;
	$maxformsort=$modx->db->getValue($modx->db->query("SELECT MAX(sort) FROM ".$forms_table." LIMIT 0,1"));
	if($maxformsort){
		$newformsort=(int)$maxformsort+1;
	}
	$flds=array(
		'name'=>$newformname,
		'title'=>$newformtitle,
		'sort'=>$newformsort,
		'email'=>$newformemail
	);
	$query=$modx->db->insert($flds,$forms_table);
	if($query){$info='<p class="info">Форма успешно добавлена</p>';}
}

if(isset($_GET['fid'])&&isset($_GET['action'])&&$_GET['action']=='edit'){//редактирование формы
	$info_type=2;
	$zagol='Редактирование формы';
	if(isset($_POST['curformname'])&&isset($_POST['curformtitle'])){
		$curformname=escape($_POST['curformname']);
		$curformtitle=escape($_POST['curformtitle']);
		$curformemail=escape($_POST['curformemail']);
		$flds=array(
			'name'=>$curformname,
			'title'=>$curformtitle,
			'email'=>$curformemail
		);
		$query=$modx->db->update($flds,$forms_table,"id=".(int)$_GET['fid']);
		if($query){$info='<p class="info">Форма успешно изменена</p>';}
	}
	$form_info=$modx->db->getRow($modx->db->query("SELECT * FROM ".$forms_table." WHERE id=".(int)$_GET['fid']." LIMIT 0,1"));
}


//список полей формы
if(isset($_GET['fid'])&&isset($_GET['action'])&&$_GET['action']=='pole'&&!isset($_GET['pid'])){
	$info_type=3;
	$zagol='Список полей формы';
	$parent=(int)$_GET['fid'];
	$require=isset($_POST['newpolerequire'])?1:0;
	if(isset($_POST['sortpole'])){//сортируем поля
		$sortpole=$_POST['sortpole'];
		foreach($sortpole as $k=>$v){//сохраняем порядок сортировки
			$query=$modx->db->query("UPDATE ".$flds_table." SET `sort`='".(int)$v."' WHERE id=".(int)$k);
		}

	}
	if(isset($_POST['newpoletitle'])&&isset($_POST['newpoletype'])&&isset($_POST['newpolevalue'])){//добавляем новое поле
		$newpoletitle=escape($_POST['newpoletitle']);
		$newpoletype=escape($_POST['newpoletype']);
		$newpolevalue=escape($_POST['newpolevalue']);
		$newpolesort=1;
		$maxpolesort=$modx->db->getValue($modx->db->query("SELECT MAX(sort) FROM ".$flds_table." WHERE parent=".$parent." LIMIT 0,1"));
		if($maxpolesort){
			$newpolesort=(int)$maxpolesort+1;
		}
		$flds=array(
			'parent'=>$parent,
			'title'=>$newpoletitle,
			'type'=>$newpoletype,
			'value'=>$newpolevalue,
			'sort'=>$newpolesort,
			'required'=>$require
		);
		$query=$modx->db->insert($flds,$flds_table);
		if($query){$info='<p class="info">Поле успешно добавлено</p>';}
	}
}//конец список полей


//редактирование поля формы
if(isset($_GET['fid'])&&isset($_GET['action'])&&$_GET['action']=='pole'&&isset($_GET['pid'])){
	$info_type=4;
	$zagol='Редактирование поля формы';
	$parent=(int)$_GET['fid'];
	$require=isset($_POST['curpolerequire'])?1:0;
	if(isset($_POST['curpoletitle'])&&isset($_POST['curpoletype'])&&isset($_POST['curpolevalue'])){//редактируем поле
		$curpoletitle=escape($_POST['curpoletitle']);
		$curpoletype=escape($_POST['curpoletype']);
		$curpolevalue=escape($_POST['curpolevalue']);
		$flds=array(
			'title'=>$curpoletitle,
			'type'=>$curpoletype,
			'value'=>$curpolevalue,
			'required'=>$require
		);
		$query=$modx->db->update($flds,$flds_table,"id=".(int)$_GET['pid']);
		if($query){$info='<p class="info">Поле успешно изменено</p>';}
	}
	$pole_info=$modx->db->getRow($modx->db->query("SELECT * FROM ".$flds_table." WHERE id=".(int)$_GET['pid']." LIMIT 0,1"));
}



$out.='<div class="sectionHeader">'.$zagol.'</div><div class="sectionBody">';

$out.='<div class="action_info">'.$info.'</div>';

//блок вывода списка форм
if($info_type==1){
	$form_list=$modx->db->query("SELECT * FROM ".$forms_table." ORDER BY sort ASC");
	$out.='<table class="fl"><thead><tr><td>id</td><td>Имя</td><td>Описание</td><td>Email</td><td>Поля</td><td>Изменить</td><td>Удалить</td></tr></thead><tbody>';
	while($row=$modx->db->getRow($form_list)){
		$out.='<tr>
				<td>'.$row['id'].'</td><td>'.$row['name'].'</td><td>'.$row['title'].'</td><td>'.$row['email'].'</td>
				<td class="actionButtons"><a href="index.php?a=112&id='.$moduleid.'&fid='.$row['id'].'&action=pole" class="button choice"> <img src="media/style/'.$theme.'/images/icons/page_white_copy.png" alt=""> Список полей</a></td>
				<td class="actionButtons"><a href="index.php?a=112&id='.$moduleid.'&fid='.$row['id'].'&action=edit" class="button edit"> <img alt="" src="media/style/'.$theme.'/images/icons/page_white_magnify.png" > Изменить</a></td>
				<td class="actionButtons"><a onclick="document.delform.delform1.value='.$row['id'].';document.delform.submit();" style="cursor:pointer;" class="button delete"> <img src="media/style/'.$theme.'/images/icons/delete.png" alt=""> удалить</a></td>
			</tr>';
	}
	$out.='</tbody></table>';
	$out.= '<br><br>
		<form action="" method="post" class="actionButtons"> 
			Название: <br><input type="text" value="" name="newformname"><br>
			Описание: <br><input type="text" value="" name="newformtitle"><br>
			Email: <br><input type="text" value="" name="newformemail"><br><br>
			<input type="submit" value="Добавить форму">
		</form>
	';
}
// конец блока вывода списка форм


//блок редактирования формы
if($info_type==2){
	$out.= '
		<form action="" method="post" class="actionButtons"> 
			Название: <br><input type="text" value="'.$form_info['name'].'" name="curformname" size="50"><br> 
			Описание: <br><input type="text" value="'.$form_info['title'].'" name="curformtitle" size="50"><br>
			Email: <br><input type="text" value="'.$form_info['email'].'" name="curformemail" size="50"><br><br>
			<input type="submit" value="Сохранить">
		</form><br><br>
		<a href="index.php?a=112&id='.$moduleid.'">К списку форм</a>
	';
}



//блок вывода списка полей формы
if($info_type==3){
	$form_list=$modx->db->query("SELECT * FROM ".$flds_table." WHERE parent=".(int)$_GET['fid']." ORDER BY sort ASC");
	$out.='
	<form id="sortpole" action="" method="post" class="actionButtons">
		<table class="fl"><thead><tr><td>Имя</td><td>Тип</td><td>Значение</td><td>Порядок</td><td>Изменить</td><td>Удалить</td></tr></thead><tbody>';
		while($row=$modx->db->getRow($form_list)){
			$out.='
					<tr>
						<td>'.$row['title'].' '.($row['required']==1?'<b>(+)</b>':'').'</td><td> '.$type[$row['type']].' </td><td> '.nl2br($row['value']).' </td>
						<td><input type="text" name="sortpole['.$row['id'].']" value="'.$row['sort'].'" class="sort small"></td>
						<td> <a href="index.php?a=112&id='.$moduleid.'&fid='.$row['parent'].'&pid='.$row['id'].'&action=pole" class="button edit"><img alt="" src="media/style/'.$theme.'/images/icons/page_white_magnify.png" > Изменить</a> </td>
						<td> <a onclick="document.delpole.delpole1.value='.$row['id'].';document.delpole.submit();" style="cursor:pointer;" class="button delete"> <img src="media/style/'.$theme.'/images/icons/delete.png" alt=""> Удалить</a> </td>
					</tr>
			';
		}
	$out.='</tbody></table>
			<br><input type="submit" value="Сохранить порядок">
			</form>
			<br><br>
			<h2>Добавление нового поля</h2>
			<form action="" method="post" class="actionButtons"> 
			Название <br><input type="text" value="" name="newpoletitle"><br>
			Тип поля <br>
		';
	$options='';
	foreach($type as $k=>$v){
		$options.="<option value='".$k."'>".$v."</option>";
	}
	$out.='<select name="newpoletype">'.$options.'</select><br>';
	$out.='
		Значение (для типа "список","переключатель","флажок") в формате "значение==подпись" либо просто "подпись", если значение и подпись совпадают (каждый вариант - с новой строки):<br>
		<textarea name="newpolevalue"></textarea>
		<br>
		Обязательно <input type="checkbox" name="newpolerequire" value="1"><br><br>
		<input type="submit" value="Добавить поле">
		</form>
		<br><br>
		<a href="index.php?a=112&id='.$moduleid.'">К списку форм</a>
	';
}
// конец блока вывода списка полей формы


//блок редактирования поля
if($info_type==4){
	$out.='
		<form action="" method="post" class="actionButtons"> 
			Название: <br><input type="text" value="'.$pole_info['title'].'" name="curpoletitle"><br> 
			Тип: <br>
		';
	$options='';
	foreach($type as $k=>$v){
		$options.="<option value='".$k."' ".($k==$pole_info['type']?" selected=selected":"").">".$v."</option>";
	}
	$out.='<select name="curpoletype">'.$options.'</select><br>
			Значение (для типа "список","переключатель","флажок") в формате "значение==подпись" либо просто "подпись", если значение и подпись совпадают (каждый вариант - с новой строки): 
			<br>
			<textarea name="curpolevalue">'.$pole_info['value'].'</textarea><br>
			Обязательно: <input type="checkbox" value="1" name="curpolerequire" '.($pole_info['required']==1?' checked="checked"':'').'><br><br>
			<input type="submit" value="Сохранить изменения">
		</form><br><br>
		<a href="index.php?a=112&id='.$moduleid.'&fid='.$pole_info['parent'].'&action=pole">К списку полей</a>
	';
}


$out.='
<form action="" method="post" id="delform" name="delform"> 
	<input type="hidden" name="delform1" value="">
</form>
';

$out.='
<form action="" method="post" id="delpole" name="delpole"> 
	<input type="hidden" name="delpole1" value="">
</form>
';

$out.='</div></body></html>';
//выводим все в область контента модуля
echo $out;
?>