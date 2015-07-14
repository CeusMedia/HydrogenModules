<?php
//	if( $env->getRequest()->isAjax() )								// this is an AJAX request
//		return $content;													// deliver content only

$links		= $words['links'];
$sublinks	= array( 'admin/module' => $words['links_admin_module'] );

$controller	= $env->request->get( 'controller' );
$action		= $env->request->get( 'action' );

if( $controller == "admin/module/source" )
	$sublinks	= array();

$naviMain	= new CMF_Hydrogen_View_Helper_Navigation_SingleList( $links, NULL, 'layout-navigation-main-inner' );
$naviMain	= $naviMain->render( $controller.'/'.$action, TRUE );

$naviSub	= "";
foreach( $sublinks as $path => $links ){
	if( substr( $controller.'/', 0, strlen( $path ) ) == $path ){
		$naviSub	= new CMF_Hydrogen_View_Helper_Navigation_SingleList( $links, NULL, 'layout-navigation-sub-inner' );
		$naviSub	= $naviSub->render( $controller, TRUE );
	}
}

$model			= new Model_Instance( $env );
$optInstance	= array( '' => '-');
foreach( $model->getAll() as $instanceId => $instance )
	$optInstance[$instanceId]	= $instance->title;
asort( $optInstance );
$instanceId		= $env->getSession()->get( 'instanceId' );
$optInstance	= UI_HTML_Elements::Options( $optInstance, $instanceId );

$badges	= array();
$infos	= array(
	$words['footer_info']['copyright'],
	sprintf( $words['footer_info']['date'], date( 'd.m.Y') ),
	sprintf( $words['footer_info']['time'], date( 'H:i:s') ),
);
if( !$config->get( 'app.production' ) ){
	$infos[]	= sprintf( $words['footer_info']['time_stopped'], $env->getClock()->stop( 0, 2 ) );
	if( $env->has( 'dbc' ) && $env->getDatabase()->numberStatements )
		$infos[]	= sprintf( $words['footer_info']['requests'], $env->getDatabase()->numberStatements );
}

$userId		= (int) $session->get( 'userId' );
$roleId		= (int) $session->get( 'roleId' );
if( $userId ){
	$modelUser	= new Model_User( $env );
	$infos[]	= 'Benutzer: '.$modelUser->get( $userId, 'username' ).' <small>('.$userId.')</small>';
}
if( $roleId ){
	$modelRole	= new Model_Role( $env );
	$infos[]	= 'Rolle: '.$modelRole->get( $userId, 'title' ).' <small>('.$roleId.')</small>';
}

$badges[]	= '<a href="http://validator.w3.org/check?uri=referer"><img style="border: 0; width: 48px; height: 16px" src="http://www.w3.org/Icons/valid-xhtml10" alt="validate HTML (as XHTML 1.0 Strict)"/></a>';
$badges[]	= '<a href="http://jigsaw.w3.org/css-validator/check/referer"><img style="border: 0; width: 48px; height: 16px" src="http://jigsaw.w3.org/css-validator/images/vcss" alt="validate CSS"/></a>';
$infos		= '<span>'.join( '</span><span>', $infos ).'</span>';
$badges		= '<span>'.join( '</span><span>', $badges ).'</span>';

$linkReset	= '<a href="./?resetInstanceId">Instanz</a>';

$path		= $env->getRequest()->get( '__path' );
$body		= '
<script>
function selectInstanceId(id, forward){
	var url = "./admin/instance/select/"+id;
	if(forward)
		url += "?forward="+forward;
	document.location.href = url;
}
</script>
<div id="layout-page">
	<div id="layout-navigation-top">
		<div id="selector-instance">
			<label for="input_instanceId">'.$linkReset.':</label>&nbsp;
			<select id="input_instanceId" name="instanceId" onchange="selectInstanceId($(this).val(), \''.$path.'\');">'.$optInstance.'</select>
		</div>
	</div>
	<div id="layout-header"><h1>Hydra</h1></div>
	<div id="layout-navigation">
		<div id="layout-navigation-main">
			'.$naviMain.'
		</div>
	</div>
	<div id="layout-navigation-sub">
		'.$naviSub.'
		<div style="clear: left"></div>
	</div>
	<div id="layout-field">
		<div id="layout-messenger">'.$messenger->buildMessages().'</div>
		<div id="layout-content">
			'.$content.'
			<div style="clear: both"></div>
		</div>
	</div>
	<div id="layout-footer">
		<div id="layout-footer-inner">
			<div id="footer-info">
				'.$infos.'
			</div>
			<div id="footer-badges">
				'.$badges.'
			</div>
			<div style="clear: both"></div>
			<div id="layer-dev">
				<xmp>'.$dev.'</xmp>
			</div>
		</div>
	</div>
</div>';

$env->clock->profiler->tick( 'interface: master' );

$pathJsLib		= $config->get( 'path.scripts.lib' );
$pathCssLib		= $config->get( 'path.styles.lib' );

$page->addFavouriteIcon( 'http://cdn.int1a.net/img/famfamfam/silk/brick.png' );

$page->css->primer->addUrl( $pathCssLib.'xmp.formats.css' );
$page->css->primer->addUrl( $pathCssLib.'layout.column.css' );
$page->addPrimerStyle( 'layout.messenger.css' );
$page->addPrimerStyle( 'form.css' );
$page->addPrimerStyle( 'form.button.css' );
$page->addPrimerStyle( 'form.fieldset.css' );
$page->addPrimerStyle( 'pagination.css' );
$page->addPrimerStyle( 'layout.navigation.css' );
$page->addPrimerStyle( 'layout.footer.css' );
$page->addThemeStyle( 'layout.css' );
$page->addThemeStyle( 'layout.navigation.sub.css' );
$page->addThemeStyle( 'layout.footer.css' );
$page->addThemeStyle( 'form.fieldset.css' );
$page->addThemeStyle( 'layer.css' );
$page->addThemeStyle( 'table.css' );
$page->addThemeStyle( 'style.css' );
$page->addThemeStyle( 'font.css' );
$page->addBody( $body );
return $page->build( array( 'class' => 'colored' ) );
?>
