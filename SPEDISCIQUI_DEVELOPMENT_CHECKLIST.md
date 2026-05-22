# TODO-LIST: Sviluppo SpedisciQui Shipping → Itella Architecture

## FASE 1: Foundation - Ristrutturazione Classes

### 1.1 Creazione cartella classes
- [ ] Creare directory `modules/spedisciquishipping/classes/`
- [ ] Creare file `modules/spedisciquishipping/classes/index.php`

### 1.2 Implementare SpedisciCart.php
- [ ] Creare classe `SpedisciCart` con namespace PrestaShop
- [ ] Implementare metodo `getOrderSpedisciCartInfo($id_cart)`
- [ ] Implementare metodo `saveOrder($orderObj)`
- [ ] Implementare metodo `updateSpedisciCart()`
- [ ] Implementare metodo `updateTrackNumber($id_cart, $track_num)`
- [ ] Implementare metodo `saveError($id_cart, $error_msg)`
- [ ] Aggiungere tabella `spedisciqui_cart` nel database (se non esiste)
- [ ] Testare integrazione con ordini esistenti

### 1.3 Implementare SpedisciShipment.php
- [ ] Creare classe `SpedisciShipment`
- [ ] Implementare metodo `registerShipment($id_order)`
- [ ] Implementare metodo `getLabel($id_order)`
- [ ] Implementare metodo `isInternational($receiver_country)`
- [ ] Aggiungere gestione eccezioni ItellaException
- [ ] Testare con API esterna

### 1.4 Aggiornare SpedisciQuiApi.php
- [ ] Aggiungere metodo `registerShipment($payload)`
- [ ] Aggiungere metodo `getTrackingInfo($tracking_number)`
- [ ] Aggiungere metodo `downloadLabel($tracking_number)`
- [ ] Aggiungere gestione errori JSON response
- [ ] Testare endpoint API

---

## FASE 2: Controller Admin - Generazione Etichette

### 2.1 Creazione controller AJAX
- [ ] Creare directory `modules/spedisciquishipping/controllers/admin/`
- [ ] Creare file `AdminSpedisciShippingAjaxController.php`
- [ ] Implementare costruttore con controllo accessi
- [ ] Implementare metodo `parseActions()` con routing
- [ ] Implementare azione `genlabel` (generazione etichetta)
- [ ] Implementare azione `printlabel` (stampa etichetta)
- [ ] Implementare azione `massgenlabel` (generazione massiccia)
- [ ] Implementare azione `savecart` (salvataggio carrello)

### 2.2 Controller Manifest
- [ ] Creare directory `modules/spedisciquishipping/controllers/admin/`
- [ ] Creare file `AdminSpedisciShippingManifestController.php`
- [ ] Implementare lista manifest disponibili
- [ ] Implementare creazione nuovo manifest
- [ ] Implementare download PDF manifest
- [ ] Aggiungere filtro per data e carrier

---

## FASE 3: Database - Tabella Manifest

### 3.1 Creazione tabella manifest
- [ ] Aggiungere query creazione tabella in `DatabaseManager.php`
- [ ] Creare tabella `spedisciqui_manifest`:
  - `id_spedisciqui_manifest` (INT UNSIGNED AUTO_INCREMENT)
  - `id_shop` (INT UNSIGNED)
  - `date_add` (DATETIME)
  - `carrier_code` (VARCHAR 50)
- [ ] Creare indice su `date_add` e `carrier_code`
- [ ] Testare creazione tabella con installazione pulita

---

## FASE 4: Hook Extra Content - Interfaccia Utente

### 4.1 Migliorare hookDisplayCarrierExtraContent
- [ ] Aggiungere verifica carrier nostro con metodo dedicato
- [ ] Aggiungere template dinamico con Smarty
- [ ] Passare variabili al template:
  - `cart` - Dati carrello
  - `insurance_price` - Prezzo assicurazione
  - `has_insurance` - Stato assicurazione corrente
  - `pickup_points` - Array punti di ritiro (se supportati)
  - `extra_services` - Servizi extra disponibili

### 4.2 Template carrier-extra-content.tpl
- [ ] Aggiungere checkbox assicurazione con prezzo
- [ ] Aggiungere dropdown tipo spedizione (courier/pickup)
- [ ] Aggiungere dropdown punti di ritiro (se supportati)
- [ ] Aggiungere checkbox servizi extra (fragile, oversized)
- [ ] Aggiungere JavaScript per interazioni
- [ ] Testare su PrestaShop 1.6 e 1.7+

