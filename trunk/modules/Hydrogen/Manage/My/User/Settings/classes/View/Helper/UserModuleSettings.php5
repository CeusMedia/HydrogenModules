<?php
class View_Helper_UserModuleSettings {

	public function __construct( $env ){
		throw new Exception( 'View_Helper_UserModuleSettings is deprecated' );
		$this->env	= $env;
	}

	protected function getModuleWords( $module ){
		$path		= $this->env->getConfig()->get( 'path.locales' );
		$language	= $this->env->getLanguage()->getLanguage();
		$moduleKey	= $this->getSingular( str_replace( '_', '/', strtolower( $module->id ) ) );
		$localeFile	= $language.'/'.$moduleKey.'.ini';
		$moduleWords	= array();
		foreach( $module->files->locales as $locale ){
			if( $localeFile == $locale->file ){
				$reader	= new File_INI_Reader( $path.$locale->file, TRUE );
				if( $reader->hasSection( 'module' ) )
					return $reader->getProperties( TRUE, 'module' );
			}
		}
		foreach( $module->files->locales as $locale ){
			$reader	= new File_INI_Reader( $path.$locale->file, TRUE );
			if( $reader->hasSection( 'module' ) )
				return $reader->getProperties( TRUE, 'module' );
		}
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

	protected function renderModuleSettingInput( $module, $config ){
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
		return $input;
	}

	protected function renderModuleSettings( $module, $settings, $moduleWords, $from = NULL ){
		$words		= $this->env->getLanguage()->getWords( 'manage/my/user/setting' );
		$words		= (object) $words['index'];
		$iconReset	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove' ) );
		$rows	= array();
		$list	= array();
		foreach( $module->config as $config ){
			if( $config->protected == "user" )
				$list[$config->key]	= $config;
		}
		if( $list ){
			$moduleLabel	= isset( $moduleWords['title'] ) ? $moduleWords['title'] : $module->title;

			if( $rows )
				$rows[]		= '<br/>';
			$rows[]			= '<h4>'.$moduleLabel.'</h4>';
			foreach( $list as $key => $config ){
				$config->default	= $config->value;
				$config->changed	= FALSE;

				foreach( $settings as $setting ){
					if( $module->id == $setting->moduleId ){
						if( $key == $setting->key ){
							$config->changed	= TRUE;
							$config->value		= $setting->value;
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
				$input	= $this->renderModuleSettingInput( $module, $config );
				$class	= 'row-fluid';
				$button	= '';
				if( $config->changed ){
					$class	= 'row-fluid changed';
					$url	= './manage/my/user/setting/reset/'.$module->id.'/'.$key;
					if( $from )
						$url	.= '?from='.$from;
					$button	= UI_HTML_Tag::create( 'a', $iconReset, array( 'href' => $url, 'class' => 'btn not-btn-small', 'title' => $words->buttonResetAlt ) );
					$button	= '<span class="button-reset">'.$button.'</span>';
				}
				if( $suffix )
					$input	= '<div class="input-append">'.$input.'<span class="add-on">'.$suffix.'</span></div>';
				$label	= '<div class="span4 setting-label">'.$keyLabel.'</div>';
				$field	= '<div class="span8 input">'.$input.$button.'</div>';
				$rows[]	= '<div class="'.$class.'" data-value="'.htmlentities( $config->value, ENT_QUOTES, 'UTF-8' ).'">'.$label.$field.'</div>';
			}
		}
		return join( $rows );
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
<h3>'.$w->legend.'</h3>
<div class="row-fluid" id="manageMyUserModuleSettings">
	<form name="form-manage-my-user-settings" action="'.$formUri.'" method="post">
		'.$table.'
		<hr/>
		<div class="buttonbar">'.$buttonSave.'</div>
	</form>
</div>
<script>
$(document).ready(function(){
	$("#manageMyUserModuleSettings :input").bind("keyup mouseup change",function(){
		var row = $(this).closest("div.row-fluid");
		var changed = row.data("value") != $(this).val();
		changed ? row.addClass("modified") : row.removeClass("modified");
	});
});
</script>';
	}
	
	public function renderTable( $settings = NULL, $from = NULL, $widthCol1 = 40, $widthCol2 = 60 ){
		$rows		= array();

		if( $settings === NULL ){
			$userId		= $this->env->getSession()->get( 'userId' );
			$model		= new Model_User_Setting( $this->env );
			$settings	= $model->getAllByIndex( 'userId', $userId );								//  get all user settings from database
		}

		foreach( $this->env->getModules()->getAll() as $module ){
			$moduleWords	= $this->getModuleWords( $module );
			$key			= isset( $moduleWords['title'] ) ? $moduleWords['title'] : $module->id;
			$panel			= $this->renderModuleSettings( $module, $settings, $moduleWords, $from );
			$panel ? $rows[$key]	= $panel : NULL;
		}
		ksort( $rows );
		return join( '<br/>', $rows );
	}
}
?>
