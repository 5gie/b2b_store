{**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

{if $status == 'ok'}
    {if $credit_alert}
        <div class="alert alert-warning">{$credit_alert}</div>
    {/if}
    <table class="table">
        <tr>
            <th>{l s='Wartność zamówienia' d='Modules.B2BStore.Shop'}</th>
            <td>{$total_tax_excl} <small class="text-muted">({l s='Netto' d='Modules.B2BStore.Shop'})</small></td>
        </tr>
        <tr>
            <th>{l s='Twój kredyt' d='Modules.B2BStore.Shop'}</th>
            <td>{$credit} <small class="text-muted">({l s='Netto' d='Modules.B2BStore.Shop'})</small></td>
        </tr>
        <tr>
            <th>{l s='Niespłacone zobowiązania' d='Modules.B2BStore.Shop'}</th>
            <td>{$unpaid_orders} <small class="text-muted">({l s='Netto' d='Modules.B2BStore.Shop'})</small></td>
        </tr>
        <tr>
            <th>{l s='Pozostała kwota' d='Modules.B2BStore.Shop'}</th>
            <td>{$diff_amount} <small class="text-muted">({l s='Netto' d='Modules.B2BStore.Shop'})</small></td>
        </tr>
    </table>
    <p>
      {l s='Jeśli masz pytania, uwagi lub wątpliwości, prosimy o kontakt z naszą [1]obsługą klienta[/1].' d='Modules.B2BStore.Shop' sprintf=['[1]' => "<a href='{$contact_url}'>", '[/1]' => '</a>']}
    </p>
{else}
    <p class="warning">
      {l s='Zauważyliśmy problem z Twoim zamówieniem. Jeżeli uważasz, że nastąpił jakiś błąd, proszę skontaktuj się z naszą [1]obsługą klienta[/1].' d='Modules.B2BStore.Shop' sprintf=['[1]' => "<a href='{$contact_url}'>", '[/1]' => '</a>']}
    </p>
{/if}
