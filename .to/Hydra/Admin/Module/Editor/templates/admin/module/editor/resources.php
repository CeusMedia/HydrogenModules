<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/**	@todo	kriss: realize new view button, see implementation @scripts */

$w			= (object) $words['tab-resources'];
$pathIcons	= 'https://cdn.ceusmedia.de/img/famfamfam/silk/';
$pathIcons	= 'https://cdn.ceusmedia.de/img/famfamfam/silk/';

$tableResources	= '<br/><div>'.$w->listNone.'</div><br/>';

/*remark( $pathApp );
print_m( $configApp->getAll() );
die;
*/
$count			= 0;

$pathTemplates	= $configApp->get( 'path.templates' );
$pathLocales	= $configApp->get( 'path.locales' );
$pathThemes		= $configApp->get( 'path.themes' );
$pathImages		= $configApp->get( 'path.images' );
$pathScripts	= $configApp->get( 'path.scripts' );
$pathScriptsLib	= $configApp->get( 'path.scripts.lib' );
$pathStylesLib	= $configApp->get( 'path.styles.lib' );

$pathThemePrimer	= $configApp->get( 'layout.primer' ).'/';
$pathThemeCustom	= $configApp->get( 'layout.theme' ).'/';

$iconView	= HtmlElements::Image( $pathIcons.'eye.png', 'anzeigen' );
$iconEdit	= HtmlElements::Image( $pathIcons.'pencil.png', 'bearbeiten' );
$iconUnlink	= HtmlElements::Image( $pathIcons.'link_delete.png', 'abmelden' );
$iconRemove	= HtmlElements::Image( $pathIcons.'bin_closed.png', 'entfernen' );

function checkFile( $uri ){
	if( preg_match( "/^[a-z]+:\/\//", $uri ) ){
		try{
			Net_Reader::readUrl( $uri );
			return TRUE;
		}
		catch( Exception $e ){}
		return FALSE;
	}
	else if( preg_match( "/^\//", $uri ) ){
#		remark( $uri );
#		remark( getEnv( 'DOCUMENT_ROOT' ) );
#		if( substr( $uri, 0, strlen( getEnv( 'DOCUMENT_ROOT' ) ) ) == getEnv( 'DOCUMENT_ROOT' ) )
			return file_exists( $uri );
#		return file_exists( getEnv( 'DOCUMENT_ROOT' ).$uri );
	}
	return file_exists( $uri );
}

#print_m( $module );
#die;

//  --  TABLE: CLASSES  --  //
$classes	= '';
if( $module->files->classes ){
	$rows	= [];
	foreach( $module->files->classes as $item ){
		$count++;
		$class	= NULL;
		$uri	= $pathApp.'/classes/'.$item->file;

		$urlView		= './admin/module/editor/viewCode/'.$moduleId.'/class/'.base64_encode( $item->file );
		$urlUnlink		= './admin/module/editor/removeFile/'.$moduleId.'/class/'.base64_encode( $item->file ).'?tab=resources';
		$buttonView		= HtmlElements::Link( $urlView, $iconView, 'button tiny layer-html', '['.$module->title.'] '.$item->file );
		$buttonUnlink	= HtmlElements::Link( $urlUnlink, $iconUnlink, 'button tiny' );

		if( !checkFile( $uri ) ){
			$this->env->messenger->noteError( 'Missing: '.$uri );
			$class	= 'missing';
			$buttonView	= HtmlElements::Link( $urlView, $iconView, 'button tiny layer-html disabled' );
		}
		$label		= HtmlTag::create( 'span', $item->file, ['class' => 'icon class'] );
		$rows[]		= '<tr class="'.$class.'"><td>'.$label.'</td><td>'.$buttonView.$buttonUnlink.'</td></tr>';
	}
	$heads		= HtmlElements::TableHeads( ["Datei", "Aktion"] );
	$colgroup	= HtmlElements::ColumnGroup( ["85%", "15%"] );
	$table		= '<table>'.$colgroup.$heads.'</tr>'.join( $rows ).'</table>';
	$classes	= '<h4>'.$w->resourceClasses.'</h4>'.$table.'<br/>';
}

