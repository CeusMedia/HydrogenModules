<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Bootstrap\Alert;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var object $newsletter */
/** @var object $user */

if( !$env->getModules()->has( 'Resource_Disclosure' ) )
	return Alert::create( 'Install module <strong><kbd>Resource_Disclosure</kbd></strong> to show a visual rights map.' )
		->setClass(Alert::CLASS_INFO);

//$w		= (object) $words['editRights'];

$wordsRole	= $env->getLanguage()->getWords( 'manage/role' );

$disclosure			= new Resource_Disclosure( $env );
$controllerActions	= [];
$controllerClasses	= $disclosure->reflect( 'classes/Controller/' );
foreach( $controllerClasses as $className => $classData ){
	$className	= strtolower( str_replace( '_', '/', $className ) );
	$controllerActions[$className]	= [];
	foreach( $classData->methods as $methodName => $methodData )
		$controllerActions[$className][]	= $methodName;
}

$acl	= $env->getAcl();
$number	= 0;
$list	= [];
foreach( $controllerActions as $controller => $actions ){
	if( ( $size = count( $actions ) ) ){
		$number++;
		$width	= round( 100 / $size, 8 ).'%';
		$row	= [];
		$row[]	= HtmlTag::create( 'div', $number, ['class' => 'counter'] );
		foreach( $actions as $action ){
			$access		= $acl->hasRight( $user->roleId, $controller, $action );
			$class		= $access ? 'yes' : 'no';
			$path		= str_replace( "_", "/", $controller ).'/'.$action;
			$title		= $controller.'/'.$action;
			$labelC		= str_replace( " ", "->", ucwords( str_replace( "_", " ", $controller ) ) );
			$labelS		= $wordsRole['type-right'][$access];
			$label		= 'Controller: '.$labelC.'\nAction: '.$action.'\nAccess: '.$labelS;
			$attr		= [
				'class'		=> $class,
				'style'		=> "width: ".$width,
				'title'		=> $title,
				'onclick'	=> 'alert(\''.$label.'\');',
			];
			$row[]	= HtmlTag::create( 'div', '', $attr );
		}
		$list[]	= HtmlTag::create( 'div', join( $row ), ['class' => 'bar'] );
	}
}

return '
<div class="content-panel">
	<h4>'.$words['editRights']['heading'].'</h4>
	<div class="content-panel-inner">
		'.HtmlTag::create( 'div', $list, ['class' => 'acl-card'] ).'
	</div>
</div>
';
?>
