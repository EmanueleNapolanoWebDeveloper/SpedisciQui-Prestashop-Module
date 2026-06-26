# SpedisciQui Shipping

Modulo PrestaShop per l'integrazione con la piattaforma di spedizioni **SpedisciQui**.  
Permette ai merchant di gestire corrieri, tariffe, spedizioni e indirizzi mittente direttamente dal back office di PrestaShop.

**Versione:** 1.0.0  
**Compatibilità:** PrestaShop 1.6+  
**Autore:** SpedisciQui  

---

## Requisiti

| Requisito | Dettaglio |
|-----------|-----------|
| PrestaShop | 1.6 o superiore |
| PHP | >= 7.4 (consigliato 8.0+) |
| Dipendenze | Guzzle HTTP Client ^7.0 |
| Permessi | Scrittura sulla directory `modules/spedisciquishipping/` |

---

## Installazione

1. Copia la cartella `spedisciquishipping` in `modules/` del tuo PrestaShop.
2. Da **Moduli > Moduli installati**, cerca "SpedisciQui Shipping".
3. Clicca su **Installa**.
4. Al termine dell'installazione verrà avviato automaticamente il wizard di configurazione.

### Cosa fa l'installazione

- Crea tutte le tabelle del database tramite migrazioni (`SQMigrations`).
- Registra i tab nel back office:
  - **SpedisciQui Setup** — wizard iniziale (tab radice, fuori dalla menu sidebar)
  - **Corrieri SpedisciQui** — gestione corrieri e tariffe (sotto *Spedizioni*)
  - **Ordini SpedisciQui** — elenco spedizioni (sotto *Ordini*)
  - **Mittente SpedisciQui** — gestione indirizzi mittente (sotto *Parametri negozio*)
- Registra gli hook PrestaShop:
  - `actionValidateOrder`
  - `actionProductFormBuilderModifier`
- Inserisce le configurazioni di default in `spedisciqui_config`.

---

## Configurazione iniziale (Setup Wizard)

Dopo l'installazione il modulo reindirizza automaticamente al setup in 4 step:

| Step | Nome | Descrizione |
|------|------|-------------|
| 1 | **TOKEN** | Inserisci il Bearer token e l'URL API di SpedisciQui |
| 2 | **SENDER** | Configura l'indirizzo mittente (nome, via, CAP, città, provincia, paese) |
| 3 | **PACKAGE** | Definisci le dimensioni di default dei pacchi (Larghezza, Altezza, Profondità, Peso) |
| 4 | **CARRIER** | Importa i corrieri da SpedisciQui e crea le tariffe per peso |

Se la configurazione viene interrotta a metà, il wizard riprenderà dall'ultimo step completato.  
Al termine (`SetupSteps::DONE`) l'utente viene reindirizzato alla **Dashboard**.

---

## Architettura del modulo

### Struttura file