//  --  TABLE: TEMPLATES  --  //
$templates	= '';
if( $module->files->templates ){
	$rows	= [];
	foreach( $module->files->templates as $item ){
		$count++;
		$class	= NULL;
		$uri	= $pathApp.$pathTemplates.$item->file;

		$urlView		= './admin/module/editor/viewCode/'.$moduleId.'/template/'.base64_encode( $item->file );
		$urlUnlink		= './admin/module/editor/removeFile/'.$moduleId.'/template/'.base64_encode( $item->file ).'?tab=resources';
		$buttonView		= HtmlElements::Link( $urlView, $iconView, 'button tiny layer-html' );
		$buttonUnlink	= HtmlElements::Link( $urlUnlink, $iconUnlink, 'button tiny' );

		if( !checkFile( $uri ) ){
			$this->env->messenger->noteError( 'Missing: '.$uri );
			$class	= 'missing';
			$buttonView	= HtmlElements::Link( $urlView, $iconView, 'button tiny layer-html disabled' );
		}
		$label		= HtmlTag::create( 'span', $item->file, ['class' => 'icon template'] );
		$rows[]		= '<tr class="'.$class.'"><td>'.$label.'</td><td>'.$buttonView.$buttonUnlink.'</td></tr>';
	}
	$heads		= HtmlElements::TableHeads( ["Datei", "Aktion"] );
	$colgroup	= HtmlElements::ColumnGroup( ["85%", "15%"] );
	$table		= '<table>'.$colgroup.$heads.'</tr>'.join( $rows ).'</table>';
	$templates	= '<h4>'.$w->resourceTemplates.'</h4>'.$table.'<br/>';
}



//$classes	= xmp( \CeusMedia\HydrogenFramework\View\Helper\Diff::htmlDiff( file_get_contents( 'config.ini.inc' ), file_get_contents( 'config.ini.inc.dist' ) ) );
//die( $classes );

//  --  TABLE: LOCALES  --  //
$locales	= '';
if( $module->files->locales ){
	$rows	= [];
	foreach( $module->files->locales as $item ){
		$count++;
		$class	= NULL;
		$uri	= $pathApp.$pathLocales.$item->file;

		$urlView		= './admin/module/editor/viewCode/'.$moduleId.'/locale/'.base64_encode( $item->file );
		$urlUnlink		= './admin/module/editor/removeFile/'.$moduleId.'/locale/'.base64_encode( $item->file ).'?tab=resources';
		$buttonView		= HtmlElements::Link( $urlView, $iconView, 'button tiny layer-html' );
		$buttonUnlink	= HtmlElements::Link( $urlUnlink, $iconUnlink, 'button tiny' );

		if( !checkFile( $uri ) ){
			$this->env->messenger->noteError( 'Missing: '.$uri );
			$class	= 'missing';
			$buttonView	= HtmlElements::Link( $urlView, $iconView, 'button tiny layer-html disabled' );
		}
		$label		= HtmlTag::create( 'span', $item->file, ['class' => 'icon locale'] );
		$rows[]		= '<tr class="'.$class.'"><td>'.$label.'</td><td>'.$buttonView.$buttonUnlink.'</td></tr>';
	}
	$heads		= HtmlElements::TableHeads( ["Datei", "Aktion"] );
	$colgroup	= HtmlElements::ColumnGroup( ["85%", "15%"] );
	$table		= '<table>'.$colgroup.$heads.'</tr>'.join( $rows ).'</table>';
	$locales	= '<h4>'.$w->resourceLocales.'</h4>'.$table.'<br/>';
}

