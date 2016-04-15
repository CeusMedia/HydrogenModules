 <?php

//$w		= (object) $words['editRights'];

$wordsRole	= $env->getLanguage()->getWords( 'manage/role' );

$acl	= $env->getAcl();
$matrix	= $acl->index();
$number	= 0;
$list	= array();
foreach( $matrix as $controller => $actions ){
	if( ( $size = count( $actions ) ) ){
		$number++;
		$width	= round( 100 / $size, 8 ).'%';
		$row	= array();
		$row[]	= UI_HTML_Tag::create( 'div', $number, array( 'class' => 'counter' ) );
		foreach( $actions as $action ){
			$access		= $acl->hasRight( $user->roleId, $controller, $action );
			$class		= $access ? 'yes' : 'no';
			$path		= str_replace( "_", "/", $controller ).'/'.$action;
			$title		= $controller.'/'.$action;
			$labelC		= str_replace( " ", "->", ucwords( str_replace( "_", " ", $controller ) ) );
			$labelS		= $wordsRole['type-right'][$access];
			$label		= 'Controller: '.$labelC.'\nAction: '.$action.'\nAccess: '.$labelS;
			$attr		= array(
				'class'		=> $class,
				'style'		=> "width: ".$width,
				'title'		=> $title,
				'onclick'	=> 'alert(\''.$label.'\');',
			);
			$row[]	= UI_HTML_Tag::create( 'div', '', $attr );
		}
		$list[]	= UI_HTML_Tag::create( 'div', join( $row ), array( 'class' => 'bar' ) );
	}
}

return '
<div class="content-panel">
	<h4>'.$words['editRights']['heading'].'</h4>
	<div class="content-panel-inner">
		'.UI_HTML_Tag::create( 'div', $list, array( 'class' => 'acl-card' ) ).'
	</div>
</div>
';
?>
