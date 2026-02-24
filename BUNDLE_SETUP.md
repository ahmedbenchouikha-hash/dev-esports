# BUNDLE SETUP GUIDE

## 1. Install API Platform Bundle

```bash
composer require api-platform/core
```

### Configure API Platform (config/packages/api_platform.yaml)

```yaml
api_platform:
  title: Dev Esports API
  description: esports platform API
  version: 1.0.0

  # Automatically expose all Doctrine ORM entities as API resources
  mapping:
    paths:
      - "%kernel.project_dir%/src/Entity"

  # Enable Swagger UI
  swagger:
    swagger_ui:
      enabled: true
    api_docs:
      enabled: true

  # Pagination
  pagination:
    items_per_page: 30
    items_per_page_parameter_name: itemsPerPage
    maximum_items_per_page: 100
```

### Make Entities API Resources

Edit `src/Entity/Game.php`:

```php
<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Delete(),
    ]
)]
class Game
{
    // ... existing entity code ...
}
```

Do the same for `MatchStatistic` and `Ticket` entities.

### Access API Docs

```
http://localhost:8000/api/docs
```

---

## 2. Install Doctrine Extensions Bundle

```bash
composer require stof/doctrine-extensions-bundle
```

### Configure (config/packages/stof_doctrine_extensions.yaml)

```yaml
stof_doctrine_extensions:
  default_locale: en_US
  translation_fallback: true
  orm:
    default:
      sluggable: true
      timestampable: true
      blameable: true
```

### Add Timestamps to Entities

Edit `src/Entity/Game.php`:

```php
<?php

namespace App\Entity;

use Gedmo\Mapping\Annotation as Gedmo;

class Game
{
    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTime $createdAt = null;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'update')]
    private ?DateTime $updatedAt = null;

    // ... rest of entity ...

    public function getCreatedAt(): ?DateTime { return $this->createdAt; }
    public function getUpdatedAt(): ?DateTime { return $this->updatedAt; }
}
```

---

## 3. Install API Documentation Bundle

```bash
composer require nelmio/api-doc-bundle
```

### Configure (config/packages/nelmio_api_doc.yaml)

```yaml
nelmio_api_doc:
  documentation:
    info:
      title: Dev Esports API
      description: Complete esports management API
      version: 1.0.0
    servers:
      - url: http://localhost:8000
        description: Development server
  areas:
    path_patterns:
      - ^/api
```

### Access Documentation

```
http://localhost:8000/api/doc
```

---

## 4. Optional: JWT Authentication Bundle

Only install if you need API authentication:

```bash
composer require lexik/jwt-authentication-bundle
```

### Generate JWT Keys

```bash
php bin/console lexik:jwt:generate-keypair
```

This creates:

- `config/jwt/private.pem` (private key)
- `config/jwt/public.pem` (public key)

### Configure (config/packages/lexik_jwt_authentication.yaml)

```yaml
lexik_jwt_authentication:
  secret_key: "%env(resolve:JWT_SECRET_KEY)%"
  public_key: "%env(resolve:JWT_PUBLIC_KEY)%"
  pass_phrase: "%env(JWT_PASSPHRASE)%"
  token_ttl: 3600
```

---

## SUMMARY

After following these steps, you'll have:

✅ Auto-generated REST APIs for all entities
✅ Swagger/OpenAPI documentation
✅ Automatic timestamping for records
✅ Beautiful API explorer at `/api/docs`
✅ (Optional) JWT authentication for API security

**Next Steps:** See API_SETUP.md for integrating external APIs