---

## FASE 5: Tracking Number Integration

### 5.1 Estensione tabella cart options
- [ ] Verificare tabella `spedisciqui_cart_options`
- [ ] Aggiungere colonna `tracking_number` (VARCHAR 100)
- [ ] Aggiungere colonna `label_url` (VARCHAR 255)
- [ ] Aggiungere colonna `shipment_id` (VARCHAR 100)
- [ ] Aggiungere migration per tabelle esistenti

### 5.2 Aggiornare flusso checkout
- [ ] Modificare hookActionValidateStepComplete
- [ ] Salvare insurance choice nel database
- [ ] Aggiornare prezzo spedizione con insurance
- [ ] Testare flusso completo checkout

---

## FASE 6: Notifiche Email

### 6.1 Hook actionValidateStepComplete
- [ ] Riabilitare hook disabilitato
- [ ] Implementare salvataggio assicurazione nel database
- [ ] Aggiornare prezzo totale carrello
- [ ] Testare con ordini di prova

### 6.2 Hook actionOrderStatusPostUpdate
- [ ] Aggiungere invio email con tracking number
- [ ] Inserire link tracking nell'email
- [ ] Configurare template email personalizzato
- [ ] Aggiungere log email inviate

### 6.3 Hook displayAdminOrder
- [ ] Creare template `blockinorder.tpl`
- [ ] Mostrare tracking number corrente
- [ ] Aggiungere pulsante "Genera etichetta"
- [ ] Aggiungere pulsante "Stampa etichetta"
- [ ] Aggiungere form modifica opzioni spedizione

---

## FASE 7: Pagina Admin Ordine

### 7.1 Creazione template admin
- [ ] Creare `views/templates/admin/blockinorder.tpl`
- [ ] Aggiungere visualizzazione tracking number
- [ ] Aggiungere form modifica pacchi e peso
- [ ] Aggiungere checkbox COD (con importo)
- [ ] Aggiungere dropdown carrier (courier/pickup)
- [ ] Aggiungere dropdown punti di ritiro
- [ ] Aggiungere checkbox servizi extra
- [ ] Aggiungere campo commenti

### 7.2 JavaScript interattività
- [ ] Aggiungere toggle COD con abilitazione campo importo
- [ ] Aggiungere toggle pickup con nascondi/mostra punti
- [ ] Aggiungere validazione form prima submit
- [ ] Aggiungere chiamata AJAX per salvataggio
- [ ] Aggiungere chiamata AJAX per generazione etichetta

### 7.3 Controller hook
- [ ] Implementare `hookDisplayAdminOrder()` in spedisciquishipping.php
- [ ] Aggiungere controllo carrier Itella
- [ ] Caricare dati ordine da SpedisciCart
- [ ] Passare variabili al template

---

## FASE 8: Testing & Polish

### 8.1 Test Integration
- [ ] Test ordine con spedizione courier
- [ ] Test ordine con spedizione pickup (se supportato)
- [ ] Test generazione etichetta API
- [ ] Test stampa PDF etichetta
- [ ] Test manifest giornaliero
- [ ] Test notifica email tracking

### 8.2 Test Multi-Versione
- [ ] Test su PrestaShop 1.6.x
- [ ] Test su PrestaShop 1.7.x
- [ ] Test su PrestaShop 8.x

### 8.3 Gestione Errori
- [ ] Aggiungere try-catch su tutte le chiamate API
- [ ] Loggare errori in tabella dedicata
- [ ] Mostrare messaggi utente friendly
- [ ] Aggiungere retry automatico per errori transienti

### 8.4 Documentazione
- [ ] Creare file README.md aggiornato
- [ ] Documentare API endpoint disponibili
- [ ] Documentare hook disponibili
- [ ] Aggiungere esempi di utilizzo

---

## Priorità di Sviluppo Consigliata

1. **Critico:** FASE 1 (Foundation) - Senza questa base non si può procedere
2. **Alto:** FASE 2 (Controller Admin) - Necessario per operatività
3. **Medio:** FASE 4 (Hook Extra Content) - Migliora UX
4. **Basso:** FASE 3 (Manifest) - Solo se necessario per B2B
5. **Facoltativo:** FASE 5-8 - Nice to have

---

## Note Tecniche

- Non modificare il flusso esistente di `ShippingCostResolve`
- Mantenere compatibilità con API esterna esistente
- Testare ogni modifica con ordini reali
- Seguire coding standards PrestaShop
- Aggiungere commenti PHPDoc a tutte le funzioni