<?php

$w			= (object) $words['tab-config'];

$count			= count( $module->config );
$tableConfig	= '';

$tableConfig	= '<br/><div>'.$w->listNone.'</div><br/>';
if( count( $module->config ) ){
	$rows	= array();
	foreach( $module->config as $key => $value ){

		$urlRemove	= './admin/module/editor/removeConfig/'.$moduleId.'/'.$key;
		$linkRemove	= UI_HTML_Elements::LinkButton( $urlRemove, '', 'button icon tiny remove', $w->buttonRemoveConfirm );
		$class	= "";
		if( $value->mandatory ){
			if( $value->mandatory == "yes" )
				$class = " mandatory";
			else if( preg_match( "/^.+:.*$/", $value->mandatory ) ){
				list( $relatedKey, $relatedValue )	= explode( ':', $value->mandatory );
				$relatedValue	= explode( ',', $relatedValue );
				if( isset( $module->config[$relatedKey] ) ){
					if( in_array( $module->config[$relatedKey]->value, $relatedValue ) )
						$class = " mandatory";
				}
			}
		}
		$name	= 'config['.$key.']';
		$inputName	= 'config['.$key.']';
		$inputId	= 'input_config_'.$key;
		$inputValue	= htmlentities( $value->value, ENT_QUOTES, 'UTF-8' );
		$inputTitle	= trim( htmlentities( $value->title, ENT_QUOTES, 'UTF-8' ) );
		switch( $value->type ){
			case 'boolean':
				$strValue	= $value->value === TRUE ? 'yes' : 'no';
				$options	= UI_HTML_Elements::Options( $words['boolean-values'], $strValue );
				$inputLabel	= UI_HTML_Tag::create( 'input', NULL, array( 'id' => 'label_'.$inputId, 'value' => $words['boolean-values'][$strValue], 'class' => 'label s active-'.$strValue, 'readonly' => TRUE, 'title' => $inputTitle ) );
				$input		= UI_HTML_Tag::create( 'select', $options, array( 'class' => 's'.$class.' active-'.$strValue, 'name' => $name, 'id' => $inputId, 'title' => $inputTitle ) );
				break;
			case 'int':
			case 'integer':
				$inputLabel	= UI_HTML_Tag::create( 'input', NULL, array( 'id' => 'label_'.$inputId, 'value' => $inputValue, 'class' => 'label max', 'readonly' => TRUE, 'title' => $inputTitle ) );
				$input		= UI_HTML_Tag::create( 'input', NULL, array( 'id' => $inputId, 'name' => $inputName, 'value' => $inputValue, 'class' => 's'.$class ) );
				break;
			default:
				$inputLabel	= UI_HTML_Tag::create( 'input', NULL, array( 'id' => 'label_'.$inputId, 'value' => $inputValue, 'class' => 'label max', 'readonly' => TRUE, 'title' => $inputTitle ) );
				if( count( $value->values ) ){
					$options	= array_combine( $value->values, $value->values );
					$options	= UI_HTML_Elements::Options( $options, $value->value );
					$input		= UI_HTML_Tag::create( 'select', $options, array( 'name' => $inputName, 'id' => $inputId, 'class' => 'm'.$class, 'title' => $inputTitle ) );
				}
				else{
					$attr	= array( 'id' => $inputId, 'name' => $inputName, 'value' => $inputValue, 'class' => 'max'.$class, 'title' => $inputTitle );
					$input	= UI_HTML_Tag::create( 'input', NULL, $attr );
				}
				break;
		}
		$label  = $key;
		if( strlen( $inputTitle ) )
			$label  = UI_HTML_Tag::create( 'acronym', $key, array( 'title' => $inputTitle ) );

		$label	= UI_HTML_Tag::create( 'label', $label, array( 'class' => $class, 'for' => $inputId ) );
		$id		= str_replace( '.', '_', $key );
		$cells	= array(
			UI_HTML_Tag::create( 'td', $label, array( 'class' => 'cell-config-key' ) ),
			UI_HTML_Tag::create( 'td', $words['config-types'][$value->type], array( 'class' => "cell-config-type" ) ),
			UI_HTML_Tag::create( 'td', $inputLabel.$input, array( 'class' => 'cell-config-value' ) ),
			UI_HTML_Tag::create( 'td', $linkRemove, array() ),
		);
		$rows[$key]	= UI_HTML_Tag::create( 'tr', $cells, array( 'id' => "config_".$id ) );
	}
#	natcasesort( $rows );
	$heads			= array( $w->headKey, $w->headType, $w->headValue, $w->headAction );
	$tableHeads		= UI_HTML_Elements::TableHeads( $heads );
	$tableColumns	= UI_HTML_Elements::ColumnGroup( array( '25%', '15%', '50%', '10%' ) );
	$tableConfig	= '<table>'.$tableColumns.$tableHeads.join( $rows ).'</table>';
	$tableConfig	.= UI_HTML_Elements::Button( 'save', $w->buttonSave, 'button save' );
}

