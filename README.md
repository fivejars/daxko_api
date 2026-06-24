# Daxko API

Drupal module that provides a service layer for integrating with the [Daxko Partners API](https://api.partners.daxko.com/). It handles OAuth2 authentication, token caching, and exposes dedicated services for Branches, Memberships, and Programs.

Compatible with **Drupal 10** and **Drupal 11**.

---

## Requirements

- PHP 8.1+ (uses `declare(strict_types=1)` throughout)
- Drupal 10.x or 11.x
- A Daxko Partners API account with OAuth2 credentials (`client_id`, `client_secret`, `scope`)

---

## Installation

### Via Composer (recommended)

```bash
composer require fivejars/daxko_api
drush en daxko_api
```

### Manual

1. Place the module directory at `web/modules/custom/daxko_api` (or `modules/custom/daxko_api`).
2. Enable it via Drush or the admin UI:

```bash
drush en daxko_api
drush cr
```

---

## Configuration

Navigate to **Administration → Configuration → Web Services → Daxko API settings**
(`/admin/config/services/daxko`).

Requires the `administer site configuration` permission.

| Field | Description |
|-------|-------------|
| **Client ID** | OAuth2 client ID from Daxko. |
| **Client Secret** | OAuth2 client secret from Daxko. |
| **Scope** | The API scope granted to your credentials (provided by Daxko). |
| **Delay** | Optional delay (ms) between HTTP requests: `0` (disabled), `100`–`600`. Useful for rate-limit compliance. |

Settings are stored in `daxko_api.settings` config object.

### Drush / config management

```bash
# Export after saving the form
drush cex

# Or set via config import — daxko_api.settings.yml:
# client_id: 'YOUR_CLIENT_ID'
# client_secret: 'YOUR_CLIENT_SECRET'
# scope: 'YOUR_SCOPE'
# delay: 0
```

> **Security note:** Do not commit real credentials to version control. Use environment-variable overrides in `settings.php`:
> ```php
> $config['daxko_api.settings']['client_id'] = getenv('DAXKO_CLIENT_ID');
> $config['daxko_api.settings']['client_secret'] = getenv('DAXKO_CLIENT_SECRET');
> $config['daxko_api.settings']['scope'] = getenv('DAXKO_SCOPE');
> ```

---

## Architecture

```
daxko_api/
├── daxko_api.info.yml          # Module metadata
├── daxko_api.services.yml      # Service container definitions
├── daxko_api.routing.yml       # Settings page route
├── daxko_api.links.menu.yml    # Admin menu link
├── composer.json
└── src/
    ├── DaxkoClientInterface.php        # Contract for HTTP client
    ├── DaxkoClient.php                 # OAuth2-aware HTTP client
    ├── DaxkoEndpointBase.php           # Base class for endpoint services
    ├── DaxkoApiMembershipInterface.php # Contract for membership endpoints
    ├── DaxkoApiProgramInterface.php    # Contract for program endpoints
    ├── Membership.php                  # Membership endpoint service
    ├── Program.php                     # Program endpoint service
    ├── Branch.php                      # Branch endpoint service
    └── Form/
        └── SettingsForm.php            # Admin settings form
```

### Service dependency graph

```
daxko_api.daxko_client  ←── config.factory
                        ←── http_client_factory
                        ←── cache.default
                        ←── logger.channel.daxko_api

daxko_api.membership    ←── daxko_api.daxko_client
daxko_api.program       ←── daxko_api.daxko_client
daxko_api.branch        ←── daxko_api.daxko_client
```

---

## Services

### `daxko_api.daxko_client`

Class: `Drupal\daxko_api\DaxkoClient`
Interface: `Drupal\daxko_api\DaxkoClientInterface`

Low-level authenticated HTTP client. Automatically:
- Fetches and caches the OAuth2 Bearer token (`daxko.access_token` in the default cache bin, respecting `expires_in` from the token response).
- Attaches `Authorization: Bearer <token>` and `Accept: application/json` headers to every request.
- Logs errors via `logger.channel.daxko_api` (Watchdog channel `daxko_api`).
- Returns an empty array on any error (never throws to callers).

**Base URL:** `https://api.partners.daxko.com/`

**Token endpoint:** `POST /auth/token`

```php
/** @var \Drupal\daxko_api\DaxkoClientInterface $client */
$client = \Drupal::service('daxko_api.daxko_client');

$data = $client->request('GET', '/api/v1/branches');
```

#### `DaxkoClientInterface::request()`

```php
public function request(string $method, string $uri = '', array $options = []): array;
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$method` | `string` | HTTP verb: `GET`, `POST`, `PUT`, etc. |
| `$uri` | `string` | Endpoint path, e.g. `/api/v1/branches`. |
| `$options` | `array` | Guzzle request options (`query`, `json`, `form_params`, `headers`, …). |

Returns the decoded JSON response as an associative array, or `[]` on failure.

---

### `daxko_api.branch`

Class: `Drupal\daxko_api\Branch`

#### `loadAll(): array`

Returns all branches.

```
GET /api/v1/branches
```

```php
/** @var \Drupal\daxko_api\Branch $branch */
$branch = \Drupal::service('daxko_api.branch');

$branches = $branch->loadAll();
// [['id' => '...', 'name' => '...', ...], ...]
```

#### `load(string $id): array`

Returns a single branch by ID. Throws `\InvalidArgumentException` if `$id` is empty.

```
GET /api/v1/branches/{id}
```

```php
$info = $branch->load('branch-id-123');
```

---

### `daxko_api.membership`

Class: `Drupal\daxko_api\Membership`
Interface: `Drupal\daxko_api\DaxkoApiMembershipInterface`

#### `getTypes(string $branch_id, bool $discount_group_ids = false, string $registration_type = 'online'): array`

Returns available membership types for a branch.

```
GET /api/v1/membership/membership_types
```

| Parameter | Default | Description |
|-----------|---------|-------------|
| `$branch_id` | — | Required. Restricts results to this branch. |
| `$discount_group_ids` | `false` | When `true`, allows discount group IDs to be applied to fees. |
| `$registration_type` | `'online'` | `'online'` or `'in_house'`. |

```php
/** @var \Drupal\daxko_api\Membership $membership */
$membership = \Drupal::service('daxko_api.membership');

$types = $membership->getTypes('branch-id-123');
```

#### `getAgeGroups(): array`

Returns all membership age groups.

```
GET /api/v1/membership/age_groups
```

```php
$groups = $membership->getAgeGroups();
```

#### `getBranches(string $registration_type = 'online', bool $renew = false): array`

Returns branches that support membership join (or renewal).

```
GET /api/v1/membership/branches
```

| Parameter | Default | Description |
|-----------|---------|-------------|
| `$registration_type` | `'online'` | `'online'` or `'in_house'`. |
| `$renew` | `false` | When `true`, includes branches that allow membership renewal. |

```php
$branches = $membership->getBranches('online', TRUE);
```

#### `join(string $type_id, ?string $registration_type = null): array`

Starts a new membership join process. Returns a `cart_id` used in subsequent calls.

```
POST /api/v1/membership/join
```

```php
$result = $membership->join('membership-type-id');
// ['cart_id' => 'db2c4395-...', 'links' => [...]]
```

#### `review(string $cart_id): array`

Retrieves cart/membership information for review before finalizing.

```
GET /api/v1/membership/{cart_id}
```

```php
$cart = $membership->review('db2c4395-888c-42ce-9056-08e2f8a5f2d0');
```

#### `renew(string $type_id, string $unit_id, string $registration_type = 'online'): array`

Starts a membership renewal process.

```
POST /api/v1/membership/renewal
```

| Parameter | Description |
|-----------|-------------|
| `$type_id` | Membership type ID. |
| `$unit_id` | The existing unit (member) to renew. |
| `$registration_type` | `'online'` or `'in_house'`. |

```php
$result = $membership->renew('type-id', 'unit-id');
// ['cart_id' => '...', 'links' => [...]]
```

---

### `daxko_api.program`

Class: `Drupal\daxko_api\Program`
Interface: `Drupal\daxko_api\DaxkoApiProgramInterface`

#### `getCategories(array $location_ids = [], int $limit = 100, array $params = []): array`

Returns program categories, optionally filtered by location.

```
GET /api/v1/programs/categories
```

```php
/** @var \Drupal\daxko_api\Program $program */
$program = \Drupal::service('daxko_api.program');

$categories = $program->getCategories(['loc-id-1', 'loc-id-2'], 50);
```

#### `search(array $category_ids = [], array $location_ids = [], array $filters = [], int $limit = 100, string $sort = '-score'): array`

Searches program offerings. Returns results in a unified format regardless of program type.

```
GET /api/v1/programs/offerings/search
```

| Parameter | Default | Description |
|-----------|---------|-------------|
| `$category_ids` | `[]` | Restrict to these categories. |
| `$location_ids` | `[]` | Restrict to these locations. |
| `$filters` | `[]` | Additional query filters (merged into the query string). |
| `$limit` | `100` | Max results (API max is 100). |
| `$sort` | `'-score'` | Sort order: `name`, `score`, `start_date`. Prefix with `+` (asc) or `-` (desc). |

```php
$results = $program->search(
  category_ids: ['cat-1'],
  location_ids: ['loc-1'],
  limit: 25,
  sort: '+name',
);
```

#### `getProgramLocations(array $category_ids = [], int $limit = 100, array $params = []): array`

Returns locations that have programs matching the given criteria.

```
GET /api/v1/programs/locations
```

Supported `$params` keys:

| Key | Description |
|-----|-------------|
| `as_of` | Date range object: `{start, end, mode}` |
| `offering_types` | `session`, `package`, `rate_plan`, `camp_instance` |
| `registration_type` | `'online'` or `'in_house'` |
| `include_inactive_categories` | Boolean |
| `include_inactive_locations` | Boolean |
| `date_ranges` | Date range restrictions |

```php
$locations = $program->getProgramLocations(['cat-1'], 50, [
  'registration_type' => 'online',
]);
```

#### `getOfferingList(string $program_id, array $offering_ids = [], array $filters = []): array`

Returns offerings available for registration under a specific program.

```
GET /api/v1/programs/{program_id}/offerings
```

Supported `$filters` keys: `category_ids`, `location_ids`, `registration_type`.

```php
$offerings = $program->getOfferingList('program-id-123');
```

#### `getOfferingDetails(string $program_id, string $offering_id, string $location_id): array`

Returns full details for a specific offering.

```
GET /api/v1/programs/{program_id}/offerings/{offering_id}
```

```php
$details = $program->getOfferingDetails('prog-id', 'offering-id', 'loc-id');
```

---

## Extending the module

### Adding a new endpoint service

1. Create a class in `src/` extending `DaxkoEndpointBase`.
2. (Optional) Define an interface.
3. Register it in `daxko_api.services.yml`:

```yaml
daxko_api.my_endpoint:
  class: Drupal\daxko_api\MyEndpoint
  arguments: ['@daxko_api.daxko_client']
```

### Replacing the HTTP client

Implement `DaxkoClientInterface` and swap the service class in your module's `services.yml`:

```yaml
services:
  daxko_api.daxko_client:
    class: Drupal\my_module\MyDaxkoClient
    arguments: ['@config.factory', '@http_client_factory', '@cache.default', '@logger.channel.daxko_api']
```

---

## Logging

All events are logged to the `daxko_api` channel, viewable at **Reports → Recent log messages** (filter by type `daxko_api`), or via Drush:

```bash
drush watchdog:show --type=daxko_api
```

Logged events:
- `info` — New access token granted successfully.
- `error` — Failed to retrieve access token (with message).
- `error` — Failed API request (with method, URI, and message).

---

## Token caching

The OAuth2 Bearer token is cached in Drupal's default cache bin under the key `daxko.access_token`. The cache expiry is set to `time() + expires_in` from the token response, so no manual cache clearing is needed on token expiry.

To force a token refresh:

```bash
drush cr
# or selectively:
drush php-eval "\Drupal::cache()->delete('daxko.access_token');"
```

---

## Maintainers

Developed and maintained by [Five Jars](https://fivejars.com).
