<?php
return '
<div class="content-panel">
	<h3>Pay In</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<p>
				...
			</p>
			<div class="buttonbar">
				<a href="./manage/my/mangopay/wallet/view/'.$walletId.'" class="btn btn-small"><b class="fa fa-arrow-left"></b> zurück</a>
				<a href="./manage/my/mangopay/wallet/payIn/'.$walletId.'/bankwire" class="btn btn-primary"><b class="fa fa-arrow-right"></b> Überweisung</a>
				<a href="./manage/my/mangopay/wallet/payIn/'.$walletId.'/card" class="btn btn-primary"><b class="fa fa-credit-card"></b> Kreditkarte</a>
				<a href="./manage/my/mangopay/wallet/payIn/'.$walletId.'/card_web" class="btn btn-primary"><b class="fa fa-credit-card"></b> Kreditkarte / Web</a>
				<a href="./manage/my/mangopay/wallet/payIn/'.$walletId.'/directdebit" class="btn btn-primary"><b class="fa fa-money"></b> Lastschrift</a>
			</div>
		</div>
	</div>
</div>';
?>