$optType	= UI_HTML_Elements::Options( $words['config-types'] );

$optBoolean	= UI_HTML_Elements::Options( $words['boolean-values'] );

$wf			= (object) $words['tab-config-add'];

$panelAdd	= '
<form id="form_admin_module_config_add" action="./admin/module/editor/addConfig/'.$moduleId.'?tab=config" method="post">
	<fieldset>
		<legend class="icon add">'.$wf->legend.'</legend>
		<ul class="input">
			<li>
				<label for="input_name" class="mandatory">'.$wf->labelName.'</label><br/>
				<input type="text" name="name" id="input_name" class="max mandatory" value=""/>
			</li>
			<li>
				<label for="input_type">'.$wf->labelType.'</label><br/>
				<select name="type" id="input_type" class="max" onchange="showOptionals(this);">'.$optType.'</select>
			</li>
			<li class="optional type-boolean">
				<label for="input_value_boolean">'.$wf->labelValueBoolean.'</label><br/>
				<select name="value_boolean" id="input_value_boolean" class="max">'.$optBoolean.'</select>
			</li>
			<li class="optional type-string type-integer type-float">
				<label for="input_value">'.$wf->labelValue.'</label><br/>
				<input type="text" name="value" id="input_value" class="max" value=""/>
			</li>
			<li class="optional type-string type-integer type-float">
				<label for="input_values">'.$wf->labelValues.'</label><br/>
				<input type="text" name="values" id="input_values" class="max" value=""/>
			</li>
			<li>
				<label for="input_mandatory">
					<input type="checkbox" name="mandatory" id="input_mandatory" value="yes"/>&nbsp;'.$wf->labelMandatory.'
				</label>
			</li>
			<li>
				<label for="input_protected">
					<input type="checkbox" name="protected" id="input_protected" value="yes"/>&nbsp;'.$wf->labelProtected.'
				</label>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::Button( 'add', $wf->buttonAdd, 'button add' ).'
		</div>
	</fieldset>
</form>
<script>
function switchConfigInput(elemTr, event){
	if( typeof event !== "undefined" ){
		event.stopPropagation();
		event.preventDefault();
	}
	elemTr.find(":input.label").hide();
	elemTr.find(":input").not(".label").show().focus();
}
$(document).ready(function(){
	$("#form_admin_module_config tr td.cell-config-value :input").not(".label").hide();
	$("#form_admin_module_config :input.label").bind("mousedown",function(event){
		switchConfigInput($(this).parent(), event);
	});
	$("#form_admin_module_config tr td.cell-config-key label").bind("click", function(event){
		switchConfigInput($(this).parent().parent());
	});
	$("#form_admin_module_config_add #input_type").trigger("change");
	$("a.disabled").attr("href","#");
});
</script>
';

$panelEdit	= '
<form id="form_admin_module_config" action="./admin/module/editor/editConfig/'.$moduleId.'?tab=config" method="post">
	'.$tableConfig.'
</form>
';

return '
<div class="column-left-70">
	'.$panelEdit.'
</div>
<div class="column-right-30">
	'.$panelAdd.'
</div>
<div class="column-clear"></div>

';
?>
