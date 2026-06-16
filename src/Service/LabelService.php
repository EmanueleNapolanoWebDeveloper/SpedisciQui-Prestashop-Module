<?php

class LabelService
{

    private const LABEL_DIR = '/upload/spedisciqui/labels/';

    public function saveLabelPdf(
        string $labelBase64,
        string $trackingNumber,
        int $idOrder
    ): string|null {

        $dir = _PS_ROOT_DIR_ . self::LABEL_DIR;

        // se non esiste,crealo
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // protezione accesso web
        if (!file_exists($dir . 'htaccess')) {
            file_put_contents($dir . '.htaccess', "Order deny,allow\nDeny from all\n");
        }

        // noem file da salvare
        $filename = sprintf(
            'label_%s_order%d.pdf',
            preg_replace('/[^A-Za-z0-9\-]/', '', $trackingNumber),
            $idOrder
        );

        $filePath = $dir . $filename;

        // decodifica label
        $labelText = base64_decode($labelBase64, true);

        // controllo
        if ($labelText === false) {
            \PrestaShopLogger::addLog(
                '[SpedisciQui] Errore decode base64 label per ordine #' . $idOrder,
                3,
                null,
                'Order',
                $idOrder,
                true
            );
            return null;
        }


        require_once _PS_ROOT_DIR_ . '/vendor/tecnickcom/tcpdf/tcpdf.php';

        $pdf = new TCPDF('P', 'mm', 'A6', true, 'UTF-8', false);
        $pdf->setCreator('SpedisciQui');
        $pdf->setAuthor('SpedisciQui');
        $pdf->setTitle('Shipping Label ' . $trackingNumber);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setMargins(10, 10, 10);
        $pdf->AddPage();
        $pdf->setFont('courier', '', 9);

        $html = '<pre>' . htmlspecialchars($labelText, ENT_QUOTES, 'UTF-8') . '</pre>';
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($filePath, 'F');

        \PrestaShopLogger::addLog(
            sprintf('[SpedisciQui] Label salvata: %s | Ordine #%d', $filename, $idOrder),
            1,
            null,
            'Order',
            $idOrder,
            true
        );

        return $filePath;
    }
}