//  --  TABLE: STYLES  --  //
$styles	= '';
if( $module->files->styles ){
	$rows	= [];
	foreach( $module->files->styles as $item ){
		$count++;
		$class	= NULL;
		$source	= !empty( $item->source ) ? $item->source : 'theme';
		$theme	= !empty( $item->theme ) ? $item->theme : $configApp->get( 'layout.theme' );
		$load	= !empty( $item->load ) ? $item->load : '-';

		$uri	= $pathApp.$pathThemes;
		switch( $source ){
			case 'url':			$uri	= ""; break;												//  absolute URL
			case 'lib':			$uri	= $pathStylesLib; break;									//  URL relative to scripts library
			case 'scripts-lib':	$uri	= $pathScriptsLib; break;									//  URL relative to scripts library
			case 'primer':		$uri	.= $pathThemePrimer.'css/'; break;							//  file in primer theme folder
			case 'theme':		$uri	.= $theme.'/css/'; break;							//  file in custom theme folder
			default:			$uri	.= $item->source.'/'; break;								//  ...
		}
		$uri	.= $item->file;
		$urlView		= './admin/module/editor/viewCode/'.$moduleId.'/style/'.base64_encode( $uri );
		$urlUnlink		= './admin/module/editor/removeFile/'.$moduleId.'/style/'.base64_encode( $item->file ).'?tab=resources';
		$buttonView		= HtmlElements::Link( $urlView, $iconView, 'button tiny layer-html' );
		$buttonUnlink	= HtmlElements::Link( $urlUnlink, $iconUnlink, 'button tiny' );

		if( !checkFile( $uri ) ){
			$this->env->messenger->noteError( 'Missing: '.$uri );
			$class	= 'missing';
			$buttonView	= HtmlElements::Link( $urlView, $iconView, 'button tiny layer-html disabled' );
		}
		$label		= HtmlTag::create( 'span', $item->file, ['class' => 'icon style'] );
		$rows[]		= '<tr class="'.$class.'"><td>'.$label.'</td><td>'.$source.'</td><td>'.$load.'</td><td>'.$buttonView.$buttonUnlink.'</td></tr>';
	}
	$heads		= HtmlElements::TableHeads( ["Datei", "Quelle", "Laden", "Aktion"] );
	$colgroup	= HtmlElements::ColumnGroup( ["60%", "15%", "10%", "15%"] );
	$table		= '<table>'.$colgroup.$heads.'</tr>'.join( $rows ).'</table>';
	$styles		= '<h4>'.$w->resourceStyles.'</h4>'.$table.'<br/>';
}

//  --  TABLE: SCRIPTS  --  //
$scripts	= '';
if( $module->files->scripts ){
	$rows	= [];
	$scripts	= [];
	foreach( $module->files->scripts as $item ){
		$count++;
		$class	= NULL;
		$source	= !empty( $item->source ) ? $item->source : 'local';
		$load	= !empty( $item->load ) ? $item->load : '-';

		$uri	= $pathApp;
		switch( $source ){
			case 'url':		$uri	= ""; break;													//  absolute URL
			case 'lib':		$uri	= $pathScriptsLib; break;										//  URL relative to scripts library
			case 'local':	$uri	.= $pathScripts; break;											//  file in local scripts folder
			default:		$uri	.= $item->source.'/'; break;									//  ...
		}
		$uri	.= $item->file;

		$urlView		= './admin/module/editor/viewCode/'.$moduleId.'/script/'.base64_encode( $uri );
		$urlUnlink		= './admin/module/editor/removeFile/'.$moduleId.'/script/'.base64_encode( $item->file ).'?tab=resources';
#		$buttonView		= HtmlElements::Link( $urlView, $iconView, 'button tiny layer-html', NULL, '['.$module->title.'] '.$item->file );
		$buttonView		= HtmlTag::create( 'a', $iconView, [
			'href'		=> $urlView,
			'class'		=> 'button tiny layer-html',
			'title'		=> '['.$module->title.'] '.$item->file
		] );
		$buttonUnlink	= HtmlElements::Link( $urlUnlink, $iconUnlink, 'button tiny' );

		if( !checkFile( $uri ) ){
			$this->env->messenger->noteError( 'Missing: '.$uri );
			$class	= 'missing';
			$buttonView	= HtmlElements::Link( $urlView, $iconView, 'button tiny layer-html disabled' );
		}
		$label		= HtmlTag::create( 'span', $item->file, ['class' => 'icon script'] );
		$rows[]		= '<tr class="'.$class.'"><td>'.$label.'</td><td>'.$source.'</td><td>'.$load.'</td><td>'.$buttonView.$buttonUnlink.'</td></tr>';
	}
	$heads		= HtmlElements::TableHeads( ["Datei", "Quelle", "Laden", "Aktion"] );
	$colgroup	= HtmlElements::ColumnGroup( ["60%", "15%", "10%", "15%"] );
	$table		= '<table>'.$colgroup.$heads.'</tr>'.join( $rows ).'</table>';
	$scripts	= '<h4>'.$w->resourceScripts.'</h4>'.$table.'<br/>';
}