```
spedisciquishipping/
├── spedisciquishipping.php                 # Entry point, classe principale (estende CarrierModule)
├── composer.json                           # Autoload PSR-4 + dipendenza Guzzle
├── config_it.xml                           # Metadati modulo PrestaShop
│
├── classes/
│   ├── Core/
│   │   ├── API/
│   │   │   ├── ApiClient.php               # Client HTTP con retry e rate limiting
│   │   │   ├── CarrierApi.php              # Wrapper endpoint API SpedisciQui (corrieri)
│   │   │   └── DTO/
│   │   │       ├── ApiResponse.php         # DTO risposta API standardizzata
│   │   │       └── ShipmentCreationResult.php  # DTO risultato creazione spedizione
│   │   │
│   │   ├── Database/
│   │   │   └── SQMigrations.php            # Definizione schema DB + esecuzione migrazioni
│   │   │
│   │   └── Utilities/
│   │       ├── Installation.php            # Logica installazione (tabelle, tab, hook, config)
│   │       ├── Uninstallation.php          # Logica disinstallazione (pulizia carrier, tab, tabelle)
│   │       ├── SetupManage.php             # Gestore stato wizard di setup (current step, reset)
│   │       └── SetupSteps.php              # Costanti step setup (TOKEN, SENDER, PACKAGE, CARRIER, DONE)
│   │
│   └── Repositories/
│       ├── ConfigRepositories.php          # Accesso key/value alla tab spedisciqui_config
│       ├── CredentialsRepositories.php     # Storage cifrato credenziali API + gestione token
│       ├── CarrierRepository.php           # CRUD corrieri e tariffe peso (weight_tariffs)
│       ├── ShipmentRepository.php          # CRUD spedizioni e tracking
│       ├── PackageRepository.php           # Lettura/scrittura pacchi predefiniti
│       ├── SenderRepository.php            # CRUD indirizzi mittente
│       └── SenderProductRepository.php     # Associazione prodotto → mittente default
│
├── src/
│   ├── Hooks/
│   │   └── InstalledHooks.php              # Implementazioni hook installati
│   │
│   ├── Renderers/
│   │   ├── CredentialsRenderer.php         # Form connessione API SpedisciQui (token, URL)
│   │   ├── SenderRenderer.php              # Form gestione indirizzo mittente
│   │   ├── CarrierRenderer.php             # Form import/gestione corrieri e tariffe per peso
│   │   ├── ShipmentRenderer.php            # Liste e dettagli spedizioni (UI admin)
│   │   ├── PackageRenderer.php             # Form dimensioni pacchi predefiniti
│   │   └── DashboardRenderer.php           # Pagina principale di controllo
│   │
│   └── Service/
│       ├── CredentialServices.php          # Validazione token, salvataggio credenziali
│       ├── SenderServices.php              # Validazione indirizzo mittente, estrazione dati API
│       ├── CarrierServices.php             # Calcolo preventivo, sincronizzazione corrieri
│       ├── PackageServices.php             # Gestione dimensioni pacchi predefiniti
│       ├── ShipmentService.php             # Ricerca corriere/tariffa per carrello
│       ├── ShipmentCreationService.php     # Workflow end-to-end creazione spedizione
│       └── LabelService.php                # Generazione etichetta PDF spedizione
│
└── controllers/admin/
    ├── AdminSpedisciQuiSetupController.php      # Wizard setup
    ├── AdminSpedisciQuiCarriersController.php   # Gestione corrieri e tariffe
    ├── AdminSpedisciQuiShipmentsController.php  # Elenco e gestione spedizioni
    └── AdminSpedisciQuiSenderController.php     # Gestione indirizzi mittente
```

---

## Schema database

| Tabella | Descrizione |
|---------|-------------|
| `spedisciqui_config` | Configurazioni chiave/valore del modulo |
| `spedisciqui_api_credentials` | Token API (cifrato) con data scadenza |
| `spedisciqui_package` | Dimensioni predefinite pacchi (L, A, P, peso) |
| `spedisciqui_sender_address` | Indirizzi mittente/shipper |
| `spedisciqui_carrier` | Mapping tra corrieri PrestaShop e SpedisciQui |
| `spedisciqui_weight_tariffs` | Tariffe calcolate per fasce di peso |
| `spedisciqui_shipments` | Record spedizioni (tracking, label URL, stato) |
| `spedisciqui_sender_product` | Associazione prodotto → mittente di default |

---

## Funzionalità principali

### 1. Connessione API SpedisciQui
- Autenticazione tramite **Bearer Token**.
- Salvataggio cifrato delle credenziali in `spedisciqui_api_credentials`.
- Validazione automatica del token al salvataggio.
- Supporto URL API personalizzato.

### 2. Wizard di setup guidato
- Controllo di avanzamento salvato in `spedisciqui_config` (`SPEDISCIQUI_SETUP_STEP`).
- Validazione step-by-step con rollback in caso di errore.
- Reindirizzamento automatico all'ultimo step incompleto.

### 3. Gestione corrieri e tariffe
- Importazione corrieri direttamente dalla piattaforma SpedisciQui.
- Mappatura tra corrieri PrestaShop e corrieri SpedisciQui.
- Definizione di tariffe per fasce di peso (`weight_tariffs`) per ciascun corriere.
- Calcolo dinamico del costo di spedizione in fase di checkout.

### 4. Calcolo costi di spedizione
- Il modulo estende `CarrierModule`, quindi i costi sono calcolati automaticamente da PrestaShop durante il checkout.
- `getOrderShippingCost()` interroga `ShipmentService` per trovare la tariffa corrispondente al carrello.
- Se nessuna tariffa è applicabile, la spedizione viene esclusa o restituisce `false`.

