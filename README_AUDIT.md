# SpedisciQui Shipping - Technical Audit Report

## Executive Summary

**Overall Score: 65/100**

The module demonstrates a solid architectural foundation with clear separation of concerns using Repository, Service, and Handler patterns. However, significant issues exist in configuration management, duplicate class instantiation, missing CSRF protection, and technical debt from incomplete implementations. The codebase requires immediate attention to security concerns and architectural cleanup before production deployment.

---

## Architettura

### Punti forti
- **Separation of Concerns**: Clear division between Repositories (data access), Services (business logic), and Handlers (request processing)
- **Folder Structure**: Well-organized `classes/Core`, `classes/Service`, `classes/Handlers`, `classes/Renderers` directories
- **Database Abstraction**: Use of DbQuery for database operations instead of raw queries
- **Setup Flow**: Multi-step setup process for initial configuration is well-conceived
- **Hook Integration**: Proper use of PrestaShop hooks (`actionValidateOrder`)

### Punti deboli
- **God Object in Constructor**: `spedisciquishipping.php:67-128` - Module constructor instantiates excessive dependencies creating tight coupling
- **Duplicate Instantiation**: Lines 103-107 instantiate `CarrierRepository` twice with different dependencies, causing the first instance to be overwritten
- **Missing Dependency Injection**: Hard-coded `new` calls throughout codebase instead of injected dependencies
- **Static Context Calls**: Multiple `Context::getContext()` calls create hidden dependencies and hinder testability
- **Incomplete DashboardHandlers**: Empty class indicating incomplete feature implementation

### Problemi futuri (6 mesi)
- Configuration duplication will lead to maintenance nightmares
- Circular dependencies between services will make testing increasingly difficult
- Static Context calls will become blockers for unit testing

### Problemi futuri (2 anni)
- Architecture will become rigid and resistant to change
- Testing will require extensive mocking due to static calls
- Refactoring will be costly due to tight coupling

---

## Sicurezza

### Vulnerabilità critiche
1. **CSRF Protection Missing** (`views/templates/admin/_partials/orders_panel.tpl:836-844`)
   - Form submissions lack CSRF token validation
   - Severity: **CRITICAL** - Allows unauthorized form submissions
   - Solution: Add `Tools::getAdminTokenLite()` to forms and validate on server side

<!-- 2. **Token Exposure Risk** (`views/templates/admin/_partials/initial/token_config.tpl:267-268`)
   - Access token input field uses `type="password"` but token is visible in source
   - Severity: **CRITICAL** - Sensitive credential exposure in DOM
   - Solution: Implement proper token masking or use a different UI pattern -->

3. **No Input Validation for Numeric Fields** (`classes/Handlers/CarrierHandlers.php:176-195`)
   - Weight/price input arrays lack validation
   - Severity: **BASSO/MEDIO** - Potential for injection or data corruption
   - Solution: Validate array keys and values before processing

<!-- ### Vulnerabilità medie
1. **SQL Injection Risk** (`classes/Repositories/ConfigRepositories.php:76-85`)
   - `pSQL()` used on integer values which is incorrect
   - Severity: **MEDIUM** - Mixed quoting can lead to edge cases
   - Solution: Cast to `(int)` for integers, use `pSQL()` only for strings -->

2. **XSS in Template Escaping** (`views/templates/admin/_partials/carrier_active_dash.tpl:484-489`)
   - Date formatting uses `|truncate` without proper escaping
   - Severity: **MEDIUM** - Potential output corruption
   - Solution: Apply proper escaping modifier before truncate

3. **Missing Authorization Checks** (`classes/Hooks/checkout/CustomCheckout.php:116-117`)
   - No permission check before accessing order data
   - Severity: **MEDIUM** - Potential privilege escalation
   - Solution: Validate employee permissions if accessed in admin context

### Vulnerabilità basse
1. **Sensitive Logging** (`classes/Handlers/CarrierHandlers.php:222-228` and multiple locations)
   - Excessive use of `print_r()` in logs might expose data
   - Severity: **LOW** - Information disclosure in logs
   - Solution: Remove verbose debug logging in production

---

## Database

### Tabella: spedisciqui_config
**Punti forti:**
- PRIMARY KEY and UNIQUE KEY on `shop_key` composite index
- Proper use of `bqSQL()` for table names

