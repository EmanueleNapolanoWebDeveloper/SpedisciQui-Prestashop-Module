# SpedisciQui Shipping — Modulo per PrestaShop

> **Versione**: 1.0.0  
> **Autore**: Emanuele  
> **Tipo modulo**: Shipping & Logistics  
> **PrestaShop**: compatibile con la versione _PS_VERSION_

---

## Indice

1. [Descrizione](#descrizione)
2. [Caratteristiche principali](#caratteristiche-principali)
3. [Struttura del progetto](#struttura-del-progetto)
4. [Requisiti di sistema](#requisiti-di-sistema)
5. [Installazione](#installazione)
6. [Disinstallazione](#disinstallazione)
7. [Configurazione](#configurazione)
8. [Workflow di setup](#workflow-di-setup)
9. [Architettura e componenti interni](#architettura-e-componenti-interni)
10. [Database](#database)
11. [API](#api)
12. [Gestione dei corrieri](#gestione-dei-corrieri)
13. [Modelli di dati](#modelli-di-dati)
14. [Repository Pattern](#repository-pattern)
15. [Logging e diagnostica](#logging-e-diagnostica)
16. [Sicurezza](#sicurezza)
17. [Estensibilità](#estensibilità)
18. [Licenza](#licenza)

---

## Descrizione

`spedisciquishipping` è un modulo ufficiale per **PrestaShop** che integra la piattaforma di spedizioni **SpedisciQui** direttamente nel tuo e-commerce. Il modulo permette di:

- Autenticarsi alla piattaforma SpedisciQui tramite **Access Token**.
- Configurare il **pacco predefinito** (peso e dimensioni) utilizzato per il calcolo delle spedizioni.
- Definire l'**indirizzo mittente** predefinito.
- Sincronizzare e **installare dinamicamente i corrieri** disponibili sulla piattaforma direttamente nel backoffice di PrestaShop.
- Testare la connessione API in tempo reale.
- Rimuovere e riconfigurare i corrieri installati.

Il modulo si basa sull'**estensione `CarrierModule`** di PrestaShop, garantendo compatibilità nativa con il sistema di calcolo costi di spedizione, le zone di consegna e la gestione dei gruppi clienti.

---

## Caratteristiche principali

| Funzionalità | Descrizione |
|---|---|
| Autenticazione API | Inserimento e validazione dell'Access Token via interfaccia backoffice |
| Configurazione Pacco | Impostazione di peso (kg) e dimensioni (cm, h/l/p) del pacco standard |
| Indirizzo Mittente | Dati completi del mittente (nome, cognome, indirizzo, CAP, città, provincia, paese, telefono) |
| Sincronizzazione Corrieri | Recupero automatico dell'elenco corrieri disponibili tramite API |
| Installazione Corrieri | Aggiunta di un corriere SpedisciQui come metodo di spedizione PrestaShop con un click |
| Rimozione Corrieri | Disattivazione e rimozione dei corrieri installati |
| Test Connessione API | Verifica della comunicazione con il backend Laravel in ogni momento |
| Reset Token | Possibilità di riconfigurare l'Access Token in qualsiasi momento |
| Multi-shop | Supporto nativo per configurazioni multi-shop PrestaShop |
| Logging | Eventi critici registrati tramite `PrestaShopLogger` |
| Transazioni sicure | Tutte le operazioni DB protette da try/catch con rollback implicito |

---

## Struttura del progetto

```
spedisciquishipping/
│
├── spedisciquishipping.php          # File principale del modulo (entry point)
│
├── composer.json                    # Dipendenze PHP (GuzzleHTTP ^7.0)
├── composer.lock
├── vendor/                          # Librerie installate (GuzzleHTTP)
│   └── guzzlehttp/
│       ├── guzzle/                   # Client HTTP
│       ├── psr7/                     # PSR-7 HTTP messages
│       └── ...
│
├── Utilities/
│   ├── SpedisciQuiApi.php           # Client HTTP per l'API SpedisciQui
│   ├── ContentHandler.php           # Handler delle azioni POST del modulo
│   └── DatabaseManager.php          # Gestione creazione/eliminazione tabelle DB e mapping corrieri
│
├── Repositories/
│   ├── PackageRepository.php        # CRUD pacchi nel database del modulo
│   └── SenderRepository.php         # CRUD mittenti nel database del modulo
│
├── views/
│   ├── FormRender.php               # Rendering di tutti i form/template admin
│   ├── js/
│   │   └── config.js                # Placeholder JS per future estensioni UI
│   ├── css/
│   │   └── config.css              # Placeholder CSS per future estensioni UI
│   └── templates/
│       └── admin/
│           ├── config.tpl           # Template di configurazione legacy (placeholder)
│           ├── dashboard.tpl        # Dashboard principale (elenco corrieri, stati, azioni)
│           ├── package_form.tpl     # Form di inserimento/modifica pacco
│           └── sender_form.tpl      # Form di inserimento/modifica mittente
│
└── .gitignore                       # Regole di esclusione Git
```

---

## Requisiti di sistema

| Requisito | Valore minimo |
|---|---|
| PrestaShop | 1.7.x o superiore |
| PHP | 7.4 o superiore |
| Estensione PHP | `curl`, `json`, `mbstring` |
| Accesso HTTP | Il server PrestaShop deve potersi connettere all'API SpedisciQui |
| Composer | Opzionale — vendor/ incluso nel repository |

---

## Installazione

### Metodo 1 — Backoffice PrestaShop

1. Accedi al pannello di amministrazione di PrestaShop (`/admin`).
2. Vai a **Moduli > Moduli e Servizi**.
3. Clicca su **Carica un modulo**.
4. Carica il file zip dell'archivio del modulo oppure seleziona la cartella `spedisciquishipping` da file system.
5. Clicca su **Installa**.

PrestaShop eseguirà automaticamente il metodo `install()` del modulo, che:

- Registra il modulo nel database di PrestaShop.
- Crea tutte le tabelle personalizzate nel database (`spedisciqui_config`, `spedisciqui_package`, `spedisciqui_sender`, `spedisciqui_shipments`, `spedisciqui_carrier_mapping`).
- Azzera le configurazioni `SPEDISCIQUI_ACCESS_TOKEN` e `SPEDISCIQUI_SETUP_STEP`.

### Metodo 2 — Copia manuale

Se il modulo è già presente nel filesystem (es. in un progetto con deployment git):

```bash
# Posiziona la cartella nella directory dei moduli PrestaShop
cp -r /percorso/spedisciquishipping /path/to/prestashop/modules/

# Opzionale: installa via CLI PrestaShop
php bin/console prestashop:module install spedisciquishipping
```

### Verifica installazione

Al termine dell'installazione:

- La tabella `spedisciqui_config` deve esistere nel database PrestaShop con prefisso `_DB_PREFIX_`.
- Il modulo appare nella lista moduli del backoffice in categoria **Shipping & Logistics**.
- L'eventuale errore viene registrato nel log di PrestaShop e nel file di log PHP (`error_log`).

---

## Disinstallazione

La disinstallazione rimuove completamente tutte le tracce del modulo:

1. Vai a **Moduli > Moduli e Servizi**.
2. Cerca `SpedisciQui Shipping Primo`.
3. Clicca su **Disinstalla**.

Il metodo `uninstall()` esegue le seguenti operazioni:

- `parent::uninstall()` — deregistrazione standard PrestaShop.
- Eliminazione di tutte le tabelle del modulo (`DROP TABLE IF EXISTS`):
  - `_DB_PREFIX_spedisciqui_config`
  - `_DB_PREFIX_spedisciqui_package`
  - `_DB_PREFIX_spedisciqui_sender`
  - `_DB_PREFIX_spedisciqui_shipments`
  - `_DB_PREFIX_spedisciqui_carrier_mapping`
- Eliminazione delle chiavi di configurazione `SPEDISCIQUI_ACCESS_TOKEN` e `SPEDISCIQUI_SETUP_STEP`.

---

## Configurazione

Dopo l'installazione, accedi al pannello di configurazione del modulo. Il modulo guida l'utente attraverso tre passaggi sequenziali.

### Passo 1 — Access Token

Incolla la **chiave segreta (Access Token)** ottenuta dalla piattaforma SpedisciQui nel campo di testo dedicato, quindi clicca **Salva e verifica**.

Il token viene validato immediatamente con una chiamata a:

```
GET /api/auth/verify
Authorization: Bearer {token}
```

Se la risposta ha status code `200`, il token viene salvato in `Configuration::SPEDISCIQUI_ACCESS_TOKEN` e viene automaticalmente avanzato al passo successivo.

Se il token non è valido o il server non è raggiungibile, viene restituito un messaggio di errore.

### Passo 2 — Dati Pacco

Inserisci le dimensioni del pacco standard:

| Campo | Unità | Descrizione |
|---|---|---|
| Peso | kg | Peso del pacco in chilogrammi |
| Altezza | cm | Altezza del pacco in centimetri |
| Lunghezza | cm | Lunghezza del pacco in centimetri |
| Profondità | cm | Profondità del pacco in centimetri |

I valori sono salvati nella tabella `spedisciqui_package` e associati al `id_shop` corrente. Se esiste già un record per lo shop, il record viene aggiornato (upsert).

### Passo 3 — Indirizzo Mittente

Inserisci tutti i dati del mittente delle spedizioni:

| Campo | descrizione |
|---|---|
| Nome | Nome del mittente |
| Cognome | Cognome del mittente |
| Telefono | Numero di telefono (opzionale) |
| Indirizzo | Indirizzo completo (via, numero) |
| Città | Città di spedizione |
| CAP | Codice di avviamento postale |
| Paese | Codice ISO 3166-1 alpha-2 (es. `IT`) |
| Provincia | Sigla provincia italiana (es. `MI`) |

I dati sono salvati in `spedisciqui_sender` legati al `id_shop` corrente.

---

## Workflow di setup

```
┌─────────────────────────────┐
│  Modulo installato           │
│  Token: ❌ Assente           │
│  Setup step: null            │
└──────────────┬──────────────┘
               │
               ▼
┌─────────────────────────────┐
│  Step 1 — Access Token       │
│  Inserisci e valida il token │
└──────────────┬──────────────┘
               │
               ▼
┌─────────────────────────────┐
│  Step 2 — Dati Pacco         │
│  Peso + dimensioni           │
└──────────────┬──────────────┘
               │
               ▼
┌─────────────────────────────┐
│  Step 3 — Indirizzo Mittente │
│  Dati completi del mittente  │
└──────────────┬──────────────┘
               │
               ▼
┌─────────────────────────────┐
│  Dashboard — Corrieri        │
│  Visualizza / Aggiungi       │
│  / Rimuovi / Test API        │
└─────────────────────────────┘
```

Il flusso è gestito dalla classe `ContentHandler::resolveView()` (vedi [Architettura e componenti interni](#architettura-e-componenti-interni)).

---

## Architettura e componenti interni

### `spedisciquishipping.php` — Entry Point

Estende `CarrierModule` di PrestaShop, quindi è automaticamente riconosciuto come modulo di trasporto.

**Responsabilità**:
- Definizione metadati modulo (`name`, `tab`, `version`, `author`, `bootstrap`).
- Ciclo di vita: `install()` e `uninstall()`.
- Metodi di integrazione trasporto PrestaShop: `getOrderShippingCost()` e `getOrderShippingCostExternal()`.
- Delega della logica di rendering a `ContentHandler`.

**Dati chiave:**

```php
$this->name              = 'spedisciquishipping';
$this->tab               = 'shipping_logistics';
$this->version           = '1.0.0';
$this->author            = 'Emanuele';
$this->bootstrap          = true;
$this->need_instance      = 0;
```

`getOrderShippingCost()` restituisce attualmente un costo fisso di `5.0` — punto di estensione per integrare preventivi dinamici.

---

### `Utilities/SpedisciQuiApi.php` — Client API

Incapsula tutta la comunicazione HTTP con il backend SpedisciQui (applicazione Laravel esposta tipicamente su `http://127.0.0.1:8000`).

**Configurazione:**

```php
$baseUrl = Configuration::get('SPEDISCIQUI_API_BASE_URL') ?: 'http://127.0.0.1:8000';
$timeout = 10 secondi
```

**Metodi pubblici:**

| Metodo | Endpoint | Descrizione |
|---|---|---|
| `validateToken(string $token): bool` | `GET /api/auth/verify` | Verifica validità e scadenza dell'Access Token |
| `request(string $method, string $endpoint, array $payload = []): mixed` | Qualsiasi | Metodo generico per chiamate API autenticate con Bearer token |

**Gestione errori:**

- **401 Unauthorized**: Il token scaduto viene automaticamente eliminato da `Configuration` e viene loggato l'errore.
- **ConnectException**: Server non raggiungibile — log su livello 3 (errore).
- **ClientException**: Risposta 4xx — log su livello 3.
- **RequestException**: Altri errori di richiesta — log su livello 3.
- **JSON decode error**: Risposta non-JSON valida — log su livello 3.

---

### `Utilities/ContentHandler.php` — Controller delle azioni

Gestisce la logica delle azioni POST inviate dal backoffice.

**Metodo principale:**

```php
public function handle(): string
```

Verifica in sequenza le azioni inviate tramite `Tools::isSubmit()`:

| Azione POST | Metodo handler |
|---|---|
| `submitSpedisciQuiShipping` | `handleTokenSubmit()` — salva e valida il token |
| `submitPackageForm` | `handlePackageSubmit()` — salva dimensioni pacco |
| `submitSenderForm` | `handleSenderSubmit()` — salva dati mittente |
| `submitTestApi` | `handleTestApi()` — chiama `/api/testing` e mostra la risposta |
| `submitResetToken` | `handleResetToken()` — elimina token e reindirizza |
| `submitInstallCarrier` | `handleInstallcarrier()` — crea corriere PrestaShop da servizio API |

Dopo l'elaborazione delle azioni, viene chiamato `resolveView()` per determinare quale template visualizzare.

---

### `views/FormRender.php` — Renderizzatore template

Incapsula la logica di generazione di tutti i template backoffice.

**Metodi:**

| Metodo | Template | Descrizione |
|---|---|---|
| `renderTokenForm()` | HelperForm PrestaShop | Form di inserimento token con validazione |
| `renderPackageForm()` | `admin/package_form.tpl` | Form dimensioni pacco |
| `renderSenderForm()` | `admin/sender_form.tpl` | Form dati mittente |
| `renderDashboard()` | `admin/dashboard.tpl` | Dashboard con corrieri, installati, utente e azioni |

`renderDashboard()` effettua due chiamate API in parallelo:
- `GET /api/getCarriers` → elenco corrieri disponibili
- `GET /api/auth/user` → dati utente autenticato

---

### `Utilities/DatabaseManager.php` — Gestore database

Responsabile della creazione, eliminazione e gestione delle tabelle personalizzate del modulo.

**Tabelle gestite:**

| Tabella | Scopo |
|---|---|
| `spedisciqui_config` | Configurazioni chiave-valore per shop |
| `spedisciqui_package` | Dimensioni pacco predefinito per shop |
| `spedisciqui_sender` | Dati mittente predefinito per shop |
| `spedisciqui_shipments` | Storico spedizioni ( tracking, status, API response ) |
| `spedisciqui_carrier_mapping` | Mapping tra `serviceId` API e `carrierReferenceId` PrestaShop |

**Metodi pubblici:**

| Metodo | Descrizione |
|---|---|
| `createAllTableOnInstallation(): bool` | Crea tutte le tabelle in modo atomico (AND logico) |
| `dropAllSpedisciQuiTables(): bool` | Elimina tutte le tabelle in fase di disinstallazione |
| `getCarrierMapping(string $serviceId): ?array` | Recupera mapping per un singolo serviceId |
| `getAllCarrierMapping(): array` | Recupera tutti i mapping attivi |
| `saveCarrierMapping(string $serviceId, int $carrierReferenceId): bool` | Inserisce o aggiorna un mapping (upsert) |
| `deleteCarrierMapping(string $serviceId): bool` | Elimina un mapping per serviceId |

Tutte le operazioni sono protette da try/catch. In caso di errore, viene scritto un log tramite `PrestaShopLogger::addLog()` con livello 3 (errore grave).

---

### `Repositories/PackageRepository.php` — Repository pacchi

Implementa il pattern **Repository** per la persistenza dei dati del pacco.

**Metodi:**

```php
public function savePackage(int $id_shop, array $data): bool
public function getPackage(?int $id_shop = null): ?array
```

- `savePackage()` effettua un upsert: se esiste un record per lo `id_shop`, viene aggiornato; altrimenti viene inserito un nuovo record.
- `getPackage()` restituisce `height`, `depth`, `width`, `weight` o `null` se non esiste.

---

### `Repositories/SenderRepository.php` — Repository mittenti

Implementa il pattern **Repository** per la persistenza dei dati del mittente.

**Metodi:**

```php
public function saveSender(array $data, ?int $id_shop = null): bool
public function getSender(?int $id_shop = null): ?array
```

- `saveSender()` effettua un upsert. I campi `prov` e `country` vengono normalizzati in maiuscolo prima del salvataggio.
- `getSender()` restituisce tutti i campi del mittente o `null`.

---

## Database

### Schema delle tabelle

#### `spedisciqui_config`

```sql
CREATE TABLE `spedisciqui_config` (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_shop    INT UNSIGNED NOT NULL DEFAULT 1,
    key        VARCHAR(100) NOT NULL,
    value      TEXT DEFAULT NULL,
    date_add   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_upd   TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY shop_key (id_shop, key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Chiave unica composta `(id_shop, key)` per garantire una sola configurazione per shop.

#### `spedisciqui_package`

```sql
CREATE TABLE `spedisciqui_package` (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_shop   INT UNSIGNED NOT NULL DEFAULT 1,
    height    DECIMAL(8,2) NOT NULL,
    depth     DECIMAL(8,2) NOT NULL,
    width     DECIMAL(8,2) NOT NULL,
    weight    DECIMAL(8,2) NOT NULL,
    date_add  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_upd  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY id_shop (id_shop)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Un solo record per ogni `id_shop`.

#### `spedisciqui_sender`

```sql
CREATE TABLE `spedisciqui_sender` (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_shop   INT UNSIGNED NOT NULL DEFAULT 1,
    name      VARCHAR(150) NOT NULL,
    surname   VARCHAR(150) NOT NULL,
    address   VARCHAR(255) NOT NULL,
    zip       VARCHAR(10)  NOT NULL,
    city      VARCHAR(100) NOT NULL,
    prov      VARCHAR(5)   NOT NULL,
    country   VARCHAR(2)   NOT NULL DEFAULT 'IT',
    phone     VARCHAR(20)  DEFAULT NULL,
    date_add  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_upd  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_shop (id_shop)
);
```

#### `spedisciqui_shipments`

```sql
CREATE TABLE `spedisciqui_shipments` (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_order        INT UNSIGNED NOT NULL,
    id_shop         INT UNSIGNED NOT NULL DEFAULT 1,
    tracking_number VARCHAR(100) DEFAULT NULL,
    carrier_code    VARCHAR(50)  DEFAULT NULL,
    status          VARCHAR(50)  NOT NULL DEFAULT 'pending',
    api_response    TEXT DEFAULT NULL,
    date_add        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_upd        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY id_order (id_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Utilizzata per tracciare l'history di ogni spedizione sincronizzata.

#### `spedisciqui_carrier_mapping`

```sql
CREATE TABLE `spedisciqui_carrier_mapping` (
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    serviceId           VARCHAR(100) NOT NULL,
    carrierReferenceId  INT UNSIGNED NOT NULL,
    isActive            TINYINT(1) NOT NULL DEFAULT 1,
    date_add            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_upd            TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_service (serviceId),
    KEY idx_carrier_ref (carrierReferenceId),
    KEY idx_is_active (isActive)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Mantiene la corrispondenza tra l'`id` del servizio SpedisciQui e il `carrierReferenceId` generato da PrestaShop.

---

## API

### Configurazione base URL

Il base URL dell'API è configurabile tramite la chiave di configurazione `SPEDISCIQUI_API_BASE_URL`:

```php
$baseUrl = Configuration::get('SPEDISCIQUI_API_BASE_URL') ?: 'http://127.0.0.1:8000';
```

Modificare questo valore consente di indirizzare il modulo verso un'istanza di staging, di sviluppo o produzione dell'API SpedisciQui.

### Endpoint consumati

| Metodo | Endpoint | Scopo |
|---|---|---|
| `GET` | `/api/auth/verify` | Verifica validità Access Token (chiamato in `validateToken()`) |
| `GET` | `/api/getCarriers` | Recupero elenco corrieri disponibili (chiamato in `renderDashboard()`) |
| `GET` | `/api/auth/user` | Recupero dati utente autenticato (chiamato in `renderDashboard()`) |
| `GET` | `/api/testing` | Test generico di connettività (pulsante backoffice) |

### Headers di autenticazione

Tutte le richieste autenticate includono:

```
Authorization: Bearer {SPEDISCIQUI_ACCESS_TOKEN}
Accept: application/json
Content-Type: application/json
```

### Comportamento in caso di errore

| Scenario | Comportamento |
|---|---|
| Token non configurato | `request()` restituisce `null` e logga un errore livello 3 |
| 401 Unauthorized | Token eliminato da Configuration, log livello 2 |
| Server non raggiungibile | `null` restituito, log livello 3 |
| Risposta non-JSON | `null` restituito, log livello 3 |
| Timeout (10s) | `ConnectException` gestita, log livello 3 |

Tutti gli errori sono registrati tramite `PrestaShopLogger::addLog()` con prefisso `[SPEDISCIQUI]`.

---

## Gestione dei corrieri

### Recupero e visualizzazione

La dashboard chiama `GET /api/getCarriers` e popola la prima tabella con tutti i corrieri disponibili. Per ogni corriere sono mostrati:

- **Logo** — caricato da `logo_url` fornito dall'API.
- **Nome** — nome del corriere.
- **Servizio** — titolo del servizio di spedizione.
- **Consegna** — numero di giorni lavorativi previsti.
- **Tipo** — Nazionale o Internazionale.
- **Origine** — Ritiro a domicilio o Deposito.
- **Destinazione** — Consegna a domicilio o Punto di ritiro.
- **Stato installazione** — Badge verde "✅ Installato" se presente nel mapping, altrimenti pulsante **Aggiungi**.

### Installazione di un corriere

Quando l'amministratore clicca **Aggiungi**:

1. Viene creato un nuovo oggetto `Carrier` di PrestaShop con:
   - `active = true`
   - `is_module = true`
   - `shipping_external = true` — il costo di spedizione è delegato al modulo
   - `need_range = true` — richiede range di peso e prezzo
   - Ritardo di consegna impostato a *"2-3 giorni lavorativi"* per tutte le lingue
2. Il corriere viene associato a tutti i **gruppi clienti** (`Group::getGroups()`).
3. Il corriere viene associato a tutte le **zone geografiche** (`Zone::getZones()`).
4. Viene creato un `RangeWeight` con `delimiter1=0` e `delimiter2=999` (tutto il range di peso).
5. Vengono inserite le righe in `delivery` per ogni zona con `price=0` (costo gestito dal modulo).
6. Il corriere viene associato a tutti gli **shop** del multi-shop.
7. Il `carrierReferenceId` viene salvato in `Configuration::SPEDISCIQUI_CARRIER_{CODICE}`.
8. Il mapping `serviceId → carrierReferenceId` viene salvato in `spedisciqui_carrier_mapping`.

### Rimozione di un corriere

Cliccando **Rimuovi** su un corriere installato:

- Il corriere PrestaShop viene rimosso.
- Il mapping corrispondente in `spedisciqui_carrier_mapping` viene eliminato.
- La conferma è richiesta tramite `confirm()` JavaScript.

### Corrieri attivi

La seconda tabella della dashboard mostra tutti i corrieri attualmente installati sul negozio, con:

- ID PrestaShop (`id_carrier`)
- Nome corriere
- Codice API (`carrier_code`)
- Stato attivo/disattivo
- Pulsante **Rimuovi**

---

## Modelli di dati

### Oggetto carriere restituito da `/api/getCarriers`

```json
{
    "code": "GLS",
    "name": "GLS Corriere Espresso",
    "service_title": "Servizio Standard",
    "delivery_days": "2-3",
    "type": "national",
    "origin": "pickup",
    "destination": "home",
    "logo_url": "https://..."
}
```

### Oggetto utente restituito da `/api/auth/user`

```json
{
    "user": {
        "name": "Mario Rossi",
        "email": "mario@example.com"
    }
}
```

### Oggetto risposta `/api/testing`

```json
{
    "status": "ok",
    "message": "API operativa",
    "timestamp": "2026-05-19T12:00:00+00:00"
}
```

---

## Repository Pattern

Il codice utilizza il **Repository Pattern** per isolare la logica di accesso ai dati dal resto dell'applicazione.

Vantaggi:
- **Riutilizzo**: la stessa logica di salvataggio/lettura può essere usata da più view o handler senza duplicazione di codice SQL.
- **Manutenibilità**: modifiche allo schema `spedisciqui_package` o `spedisciqui_sender` richiedono aggiornamenti solo nel rispettivo repository.
- **Testabilità**: i repository possono essere mockati facilmente nei test unitari.
- **Separazione delle responsabilità**: `ContentHandler` non conosce i dettagli SQL, delega ai repository.

---

## Logging e diagnostica

Tutti gli eventi critici o di errore sono tracciati con il prefisso `[SPEDISCIQUI]`:

| Livello PrestaShop | Quando viene usato |
|---|---|
| 2 — Warning | Token non valido/scaduto (401) |
| 3 — Errore grave | errore DB, server non raggiungibile, errore JSON, setup errori installazione |

Debug file aggiuntivo scritto su disco:

```
/tmp/spedisciqui_debug.log
```

Contiene la risposta e l'elenco corrieri ad ogni caricamento della dashboard (`views/FormRender.php:103-107`).

---

## Sicurezza

| Aspetto | Misura adottata |
|---|---|
| Access Token | Validato lato server PRIMA del salvataggio in `Configuration` |
| Escape output | Tutti i valori Smarty passano per `escape:'htmlall':'UTF-8'` o `escape:'html':'UTF-8'` |
| SQL Injection | Tutte le query usano `pSQL()`, `(int)` casting, o `bqSQL()` |
| CSRF | Token admin PrestaShop (`Tools::getAdminTokenLite('AdminModules')`) in tutti i form |
| Esposizione token | I valori escaped con `htmlspecialchars()` quando mostrati in risposta API |
| Logging | Nessun valore sensibile scritto in chiaro nei log |

---

## Estensibilità

### Punti di estensione principali

| Punto di estensione | File | Cosa modificare |
|---|---|---|
| Costo spedizione dinamico | `spedisciquishipping.php:84` | `getOrderShippingCost()` — integra preventivo basato su peso/dimensioni/configurazione corriere |
| Chiamate API aggiuntive | `Utilities/SpedisciQuiApi.php:50` | Aggiungi nuovi metodi `request()` |
| Nuovi campi pacco/mittente | `Repositories/*Repository.php` + template `.tpl` | Aggiungi campi e salvali nel rispettivo repository |
| Nuovi workflow post-installazione | `Utilities/ContentHandler.php:240` | Estendi `resolveView()` con nuovi step |
| Dati aggiuntivi carriera PrestaShop | `Utilities/ContentHandler.php:171` | Estendi l'oggetto `Carrier` dopo `$carrier->add()` |

### Dipendenze esterne

```json
{
    "require": {
        "guzzlehttp/guzzle": "^7.0"
    }
}
```

**Guzzle 7** è l'unica dipendenza runtime. È già inclusa in `vendor/`. Per aggiornare:

```bash
composer update guzzlehttp/guzzle
```

---

## Chiavi di configurazione PrestaShop

| Chiave | Tipo | Descrizione |
|---|---|---|
| `SPEDISCIQUI_ACCESS_TOKEN` | `string | null` | Token di autenticazione API |
| `SPEDISCIQUI_SETUP_STEP` | `null | 1 | 2 | DONE` | Step corrente del wizard di configurazione |
| `SPEDISCIQUI_API_BASE_URL` | `string` | URL base dell'API (default: `http://127.0.0.1:8000`) |
| `SPEDISCIQUI_CARRIER_{CODICE}` | `int` | `carrierReferenceId` PrestaShop per ogni corriere installato |

Le chiavi `SQ_DEFAULT_PARCEL_*` e `SQ_SENDER_*` presenti in `config.tpl` sono **placeholder** e non sono attualmente gestite dal codice (il modulo usa le tabelle personalizzate invece di `Configuration` generiche).

---

## Flusso di richiesta completo

```
Admin clicca su SpedisciQui nel menu moduli
              │
              ▼
     spedisciquishipping.getContent()
              │
              ▼
     ContentHandler.handle()
              │
              ├── [nessun POST] → resolveView()
              │       ├── token assente      → renderTokenForm()
              │       ├── step == 1          → renderPackageForm()
              │       ├── step == 2          → renderSenderForm()
              │       └── altrimenti         → renderDashboard()
              │
              ├── [POST: submitSpedisciQuiShipping]  → handleTokenSubmit()
              ├── [POST: submitPackageForm]           → handlePackageSubmit()
              ├── [POST: submitSenderForm]            → handleSenderSubmit()
              ├── [POST: submitTestApi]               → handleTestApi()
              ├── [POST: submitResetToken]            → handleResetToken()
              └── [POST: submitInstallCarrier]        → handleInstallCarrier()
```

---

## Licenza

Proprietario — Tutti i diritti riservati.
