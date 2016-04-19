<?php
class View_Manage_My_User_Setting extends CMF_Hydrogen_View{


	public static function ___onRegisterTab( $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user/setting' );				//  load words
		$context->registerTab( 'setting', $words->module['tab'], 4 );								//  register main tab
	}

	protected function getModuleWords( $module ){
		$path		= $this->env->getConfig()->get( 'path.locales' );
		$language	= $this->env->getLanguage()->getLanguage();
		$moduleKey	= $this->getSingular( str_replace( '_', '/', strtolower( $module->id ) ) );
		$localeFile	= $language.'/'.$moduleKey.'.ini';
		$moduleWords	= array();
		foreach( $module->files->locales as $locale ){
			if( $localeFile == $locale->file ){
				if( file_exists( $path.$locale->file ) ){
					$reader	= new FS_File_INI_Reader( $path.$locale->file, TRUE );
					if( $reader->usesSections() && $reader->hasSection( 'module' ) )
						return $reader->getProperties( TRUE, 'module' );
				}
			}
		}
		foreach( $module->files->locales as $locale ){
			if( file_exists( $path.$locale->file ) ){
				$reader	= new FS_File_INI_Reader( $path.$locale->file, TRUE );
				if( $reader->usesSections() && $reader->hasSection( 'module' ) )
					return $reader->getProperties( TRUE, 'module' );
			}
		}
	}

	protected function getSingular( $string ){
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

	public function index(){
		$userId		= $this->getData( 'userId' );
		$settings	= $this->getData( 'settings' );
		$words		= $this->env->getLanguage()->load( 'manage/my/user/setting' );
		$w			= (object) $words['index'];
	}

	protected function renderModuleSettingInput( $module, $config, $moduleWords ){
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
					$options	= UI_HTML_Elements::Options( array_combine( $config->values, $config->values ), $config->value );
					$input		= UI_HTML_Tag::create( 'select', $options, array(
						'name'	=> $inputKey,
						'id'	=> 'input_'.$inputKey,
						'class'	=> "span3 numeric",
					) );
				}
				else{
					$input	= UI_HTML_Tag::create( 'input', NULL, array(
						'type'	=> "text",
						'name'	=> $inputKey,
						'id'	=> 'input_'.$inputKey,
						'class'	=> "span3 numeric",
						'value'	=> htmlentities( $config->value, ENT_COMPAT, 'UTF-8' ),
					) );
				}
				break;
			case 'string':
			case 'password':
				if( $config->values ){
					$labels		= array_combine( $config->values, $config->values );
					foreach( $config->values as $valueKey ){
						$key	= 'config.'.$config->key.'.option'.ucFirst( $valueKey );
						if( isset( $moduleWords[$key] ) ){
							$labels[$valueKey]	= $moduleWords[$key];
						}
					}
					$options	= UI_HTML_Elements::Options( $labels, $config->value );
					$input		= UI_HTML_Tag::create( 'select', $options, array(
						'name'	=> $inputKey,
						'class'	=> 'span6',
						'id'	=> 'input_'.$inputKey
					) );
				}
				else{
					if( preg_match( "/password$/i", $config->key."|".$config->type ) ){				//  setting is a password or key ends with 'password'
						$input	= UI_HTML_Tag::create( 'input', NULL, array(
							'type'	=> "password",
							'name'	=> $inputKey,
							'id'	=> 'input_'.$inputKey,
							'class'	=> "span6",
						) );
					}
					else if( substr_count( $config->value, "," ) ){										//  contains several values
						$input	= UI_HTML_Tag::create( 'textarea', str_replace( ",", "\n", htmlentities( $config->value, ENT_QUOTES, 'UTF-8' ) ), array(
							'name'	=> $inputKey,
							'id'	=> 'input_'.$inputKey,
							'class'	=> "span12",
							'rows'	=> (int) substr_count( $config->value, "," ),
						) );
					}
					else{
						$input	= UI_HTML_Tag::create( 'input', NULL, array(
							'type'	=> $isPassword ? "password" : "text",
							'name'	=> $inputKey,
							'id'	=> 'input_'.$inputKey,
							'class'	=> $isPassword ? "span6" : "span12",
							'value'	=> $isPassword ? NULL : htmlentities( $config->value, ENT_COMPAT, 'UTF-8' ),
						) );
					}
				}
				break;
		}
		return $input;
	}

	protected function renderModuleSettings( $module, $settings, $moduleWords, $from = NULL ){
		$words		= $this->env->getLanguage()->getWords( 'manage/my/user/setting' );
		$words		= (object) $words['index'];
		$iconReset	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
		$rows	= array();
		$list	= array();

		//  collect module settings configurable by user
		foreach( $module->config as $config ){
			if( $config->protected == "user" )
				$list[$config->key]	= $config;
		}

		//  order settings by order of pairs in module related locale file
		if( is_array( $moduleWords ) ) {										//  module has setting labels
			$sorted	= array();													//  prepare empty list of sorted pairs
			foreach( array_keys( $moduleWords ) as $key ){						//  iterate module related locale pairs
				if( preg_match( "/^config\./", $key ) ){						//  if they begin with 'config.'
					$key	= preg_replace( "/^config\./", "", $key );			//  get (possible) setting key from locale key
					if( isset( $list[$key] ) ){									//  setting key is existing in module config
						$sorted[$key]	= $list[$key];							//  append setting to sorted list
						unset( $list[$key]);									//  remove setting from unsorted list
					}
				}
			}
			$list	= array_merge( $sorted, $list );							//  append all left unsorted settings to list
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
				$input	= $this->renderModuleSettingInput( $module, $config, $moduleWords );
				$class	= 'row-fluid setting-line';
				$button	= '';
				if( $config->changed ){
					$class	.= ' changed';
					$url	= './manage/my/user/setting/reset/'.$module->id.'/'.$key;
					if( $from )
						$url	.= '?from='.$from;
					$button	= UI_HTML_Tag::create( 'a', $iconReset, array( 'href' => $url, 'class' => 'btn btn-inverse btn-mini', 'title' => $words->buttonResetAlt ) );
					$button	= '<span class="button-reset">'.$button.'</span>';
				}
				if( $suffix )
					$input	= '<div class="input-append">'.$input.'<span class="add-on">'.$suffix.'</span></div>';
				$label	= '<div class="span4 setting-label">'.$keyLabel.'</div>';
				$field	= '<div class="span8 input">'.$input.$button.'</div>';
				if( $config->type == "boolean" )
					$config->value	= $config->value ? 1 : 0;
				$data	= htmlentities( $config->value, ENT_QUOTES, 'UTF-8' );
				if( $config->type == "string" && substr_count( $config->value, "," ) )
					$data	= str_replace( ",", "\n", $data );
				$rows[]	= '<div class="'.$class.'" data-value="'.$data.'">'.$label.$field.'</div>';
			}
		}
		return join( $rows );
	}
}
?>
