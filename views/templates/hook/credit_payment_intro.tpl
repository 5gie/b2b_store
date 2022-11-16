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

<section>
  {if $credit_alert}
    <div class="alert alert-warning">{$credit_alert}</div>
  {/if}
  <table class="table">
    <tr>
      <td>{l s='Twój kredyt' d='Modules.B2BStore.Shop'}</td>
      <th>{$credit}</th>
    </tr>
    <tr>
      <td>{l s='Pozostała kwota kredytu' d='Modules.B2BStore.Shop'}</td>
      <th>{$credit_remaining_amount}</th>
    </tr>
    <tr>
      <td></td>
      <th> - {$cart_value}</th>
    </tr>
  </table>
</section>
