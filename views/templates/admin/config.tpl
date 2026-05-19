<!-- Block SpedisciQui Shipping Form -->
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title">{l mod='spedisciquishipping' s='SpedisciQui Shipping Configuration'}</h3>
    </div>
    <div class="panel-body">
        <form action="{$link->getAdminLink('AdminModulesControllersConfigure')}&configure={$module->name}&tab_module={$module->tab}&module_name={$module->name}" method="post" class="form-horizontal">
            <fieldset>
                <legend><i class="icon-truck"></i> {l mod='spedisciquishipping' s='Default Parcel Settings'}</legend>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="SQ_DEFAULT_PARCEL_LENGTH">{l mod='spedisciquishipping' s='Length (cm)'}</label>
                    <div class="col-lg-9">
                        <input type="number" class="form-control" id="SQ_DEFAULT_PARCEL_LENGTH" name="SQ_DEFAULT_PARCEL_LENGTH" value="{Configuration::get('SQ_DEFAULT_PARCEL_LENGTH')}" min="0.1" step="0.1" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="SQ_DEFAULT_PARCEL_WIDTH">{l mod='spedisciquishipping' s='Width (cm)'}</label>
                    <div class="col-lg-9">
                        <input type="number" class="form-control" id="SQ_DEFAULT_PARCEL_WIDTH" name="SQ_DEFAULT_PARCEL_WIDTH" value="{Configuration::get('SQ_DEFAULT_PARCEL_WIDTH')}" min="0.1" step="0.1" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="SQ_DEFAULT_PARCEL_HEIGHT">{l mod='spedisciquishipping' s='Height (cm)'}</label>
                    <div class="col-lg-9">
                        <input type="number" class="form-control" id="SQ_DEFAULT_PARCEL_HEIGHT" name="SQ_DEFAULT_PARCEL_HEIGHT" value="{Configuration::get('SQ_DEFAULT_PARCEL_HEIGHT')}" min="0.1" step="0.1" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="SQ_DEFAULT_PARCEL_WEIGHT">{l mod='spedisciquishipping' s='Weight (kg)'}</label>
                    <div class="col-lg-9">
                        <input type="number" class="form-control" id="SQ_DEFAULT_PARCEL_WEIGHT" name="SQ_DEFAULT_PARCEL_WEIGHT" value="{Configuration::get('SQ_DEFAULT_PARCEL_WEIGHT')}" min="0.01" step="0.01" required>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend><i class="icon-home"></i> {l mod='spedisciquishipping' s='Default Sender Address'}</legend>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="SQ_SENDER_NAME">{l mod='spedisciquishipping' s='First Name'}</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" id="SQ_SENDER_NAME" name="SQ_SENDER_NAME" value="{Configuration::get('SQ_SENDER_NAME')}" placeholder="{l mod='spedisciquishipping' s='John'}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="SQ_SENDER_COMPANY">{l mod='spedisciquishipping' s='Company (optional)'}</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" id="SQ_SENDER_COMPANY" name="SQ_SENDER_COMPANY" value="{Configuration::get('SQ_SENDER_COMPANY')}" placeholder="{l mod='spedisciquishipping' s='Acme Inc.'}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="SQ_SENDER_ADDRESS">{l mod='spedisciquishipping' s='Address Line 1'}</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" id="SQ_SENDER_ADDRESS" name="SQ_SENDER_ADDRESS" value="{Configuration::get('SQ_SENDER_ADDRESS')}" placeholder="{l mod='spedisciquishipping' s='123 Main Street'}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="SQ_SENDER_ADDRESS2">{l mod='spedisciquishipping' s='Address Line 2 (optional)'}</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" id="SQ_SENDER_ADDRESS2" name="SQ_SENDER_ADDRESS2" value="{Configuration::get('SQ_SENDER_ADDRESS2')}" placeholder="{l mod='spedisciquishipping' s='Apt 4B'}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="SQ_SENDER_CITY">{l mod='spedisciquishipping' s='City'}</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" id="SQ_SENDER_CITY" name="SQ_SENDER_CITY" value="{Configuration::get('SQ_SENDER_CITY')}" placeholder="{l mod='spedisciquishipping' s='Anytown'}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="SQ_SENDER_POSTCODE">{l mod='spedisciquishipping' s='Postal Code'}</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" id="SQ_SENDER_POSTCODE" name="SQ_SENDER_POSTCODE" value="{Configuration::get('SQ_SENDER_POSTCODE')}" placeholder="{l mod='spedisciquishipping' s='12345'}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="SQ_SENDER_COUNTRY">{l mod='spedisciquishipping' s='Country'}</label>
                    <div class="col-lg-9">
                        <select class="form-control" id="SQ_SENDER_COUNTRY" name="SQ_SENDER_COUNTRY">
                            {foreach from=$countries item=country}
                                <option value="{$country.id_country}" {if $country.id_country == Configuration::get('SQ_SENDER_COUNTRY')}selected="selected"{/if}>{$country.name|escape:'html':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="SQ_SENDER_PHONE">{l mod='spedisciquishipping' s='Phone Number'}</label>
                    <div class="col-lg-9">
                        <input type="tel" class="form-control" id="SQ_SENDER_PHONE" name="SQ_SENDER_PHONE" value="{Configuration::get('SQ_SENDER_PHONE')}" placeholder="{l mod='spedisciquishipping' s='+39 123 456 7890'}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="SQ_SENDER_EMAIL">{l mod='spedisciquishipping' s='Email Address'}</label>
                    <div class="col-lg-9">
                        <input type="email" class="form-control" id="SQ_SENDER_EMAIL" name="SQ_SENDER_EMAIL" value="{Configuration::get('SQ_SENDER_EMAIL')}" placeholder="{l mod='spedisciquishipping' s='sender@example.com'}">
                    </div>
                </div>
            </fieldset>

            <div class="form-group">
                <div class="col-lg-9 col-lg-offset-3">
                    <button type="submit" class="btn btn-default" name="submitSpedisciquiShipping">{l mod='spedisciquishipping' s='Save'}</button>
                </div>
            </div>
        </form>
    </div>
</div>