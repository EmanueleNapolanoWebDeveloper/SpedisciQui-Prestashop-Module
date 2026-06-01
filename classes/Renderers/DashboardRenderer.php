<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class DashboardRenderer
{

    private spedisciquishipping $module;
    private Context $context;



    //==========================================
    // COSTRUTTORE
    //==========================================
    public function __construct(
        spedisciquishipping $module,
        Context $context
    ) {
        $this->module = $module;
        $this->context = $context;
    }





    //==========================================
    // RENDER DASHBOARD - INIZIO
    //==========================================
    public function renderDashboard(array $data = []): string
    {

        $this->clearDashboardContext();


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

        return $this->context->smarty->fetch(
            'module:spedisciquishipping/views/templates/admin/dashboard_layout.tpl'
        );
    }
    //==========================================
    // RENDER DASHBOARD - fine
    //==========================================





    //==========================================
    // PULIZIA CONTESTO DASHBOARD -INIZIPO
    //==========================================
    private function clearDashboardContext(): void
    {
        $this->context->smarty->clearAssign('content');
    }
    //==========================================
    // PULIZIA CONTESTO DASHBOARD -FINE
    //==========================================





    //==========================================
    // PRENDERIZAZZIONI DEI PARTIALS (COMPONENTI) - INIZIO
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
    // PRENDERIZAZZIONI DEI PARTIALS (COMPONENTI) - FINE
    //==========================================





    //==========================================
    // PRENDERIZAZZIONI DASH CON CONTENT - INIZIO
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

    //==========================================
    // PRENDERIZAZZIONI DASH CON CONTENT - FINE
    //==========================================
}
