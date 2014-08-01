<?php
$w	= (object) $words['filter'];
return '
<!-- templates/admin/mail/attachment/index.filter.php -->
<div class="content-panel content-panel-form">
    <div class="content-panel-inner">
		<h3>'.$w->heading.'</h3>
		<form action="./admin/mail/attachment/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="filter" class="btn btn-primary">'.$w->buttonFilter.'</button>
				<a href="./admin/main/attachment/filter/reset" class="btn btn-small">'.$w->buttonReset.'</a>
			</div>
		</form>
	</div>
</div>
';
?>
