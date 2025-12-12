# Agent Instructions

## Code Style

### Avoid Unnecessary Variables

Do not introduce unnecessary intermediate variables when accessing object properties. Access properties directly using chain notation.

**Bad:**
```php
$sessionConfig = $serverConfig->session;
$type = $sessionConfig->type;
$ttl = $sessionConfig->ttl;
```

**Good:**
```php
$type = $serverConfig->session->type;
$ttl = $serverConfig->session->ttl;
```

Only create intermediate variables when:
1. The same property chain is accessed many times (3+) and readability improves
2. The intermediate value needs to be modified
3. The property access has side effects that should only happen once
