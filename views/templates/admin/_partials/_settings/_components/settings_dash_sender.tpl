{* main *}
<div class="panel sq-sender-panel">

    <div class="sq-sender-header">
        <div class="sq-sender-header-left">
            <div class="sq-sender-header-icon">
                <i class="ti ti-building-store" aria-hidden="true"></i>
            </div>
            <div>
                <p class="sq-sender-title">{l s='Mittente attivo' mod='spedisciquishipping'}</p>
                <p class="sq-sender-subtitle">{l s='Indirizzo di spedizione predefinito' mod='spedisciquishipping'}</p>
            </div>
        </div>
        <a href="{$editSenderUrl|escape:'html':'UTF-8'}" class="sq-btn-edit">
            <i class="ti ti-pencil" aria-hidden="true"></i>
            {l s='Modifica indirizzo' mod='spedisciquishipping'}
        </a>
    </div>

    <div class="sq-sender-body">

        {if $sender}

            <div class="sq-sender-grid">

                {* ── Colonna sinistra: dati anagrafici e indirizzo ── *}
                <div class="sq-info-block">

                    <div class="sq-company-row">
                        <div class="sq-company-avatar">
                            {$sender.company|truncate:2:''|upper|escape:'html':'UTF-8'}
                        </div>
                        <div>
                            <p class="sq-info-label">{l s='Ragione sociale' mod='spedisciquishipping'}</p>
                            <p class="sq-company-name">{$sender.company|escape:'html':'UTF-8'}</p>
                        </div>
                    </div>

                    <hr class="sq-divider">

                    <div class="sq-info-item">
                        <div class="sq-info-icon">
                            <i class="ti ti-id-badge-2" aria-hidden="true"></i>
                        </div>
                        <div>
                            <p class="sq-info-label">{l s='Etichetta' mod='spedisciquishipping'}</p>
                            <p class="sq-info-value">{$sender.label|escape:'html':'UTF-8'}</p>
                        </div>
                    </div>

                    <div class="sq-info-item">
                        <div class="sq-info-icon">
                            <i class="ti ti-user" aria-hidden="true"></i>
                        </div>
                        <div>
                            <p class="sq-info-label">{l s='Referente' mod='spedisciquishipping'}</p>
                            <p class="sq-info-value">
                                {$sender.firstname|escape:'html':'UTF-8'}
                                {$sender.lastname|escape:'html':'UTF-8'}
                            </p>
                        </div>
                    </div>

                    <div class="sq-info-item">
                        <div class="sq-info-icon">
                            <i class="ti ti-map-pin" aria-hidden="true"></i>
                        </div>
                        <div>
                            <p class="sq-info-label">{l s='Indirizzo' mod='spedisciquishipping'}</p>
                            <p class="sq-info-value">{$sender.address1|escape:'html':'UTF-8'}</p>
                            {if $sender.address2}
                                <p class="sq-info-value sq-info-value--secondary">
                                    {$sender.address2|escape:'html':'UTF-8'}
                                </p>
                            {/if}
                        </div>
                    </div>

                    <div class="sq-info-item">
                        <div class="sq-info-icon">
                            <i class="ti ti-building-community" aria-hidden="true"></i>
                        </div>
                        <div>
                            <p class="sq-info-label">{l s='Città' mod='spedisciquishipping'}</p>
                            <p class="sq-info-value">
                                {$sender.postcode|escape:'html':'UTF-8'}
                                {$sender.city|escape:'html':'UTF-8'}
                                ({$sender.state_code|escape:'html':'UTF-8'})
                            </p>
                            <p class="sq-info-value sq-info-value--secondary">
                                {$sender.country_iso|escape:'html':'UTF-8'}
                            </p>
                        </div>
                    </div>

                </div>

                {* ── Colonna destra: contatti e metadati ── *}
                <div class="sq-meta-list">

                    <div class="sq-meta-row">
                        <span class="sq-meta-label">
                            <i class="ti ti-phone" aria-hidden="true"></i>
                            {l s='Telefono' mod='spedisciquishipping'}
                        </span>
                        <span class="sq-meta-value">{$sender.phone|escape:'html':'UTF-8'}</span>
                    </div>

                    <div class="sq-meta-row">
                        <span class="sq-meta-label">
                            <i class="ti ti-mail" aria-hidden="true"></i>
                            {l s='Email' mod='spedisciquishipping'}
                        </span>
                        <span class="sq-meta-value sq-meta-value--link">
                            {if $sender.email}
                                {$sender.email|escape:'html':'UTF-8'}
                            {else}
                                <span class="sq-meta-empty">—</span>
                            {/if}
                        </span>
                    </div>

                    <div class="sq-meta-row">
                        <span class="sq-meta-label">
                            <i class="ti ti-receipt-tax" aria-hidden="true"></i>
                            {l s='Partita IVA' mod='spedisciquishipping'}
                        </span>
                        <span class="sq-meta-value">
                            {if $sender.vat_number}
                                {$sender.vat_number|escape:'html':'UTF-8'}
                            {else}
                                <span class="sq-meta-empty">—</span>
                            {/if}
                        </span>
                    </div>

                    <div class="sq-meta-row">
                        <span class="sq-meta-label">
                            <i class="ti ti-star" aria-hidden="true"></i>
                            {l s='Predefinito' mod='spedisciquishipping'}
                        </span>
                        {if $sender.is_default}
                            <span class="sq-badge sq-badge--info">
                                <i class="ti ti-check" aria-hidden="true"></i>
                                {l s='Sì' mod='spedisciquishipping'}
                            </span>
                        {else}
                            <span class="sq-badge sq-badge--neutral">
                                {l s='No' mod='spedisciquishipping'}
                            </span>
                        {/if}
                    </div>

                    <div class="sq-meta-row">
                        <span class="sq-meta-label">
                            <i class="ti ti-circle-check" aria-hidden="true"></i>
                            {l s='Stato' mod='spedisciquishipping'}
                        </span>
                        {if $sender.is_active}
                            <span class="sq-badge sq-badge--success">
                                <i class="ti ti-check" aria-hidden="true"></i>
                                {l s='Attivo' mod='spedisciquishipping'}
                            </span>
                        {else}
                            <span class="sq-badge sq-badge--danger">
                                <i class="ti ti-ban" aria-hidden="true"></i>
                                {l s='Disattivato' mod='spedisciquishipping'}
                            </span>
                        {/if}
                    </div>

                </div>

            </div>

        {else}

            <div class="sq-alert sq-alert--warning">
                <i class="ti ti-alert-triangle" aria-hidden="true"></i>
                {l s='Nessun mittente configurato.' mod='spedisciquishipping'}
            </div>

        {/if}

    </div>
</div>