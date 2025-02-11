<?php

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array<string,array<string,string|int>> $words */
/** @var Entity_Mission $mission */

//$w	= (object) $words['view'];

$this->env->getPage()->js->addScriptOnReady('WorkMissionsViewer.init('.$mission->missionId.')');

$panelFacts		= $view->loadTemplateFile( 'work/mission/view.facts.php' );
$panelContent	= $view->loadTemplateFile( 'work/mission/view.content.php' );
$panelDocuments	= $view->loadTemplateFile( 'work/mission/view.documents.php' );

return '
'.$panelFacts.'
'.$panelContent.'
'.$panelDocuments.'
<script>
var missionId = '.$mission->missionId.';
</script>
';
