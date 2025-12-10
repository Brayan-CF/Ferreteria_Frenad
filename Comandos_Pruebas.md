# Comandos de Pruebas - Sistema Ferretería Frenat

> **Framework**: Laravel 10.50.0 + PHPUnit 10.5.58  
> **Contenedor**: `ferreteria_backend`  
> **Total de pruebas**: 192 (94 Feature + 98 Unit)

---

## 1. Ejecutar TODAS las Pruebas

```bash
# Ejecutar todas las pruebas (Feature + Unit)
docker exec ferreteria_backend php artisan test
```

**Resultado esperado:**
```
Tests: 191 passed, 1 skipped (765 assertions)
Duration: ~13 seconds
```

---

## 2. Pruebas Feature (Caja Negra) - 94 Tests

### Ejecutar todas las pruebas Feature:
```bash
docker exec ferreteria_backend php artisan test tests/Feature
```

### Por módulo individual:

```bash
# Autenticación (12 tests)
docker exec ferreteria_backend php artisan test tests/Feature/Auth

# Clientes (15 tests)
docker exec ferreteria_backend php artisan test tests/Feature/Customer

# Inventario (12 tests)
docker exec ferreteria_backend php artisan test tests/Feature/Inventory

# Productos (13 tests)
docker exec ferreteria_backend php artisan test tests/Feature/Product

# Compras (12 tests)
docker exec ferreteria_backend php artisan test tests/Feature/Purchase

# Ventas (17 tests)
docker exec ferreteria_backend php artisan test tests/Feature/Sales

# Seguridad OWASP (11 tests)
docker exec ferreteria_backend php artisan test tests/Feature/Security
```

---

## 3. Pruebas Unit (Caja Blanca) - 98 Tests

### Ejecutar todas las pruebas Unit de Servicios:
```bash
docker exec ferreteria_backend php artisan test tests/Unit/Services
```

### Por servicio individual:

```bash
# AuthService (15 tests)
docker exec ferreteria_backend php artisan test tests/Unit/Services/AuthServiceTest.php

# ClienteService (19 tests)
docker exec ferreteria_backend php artisan test tests/Unit/Services/ClienteServiceTest.php

# CompraService (14 tests)
docker exec ferreteria_backend php artisan test tests/Unit/Services/CompraServiceTest.php

# InventarioService (16 tests)
docker exec ferreteria_backend php artisan test tests/Unit/Services/InventarioServiceTest.php

# ProductoService (16 tests)
docker exec ferreteria_backend php artisan test tests/Unit/Services/ProductoServiceTest.php

# VentaService (18 tests)
docker exec ferreteria_backend php artisan test tests/Unit/Services/VentaServiceTest.php
```

---

## 4. Ejecutar Prueba Específica por Nombre

```bash
# Filtrar por nombre del método de prueba
docker exec ferreteria_backend php artisan test --filter="AUTH001"

# Filtrar por nombre parcial
docker exec ferreteria_backend php artisan test --filter="login"

# Filtrar múltiples patrones
docker exec ferreteria_backend php artisan test --filter="AUTH001|AUTH002|AUTH003"
```

---

## 5. Generar Reportes

### Reporte JUnit (XML):
```bash
docker exec ferreteria_backend php artisan test --log-junit /tmp/test-results.xml

# Ver el reporte
docker exec ferreteria_backend cat /tmp/test-results.xml
```

### Copiar reporte al host:
```bash
docker cp ferreteria_backend:/tmp/test-results.xml ./test-results.xml
```

---

## 6. Opciones Útiles

```bash
# Detener al primer error
docker exec ferreteria_backend php artisan test --stop-on-failure

# Mostrar pruebas lentas
docker exec ferreteria_backend php artisan test --profile

# Ejecutar en paralelo (más rápido)
docker exec ferreteria_backend php artisan test --parallel

# Modo silencioso (solo errores)
docker exec ferreteria_backend php artisan test --quiet
```

---

## 7. Estructura de Archivos de Prueba

```
backend/tests/
├── Feature/                          # Pruebas de Caja Negra (94)
│   ├── Auth/
│   │   └── AuthenticationTest.php    # 12 tests
│   ├── Customer/
│   │   └── ClienteTest.php           # 15 tests
│   ├── Inventory/
│   │   └── InventarioTest.php        # 12 tests
│   ├── Product/
│   │   └── ProductoTest.php          # 13 tests
│   ├── Purchase/
│   │   └── CompraTest.php            # 12 tests
│   ├── Sales/
│   │   └── VentaTest.php             # 17 tests
│   └── Security/
│       └── OWASPSecurityTest.php     # 11 tests
│
└── Unit/                             # Pruebas de Caja Blanca (98)
    ├── ExampleTest.php               # 1 test
    └── Services/
        ├── AuthServiceTest.php       # 15 tests
        ├── ClienteServiceTest.php    # 19 tests
        ├── CompraServiceTest.php     # 14 tests
        ├── InventarioServiceTest.php # 16 tests
        ├── ProductoServiceTest.php   # 16 tests
        └── VentaServiceTest.php      # 18 tests
```

---

## 8. Ejemplos Rápidos

```bash
# ✅ Ejecutar TODO
docker exec ferreteria_backend php artisan test

# ✅ Solo Feature (endpoints)
docker exec ferreteria_backend php artisan test tests/Feature

# ✅ Solo Unit (servicios)
docker exec ferreteria_backend php artisan test tests/Unit/Services

# ✅ Solo pruebas de ventas
docker exec ferreteria_backend php artisan test tests/Feature/Sales tests/Unit/Services/VentaServiceTest.php

# ✅ Solo pruebas de autenticación
docker exec ferreteria_backend php artisan test tests/Feature/Auth tests/Unit/Services/AuthServiceTest.php

# ✅ Solo seguridad
docker exec ferreteria_backend php artisan test tests/Feature/Security
```

---

## 9. Solución de Problemas

### Si el contenedor no está corriendo:
```bash
# Verificar estado
docker ps | grep ferreteria

# Iniciar contenedores
docker-compose up -d

# O usar el script
./start-docker.sh
```

### Si hay errores de base de datos:
```bash
# Ejecutar migraciones
docker exec ferreteria_backend php artisan migrate

# Refrescar base de datos (CUIDADO: borra datos)
docker exec ferreteria_backend php artisan migrate:fresh --seed
```

### Limpiar caché antes de pruebas:
```bash
docker exec ferreteria_backend php artisan config:clear
docker exec ferreteria_backend php artisan cache:clear
```

---

## 10. Resumen de Códigos de Prueba

| Prefijo | Módulo | Tipo |
|---------|--------|------|
| AUTH | Autenticación | Feature/Unit |
| CLI | Clientes | Feature/Unit |
| COM | Compras | Feature/Unit |
| INV | Inventario | Feature/Unit |
| PROD | Productos | Feature/Unit |
| VTA | Ventas | Feature/Unit |
| SEC | Seguridad | Feature |
| UNIT_ | Servicios | Unit |

---

**Última actualización**: 10 de diciembre de 2025
