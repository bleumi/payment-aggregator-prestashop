<div class="row">
	<div class="col-xs-12 col-md-6">
		<p class="payment_module" id="Bleumi_payment_button">
			{if $cart->getOrderTotal() < 2}
				<a href="">
					<img src="{$domain|cat:$payment_button|escape:'html':'UTF-8'}" alt="{l s='Pay with my payment module' mod='Bleumi'}" />
					{l s='Minimum amount required in order to pay with my payment module:' mod='Bleumi'} {convertPrice price=2}
				</a>
			{else}
				<a href="{$link->getModuleLink('Bleumi', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}" title="{l s='Pay with my payment module' mod='Bleumi'}">
					<img src="{$module_dir|escape:'htmlall':'UTF-8'}/logo.png" alt="{l s='Pay with my payment module' mod='Bleumi'}" width="32" height="32" />
					{l s='Pay with my payment module' mod='Bleumi'}
				</a>
			{/if}
		</p>
	</div>
</div>
