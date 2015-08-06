<?php

$w	= (object) $words['view'];

$this->env->getPage()->js->addScriptOnReady('WorkMissionsViewer.init('.$mission->missionId.')');

$panelFacts		= $view->loadTemplateFile( 'work/mission/view.facts.php' );
$panelContent	= $view->loadTemplateFile( 'work/mission/view.content.php' );

return '
'.$panelFacts.'
'.$panelContent.'
<script>
var missionId = '.$mission->missionId.';
</script>
';
?>
