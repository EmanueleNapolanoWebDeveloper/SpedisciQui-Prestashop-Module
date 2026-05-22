<div class="spedisciqui-extra" style="margin-top: 10px;">
    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
        <input type="checkbox" name="spedisciqui_insurance_{$carrier.id}" id="sq_insurance_{$carrier.id}" value="1"
            {if $insurance_checked}checked{/if} />
        <span>
            Aggiungi assicurazione
            {if $insurance_price > 0}
                <strong>(+{$insurance_price|string_format:"%.2f"}€)</strong>
            {/if}
        </span>
    </label>
</div>