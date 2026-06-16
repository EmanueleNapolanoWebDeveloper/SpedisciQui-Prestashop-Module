# SpedisciQui Shipping Primo

Modulo ufficiale PrestaShop per l'integrazione con la piattaforma di spedizioni **SpedisciQui**.

> Versione: **1.0.0**  
> Autore: **SpedisciQui**  
> Compatibilità: **PrestaShop 1.6+**  
> Categoria: `shipping_logistics`

---

## Indice

1. [Panoramica](#panoramica)
2. [Requisiti di sistema](#requisiti-di-sistema)
3. [Installazione](#installazione)
4. [Configurazione guidata](#configurazione-guidata)
5. [Struttura del modulo](#struttura-del-modulo)
6. [Integrazione API SpedisciQui](#integrazione-api-spedisciqui)
7. [Gestione corrieri](#gestione-corrieri)
8. [Tariffe di spedizione](#tariffe-di-spedizione)
9. [Ciclo di vita di una spedizione](#ciclo-di-vita-di-una-spedizione)
10. [Generazione etichette](#generazione-etichette)
11. [Assicurazione e contrassegno](#assicurazione-e-contrassegno)
12. [Hook PrestaShop](#hook-prestashop)
13. [Tabelle database](#tabelle-database)
14. [Sicurezza](#sicurezza)
15. [Risoluzione dei problemi](#risoluzione-dei-problemi)

---

## Panoramica

`spedisciquishipping` è un modulo di tipo **CarrierModule** che integra PrestaShop con la piattaforma SpedisciQui. Il modulo consente di:

- Configurare automaticamente i corrieri SpedisciQui come metodi di spedizione PrestaShop.
- Creare spedizioni direttamente dal back-office.
- Generare etichette PDF in formato A6, protette da accesso web.
- Gestire tariffe di spedizione per fasce di peso.
- Supportare assicurazione e pagamento in contrassegno (COD).
- Tracciare lo stato delle spedizioni attraverso un flusso a 9 stati.

Il modulo utilizza una **API REST locale** (disponibile su `http://127.0.0.1:8000`) per comunicare con la piattaforma SpedisciQui e generare le spedizioni.

---

## Requisiti di sistema

| Requisito | Versione/Note |
|-----------|---------------|
| **PrestaShop** | 1.6 o superiore |
| **PHP** | Versione compatibile con PrestaShop (min. 5.6, consigliato 7.4+) |
| **OpenSSL** | Obbligatorio per cifratura AES-256-CBC |
| **Composer** | Per gestire le dipendenze PHP |
| **Gu​zzleHTTP** | `^7.0`, cliente HTTP per le chiamate API |
| **TCPDF** | `tecnickcom/tcpdf`, fornito da PrestaShop, per generazione PDF etichette |
| **Server API SpedisciQui** | In esecuzione su `http://127.0.0.1:8000` |

### Dipendenze Composer

```json
{
    "require": {
        "guzzlehttp/guzzle": "^7.0"
    }
}
```

---

## Installazione

1. Copiare la cartella `spedisciquishipping` in `/modules/` del'installazione PrestaShop.
2. Da **Modules > Module Manager**, cercare `SpedisciQui Shipping Primo` e cliccare **Installa**.
3. Il modulo creerà automaticamente:
   - 8 tabelle nel database.
   - Gli hook PrestaShop necessari.
   - La directory protetta `/upload/spedisciqui/labels/` per le etichette.
4. Completare la **configurazione guidata** (vedi sezione successiva).

### Disinstallazione

La disinstallazione:
- Elimina tutte le tabelle del modulo.
- Elimina tutti i corrieri registrati da SpedisciQui (soft-delete su PrestaShop).
- Rimuove gli hook.

---

## Configurazione guidata

Il modulo presenta un wizard a 4 passaggi accessibile dal pannello di amministrazione.

### Step 0: Token API
- Inserire il token di accesso fornito da SpedisciQui.
- Il token viene validato contro l'endpoint `/api/auth/verify`.
- Se valido, viene cifrato con AES-256-CBC e salvato nel database.

### Step 1: Indirizzo mittente
- Configurare l'indirizzo del mittente/warehouse.
- Campi obbligatori:
  - Ragione sociale, nome, cognome.
  - Indirizzo, CAP, città, provincia, paese.
  - Email, telefono.
  - Partita IVA.
- È possibile creare indirizzi multipli e impostarne uno come predefinito.

### Step 2: Dimensioni pacco predefinite
- Definire le dimensioni standard del pacco di spedizione.
- Campi:
  - Nome (es. "Scatola standard").
  - Peso (kg).
  - Lunghezza (cm).
  - Larghezza (cm).
  - Altezza (cm).
- Queste dimensioni vengono usate come fallback quando i prodotti nel carrello non hanno dimensioni definite.

### Step 3: Selezione corrieri
- Il modulo interroga `/api/getCarriers` e mostra i corrieri disponibili.
- Per ogni corriere è possibile attivarlo o disattivarlo.
- I corrieri attivati vengono registrati automaticamente in PrestaShop.

---

## Struttura del modulo

```
modules/spedisciquishipping/
├── spedisciquishipping.php           # Classe principale del modulo (CarrierModule)
├── composer.json                     # Dipendenze PHP
├── classes/
│   ├── Core/
│   │   ├── API/
│   │   │   ├── ApiClient.php         # Client HTTP per API SpedisciQui
│   │   │   ├── CarrierApi.php        # Logica specifica per i corrieri
│   │   │   └── DTO/
│   │   │       ├── ApiResponse.php
│   │   │       └── ShipmentCreationResult.php
│   │   ├── Database/
│   │   │   └── SQMigrations.php      # Definizioni tabelle DB + migrazioni
│   │   └── Utilities/
│   │       ├── SetupManage.php       # Gestione stato wizard di setup
│   │       ├── SetupSteps.php        # Costanti step wizard (0-4)
│   │       ├── Installation.php      # Logica di installazione
│   │       └── Uninstallation.php    # Logica di disinstallazione
│   ├── Repositories/
│   │   ├── ConfigRepositories.php    # Accesso configurazione chiave/valore
│   │   ├── CredentialsRepositories.php # Token cifrati (AES-256-CBC)
│   │   ├── SenderRepository.php      # CRUD indirizzi mittente
│   │   ├── CarrierRepository.php     # Mappatura corrieri + gestione corrieri PrestaShop
│   │   ├── ShipmentRepository.php    # CRUD spedizioni + aggiornamenti di stato
│   │   └── PackageRepository.php     # Dimensioni pacco predefinite
│   ├── Service/
│   │   ├── CredentialServices.php    # Logica di validazione token
│   │   ├── CarrierServices.php       # CRUD tariffe + validazione fasce peso
│   │   ├── PackageServices.php       # Estrazione dati spedizione dai prodotti
│   │   ├── SenderServices.php        # Validazione indirizzo mittente
│   │   ├── ShipmentService.php       # Calcolo costi + ViewModel
│   │   ├── ShipmentCreationService.php # Costruzione payload + creazione spedizione
│   │   └── LabelService.php          # Generazione PDF etichette (TCPDF)
│   ├── Renderers/
│   │   ├── CredentialsRenderer.php
│   │   ├── SenderRenderer.php
│   │   ├── PackageRenderer.php
│   │   ├── CarrierRenderer.php
│   │   ├── DashboardRenderer.php
│   │   └── ShipmentRenderer.php
│   ├── Handlers/
│   │   ├── ContentHandler.php        # Router principale pagine admin
│   │   ├── CredentialsHandlers.php
│   │   ├── SendersHandler.php
│   │   ├── PackageHandler.php
│   │   ├── CarrierHandlers.php
│   │   ├── ShipmentHandler.php
│   │   └── DashboardHandlers.php
│   └── Hooks/
│       └── InstalledHooks.php        # Implementazioni hook PrestaShop
└── views/
    ├── templates/
    │   ├── admin/
    │   │   ├── layouts/              # Layout dashboard e wizard setup
    │   │   ├── _partials/            # Componenti UI riutilizzabili
    │   │   │   ├── _components/      # Stepper, modali, filtri
    │   │   │   ├── _settings/        # Pannello impostazioni
    │   │   │   ├── _initial/         # Form wizard setup
    │   │   │   ├── _carrier/         # Gestione corrieri e tariffe
    │   │   │   └── _shipment/        # Lista e dettaglio spedizione
    │   │   └── hook/checkout/_partials/ # Componenti checkout
    │   └── hook/
    ├── css/                          # Fogli di stile admin e frontend
    └── js/                           # Script admin e frontend
```

---

## Integrazione API SpedisciQui

Il modulo comunica con una API REST locale ospitata su `http://127.0.0.1:8000`.

### Endpoint

| Metodo | Endpoint | Scopo | Autenticazione |
|--------|----------|-------|----------------|
| `GET` | `/api/auth/verify` | Verifica validità di un token di accesso | Bearer token |
| `GET` | `/api/getCarriers` | Recupera l'elenco dei corrieri disponibili | Bearer token |
| `POST` | `/api/v1/create_shipment` | Crea una spedizione e restituisce tracking + etichetta PDF | Bearer token |

### Timeout
Le richieste HTTP hanno un timeout di **10 secondi**.

### Autenticazione
- Il modulo utilizza **Bearer Token** per autenticare le richieste.
- Il token è cifrato con **AES-256-CBC** prima di essere salvato nel database.
- La chiave di cifratura è derivata dalla costante PrestaShop `_COOKIE_KEY_`.
- Il token ha una scadenza di **1 mese** dal salvataggio.
- Se il token non è valido, il modulo lo revoca automaticamente e ne richiede uno nuovo tramite warning nell'admin.

### Gestione degli errori API

Le risposte API sono incapsulate nel DTO `ApiResponse`:

- `success` (bool)
- `statusCode` (int)
- `data` (mixed)
- `errorMessage` (string)
- `errorType` (string): `auth`, `network`, `server`, `parse`

---

## Gestione corrieri

### Registrazione dinamica

I corrieri non sono hardcoded nel modulo. Vengono recuperati dinamicamente dall'API `/api/getCarriers` e registrati come corrieri PrestaShop.

Per ogni corriere ricevuto, il modulo:
1. Crea un oggetto `Carrier` PrestaShop.
2. Associa il corriere a tutte le zone, i negozi (multistore) e i gruppi clienti.
3. Inserisce un range di peso difensivo da **0 a 999 kg**.
4. Inserisce le regole di consegna per tutte le zone.
5. Salva la mappatura nella tabella `spedisciqui_carrier`.

### Campi mappati

| Campo API | Campo DB | Descrizione |
|-----------|----------|-------------|
| `code` | `carrier_code` | Codice univoco corriere |
| `name` | `carrier_name` | Nome visualizzato |
| `service_title` | `service_name` | Nome servizio / tempi di consegna (multilingua) |
| `destination` | `is_pickup_point` | Booleano: `true` se il corriere supporta punti di ritiro (`pickup_point`, `fermopoint`) |
| `type`, `origin`, `logo_url` | `extra_data` (JSON) | Dati aggiuntivi |
| `delivery_days` | `delay` | Giorni di consegna |

### Rimozione corrieri

- **Singolo corriere:** Soft-delete (campo `deleted = 1`) nella tabella PrestaShop `carrier`.
- **Disinstallazione modulo:** Rimozione di tutti i corrieri registrati da SpedisciQui.

---

## Tariffe di spedizione

Il modulo supporta un sistema di **tariffe per fasce di peso**.

### Configurazione

Le tariffe sono accessibili dal pannello admin e permettono di:
- Definire fasce di peso (`weight_from` inclusivo, `weight_to` esclusivo).
- Impostare un prezzo (`tariff`) e la valuta (`currency_iso`, default EUR).
- Configurare più fasce per lo stesso servizio corriere.

### Validazione

- Non sono ammesse fasce di peso sovrapposte per lo stesso servizio.
- I valori di peso e tariffa devono essere non negativi.

### Calcolo costo in tempo reale

Il metodo `CarrierServices::getApplicableTariff($serviceCode, $weight)` individua la tariffa corrispondente al peso del carrello. Se non viene trovata una tariffa valida, il costo di spedizione è `0`.

### Dimensionamento automatico pacco

Prima di calcolare il costo, il modulo determina le dimensioni del pacco in base ai prodotti nel carrello:
- **Lunghezza:** dimensione massima tra tutti i prodotti.
- **Larghezza:** dimensione massima tra tutti i prodotti.
- **Altezza:** somma delle altezze di tutti i prodotti, moltiplicata per la quantità.
- **Fallback:** se i prodotti non hanno dimensioni definite, si usano le dimensioni predefinite configurate nel wizard.

---

## Ciclo di vita di una spedizione

Il modulo gestisce 9 stati di spedizione:

```
┌────────┐     ┌─────────────────┐     ┌────────────┐
│ pending │────▶│ label_created   │────▶│ picked_up  │
└────────┘     └─────────────────┘     └────────────┘
       │               │                       │
       │               │                       ▼
       │               │               ┌────────────┐
       │               │               │ in_transit │
       │               │               └────────────┘
       │               │                       │
       │               │                       ▼
       │               │               ┌─────────────────┐
       │               │               │ out_for_delivery │
       │               │               └─────────────────┘
       │               │                       │
       │               │                       ▼
       │               │               ┌──────────┐
       │               │               │ delivered│
       │               │               └──────────┘
       │               │
       │               ├────────────────▶ failed
       │               ├────────────────▶ cancelled
       │               └────────────────▶ returned
       │
       └────────────────────────────────▶ failed
                                                       cancelled
```

### Stati

| Stato | Chiave interna | Descrizione |
|-------|---------------|-------------|
| In attesa | `pending` | Ordine confermato, spedizione non creata. |
| Etichetta creata | `label_created` | Etichetta generata, spedizione non ancora ritirata. |
| Ritirata | `picked_up` | Corriere ha ritirato il pacco. |
| In transito | `in_transit` | Pacco in viaggio verso la destinazione. |
| In consegna | `out_for_delivery` | Corriere in consegna. |
| Consegnata | `delivered` | Consegna completata. |
| Fallita | `failed` | Errore durante la creazione o gestione spedizione. |
| Annullata | `cancelled` | Spedizione annullata (non ammesso se già `delivered`). |
| Restituita | `returned` | Pacco restituito al mittente. |

### Flusso dettagliato

1. **Ordine confermato (`hookActionValidateOrder`)**
   - Viene creato un record in `spedisciqui_shipments` con stato `pending`.
   - Vengono calcolate e salvate dimensioni, peso, costo spedizione e indirizzo di consegna.

2. **Creazione spedizione (Admin)**
   - L'amministratore accede al dettaglio ordine/spedizione.
   - Il modulo costruisce il payload API includendo:
     - Dati mittente (indirizzo configurato).
     - Dati destinatario (indirizzo dell'ordine).
     - Dati pacco (dimensioni, peso, numero di pezzi).
     - Eventuale assicurazione e COD.
   - Viene chiamato `POST /api/v1/create_shipment`.

3. **Risposta API**
   - **Successo:** Viene restituito il numero di tracciamento e l'etichetta PDF (base64).
     - L'etichetta viene salvata su filesystem (directory protetta).
     - Lo stato passa a `label_created`.
   - **Errore:** Lo stato passa a `failed` e viene salvato il messaggio di errore.

4. **Azioni amministratore**
   - **Annulla spedizione:** Consente di annullare una spedizione non consegnata (passa a `cancelled`).
   - **Dettaglio spedizione:** Visualizza ordine, corriere, destinatario, tracking, etichetta e storico eventi.

---

## Generazione etichette

- Le etichette sono restituite dall'API come **PDF base64**.
- Vengono salvate in `/upload/spedisciqui/labels/label_{trackingNumber}_order{id}.pdf`.
- Il formato PDF è **A6, portrait, font Courier, senza intestazione/pie di pagina**.
- La directory è protetta da un file `.htaccess` che nega l'accesso web diretto.

### File chiave
- `classes/Service/LabelService.php`: Genera e salva il PDF utilizzando `tecnickcom/tcpdf`.

---

## Assicurazione e contrassegno

### Assicurazione
- Abilitabile/disabilitabile per ogni spedizione dal pannello di dettaglio.
- Il valore assicurato è definito in fase di creazione spedizione.
- Incluso nel payload API sotto la chiave `insurance`.

### Contrassegno (Cash on Delivery - COD)
- Rilevato automaticamente:
  - Se il metodo di pagamento dell'ordine è `ps_cashondelivery`.
  - Oppure se esplicitamente abilitato.
- L'importo del contrassegno corrisponde al totale dell'ordine (`total_paid_tax_incl`).
- Incluso nel payload API sotto la chiave `cod`.

---

## Hook PrestaShop

| Hook | Scopo | Implementazione |
|------|-------|-----------------|
| `hookActionValidateOrder` | Crea il record di spedizione quando l'ordine è validato | `classes/Hooks/InstalledHooks.php` |
| `hookActionAdminControllerSetMedia` | Inietta CSS e JS nelle pagine admin del modulo | `classes/Hooks/InstalledHooks.php` |

---

## Tabelle database

Tutte le tabelle sono prefissate con `ps_` (o il prefisso configurato in PrestaShop) e nomi `spedisciqui_*`.

| Tabella | Scopo |
|---------|-------|
| `spedisciqui_config` | Configurazione chiave/valore del modulo (scope per negozio). |
| `spedisciqui_api_credentials` | Token di accesso cifrati, IV, data di scadenza, refresh token, flag di attività. |
| `spedisciqui_package` | Dimensioni predefinite pacco per negozio. |
| `spedisciqui_sender_address` | Indirizzi mittente configurati. |
| `spedisciqui_carrier` | Mappatura tra corrieri PrestaShop e corrieri piattaforma SpedisciQui. |
| `spedisciqui_cart` | Dati carrello: selezione corriere, assicurazione, COD. |
| `spedisciqui_weight_tariffs` | Tariffe per fasce di peso per corriere/servizio. |
| `spedisciqui_shipments` | Registro centrale delle spedizioni: tracking, etichetta, stato, costi, date. |

### Gestione multi-shop

Tutti i dati sono scope per `id_shop`, quindi il modulo supporta ambienti PrestaShop **multistore** nativamente.

---

## Sicurezza

### Cifratura token
- I token API sono cifrati con **AES-256-CBC**.
- La chiave di cifratura è derivata dalla costante PrestaShop `_COOKIE_KEY_`:
  1. Hash SHA-256 della chiave.
  2. Conversione in binario (`hex2bin`).
  3. Utilizzo come chiave AES a 256 bit.
- Il vettore di inizializzazione (IV) è salvato insieme al token cifrato.

### Protezione etichette
- La directory `/upload/spedisciqui/labels/` contiene un file `.htaccess` che blocca l'accesso web diretto.
- Solo il backend PrestaShop può accedere ai PDF tramite lettura filesystem.

### Validazione input
- Regex per tracking number: `^[A-Za-z0-9\-_]{3,50}$`.
- Validazione pesi e tariffe: valori non negativi.
- Validazione intervalli di peso: prevenzione di sovrapposizioni.

### Logging
- Tutte le operazioni critiche sono tracciate tramite `PrestaShopLogger::addLog()` con tag `[SpedisciQui]`.
- Severità:
  - `1` = Info
  - `2` = Warning
  - `3` = Errore
  - `4` = Critico

---

## Risoluzione dei problemi

### Il modulo non riesce a connettersi all'API
- Verificare che il server API SpedisciQui sia in esecuzione su `http://127.0.0.1:8000`.
- Controllare che il firewall permetta connessioni sulla porta 8000.
- Verificare il token API nella sezione impostazioni.

### Token non valido / scaduto
- Il modulo revoca automaticamente il token se la verifica fallisce.
- Accedere al pannello admin e reinserire un nuovo token.

### Corrieri non appaiono nel checkout
- Verificare che la zona di spedizione del cliente sia tra quelle associate al corriere.
- Controllare che il peso dell'ordine ricada in una tariffa configurata.

### Etichette non generabili
- Verificare che la directory `/upload/spedisciqui/labels/` sia scrivibile dal web server.
- Controllare i permessi del filesystem.
- Verificare la disponibilità di TCPDF nella cartella `/vendor/` di PrestaShop.

### Errore di cifratura token
- Se la `_COOKIE_KEY_` PrestaShop è stata modificata dopo l'installazione, i token cifrati potrebbero risultare illeggibili. Rimuovere il token e reinserirlo.

---

## Supporto

Per assistenza tecnica o commerciale, contattare il team **SpedisciQui**.
