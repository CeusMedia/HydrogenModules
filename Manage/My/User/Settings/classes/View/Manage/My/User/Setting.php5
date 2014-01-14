<?php
class View_Manage_My_User_Setting extends CMF_Hydrogen_View{

	public function index(){}

}
class View_Helper_MyUserConfig {

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

	public function renderTable( $settings, $widthCol1 = 20, $widthCol2 = 80 ){
		$language	= $this->env->getLanguage()->getLanguage();
		$path		= $this->env->getConfig()->get( 'path.locales' );
		$rows		= array();
		foreach( $this->env->getModules()->getAll() as $module ){
			$list	= array();
			foreach( $module->config as $config ){
				if( $config->protected == "user" )
					$list[$config->key]	= $config;
			}
			if( $list ){
				$moduleKey	= $this->getSingular( str_replace( '_', '/', strtolower( $module->id ) ) );
				$localeFile	= $language.'/'.$moduleKey.'.ini';
				$words	= array();
				foreach( $module->files->locales as $locale ){
					if( $localeFile == $locale->file ){
						$reader	= new File_INI_Reader( $path.$locale->file, TRUE );
						if( $reader->hasSection( 'module' ) )
							$words	= $reader->getProperties( TRUE, 'module' );
					}
				}
				$moduleLabel	= isset( $words['title'] ) ? $words['title'] : $module->title;
				if( $rows )
					$rows[]		= '<tr><td><br/></td></tr>';
				$rows[]			= '<tr><td><big>'.$moduleLabel.'</big></td></tr>';
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
					if( isset( $words['config.'.$config->key] ) )
						$keyLabel	= $words['config.'.$config->key];
					$suffix	= '';
					if( isset( $words['config.'.$config->key.'_suffix'] ) )
						$suffix	= '<span class="suffix">'.$words['config.'.$config->key.'_suffix'].'</span>';
					$inputKey	= $module->id.'::'.$config->key;
					switch( $config->type ){
						case 'bool':
						case 'boolean':
							$checked	= $config->value ? ' checked="checked"' : '';
							$checked1	= $config->value ? ' checked="checked"' : '';
							$checked0	= !$config->value ? ' checked="checked"' : '';
							$input	= '<input type="checkbox" value="1"'.$checked.'>';
							$input	= '<label><input type="radio" name="'.$inputKey.'" id="input_'.$inputKey.'" value="1"'.$checked1.'>Ja</label>&nbsp;<label><input type="radio" name="'.$inputKey.'" id="input_'.$inputKey.'" value="0"'.$checked0.'>Nein</label>';
							break;
						case 'float':
						case 'integer':
							$input	= '<input type="text" name="'.$inputKey.'" id="input_'.$inputKey.'" value="'.htmlentities( $config->value, ENT_COMPAT, 'UTF-8' ).'" class="xs numeric"/>';
							break;
						case 'string':
							$input	= '<input type="text" name="'.$inputKey.'" id="input_'.$inputKey.'" value="'.htmlentities( $config->value, ENT_COMPAT, 'UTF-8' ).'"/>';
remark( $config->key );
							if( preg_match( "/password$/", $config->key ) )
								$input	= '<input type="password" name="'.$inputKey.'" id="input_'.$inputKey.'"/>';
							break;
					}
					$class	= '';
					$button	= '';
					if( $config->changed ){
						$class	= 'changed';
						$url	= './manage/my/user/setting/reset/'.$module->id.'/'.$key;
						$button	= UI_HTML_Elements::LinkButton( $url, '', 'button tiny remove' );
						$button	= '<span class="button-reset">'.$button.'</span>';
					}
					$rows[]	= '<tr class="'.$class.'"><td class="label">'.$keyLabel.'</td><td class="input">'.$input.$suffix.$button.'</td></tr>';
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
			</table>';
	}
}
?>
