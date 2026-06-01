<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class DashboardRenderer
{

    private spedisciquishipping $module;
    private Context $context;

    public function __construct(
        spedisciquishipping $module,
        Context $context
    ) {
        $this->module = $module;
        $this->context = $context;
    }

    //==========================================
    // RENDER DASHBOARD
    //==========================================
    public function renderDashboard(array $data = []): string
    {
        PrestaShopLogger::addLog(
            '[SQ-DEBUG] renderDashboard() chiamato. data keys: ' . implode(',', array_keys($data)),
            1,
            null,
            'SpedisciQuiShipping'
        );

        $this->clearDashboardContext();

        PrestaShopLogger::addLog(
            '[SQ-DEBUG] Dopo clearDashboardContext. Smarty vars: '
                . implode(',', array_keys($this->context->smarty->getTemplateVars())),
            1,
            null,
            'SpedisciQuiShipping'
        );

        if (!empty($data)) {
            $this->context->smarty->assign($data);
            PrestaShopLogger::addLog(
                '[SQ-DEBUG] assign() eseguito con: ' . json_encode(array_keys($data)),
                1,
                null,
                'SpedisciQuiShipping'
            );
        } else {
            PrestaShopLogger::addLog(
                '[SQ-DEBUG] WARN: data vuoto, assign() saltato',
                2,
                null,
                'SpedisciQuiShipping'
            );
        }

        PrestaShopLogger::addLog(
            '[SQ-DEBUG] Pre-fetch vars: '
                . implode(',', array_keys($this->context->smarty->getTemplateVars())),
            1,
            null,
            'SpedisciQuiShipping'
        );

        return $this->context->smarty->fetch(
            'module:spedisciquishipping/views/templates/admin/dashboard_layout.tpl'
        );
    }


    //==========================================
    // PULIZIA CONTESTO DASHBOARD
    //==========================================
    private function clearDashboardContext(): void
    {
        $this->context->smarty->clearAssign('content');
    }

    //==========================================
    // PRENDERIZAZZIONI DEI PARTIALS (COMPONENTI)
    //==========================================
    private function renderPartials(
        string $template,
        array $data = []
    ): string {

        if (!empty($data)) {
            $this->context->smarty->assign($data);
        }
        return $this->context->smarty->fetch($template);
    }


    //==========================================
    // PRENDERIZAZZIONI DASH CON CONTENT
    //==========================================
    public function renderWithContent(
        string $content,
        array $data = []
    ): string {
        $this->clearDashboardContext();

        $this->context->smarty->assign(array_merge([
            'content' => $content,
        ], $data));

        return $this->context->smarty->fetch(
            'module:spedisciquishipping/views/templates/admin/dashboard_layout.tpl'
        );
    }
}