**Problemi:**
- Column name `key` is a reserved word (line 19) - potential SQL compatibility issues
- Missing `id_shop` index optimization (only in UNIQUE KEY)
- Severity: **MEDIUM**

**Suggerimenti:**
- Rename `key` to `config_key` for SQL compatibility
- Add separate `KEY idx_shop (id_shop)` index

### Tabella: spedisciqui_api_credentials
**Punti forti:**
- Unique constraint on `id_shop` prevents duplicates
- `is_active` index for filtering

**Problemi:**
- `access_token` stored as plain text in TEXT field. No encryption
- Severity: **HIGH**

### Tabella: spedisciqui_package
**Punti forti:**
- Indexes on `id_shop` and `is_default`

**Problemi:**
- `savePackage()` method doesn't use transactions - race conditions possible
- Severity: **MEDIUM**

### Tabella: spedisciqui_sender_address
**Punti forti:**
- Comprehensive indexing including `id_shop`, `is_default`, `is_active`

**Problemi:**
- Query at line 23-28 uses raw SQL instead of DbQuery
- Severity: **LOW**

### Tabella: spedisciqui_carrier
**Punti forti:**
- Foreign key constraint to `ps_carrier` table
- Multiple useful indexes

**Problemi:**
- Query at line 38-39 in `getCarrierByCode()` lacks closing quote in SQL (potential syntax error)
- Severity: **HIGH** - Syntax error will cause runtime failure

### Tabella: spedisciqui_weight_tariffs
**Punti forti:**
- Composite index `idx_lookup` for common queries
- Weight range index for pricing lookups
- Foreign key constraint

**Problemi:**
- Missing `id_shop` in WHERE clause for multi-shop scenarios in some queries
- Severity: **MEDIUM**

### Tabella: spedisciqui_shipments
**Punti forti:**
- Extensive indexing including order, shop, carrier, tracking, status
- Foreign key constraint to `spedisciqui_carrier`
- ENUM column for status validation

**Problemi:**
- Missing `id_shop` filtering in `updateStatus()` method
- Severity: **LOW**

---

## Performance

### Query Analysis
- **N+1 Query**: `ShipmentRenderer::getShipments()` executes JOIN queries but could be optimized for large datasets
- **Duplicate Queries**: `getSavedCarriers()` called twice in `ContentHandler::handle()` (lines 132, 133)
- **Missing Indexes**: `spedisciqui_config.key` column lacks dedicated index

### Rendering Smarty
- Large inline CSS blocks (600+ lines) in templates - better moved to separate `.css` files
- `updatePreview()` JavaScript runs on every input event - could debounce
- Empty `orders_detail.tpl` file indicates incomplete feature

### Caching
- **Rating: ABSENT**
- No caching layer for API responses
- No caching for carrier lists
- Configuration values queried repeatedly without caching

---

## Qualità del codice

### Naming Issues
| File | Issue | Suggestion |
|------|-------|------------|
| `spedisciquishipping.php` | Class name uses lowercase (`spedisciquishipping`) | Should follow PSR-4: `SpedisciQuiShipping` |
| `SQMigrations.php` | Class name doesn't match filename case (`SQMigrations` vs file) | Rename to `SqMigrations` or `SpedisciQuiMigrations` |
| `DatabaseManager.php` | Duplicate table creation logic exists in `SQMigrations.php` | Consolidate into single class |

### Complexity Issues
- `CarrierRepository::saveCarrierInPS()`: 112 lines, handles carrier creation, zone associations, shop associations, ranges
- **Severity**: Method does too many things - violates Single Responsibility
- `CarrierServices::saveTariffs()`: 80 lines with nested try/catch and validation logic

### Duplications
1. **Duplicate Table Creation Logic** (`Utilities/DatabaseManager.php` vs `classes/Core/Database/SQMigrations.php`)
   - Both files create tables with similar structure
   - Severity: **HIGH** - Technical debt, maintenance burden

2. **API Client Instantiation** - Multiple places create new `ApiClient` instances instead of reusing
   - `CarrierApi.php:62`, `CustomCheckout.php:62`, `CredentialServices.php:12`

### Error Handling
- Consistent use of `PrestaShopLogger::addLog()`
- Missing specific exception types - all catch generic `Exception`
- No centralized error handler

---

## PrestaShop Best Practices

### Violations
1. **PSR-4 Autoloading Missing** - Module uses `require` statements instead of proper autoloading
   - File: `spedisciquishipping.php:11-53`
   - Severity: **MEDIUM**