//  --  TABLE: IMAGES  --  //
$images	= '';
if( $module->files->images ){
	$rows	= [];
	foreach( $module->files->images as $item ){
		$count++;
		$class		= NULL;
		$source		= !empty( $item->source ) ? $item->source : 'local';
		$theme		= !empty( $item->theme ) ? $item->theme : $configApp->get( 'layout.theme' );
#		$preload	= !empty( $item->preload ) ? $item->preload : NULL;
		$uri		= $pathApp;
		switch( $source ){
			case 'url':		$uri	= ""; break;													//  absolute URL
			case 'theme':	$uri	.= $pathThemes.$theme.'/img/'; break;					//  URL relative to scripts library
			case 'local':	$uri	.= $pathImages; break;											//  file in local scripts folder
			default:		$uri	.= $item->source.'/'; break;									//  ...
		}
		$uri	.= $item->file;

		$urlView		= './admin/module/editor/viewCode/'.$moduleId.'/image/'.base64_encode( $uri );
		$urlUnlink		= './admin/module/editor/removeFile/'.$moduleId.'/image/'.base64_encode( $item->file ).'?tab=resources';
		$buttonView		= HtmlElements::Link( $urlView, $iconView, 'button tiny layer-image', '['.$module->title.'] '.$item->file );
		$buttonUnlink	= HtmlElements::Link( $urlUnlink, $iconUnlink, 'button tiny' );

		if( !checkFile( $uri ) ){
			$this->env->messenger->noteError( 'Missing: '.$uri );
			$class	= 'missing';
			$buttonView	= HtmlElements::Link( $urlView, $iconView, 'button tiny layer-image disabled' );
		}
		$label		= HtmlTag::create( 'span', $item->file, ['class' => 'icon image'] );
		$rows[]		= '<tr class="'.$class.'"><td>'.$label.'</td><td>'.$source.'</td><td>'.$buttonView.$buttonUnlink.'</td></tr>';
	}
	$heads		= HtmlElements::TableHeads( ["Datei", "Quelle", "Aktion"] );
	$colgroup	= HtmlElements::ColumnGroup( ["75%", "10%", "15%"] );
	$table		= '<table>'.$colgroup.$heads.'</tr>'.join( $rows ).'</table>';
	$images	= '<h4>'.$w->resourceImages.'</h4>'.$table.'<br/>';
}

