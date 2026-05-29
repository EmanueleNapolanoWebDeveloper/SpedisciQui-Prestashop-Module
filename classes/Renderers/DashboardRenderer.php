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
    public function renderDashboard(
        array $data = []
    ): string {
        // pulizia context
        $this->clearDashboardContext();

        if (!empty($data)) {
            $this->context->smarty->assign($data);
        }

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
