{* partials/carriers_panel.tpl *}

<div class="panel">

    {include
        file="module:spedisciquishipping/views/templates/admin/_partials/_carrier/components/carrier_active_dash.tpl"
        savedCarriers=$savedCarriers
        action=(isset($action))
        ?
        $action
        :
        $formAction
        module_action_url=(isset($module_action_url))
        ?
        $module_action_url
        :
        $formAction
        module_name=(isset($module_name))
        ?
        $module_name
        :
        'spedisciquishipping'
    }

    {include
        file="module:spedisciquishipping/views/templates/admin/_partials/_carrier/components/carrier_list_dash.tpl"
        carriers=$carriers
        savedCodes=$savedCodes
        action=(isset($formAction))
        ?
        $formAction
        :
        $action
    }

</div>