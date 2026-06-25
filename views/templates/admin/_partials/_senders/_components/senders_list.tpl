{*
* sender_list.tpl
*
* Variabili Smarty attese dal controller:
* {$senders} array<int, array<string,mixed>> — lista sender normalizzati
    * {$form_action} string — URL action del controller
    * {$show_shop_column} bool — true in contesto multishop
    * {$token} string — token PS per delete form
    *}

    <div class="sq-senders-page">

        {* ===== HEADER ===== *}
        <div class="sq-page-header">
            <div class="sq-page-header__left">
                <h1 class="sq-page-header__title">
                    <span class="sq-page-header__icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                    </span>
                    Indirizzi mittente
                </h1>
                <p class="sq-page-header__sub">
                    {if $senders|@count > 0}
                        {$senders|@count} mittente{if $senders|@count != 1}i{/if} configurato{if $senders|@count != 1}i{/if}
                    {else}
                        Nessun mittente configurato
                    {/if}
                </p>
            </div>
            <div class="sq-page-header__actions">
                <a href="{$form_action|escape:'html'}&action=create" class="sq-btn sq-btn--primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Aggiungi mittente
                </a>
            </div>
        </div>

        {* ===== EMPTY STATE ===== *}
        {if empty($senders)}
            <div class="sq-empty-state">
                <div class="sq-empty-state__icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                </div>
                <h3 class="sq-empty-state__title">Nessun mittente ancora configurato</h3>
                <p class="sq-empty-state__desc">
                    Aggiungi il primo indirizzo mittente per poter creare spedizioni.
                </p>
                <a href="{$form_action|escape:'html'}&action=create" class="sq-btn sq-btn--primary">
                    Configura il primo mittente
                </a>
            </div>

            {* ===== TABLE ===== *}
        {else}
            <div class="sq-card">
                <div class="sq-table-wrap">
                    <table class="sq-table" id="sq-senders-table">
                        <thead>
                            <tr>
                                <th class="sq-table__th sq-table__th--id">#</th>
                                <th class="sq-table__th">Mittente</th>
                                <th class="sq-table__th">Indirizzo</th>
                                <th class="sq-table__th sq-table__th--center">Paese</th>
                                {if $show_shop_column}
                                    <th class="sq-table__th">Shop</th>
                                {/if}
                                <th class="sq-table__th">Contatti</th>
                                <th class="sq-table__th sq-table__th--center">Stato</th>
                                <th class="sq-table__th sq-table__th--date">Aggiornato</th>
                                <th class="sq-table__th sq-table__th--actions">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $senders as $sender}
                                <tr class="sq-table__row{if $sender.is_default} sq-table__row--default{/if}">

                                    {* ID *}
                                    <td class="sq-table__td sq-table__td--id">
                                        <span class="sq-badge sq-badge--neutral">{$sender.id_sender}</span>
                                    </td>

                                    {* Mittente *}
                                    <td class="sq-table__td">
                                        <div class="sq-sender-identity">
                                            <div class="sq-sender-identity__avatar">
                                                {$sender.company|strip_tags|truncate:1:'':true|upper}
                                            </div>
                                            <div class="sq-sender-identity__info">
                                                {if $sender.company}
                                                    <span class="sq-sender-identity__company">{$sender.company|escape:'html'}</span>
                                                {/if}
                                                <span class="sq-sender-identity__name">
                                                    {$sender.firstname|escape:'html'} {$sender.lastname|escape:'html'}
                                                </span>
                                                {if $sender.label}
                                                    <span class="sq-sender-identity__label">{$sender.label|escape:'html'}</span>
                                                {/if}
                                            </div>
                                        </div>
                                    </td>

                                    {* Indirizzo *}
                                    <td class="sq-table__td">
                                        <div class="sq-address">
                                            <span class="sq-address__line1">{$sender.address1|escape:'html'}</span>
                                            {if $sender.address2}
                                                <span class="sq-address__line2">{$sender.address2|escape:'html'}</span>
                                            {/if}
                                            <span class="sq-address__city">
                                                {$sender.postcode|escape:'html'} {$sender.city|escape:'html'}
                                                {if $sender.state_code} ({$sender.state_code|escape:'html'}){/if}
                                            </span>
                                        </div>
                                    </td>

                                    {* Paese *}
                                    <td class="sq-table__td sq-table__td--center">
                                        <span class="sq-flag-badge">
                                            <span class="sq-flag-badge__iso">{$sender.country_iso|upper|escape:'html'}</span>
                                        </span>
                                    </td>

                                    {* Shop (solo multishop) *}
                                    {if $show_shop_column}
                                        <td class="sq-table__td">
                                            <span class="sq-badge sq-badge--shop">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                                    <polyline points="9 22 9 12 15 12 15 22" />
                                                </svg>
                                                {$sender.shop_name|escape:'html'}
                                            </span>
                                        </td>
                                    {/if}

                                    {* Contatti *}
                                    <td class="sq-table__td">
                                        <div class="sq-contacts">
                                            {if $sender.email}
                                                <a href="mailto:{$sender.email|escape:'html'}" class="sq-contact-link">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                                        <polyline points="22,6 12,13 2,6" />
                                                    </svg>
                                                    {$sender.email|escape:'html'}
                                                </a>
                                            {/if}
                                            {if $sender.phone}
                                                <span class="sq-contact-plain">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.48 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.29 6.29l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                                                    </svg>
                                                    {$sender.phone|escape:'html'}
                                                </span>
                                            {/if}
                                        </div>
                                    </td>

                                    {* Stato *}
                                    <td class="sq-table__td sq-table__td--center">
                                        <div class="sq-status-pills">
                                            {if $sender.is_active}
                                                <span class="sq-pill sq-pill--active">Attivo</span>
                                            {else}
                                                <span class="sq-pill sq-pill--inactive">Inattivo</span>
                                            {/if}
                                            {if $sender.is_default}
                                                <span class="sq-pill sq-pill--default">Default</span>
                                            {/if}
                                        </div>
                                    </td>

                                    {* Data aggiornamento *}
                                    <td class="sq-table__td sq-table__td--date">
                                        {if $sender.date_upd}
                                            <span class="sq-date">
                                                {$sender.date_upd|date_format:'%d/%m/%Y'}
                                            </span>
                                            <span class="sq-time">
                                                {$sender.date_upd|date_format:'%H:%M'}
                                            </span>
                                        {else}
                                            <span class="sq-date sq-date--empty">—</span>
                                        {/if}
                                    </td>

                                    {* Azioni *}
                                    <td class="sq-table__td sq-table__td--actions">
                                        <div class="sq-row-actions">
                                            <a href="{$form_action|escape:'html'}&id_sender={$sender.id_sender}" class="sq-action-btn sq-action-btn--edit" title="Modifica mittente">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                </svg>
                                                <span>Modifica</span>
                                            </a>

                                            <form method="post" action="{$form_action|escape:'html'}" class="sq-delete-form" data-confirm="Eliminare il mittente &quot;{if $sender.company}{$sender.company|escape:'html'}{else}{$sender.firstname|escape:'html'} {$sender.lastname|escape:'html'}{/if}&quot;?">
                                                <input type="hidden" name="id_sender" value="{$sender.id_sender}">
                                                <input type="hidden" name="token" value="{$token|escape:'html'}">
                                                <button type="submit" name="submitDeleteSender" class="sq-action-btn sq-action-btn--delete" title="Elimina mittente">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="3 6 5 6 21 6" />
                                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                                        <path d="M10 11v6" />
                                                        <path d="M14 11v6" />
                                                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                                                    </svg>
                                                    <span>Elimina</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>

                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        {/if}

    </div>{* /sq-senders-page *}



    {* ===== JS confirm delete ===== *}
    <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.sq-delete-form').forEach(function(form) {
                    form.addEventListener('submit', function(e) {
                        var msg = form.dataset.confirm || 'Confermi l\'eliminazione?';
                        if (!window.confirm(msg)) {
                            e.preventDefault();
                        }
                    });
                });
            });
        }());
    </script>
