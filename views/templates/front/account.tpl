{extends file="customer/page.tpl"}

{block name='page_title'}
  {l s='Twój kredyt kupiecki' d='Modules.B2BStore.Shop'}
{/block}

{block name='page_content'}
    <div class="card page-content">
        <div class="card-body">
        {if $credit_alert}
            <div class="alert alert-warning">{$credit_alert}</div>
        {/if}
        {if $credit}
            <table class="table">
                <tr>
                    <th>Wartość twojego kredytu</th>
                    <td>{$credit}</td>
                </tr>
                <tr>
                    <th>Niespłacone zobowiązania</th>
                    <td>{$unpaid_orders}</td>
                </tr>
                <tr>
                    <th>Pozostała kwota</th>
                    <td>{$diff_amount}</td>
                </tr>
            </table>
        {else}
            <p>{l s='Nie udzielono jeszcze żadnego kredytu.' d='Modules.B2BStore.Shop'}</p>
        {/if}
        </div>
    </div>
    <div class="card page-content">
        <div class="card-body">
            {if $list_orders}
                <table class="table">
                    <thead>
                        <tr>
                        <th>{l s='Order reference' d='Shop.Theme.Checkout'}</th>
                        <th>{l s='Date' d='Shop.Theme.Checkout'}</th>
                        <th>{l s='Total price' d='Shop.Theme.Checkout'}</th>
                        <th class="hidden-md-down">{l s='Payment' d='Shop.Theme.Checkout'}</th>
                        <th class="hidden-md-down">{l s='Status' d='Shop.Theme.Checkout'}</th>
                        <th>{l s='Invoice' d='Shop.Theme.Checkout'}</th>
                        <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$list_orders item=order}
                        <tr>
                            <th scope="row">{$order.details.reference}</th>
                            <td>{$order.details.order_date}</td>
                            <td class="text-xs-right">{$order.totals.total.value}</td>
                            <td class="hidden-md-down">{$order.details.payment}</td>
                            <td>
                            <span
                                class="label label-pill {$order.history.current.contrast}"
                                style="background-color:{$order.history.current.color}"
                            >
                                {$order.history.current.ostate_name}
                            </span>
                            </td>
                            <td class="text-sm-center hidden-md-down">
                            {if $order.details.invoice_url}
                                <a href="{$order.details.invoice_url}"><i class="material-icons">&#xE415;</i></a>
                            {else}
                                -
                            {/if}
                            </td>
                            <td class="text-sm-center order-actions">
                                <a href="{$order.details.details_url}" data-link-action="view-order-details">
                                    {l s='Details' d='Shop.Theme.Customeraccount'}
                                </a>
                            </td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
            {/if}
        </div>
    </div>
{/block}
