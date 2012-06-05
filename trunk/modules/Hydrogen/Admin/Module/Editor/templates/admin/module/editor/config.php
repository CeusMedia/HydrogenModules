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
		switch( $value->type ){
			case 'boolean':
				$strValue	= $value->value === TRUE ? 'yes' : 'no';
				$options	= UI_HTML_Elements::Options( $words['boolean-values'], $strValue );
				$input		= UI_HTML_Tag::create( 'select', $options, array( 'class' => 's'.$class.' active-'.$strValue, 'name' => $name, 'id' => 'input_'.$name ) );
				$inputLabel	= UI_HTML_Tag::create( 'input', NULL, array( 'id' => 'label_'.$name, 'value' => $words['boolean-values'][$strValue], 'class' => 'label s active-'.$strValue, 'readonly' => TRUE ) );
				break;
			case 'int':
			case 'integer':
				$input		= UI_HTML_Elements::Input( 'config['.$key.']', $value->value, 's'.$class );
				$inputLabel	= UI_HTML_Tag::create( 'input', NULL, array( 'id' => 'label_'.$name, 'value' => $value->value, 'class' => 'label max', 'readonly' => TRUE ) );
				break;
			default:
				$inputLabel	= UI_HTML_Tag::create( 'input', NULL, array( 'id' => 'label_'.$name, 'value' => $value->value, 'class' => 'label max', 'readonly' => TRUE ) );
				if( count( $value->values ) ){
					$options	= array_combine( $value->values, $value->values );
					$options	= UI_HTML_Elements::Options( $options, $value->value );
					$input		= UI_HTML_Elements::Select( 'config['.$key.']', $options, 'm'.$class );
				}
				else
					$input	= UI_HTML_Elements::Input( 'config['.$key.']', $value->value, 'max'.$class );
				break;
		}
		$label	= UI_HTML_Tag::create( 'label', $key, array( 'class' => $class, 'for' => 'input_'.$name ) );
		$id		= str_replace( '.', '_', $key );
		$cells	= array(
			UI_HTML_Tag::create( 'td', $label, array() ),
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
$(document).ready(function(){
	$("#form_admin_module_config tr td.cell-config-value :input").not(".label").hide();
	$("#form_admin_module_config input.label").bind("focus",function(){
		$(this).parent().find(":hidden").show().focus();
		$(this).hide();
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