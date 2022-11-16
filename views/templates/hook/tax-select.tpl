{if $tax_actions}
    <div class="b2b-tax-display-select">
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="taxDisplayMethod" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {$tax_actions[$current_method].name}
                <span class="material-icons">chevron_down</span>
            </button>
            <div class="dropdown-menu" aria-labelledby="taxDisplayMethod">
                {foreach from=$tax_actions item=action key=key}
                    {if $current_method != $key} 
                    <a class="dropdown-item" href="{$action.url}">{$action.name}</a>
                    {/if}
                {/foreach}
            </div>
        </div>
    </div>
{/if}