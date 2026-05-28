{* partials/carriers_panel.tpl *}

<div class="panel">


    {include file="module:spedisciquishipping/views/templates/admin/_partials/_carrier/carrier_list_dash.tpl"
                            carriers=$carriers
                            savedCodes=$savedCodes
                            action=$action
                        }

    {include file="module:spedisciquishipping/views/templates/admin/_partials/_carrier/carrier_active_dash.tpl"
                savedCarriers=$savedCarriers
                action=$action
                module_action_url=$module_action_url
                module_name=$module_name
}


</div>