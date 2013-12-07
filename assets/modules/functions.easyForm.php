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

function makeTpl($id){
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
				<form id="f'.$id.'" action="[~[*id*]~]" method="post"><table class="f_table">
			';
			foreach($form as $k=>$v){
				$req=$v['required']==1?1:0;
				$type=$v['type']==3?'email':'string';
				$f.='<tr><td class="f_title">'.$v['title'].' '.($req==1?'<span class="red">*</span>':'').'</td><td>';
				
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
						$opt.="<option value='".$v1."'>".$v1."</option>";
					}
					$f.=$opt."</select></div>";
					break;
				
				  case 6:
					$f.="<div class='radio'>";
					$opts=explode("\n",$v['value']);
					$opt='';
					foreach($opts as $k1=>$v1){
						$v1=trim($v1);
						$opt.="<label>".$v1." <input type='radio' name='param".$k."' value='".$v1."' ".($k1==0?"eform='".$v['title']."::".$req."'":"")."></label>";
					}
					$f.=$opt."</div>";
					break;
					
				  case 7:
					$f.="<div class='checkbox'>";
					$opts=explode("\n",$v['value']);
					$opt='';
					foreach($opts as $k1=>$v1){
						$v1=trim($v1);
						$opt.="<label>".$v1." <input type='checkbox' name='param".$k."[]' value='".$v1."' ".($k1==0?"eform='".$v['title']."::".$req."'":"")."></label>";
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
			
				$f.='</td></tr>';
			}
			$f.='<tr><td></td><td><div class="sendbuttons"><input type="submit" value="Отправить"</div></td></tr>';
			$f.='</table></form>';
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