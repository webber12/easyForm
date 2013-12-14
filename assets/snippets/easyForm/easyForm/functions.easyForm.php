<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}

function eParseTpl($arr1,$arr2,$output){
	return str_replace($arr1,$arr2,$output);
}


function getFormInfo($id){
	global $modx;
	$info=array();
	if(isset($id)){
		$info=$modx->db->getRow($modx->db->query("SELECT name,email FROM ".$modx->getFullTableName('forms')." WHERE id=".$id." LIMIT 0,1"));
	}
	return $info;
}

function makeTpl($id,$capcha=false,$config='default'){
	global $modx;

	$outer='';
	$fields='';
	
	if(isset($id)&&$modx->db->getRecordCount($modx->db->query("SELECT * FROM ".$modx->getFullTableName('forms')." WHERE id=".$id." LIMIT 0,1"))==1)
	{
		//подключаем файл конфигурации с шаблонами
		if(is_file(MODX_BASE_PATH.'assets/modules/easyForm/config/config.'.$config.'.php')){
			include_once(MODX_BASE_PATH.'assets/modules/easyForm/config/config.'.$config.'.php');
		}
		else{
			include_once(MODX_BASE_PATH.'assets/modules/easyForm/config/config.default.php');
		}
		
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
			
			$fields='';
			
			foreach($form as $k=>$v){
				$req=$v['required']==1?1:0;
				$type=$v['type']==3?'email':'string';
				
				switch($v['type']){
				  case 2:
					$field="<textarea name='param".$k."' class='f_txtarea' eform='".$v['title'].":".$type.":".$req."'></textarea>";
					break;
				
				  case 5:
					$opts=explode("\n",$v['value']);
					$opt='';
					foreach($opts as $k1=>$v1){
						$v1=trim($v1);
						$arr=explode("==",$v1);
						$key=$arr[0];
						$val=isset($arr[1])&&$arr[1]!=''?$arr[1]:$arr[0];
						$opt.="<option value='".$key."'>".$val."</option>";
					}
					$field="<div class='selector'><select name='param".$k."' class='f_selector' eform='".$v['title']."::".$req."'>".$opt."</select></div>";
					break;
				
				  case 6:
					$opts=explode("\n",$v['value']);
					$opt='';
					foreach($opts as $k1=>$v1){
						$v1=trim($v1);
						$arr=explode("==",$v1);
						$key=$arr[0];
						$val=isset($arr[1])&&$arr[1]!=''?$arr[1]:$arr[0];
						$opt.="<label>".$val." <input type='radio' name='param".$k."' value='".$key."' ".($k1==0?"eform='".$v['title']."::".$req."'":"")."></label>";
					}
					$field="<div class='radio'>".$opt."</div>";
					break;
					
				  case 7:
					$opts=explode("\n",$v['value']);
					$opt='';
					foreach($opts as $k1=>$v1){
						$v1=trim($v1);
						$arr=explode("==",$v1);
						$key=$arr[0];
						$val=isset($arr[1])&&$arr[1]!=''?$arr[1]:$arr[0];
						$opt.="<label>".$val." <input type='checkbox' name='param".$k."[]' value='".$key."' ".($k1==0?"eform='".$v['title']."::".$req."'":"")."></label>";
					}
					$field="<div class='checkbox'>".$opt."</div>";
					break;
					
				  case 8:
					$field="<input type='file' name='param".$k."' class='f_file' eform='".$v['title'].":".$type.":".$req."'>";
					break;
					
				  case 9:
					$opts=explode("\n",$v['value']);
					$opt='';
					foreach($opts as $k1=>$v1){
						$v1=trim($v1);
						$arr=explode("==",$v1);
						$key=$arr[0];
						$val=isset($arr[1])&&$arr[1]!=''?$arr[1]:$arr[0];
						$opt.="<option value='".$key."'>".$val."</option>";
					}
					$field="<div class='multiselector'><select name='param".$k."[]' class='f_multiselector' multiple='multiple' size='5' eform='".$v['title']."::".$req."'>".$opt."</select></div>";
					break;
				
				  case 10:
					$field="<input type='hidden' name='param".$k."' value='' class='f_hidden' eform='".$v['title'].":".$type.":".$req."'>";
					break;
					
				  default:
					$field="<input type='text' name='param".$k."' value='' class='f_txt' eform='".$v['title'].":".$type.":".$req."'>";
					break;
				}
	
				$req_text=($req==1?'<span class="red">*</span>':'');
				$fields.=eParseTpl(
					array('[+num+]','[+title+]','[+req_text+]','[+field+]'),
					array($k,$v['title'],$req_text,$field),
					$rowTpl
				);
			}
			if($capcha){
				$fields.=eParseTpl(
					array('[+id+]','[+capcha_dir+]'),
					array($id,MODX_BASE_URL.MGR_DIR),
					$capchaTpl
				);
			}
		
			$outer=eParseTpl(
				array('[+form_name+]','[form_description]','[+id+]','[+fields+]'),
				array($forma_info['name'],$forma_info['title'],$id,$fields),
				$outerTpl
			);
		}
	return $outer;
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