<?php

$count	= 0;
/*print_m( $module );
print_m( $moduleId );
die;*/
$instances	= array();
$logic		= Logic_Instance::getInstance( $this->env );
$model		= new Model_Instance( $this->env );
foreach( $model->getAll() as $instanceId => $instance ){
	$instance->modules	= $logic->listModules( $instanceId );
	if( array_key_exists( $moduleId, $instance->modules['installed'] ) ){
		$instance->id	= $instanceId;
		$instances[$instance->title]	= $instance;
	}
}
ksort( $instances );

if( !$instances )
	return;

$instanceId	= $this->env->getSession()->get( 'instanceId' );
$list		= array();
foreach( $instances as $instance ){
	$count			+= 1;
	$version		= $instance->modules['installed'][$moduleId]->versionInstalled;
	$version		= UI_HTML_Tag::create( 'small', '&nbsp;('.$version.')', array( 'class' => 'muted' ) );
	$link			= UI_HTML_Tag::create( 'a', $instance->title, array(
		'href'				=> './admin/instance/select/'.$instance->id,
		'class'				=> 'instance',
		'data-instance-id'	=> $instance->id,
		'onclick'			=> "return selectInstanceId('".$instance->id."', 'admin/module/viewer/view/".$moduleId."');",
	) );
	$instance->url	= $instance->protocol.$instance->host.$instance->path;
	$list[]			= UI_HTML_Tag::create( 'li', $link.$version, array(
		'class'		=> $instanceId === $instance->id ? 'active' : NULL,
		'data-url'	=> htmlentities( $instance->url, ENT_QUOTES, 'UTF-8' ),
	) );
}
$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'instances' ) );

return '
<h4>Wird verwendet von:</h4>
<div class="index">
	'.$list.'
</div>
';
?>
