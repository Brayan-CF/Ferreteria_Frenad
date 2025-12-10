# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer Bearer {YOUR_AUTH_TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Obtenga su token en <code>POST /api/auth/auth/login</code> con email y password. El token debe enviarse en el header Authorization como: <code>Bearer {token}</code>
