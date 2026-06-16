{**
 * Servizi aggiuntivi — incluso dentro il form principale
 *}
{if isset($vm.options.available) && $vm.options.available|count > 0}
    <div class="sr-card" style="margin-bottom:12px;">
        <p class="sr-card-title">
            <i class="icon-plus-sign"></i>
            {l s='Servizi aggiuntivi' mod='spedisciquishipping'}
        </p>
        {foreach $vm.options.available as $opt}
            {assign var='opt_selected' value=false}
            {if isset($vm.options.selected[$opt.type]) && $vm.options.selected[$opt.type]}
                {assign var='opt_selected' value=true}
            {/if}
            <div class="sr-opt{if $opt_selected} sr-opt-active{/if}{if !$opt.enabled} sr-opt-disabled{/if}"
                id="sr-opt-{$opt.type|escape:'html':'UTF-8'}">
                <div class="sr-opt-header" {if $opt.enabled}onclick="sqToggleOption('{$opt.type|escape:'js':'UTF-8'}')" {/if}>
                    <div class="sr-opt-check">
                        <input type="checkbox" name="option_{$opt.type|escape:'html':'UTF-8'}"
                            id="sr-chk-{$opt.type|escape:'html':'UTF-8'}" value="1" {if $opt_selected}checked{/if}
                            {if !$opt.enabled}disabled{/if} data-type="{$opt.type|escape:'html':'UTF-8'}"
                            data-cost-rate="{$opt.cost_rate|floatval}"
                            data-cost-type="{$opt.cost_formula|default:'percentage'|escape:'html':'UTF-8'}"
                            onchange="sqOnCheckChange('{$opt.type|escape:'js':'UTF-8'}')">
                    </div>
                    <div class="sr-opt-info">
                        <p class="sr-opt-label">
                            {$opt.label|escape:'html':'UTF-8'}
                            {if isset($opt.tag) && $opt.tag}
                                <span class="sr-opt-tag {if $opt.enabled}sr-opt-tag-rec{else}sr-opt-tag-soon{/if}">
                                    {$opt.tag|escape:'html':'UTF-8'}
                                </span>
                            {/if}
                        </p>
                        <p class="sr-opt-desc">{$opt.description|escape:'html':'UTF-8'}</p>
                    </div>
                </div>
                {if $opt.has_value_field}
                    <div class="sr-opt-body{if $opt_selected} sr-opt-body-visible{/if}"
                        id="sr-body-{$opt.type|escape:'html':'UTF-8'}">
                        <label class="sr-field-label" for="sr-val-{$opt.type|escape:'html':'UTF-8'}">
                            {$opt.value_label|escape:'html':'UTF-8'}
                        </label>
                        <div class="sr-input-row">
                            <div class="sr-euro-wrap">
                                <span class="sr-euro-sym">€</span>
                                <input type="number" class="sr-value-input" id="sr-val-{$opt.type|escape:'html':'UTF-8'}"
                                    name="option_{$opt.type|escape:'html':'UTF-8'}_value" min="{$opt.value_min|floatval}"
                                    max="{$opt.value_max|floatval}" step="0.01" value="{$vm.order.total_paid|floatval}"
                                    placeholder="0.00" oninput="sqCalcOptionCost('{$opt.type|escape:'js':'UTF-8'}')">
                            </div>
                            <span class="sr-cost-preview" id="sr-cost-{$opt.type|escape:'html':'UTF-8'}"></span>
                        </div>
                        {if isset($opt.notice) && $opt.notice}
                            <div class="sr-notice">
                                <i class="icon-info-circle"></i>
                                <span>{$opt.notice|escape:'html':'UTF-8'}</span>
                            </div>
                        {/if}
                    </div>
                {/if}
            </div>
        {/foreach}
    </div>
{/if}