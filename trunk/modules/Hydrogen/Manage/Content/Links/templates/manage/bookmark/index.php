<?php
return '
'.$this->renderTabs().'
<div class="row-fluid">
	<div class="span3">
		<h3>Lesezeichen</h3>
		'.$this->renderList().'
	</div>
	<div class="span9">
		<h3>Lesezeichen</h3>
		Hier kannst du Verknüpfungen zu Internetseiten anderer Anbieter speichern, die du öfter einbinden willst.<br/>
		Die notierten Links lassen sich dann z.B. im HTML-Editor der Seiten oder Neuigkeiten komfortabel verwenden.<br/>
		<br/>
		<a href="./manage/bookmark/add" class="btn btn-small btn-info"><i class="icon-plus icon-white"></i> hinzufügen</a>
	</div>
</div>
';
?>