### 5. Creazione spedizioni
- Workflow guidato tramite `ShipmentCreationService`.
- Chiamate API verso SpedisciQui per creare la spedizione.
- Salvataggio tracking number e URL label PDF in `spedisciqui_shipments`.
- Supporto etichette PDF stampabili.

### 6. Gestione indirizzi mittente
- Configurazione multipla di indirizzi mittente.
- Associazione prodotto → mittente (`sender_product`).
- Validazione indirizzi tramite API SpedisciQui.

### 7. Hook PrestaShop
- **`actionValidateOrder`** — Trigger automatico post-conferma ordine (es. creazione spedizione).
- **`actionProductFormBuilderModifier`** — Estensione della pagina prodotto per associazione mittente.

---

## Controllers admin

| Controller | Classe | Descrizione |
|------------|--------|-------------|
| Setup | `AdminSpedisciQuiSetupController` | Wizard 4 step installazione |
| Corrieri | `AdminSpedisciQuiCarriersController` | Import/gestione corrieri e tariffe |
| Spedizioni | `AdminSpedisciQuiShipmentsController` | Lista, dettaglio e azioni su spedizioni |
| Mittente | `AdminSpedisciQuiSenderController` | CRUD indirizzi mittente |

---

## Servizi principali

- **`CredentialServices`** — Validazione e salvataggio token API.
- **`SenderServices`** — Validazione indirizzi, estrazione dati mittente da API.
- **`CarrierServices`** — Sincronizzazione corrieri, calcolo tariffe per peso.
- **`PackageServices`** — Gestione pacchi predefiniti.
- **`ShipmentService`** — Selezione tariffa/corriere per carrello.
- **`ShipmentCreationService`** — Flusso completo creazione spedizione (chiamate API, salvataggio tracking, label).
- **`LabelService`** — Download e rendering etichetta PDF.

---

## Configurazioni

| Chiave | Descrizione | Default |
|--------|-------------|---------|
| `SPEDISCIQUI_API_URL` | URL base API SpedisciQui | (vuoto) |
| `SPEDISCIQUI_DEFAULT_CURRENCY` | Valuta di default | `EUR` |
| `SPEDISCIQUI_TIMEOUT` | Timeout richieste API (secondi) | `30` |
| `SPEDISCIQUI_SETUP_STEP` | Step corrente wizard setup | `0` |

---

## Flusso di utilizzo tipico

1. **Installazione**: installare il modulo, viene avviato il setup wizard.
2. **Configurazione API**: inserire token e URL, validare la connessione.
3. **Mittente**: compilare l'indirizzo del mittente (verificato da API).
4. **Pacchi**: impostare le dimensioni standard dei colli.
5. **Corrieri**: importare i corrieri da SpedisciQui, impostare le tariffe per peso.
6. **Checkout**: PrestaShop mostra automaticamente i metodi di spedizione configurati con i relativi costi.
7. **Ordini**: al momento dell'invio (o automaticamente via hook) viene creata la spedizione su SpedisciQui, generato tracking e label.

---

## Logging e debug

Il modulo utilizza `PrestaShopLogger::addLog()` con prefisso `[SpedisciQui]`.  
I log sono consultabili da **Parametri avanzati > Log**.

Livelli di log:
- `1` — Informazioni
- `2` — Avvisi
- `3` — Errori critici

---

## Disinstallazione

La disinstallazione:
- Rimuove tutti i corrieri creati dal modulo.
- Elimina i tab amministrativi.
- Rimuove le tabelle del database tramite migrazioni inverse.
- Cancella le configurazioni salvate.

---

## Note tecniche

- Il modulo non implementa ancora la sezione **MultiSender** (in fase di sviluppo).
- Le credenziali API sono salvate in modo cifrato nel database.
- Il client HTTP (`ApiClient`) include gestione di retry e rate limiting lato client.
- L'autoloading classi è basato su PSR-4 mappato sulla cartella `/classes`.

---

## Troubleshooting

| Problema | Soluzione |
|----------|-----------|
| Errore installazione moduli | Verifica che Guzzle sia installato (`composer install` nella cartella del modulo) |
| Setup bloccato | Controlla i log PrestaShop per errori di connessione API |
| Tariffe non visibili in checkout | Verifica che il peso del carrello sia compreso in almeno una fascia |
| Token scaduto | Rigenera il token su SpedisciQui e salvalo nel setup |

---

## Supporto

Per assistenza o segnalazione bug, contattare il supporto SpedisciQui.
