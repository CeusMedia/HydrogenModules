<?php

return '
<div class="content-panel content-panel-info">
	<h3>Execution</h3>
	<div class="content-panel-inner">
		<div id="request-data">
			<dl class="not-dl-horizontal">
				<dt>Time Init</dt>
				<dd>'.$time_init.' ms</dd>
				<dt>Time Render</dt>
				<dd>'.$time_render.' ms</dd>
				<dt>URL</dt>
				<dd id="data-url">'.$url.'</dd>
			</dl>
		</div>
	</div>
</div>
';
