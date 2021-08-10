<div class="panel">
	<div class="row Bleumi-header">
		<img src="{$module_dir|escape:'html':'UTF-8'}views/img/bleumi_config_logo.png" class="col-xs-6 col-md-3 text-center" id="payment-logo" />
			<div class="col-xs-6 col-md-6">
		</div>
		<div class="col-xs-12 col-md-3 text-center">
			<a href="https://account.bleumi.com/account/?app=payment&mode=production&tab=integration" onclick="MyFunction();return false;" class="btn btn-primary" target="_blank" id="create-account-btn">{l s='Create an account' mod='Bleumi'}</a><br />
			{l s='Already have one?' mod='Bleumi'}<a href="https://account.bleumi.com/account/?app=payment&mode=production&tab=integration" onclick="MyFunction();return false;" target="_blank"> {l s='Log in' mod='Bleumi'}</a>
		</div>
	</div>
	<div class="row">
			<div class="col-md-12">
			{l s='Accept Traditional and Crypto Currency Payments.' mod='Bleumi'}
			</div>
	</div>
	<div class="Bleumi-content">
		<hr />

		<div class="row">
			<div class="col-md-12">
				<p class="text-muted">{l s='You can pay with PayPal, Credit/Debit Card, Algorand, USD Coin, Celo, Celo Dollar, R-BTC, Dollar on Chain.' mod='Bleumi'}</p>
			</div>
		</div>
	</div>
</div>