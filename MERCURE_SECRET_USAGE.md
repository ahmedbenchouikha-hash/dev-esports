# Mercure secret usage documentation

- The Mercure JWT secret is now set in .env.local as MERCURE_JWT_SECRET.
- config/packages/mercure.yaml uses this secret for JWT signing:
  jwt:
    secret: '%env(MERCURE_JWT_SECRET)%'
- No hardcoded JWT values found in src/ or config/.
- APP_SECRET remains unchanged for Symfony core.

To update the JWT for subscriptions, use the secret from .env.local.