2. **No Upgrade Script** - Module lacks `upgrade.php` for version migrations

3. **Missing Module Name Constant** - Module name hardcoded in multiple places instead of using `this->name`

4. **Template Security** (`dashboard_layout.tpl:18`)
   - Using `{$content nofilter}` bypasses escaping
   - Severity: **MEDIUM** - XSS risk if content contains user data

5. **Configuration API** - Not using PrestaShop Configuration class for simple settings, storing in custom table instead

---

## Scalabilità

### Colli di bottiglia a 10,000 spedizioni/giorno
1. **Shipment Creation in Hook** (`CustomCheckout.php:168`)
   - `hookActionValidateOrder()` creates shipment synchronously
   - **Risk**: Blocking operation during checkout
   - **Solution**: Queue-based async processing

2. **Database Index Missing**
   - `spedisciqui_config` table missing dedicated `config_key` index
   - **Risk**: N+1 query during carrier price lookups

3. **API Calls Not Cached**
   - Every carrier price calculation calls API (`CarrierApi.php:39-85`)
   - **Risk**: API rate limiting, checkout delays

4. **No Pagination Optimization**
   - `ShipmentRenderer::getShipments()` uses LIMIT/OFFSET which degrades with large datasets
   - **Risk**: Slow dashboard loading with history

---

## Manutenibilità

**Rating: 6/10**

- Well-commented code with clear intent
- Inconsistent naming conventions
- Missing unit tests (no tests directory)
- Tight coupling makes mocking difficult
- Configuration scattered across files

---

## Priorità di intervento

### Critiche
1. ✅ **CSRF Protection** - Must be implemented in all forms
2. ✅ **Remove Duplicate Object Instantiation** - Fix `spedisciquishipping.php` lines 103-118
3. ✅ **Fix SQL Syntax Error** - `getCarrierByCode()` missing closing quote
4. ✅ **Remove Duplicate DatabaseManager** - Consolidate with SQMigrations

### Alte
1. ✅ **Remove Unused Files** - `Utilities/DatabaseManager.php`, `classes/Handlers/DashboardHandlers.php`, `views/templates/admin/_partials/_orders/orders_detail.tpl`
2. ✅ **Implement Proper DI** - Replace `new` calls with constructor injection
3. ✅ **Add Caching Layer** - Cache API responses and configuration
4. ✅ **Fix Template Escaping** - Remove `nofilter` or validate content

### Medie
1. ✅ **Rename Classes for Consistency** - Follow PSR-4 standards
2. ✅ **Extract Large Methods** - Refactor `saveCarrierInPS()`, `saveTariffs()`
3. ✅ **Add Indexes** - `spedisciqui_config.config_key` index
4. ✅ **Move Inline CSS** - Extract to `.css` files

### Basse
1. ✅ **Clean Up Debug Code** - Remove commented code and debug logs
2. ✅ **Add PHP Docblocks** - For IDE support
3. ✅ **Optimize Logging** - Reduce verbosity in production

---

## Roadmap Consigliata

### Breve termine (1-2 settimane)
1. Implement CSRF protection in all admin forms
2. Remove duplicate class instantiation in module constructor
3. Fix SQL syntax errors and add missing validation
4. Remove unused/duplicate files

### Medio termine (1-2 mesi)
1. Refactor to proper dependency injection
2. Implement caching for API responses and carrier lists
3. Extract large methods for better testability
4. Add unit test infrastructure

### Lungo termine (3-6 mesi)
1. Implement async queue for shipment creation
2. Add upgrade script functionality
3. Implement proper PSR-4 autoloading
4. Add monitoring and performance metrics

---

## Conclusione

Come revisore senior, non consiglierei questo modulo per la produzione in questo stato. Sebbene mostri buona comprensione dei pattern architetturali e una struttura di base solida, i problemi criticici di sicurezza (CSRF, SQL injection edge cases) e le violazioni architetturali gravi (istanza duplice di oggetti, dipendenze mancanti) rappresentano rischi significativi.

**Raccomandazione**: Prima della produzione, è necessario risolvere almeno tutte le criticità di sicurezza e le duplicazioni di codice. Una volta completati questi interventi, il modulo avrebbe un potenziale eccellente grazie alla sua architettura modulare e alla separazione delle responsabilità già implementata.