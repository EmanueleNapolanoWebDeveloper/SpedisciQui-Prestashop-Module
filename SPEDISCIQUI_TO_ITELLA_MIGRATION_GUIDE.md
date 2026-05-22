# GUIDA MIGRAZIONE: SpedisciQui Shipping → Itella Shipping Architecture

## Panoramica

Questa guida dettagliata spiega come trasformare il modulo `spedisciquishipping` in una soluzione completa e professionale come `itella-shipping-prestashop-master`.

---

## Confronto Architettura

### Struttura Attuale (spedisciquishipping)
```
modules/spedisciquishipping/
├── spedisciquishipping.php          # Main controller (152 righe)
├── Hooks/
│   └── CarrierHooks.php             # Gestione hook (140 righe)
├── Utilities/
│   ├── SpedisciQuiApi.php           # Wrapper API (100 righe)
│   ├── DatabaseManager.php          # Gestione DB (331 righe)
│   ├── ShippingCostResolve.php      # Calcolo costi (193 righe)
│   ├── ContentHandler.php           # Handler admin (311 righe)
│   └── FormRender.php               # Helper form (148 righe)
├── Repositories/
│   ├── PackageRepository.php        # Gestione pacchi (68 righe)
│   └── SenderRepository.php         # Gestione mittente (62 righe)
└── views/templates/
    ├── hooks/carrier-extra-content.tpl
    └── admin/*.tpl
```

### Struttura Target (itella-shipping)
```
modules/itella-shipping-prestashop-master/
├── itellashipping.php               # Main controller (1853 righe)
├── classes/
│   ├── ItellaCart.php              # Gestione carrello spedizione
│   ├── ItellaShipment.php          # Registrazione spedizioni API
│   ├── ItellaManifest.php          # Generazione manifest
│   └── ItellaStore.php             # Gestione punti di ritiro
├── controllers/
│   ├── admin/
│   │   ├── AdminItellashippingAjaxController.php
│   │   ├── AdminItellashippingItellaManifestController.php
│   │   └── AdminItellashippingItellaStoreController.php
│   └── front/
│       └── front.php
├── vendor/itella-api/              # SDK Itella integrato
└── views/templates/admin/         # Template admin ordine
```

---

## Fasi di Sviluppo

### FASE 1: Migrazione Architettura Classes

**Azione:** Creare la cartella `classes/` con classi dedicate.

**Motivazione:** 
- Separazione responsabilità (SRP)
- Maggiore testabilità
- Codice più manutenibile
- Segue lo standard PrestaShop

**Classi da creare:**
- `classes/SpedisciCart.php` - Gestione carrello spedizione
- `classes/SpedisciShipment.php` - Registrazione spedizioni API
- `classes/SpedisciManifest.php` - Generazione manifest

---

### FASE 2: Controller Admin

**Azione:** Implementare controller admin per gestione etichette.

**File da creare:**
- `controllers/admin/AdminSpedisciShippingAjaxController.php`
- `controllers/admin/AdminSpedisciShippingManifestController.php`

**Funzionalità:**
- Generazione etichette direttamente dall'ordine
- Stampa diretta PDF
- Gestione errori centralizzata

---

### FASE 3: Sistema Manifest

**Azione:** Implementare sistema manifest.

**Tabella database:**
```sql
CREATE TABLE `ps_spedisciqui_manifest` (
    `id_spedisciqui_manifest` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_shop` INT UNSIGNED DEFAULT NULL,
    `date_add` DATETIME NOT NULL,
    `carrier_code` VARCHAR(50) DEFAULT NULL,
    PRIMARY KEY (`id_spedisciqui_manifest`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

### FASE 4: Migliorare Hook Extra Content

**Azione:** Aggiungere interfaccia per:
- Selezione tipo spedizione (courier vs pickup)
- Punti di ritiro (se supportati)
- Servizi extra (fragile, assicurazione)

**Benefici:**
- Maggiore controllo utente
- Servizi extra opzionali
- Preparazione per funzionalità future

---

### FASE 5: Integrazione Tracking Number

**Azione:** Aggiungere endpoint API per:
- `registerShipment()` - Registrazione spedizione
- `getTrackingInfo()` - Recupero info tracking
- `downloadLabel()` - Download etichetta PDF

**Tabella da estendere:**
```sql
ALTER TABLE `ps_spedisciqui_cart_options` 
ADD COLUMN `tracking_number` VARCHAR(100) DEFAULT NULL,
ADD COLUMN `label_url` VARCHAR(255) DEFAULT NULL,
ADD COLUMN `shipment_id` VARCHAR(100) DEFAULT NULL;
```

---

### FASE 6: Sistema Notifiche Email

**Hook da implementare:**
- `actionValidateStepComplete` - Salvataggio scelte utente
- `actionOrderStatusPostUpdate` - Notifica tracking
- `displayAdminOrder` - Blocco admin ordine

---

### FASE 7: Pagina Admin Ordine

**Template da creare:**
- `views/templates/admin/blockinorder.tpl`

**Funzionalità:**
- Visualizzazione tracking corrente
- Pulsante "Genera etichetta"
- Pulsante "Stampa etichetta"
- Modifica opzioni spedizione

---

## Roadmap Sviluppo

### Settimana 1-2: Foundation
1. Creare cartella `classes/`
2. Spostare logica in `SpedisciCart.php`
3. Creare `SpedisciShipment.php`
4. Aggiornare `SpedisciQuiApi.php` con endpoint tracking

### Settimana 3-4: Admin & Controller
1. Controller AJAX per generazione etichette
2. Template admin ordine
3. Controller manifest

### Settimana 5-6: UX & Features
1. Migliorare hook extra content
2. Aggiungere pickup points (se supportati)
3. Sistema notifiche email

### Settimana 7-8: Test & Polish
1. Test integration end-to-end
2. Gestione errori migliorata
3. Documentazione