//  --  TABLE: FILES  --  //
$files	= '';
if( $module->files->files ){
	$rows	= [];
	foreach( $module->files->files as $item ){
		$count++;
		$class		= NULL;
		$source		= !empty( $item->source ) ? $item->source : 'local';
#		$preload	= !empty( $item->preload ) ? $item->preload : NULL;
		$uri		= $pathApp;
		switch( $source ){
			case 'url':		$uri	= ""; break;													//  absolute URL
			case 'local':	$uri	.= ""; break;													//  file in local scripts folder
			default:		$uri	.= ''; break;													//  ...
		}
		$uri	.= $item->file;

		$urlView		= './admin/module/editor/viewCode/'.$moduleId.'/file/'.base64_encode( $uri );
		$urlUnlink		= './admin/module/editor/removeFile/'.$moduleId.'/file/'.base64_encode( $item->file ).'?tab=resources';
		$buttonView		= HtmlElements::Link( $urlView, $iconView, 'button tiny layer-html' );
		$buttonUnlink	= HtmlElements::Link( $urlUnlink, $iconUnlink, 'button tiny' );

		if( !checkFile( $uri ) ){
			$this->env->messenger->noteError( 'Missing: '.$uri );
			$class	= 'missing';
			$buttonView	= HtmlElements::Link( $urlView, $iconView, 'button tiny layer-html disabled' );
		}
		$label		= HtmlTag::create( 'span', $item->file, ['class' => 'icon file'] );
		$rows[]		= '<tr class="'.$class.'"><td>'.$label.'</td><td>'.$source.'</td><td>'.$buttonView.$buttonUnlink.'</td></tr>';
	}
	$heads		= HtmlElements::TableHeads( ["Datei", "Quelle", "Aktion"] );
	$colgroup	= HtmlElements::ColumnGroup( ["75%", "10%", "15%"] );
	$table		= '<table>'.$colgroup.$heads.'</tr>'.join( $rows ).'</table>';
	$files		= '<h4>'.$w->resourceFiles.'</h4>'.$table.'<br/>';
}



$iconAdd	= HtmlTag::create( 'img', NULL, ['href' => $pathIcons.'add.png'] );
$optType	= HtmlElements::Options( $words['resource-types'] );

$optSourceScript	= HtmlElements::Options( $words['sources-script'] );
$optSourceStyle		= HtmlElements::Options( $words['sources-style'] );
$optSourceImage		= HtmlElements::Options( $words['sources-image'] );
$optSourceFile		= HtmlElements::Options( $words['sources-file'] );
$optLoad			= HtmlElements::Options( ['' => 'durch Modul', 'auto' => 'automatisch'] );

$panelAdd	= '
	<form id="form_admin_module_resource_add" action="./admin/module/editor/addFile/'.$moduleId.'?tab=resources" method="post">
		<fieldset>
			<legend>Datei hinzufügen</legend>
			<ul class="input">
				<li>
					<label for="input_type">Typ</label><br/>
					<select name="type" id="input_type" onchange="showOptionals(this);">'.$optType.'</select>
				</li>
				<li>
					<label for="input_resource">Datei</label><br/>
					<input type="text" name="resource" id="input_resource" class="max"/>
				</li>
				<li class="optional type-script">
					<label for="input_source_script">Quelle</label><br/>
					<select name="source_script" id="input_source_script" class="max">'.$optSourceScript.'</select>
				</li>
				<li class="optional type-style">
					<label for="input_source_style">Quelle</label><br/>
					<select name="source_style" id="input_source_style" class="max">'.$optSourceStyle.'</select>
				</li>
				<li class="optional type-image">
					<label for="input_source_image">Quelle</label><br/>
					<select name="source_image" id="input_source_image" class="max">'.$optSourceImage.'</select>
				</li>
				<li class="optional type-file">
					<label for="input_source_style">Quelle</label><br/>
					<select name="source_file id="input_source_file" class="max">'.$optSourceFile.'</select>
				</li>
				<li class="optional type-script type-style">
					<label for="input_load">Laden</label><br/>
					<select name="load" id="input_load" class="max">'.$optLoad.'</select>
				</li>
			</ul>
			<div class="buttonbar">
				'.HtmlElements::Button( 'addFile', 'hinzufügen', 'button add' ).'
			</div>
		</fieldset>
	</form>
';

if( $count)
	$tableResources	= $classes.$templates.$locales.$styles.$scripts.$images.$files;

return '
<div class="column-left-70">
	'.$tableResources.'
</div>
<div class="column-right-30">
	'.$panelAdd.'
</div>
<div class="column-clear"></div>
<script>
function showOptionals(elem){
	var form = $(elem.form);
	var type = $(elem).attr("name")+"-"+$(elem).val();
	form.find("li.optional").hide();
	form.find("li.optional."+type).show();
}

$(document).ready(function(){
	$("#form_admin_module_resource_add #input_type").trigger("change");
	$("a.disabled").attr("href","#");
});
</script>
';

?>
