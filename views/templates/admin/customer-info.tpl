<div class="panel col-lg-12">
    <h3>Informacje o kliencie</h3>
    <div class="row">
        <div class="col-lg-6">
            <table class="table table-responsive-md">
                <tbody>
                    <tr>
                        <td><b>Imię i Nazwisko</b></td>
                        <td>{$customer.firstname} {$customer.lastname}</td>
                    </tr>
                    <tr>
                        <td><b>Adres e-mail</b></td>
                        <td>{$customer.email}</td>
                    </tr>
                    <tr>
                        <td><b>Firma</b></td>
                        <td>{$customer.company}</td>
                    </tr>
                    <tr>
                        <td><b>NIP</b></td>
                        <td>{$customer.siret}</td>
                    </tr>
                    <tr>
                        <td><b>B2B</b></td>
                        <td>
                            {if $is_b2b}
                            <i class="icon-check" style="color:#72c279"></i> 
                            {else}
							<i class="icon-remove" style="color:#e08f95"></i> 
                            {/if}
                        </td>
                    </tr>
                    <tr>
                        <td><b>Zarejestrowany</b></td>
                        <td>{$customer.date_add}</td>
                    </tr>
                    {if $customer_stats}
                    <tr>
                        <td><b>Ostatnie logowanie</b></td>
                        <td>{$customer_stats.last_visit}</td>
                    </tr>
                    {/if}
                </tbody>
            </table>
        </div>
        <div class="col-lg-6">
            <table class="table table-responsive-md">
                <tbody>
                    {if $customer_stats}
                    <tr>
                        <td><b>Ilość zamówień</b></td>
                        <td>{$customer_stats.nb_orders}</td>
                    </tr>
                    <tr>
                        <td><b>Wartość zamówień</b></td>
                        <td>{$customer_stats.total_orders}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td><b>Kredyt</b></td>
                        <td>{$credit}</td>
                    </tr>
                    <tr>
                        <td><b>Do spłaty</b></td>
                        <td>{$unpaid_orders}</td>
                    </tr>
                    <tr>
                        <td><b>Pozostało</b></td>
                        <td>{$credit_remaining_amount}</td>
                    </tr>
                    <tr>
                        <td><b>Min. kw. zamówienia</b></td>
                        <td>{$min_order_amount}</td>
                    </tr>
                    <tr>
                        <td><b>Domyślna Waluta</b></td>
                        <td>{$customer_currency}</td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>
</div>