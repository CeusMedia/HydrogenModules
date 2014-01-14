<?php
class View_Helper_UserModuleSettings {

	public function __construct( $env ){
		$this->env	= $env;
	}

	public function getSingular( $string ){
		if( preg_match( "/des$/", $string ) )
			$string	= preg_replace( "/des$/", "de", $string );
		else if( preg_match( "/ies$/", $string ) )
			$string	= preg_replace( "/ies$/", "y", $string );
		else if( preg_match( "/es$/", $string ) )
			$string	= preg_replace( "/es$/", "", $string );
		else if( preg_match( "/s$/", $string ) )
			$string	= preg_replace( "/s$/", "", $string );
		return $string;
	}

	public function renderPanel( $from = NULL, $widthCol1 = 40, $widthCol2 = 60 ){
		$userId		= $this->env->getSession()->get( 'userId' );
		$model		= new Model_User_Setting( $this->env );
		$settings	= $model->getAllByIndex( 'userId', $userId );						//  get all user settings fr$

		$words		= $this->env->getLanguage()->load( 'manage/my/user/setting' );
		$w		= (object) $words['index'];
		$table		= $this->renderTable( $settings, $from, $widthCol1, $widthCol2 );
		if( !$table )
			return '';
		$formUri 	= './manage/my/user/setting/update';
		if( $from )
			$formUri .= '?from='.$from;
		$buttonSave	= UI_HTML_Elements::Button( 'save', '<i class="icon-ok icon-white"></i> '.$w->buttonSave, 'btn btn-success btn-small' );
		return '
<fieldset id="manageMyUserModuleSettings">
	<legend class="icon config">'.$w->legend.'</legend>
	<form name="form-manage-my-user-settings" action="'.$formUri.'" method="post">
		'.$table.'
		<div class="buttonbar">'.$buttonSave.'</div>
	</form>
</fieldset>
<script>
$(document).ready(function(){
	$("#manageMyUserModuleSettings table tbody tr :input").bind("keyup mouseup change",function(){
		var row = $(this).closest("tr");
		var changed = row.data("value") != $(this).val();
		changed ? row.addClass("modified") : row.removeClass("modified");
	});
});
</script>';
	}

	public function renderTable( $settings = NULL, $from = NULL, $widthCol1 = 40, $widthCol2 = 60 ){
		$path		= $this->env->getConfig()->get( 'path.locales' );
		$language	= $this->env->getLanguage()->getLanguage();
		$words		= $this->env->getLanguage()->getWords( 'manage/my/user/setting' );
		$words		= (object) $words['index'];
		$rows		= array();

		if( $settings === NULL ){
			$userId		= $this->env->getSession()->get( 'userId' );
			$model		= new Model_User_Setting( $this->env );
			$settings	= $model->getAllByIndex( 'userId', $userId );								//  get all user settings from database
		}

		foreach( $this->env->getModules()->getAll() as $module ){
			$list	= array();
			foreach( $module->config as $config ){
				if( $config->protected == "user" )
					$list[$config->key]	= $config;
			}
			if( $list ){
				$moduleKey	= $this->getSingular( str_replace( '_', '/', strtolower( $module->id ) ) );
				$localeFile	= $language.'/'.$moduleKey.'.ini';
				$moduleWords	= array();
				foreach( $module->files->locales as $locale ){
					if( $localeFile == $locale->file ){
						$reader	= new File_INI_Reader( $path.$locale->file, TRUE );
						if( $reader->hasSection( 'module' ) )
							$moduleWords	= $reader->getProperties( TRUE, 'module' );
					}
				}
				$moduleLabel	= isset( $moduleWords['title'] ) ? $moduleWords['title'] : $module->title;
				if( $rows )
					$rows[]		= '<tr><td><br/></td></tr>';
				$rows[]			= '<tr><th colspan="2"><big>'.$moduleLabel.'</big></td></tr>';
				foreach( $list as $key => $config ){
					$config->default	= $config->value;
					$config->changed	= FALSE;

					foreach( $settings as $setting ){
						if( $module->id == $setting->moduleId ){
							if( $key == $setting->key ){
								$config->changed	= TRUE;
								$config->value		= $setting->value;
#								print_m( $setting );
#								print_m( $config );
							}
						}
					}

					$keyLabel	= $config->key;
					if( isset( $moduleWords['config.'.$config->key] ) )
						$keyLabel	= $moduleWords['config.'.$config->key];
					$suffix	= '';
					if( isset( $moduleWords['config.'.$config->key.'_suffix'] ) ){
						$suffix	= $moduleWords['config.'.$config->key.'_suffix'];
						if( preg_match( '/^icon-/', trim( $suffix ) ) )
							$suffix	= '<i class="'.$suffix.'"></i>';
						else
							$suffix	= '<span class="suffix">'.$moduleWords['config.'.$config->key.'_suffix'].'</span>';
					}
					$inputKey	= $module->id.'::'.$config->key;
					switch( $config->type ){
						case 'bool':
						case 'boolean':
							$checked	= $config->value ? ' checked="checked"' : '';
							$checked1	= $config->value ? ' checked="checked"' : '';
							$checked0	= !$config->value ? ' checked="checked"' : '';
							$input	= '<label class="radio inline">
								<input type="radio" name="'.$inputKey.'" id="input_'.$inputKey.'" value="1"'.$checked1.'>Ja
							</label>&nbsp;
							<label class="radio inline">
								<input type="radio" name="'.$inputKey.'" id="input_'.$inputKey.'" value="0"'.$checked0.'>Nein
							</label>';
							break;
						case 'float':
						case 'integer':
							if( $config->values ){
								$opt	= UI_HTML_Elements::Options( array_combine( $config->values, $config->values ), $config->value );
								$input	= '<select name="'.$inputKey.'" class="input-mini numeric" id="input_'.$inputKey.'">'.$opt.'</select>';
							}
							else
								$input	= '<input type="text" name="'.$inputKey.'" id="input_'.$inputKey.'" value="'.htmlentities( $config->value, ENT_COMPAT, 'UTF-8' ).'" class="xs numeric"/>';
							break;
						case 'string':
							$input	= '<input type="text" name="'.$inputKey.'" id="input_'.$inputKey.'" value="'.htmlentities( $config->value, ENT_COMPAT, 'UTF-8' ).'"/>';
							if( preg_match( "/password$/", $config->key ) )
								$input	= '<input type="password" name="'.$inputKey.'" id="input_'.$inputKey.'"/>';
							break;
					}
					$class	= '';
					$button	= '';
					if( $config->changed ){
						$class	= 'changed';
						$url	= './manage/my/user/setting/reset/'.$module->id.'/'.$key;
						if( $from )
							$url	.= '?from='.$from;
						$button	= UI_HTML_Elements::LinkButton( $url, '', 'button tiny remove', $words->buttonResetAlt );
						$button	= '<span class="button-reset">'.$button.'</span>';
					}
					if( $suffix )
						$input	= '<div class="input-append">'.$input.'<span class="add-on">'.$suffix.'</span></div>';
					$rows[]	= '<tr class="'.$class.'" data-value="'.htmlentities( $config->value, ENT_QUOTES, 'UTF-8' ).'"><td class="-label">'.$keyLabel.'</td><td class="input">'.$input.$button.'</td></tr>';
				}
			}
		}
		if( !$rows )
			return '';
		return '
			<table>
				<colgroup>
					<col width="'.$widthCol1.'%"/>
					<col width="'.$widthCol2.'%"/>
				</colgroup>
				<tbody>
					'.join( $rows ).'
				</tbody>
			</table>
';
	}
}
?>
