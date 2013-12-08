<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}

function getFormInfo($id){
	global $modx;
	$info=array();
	if(isset($id)){
		$info=$modx->db->getRow($modx->db->query("SELECT name,email FROM ".$modx->getFullTableName('forms')." WHERE id=".$id." LIMIT 0,1"));
	}
	return $info;
}

function makeTpl($id,$capcha=false){
	global $modx;
	$f='';
	if(isset($id)&&$modx->db->getRecordCount($modx->db->query("SELECT * FROM ".$modx->getFullTableName('forms')." WHERE id=".$id." LIMIT 0,1"))==1)
	{
		$forma=$modx->db->query("SELECT * FROM ".$modx->getFullTableName('form_fields')." WHERE parent=".$id." ORDER BY sort ASC");
		$forma_info=$modx->db->getRow($modx->db->query("SELECT * FROM ".$modx->getFullTableName('forms')." WHERE id=".$id." LIMIT 0,1"));
		if($modx->db->getRecordCount($forma)>0){
			$form=array();
			while($row=$modx->db->getRow($forma)){
				$form[$row['id']]['title']=$row['title'];
				$form[$row['id']]['type']=$row['type'];
				$form[$row['id']]['value']=$row['value'];
				$form[$row['id']]['required']=$row['required'];
			}

			$f='
				<div class="f_zagol">'.$forma_info['name'].'</div>
				<div class="f_description">'.$forma_info['title'].'</div>
				<p>[+validationmessage+]</p>
				<form id="f'.$id.'" action="[~[*id*]~]" method="post">
				<div class="f_form f_form'.$id.'">
			';
			foreach($form as $k=>$v){
				$req=$v['required']==1?1:0;
				$type=$v['type']==3?'email':'string';
				$f.='<div class="f_row f_row'.$k.'"><div class="f_title">'.$v['title'].' '.($req==1?'<span class="red">*</span>':'').'</div><div class="field">';
				
				switch($v['type']){
				  case 2:
					$f.="<textarea name='param".$k."' class='f_txtarea' eform='".$v['title'].":".$type.":".$req."'></textarea>";
					break;
				
				  case 5:
					$f.="<div class='selector'><select name='param".$k."' class='f_selector' eform='".$v['title']."::".$req."'>";
					$opts=explode("\n",$v['value']);
					$opt='';
					foreach($opts as $k1=>$v1){
						$v1=trim($v1);
						$arr=explode("==",$v1);
						$key=$arr[0];
						$val=isset($arr[1])&&$arr[1]!=''?$arr[1]:$arr[0];
						$opt.="<option value='".$key."'>".$val."</option>";
					}
					$f.=$opt."</select></div>";
					break;
				
				  case 6:
					$f.="<div class='radio'>";
					$opts=explode("\n",$v['value']);
					$opt='';
					foreach($opts as $k1=>$v1){
						$v1=trim($v1);
						$arr=explode("==",$v1);
						$key=$arr[0];
						$val=isset($arr[1])&&$arr[1]!=''?$arr[1]:$arr[0];
						$opt.="<label>".$val." <input type='radio' name='param".$k."' value='".$key."' ".($k1==0?"eform='".$v['title']."::".$req."'":"")."></label>";
					}
					$f.=$opt."</div>";
					break;
					
				  case 7:
					$f.="<div class='checkbox'>";
					$opts=explode("\n",$v['value']);
					$opt='';
					foreach($opts as $k1=>$v1){
						$v1=trim($v1);
						$arr=explode("==",$v1);
						$key=$arr[0];
						$val=isset($arr[1])&&$arr[1]!=''?$arr[1]:$arr[0];
						$opt.="<label>".$val." <input type='checkbox' name='param".$k."[]' value='".$key."' ".($k1==0?"eform='".$v['title']."::".$req."'":"")."></label>";
					}
					$f.=$opt."</div>";
					break;
					
				  case 8:
					$f.="<input type='file' name='param".$k."' class='f_file' eform='".$v['title'].":".$type.":".$req."'>";
					break;
					
				  default:
					$f.="<input type='text' name='param".$k."' value='' class='f_txt' eform='".$v['title'].":".$type.":".$req."'>";
					break;
				}
			
				$f.='</div></div>';
			}
			if($capcha){
				$f.='
				<div class="f_capcha">
					<div class="f_title">Введите код с картинки: </div>
					<div class="f_field"><input type="text" class="f_ver" name="vericode" /><div class="f_image_capcha"><img class="feed" id="capcha'.$id.'" src="[+verimageurl+]" alt="Введите код" /></div><div class="f_renew_capcha"><a href="javascript:;" onclick="document.getElementById(\'capcha'.$id.'\').src=\''.MODX_BASE_URL.MGR_DIR.'/includes/veriword.php?rand=\'+Math.random();">обновить картинку</a></div></div>
				</div>
				';
			}
			$f.='<div class="f_sendbutton"><input type="submit" value="Отправить"</div>';
			$f.='</div></form>';
		}
	return $f;
	}
}

function makeReportTpl($id){
	global $modx;
	$f='';
	if(isset($id)&&$modx->db->getRecordCount($modx->db->query("SELECT * FROM ".$modx->getFullTableName('forms')." WHERE id=".$id." LIMIT 0,1"))==1)
	{
		$forma=$modx->db->query("SELECT * FROM ".$modx->getFullTableName('form_fields')." WHERE parent=".$id." ORDER BY sort ASC");
		$forma_info=$modx->db->getRow($modx->db->query("SELECT * FROM ".$modx->getFullTableName('forms')." WHERE id=".$id." LIMIT 0,1"));
		if($modx->db->getRecordCount($forma)>0){
			$form=array();
			while($row=$modx->db->getRow($forma)){
				$form[$row['id']]['title']=$row['title'];
				$form[$row['id']]['type']=$row['type'];
				$form[$row['id']]['value']=$row['value'];
				$form[$row['id']]['required']=$row['required'];
			}
			$f.='<table>';
			foreach($form as $k=>$v){
				$f.='<tr><td style="padding-right:10px;"><b>'.$v['title'].':</b></td><td>[+param'.$k.'+]</td></tr>';
			}
			$f.='</table>';
		}
	return $f;
	}
}
?>