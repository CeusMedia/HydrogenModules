<?php

$w	= (object) $words['view'];

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
?>
