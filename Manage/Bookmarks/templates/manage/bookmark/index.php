<?php
return '
<div class="row-fluid">
	<div class="span3">
		<div class="content-panel">
			<h3>Lesezeichen</h3>
			<div class="content-panel-inner">
				'.$this->renderList().'
			</div>
		</div>
	</div>
	<div class="span9">
		<div class="content-panel">
			<h3>Lesezeichen</h3>
			<div class="content-panel-inner">
				Hier kannst du Verknüpfungen zu Internetseiten anderer Anbieter speichern, die du öfter einbinden willst.<br/>
				Die notierten Links lassen sich dann z.B. im HTML-Editor der Seiten oder Neuigkeiten komfortabel verwenden.<br/>
				<br/>
				<a href="./manage/bookmark/add" class="btn btn-small btn-success"><i class="icon-plus icon-white"></i> hinzufügen</a>
			</div>
		</div>
	</div>
</div>
';
?>
