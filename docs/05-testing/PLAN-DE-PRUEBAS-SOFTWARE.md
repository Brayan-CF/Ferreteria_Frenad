# Plan de Pruebas de Software - Ferretería FRENAD

## 📋 Información del Documento

| Campo | Valor |
|-------|-------|
| **Proyecto** | Sistema de Gestión Ferretería FRENAD |
| **Versión** | 1.0 |
| **Fecha** | 2025-01-22 |
| **Autor** | Equipo de Desarrollo |
| **Estado** | ✅ COMPLETADO |

---

## 1. Introducción

### 1.1 Propósito
Este documento define el plan de pruebas de software para el sistema de gestión de la Ferretería FRENAD. El objetivo es garantizar la calidad, funcionalidad y seguridad del backend mediante pruebas unitarias, de integración y de seguridad.

### 1.2 Alcance
Las pruebas cubrirán:
- **Backend API REST** desarrollado en Laravel 10
- **Módulos**: Auth, Product, Customer, Sales, Inventory, Purchase, Reports
- **Base de datos**: PostgreSQL 15
- **Autenticación**: JWT (JSON Web Tokens)

### 1.3 Objetivos de las Pruebas
1. ✅ Verificar que todas las funcionalidades del backend operan correctamente
2. ✅ Asegurar que los endpoints API responden según las especificaciones
3. ✅ Validar la integridad de datos en operaciones CRUD
4. ✅ Detectar vulnerabilidades de seguridad según OWASP Top 10
5. ✅ Documentar los resultados para futuras referencias

### 1.4 Resultados Finales

| Métrica | Valor |
|---------|-------|
| **Total de Pruebas** | 94 |
| **Pruebas Exitosas** | 94 ✅ |
| **Pruebas Fallidas** | 0 ❌ |
| **Aserciones Totales** | 403 |
| **Cobertura de Módulos** | 100% |

---

## 2. Estrategia de Pruebas

### 2.1 Niveles de Prueba

| Nivel | Descripción | Framework | Cobertura |
|-------|-------------|-----------|-----------|
| **Unitarias** | Pruebas de servicios y modelos aislados | PHPUnit | Servicios y lógica de negocio |
| **Integración** | Pruebas de API endpoints completos | PHPUnit + Laravel | Endpoints REST |
| **Seguridad** | Pruebas OWASP | PHPUnit | Vulnerabilidades comunes |

### 2.2 Tipos de Prueba

```
┌─────────────────────────────────────────────────────────────────┐
│                    PIRÁMIDE DE PRUEBAS                         │
├─────────────────────────────────────────────────────────────────┤
│                        ▲                                        │
│                       /E2E\           (Manual/Browser)          │
│                      /─────\                                    │
│                     /       \                                   │
│                    / SEGURIDAD\        OWASP Tests ✅          │
│                   /────────────\                               │
│                  /              \                               │
│                 /  INTEGRACIÓN   \     API Feature Tests ✅    │
│                /──────────────────\                            │
│               /                    \                            │
│              /    PRUEBAS UNIT     \   Service Unit Tests ✅   │
│             /────────────────────────\                         │
│            /                          \                         │
└─────────────────────────────────────────────────────────────────┘
```

### 2.3 Entorno de Pruebas

```yaml
Sistema Operativo: Linux (Docker)
PHP Version: 8.2.29
Framework: Laravel 10.50.0
Base de Datos: PostgreSQL 15.8
Test Runner: PHPUnit 10.5.58
Autenticación: JWT (tymon/jwt-auth)
```

---

## 3. Casos de Prueba por Módulo

### 3.1 Módulo Auth (12 pruebas ✅)

| ID | Caso de Prueba | Tipo | Prioridad | Estado |
|----|----------------|------|-----------|--------|
| AUTH-001 | Login con credenciales válidas | Feature | Alta | ✅ Pasó |
| AUTH-002 | Login con credenciales inválidas | Feature | Alta | ✅ Pasó |
| AUTH-002b | Login con email inexistente | Feature | Alta | ✅ Pasó |
| AUTH-002c | Login con campos vacíos | Feature | Alta | ✅ Pasó |
| AUTH-002d | Login con formato email inválido | Feature | Media | ✅ Pasó |
| AUTH-004 | Logout exitoso | Feature | Alta | ✅ Pasó |
| AUTH-005 | Obtener perfil autenticado | Feature | Media | ✅ Pasó |
| AUTH-006 | Acceso sin token (401) | Feature | Alta | ✅ Pasó |
| AUTH-006b | Múltiples endpoints requieren auth | Feature | Alta | ✅ Pasó |
| AUTH-007 | Token inválido rechazado | Feature | Alta | ✅ Pasó |
| AUTH-007b | Token malformado rechazado | Feature | Alta | ✅ Pasó |
| AUTH-Extra | Token funciona múltiples requests | Feature | Media | ✅ Pasó |

### 3.2 Módulo Product (13 pruebas ✅)

| ID | Caso de Prueba | Tipo | Prioridad | Estado |
|----|----------------|------|-----------|--------|
| PROD-001 | Listar productos paginados | Feature | Alta | ✅ Pasó |
| PROD-002 | Buscar productos por nombre | Feature | Media | ✅ Pasó |
| PROD-003 | Filtrar por categoría | Feature | Media | ✅ Pasó |
| PROD-003b | Filtrar productos activos | Feature | Media | ✅ Pasó |
| PROD-004 | Obtener producto individual | Feature | Alta | ✅ Pasó |
| PROD-004b | Retorna 404 producto inexistente | Feature | Alta | ✅ Pasó |
| PROD-005 | Crear producto válido | Feature | Alta | ✅ Pasó |
| PROD-005b | Crear producto sin campos requeridos | Feature | Alta | ✅ Pasó |
| PROD-005c | Validar lógica de precios | Feature | Media | ✅ Pasó |
| PROD-006 | Actualizar producto | Feature | Media | ✅ Pasó |
| PROD-007 | Desactivar producto | Feature | Media | ✅ Pasó |
| PROD-Auth | Requiere autenticación | Feature | Alta | ✅ Pasó |
| PROD-Stats | Estadísticas de productos | Feature | Baja | ✅ Pasó |

### 3.3 Módulo Customer (15 pruebas ✅)

| ID | Caso de Prueba | Tipo | Prioridad | Estado |
|----|----------------|------|-----------|--------|
| CLI-001 | Listar clientes paginados | Feature | Alta | ✅ Pasó |
| CLI-002 | Buscar cliente por nombre | Feature | Media | ✅ Pasó |
| CLI-002b | Filtrar clientes frecuentes | Feature | Media | ✅ Pasó |
| CLI-002c | Filtrar clientes activos | Feature | Media | ✅ Pasó |
| CLI-003 | Crear cliente válido | Feature | Alta | ✅ Pasó |
| CLI-003b | No crear cliente sin nombre | Feature | Alta | ✅ Pasó |
| CLI-004 | Obtener cliente individual | Feature | Media | ✅ Pasó |
| CLI-004b | Retorna 404 cliente inexistente | Feature | Media | ✅ Pasó |
| CLI-005 | Actualizar cliente | Feature | Media | ✅ Pasó |
| CLI-005b | Actualizar límite de crédito | Feature | Media | ✅ Pasó |
| CLI-006 | Desactivar cliente sin deuda | Feature | Media | ✅ Pasó |
| CLI-Auth | Requiere autenticación | Feature | Alta | ✅ Pasó |
| CLI-History | Historial de compras | Feature | Baja | ✅ Pasó |
| CLI-Credits | Créditos del cliente | Feature | Media | ✅ Pasó |
| CLI-Email | Validación formato email | Feature | Media | ✅ Pasó |

### 3.4 Módulo Sales (17 pruebas ✅)

| ID | Caso de Prueba | Tipo | Prioridad | Estado |
|----|----------------|------|-----------|--------|
| VTA-001 | Listar ventas paginadas | Feature | Alta | ✅ Pasó |
| VTA-001b | Filtrar ventas por estado | Feature | Media | ✅ Pasó |
| VTA-001c | Filtrar ventas por tipo | Feature | Media | ✅ Pasó |
| VTA-001d | Filtrar ventas por método de pago | Feature | Media | ✅ Pasó |
| VTA-002 | Crear venta contado | Feature | Alta | ✅ Pasó |
| VTA-003 | Crear venta crédito | Feature | Alta | ✅ Pasó |
| VTA-005 | No permitir venta sin items | Feature | Alta | ✅ Pasó |
| VTA-005b | Validar campos requeridos | Feature | Alta | ✅ Pasó |
| VTA-006 | Obtener detalle de venta | Feature | Media | ✅ Pasó |
| VTA-006b | Retorna 404 venta inexistente | Feature | Media | ✅ Pasó |
| VTA-007 | Ventas del día | Feature | Media | ✅ Pasó |
| VTA-008 | Top productos vendidos | Feature | Baja | ✅ Pasó |
| VTA-008b | Ventas por método de pago | Feature | Baja | ✅ Pasó |
| VTA-Auth | Requiere autenticación | Feature | Alta | ✅ Pasó |
| VTA-Auth2 | Crear venta requiere auth | Feature | Alta | ✅ Pasó |
| VTA-Unique | Número de venta único | Feature | Alta | ✅ Pasó |
| VTA-Calc | Calcula totales correctamente | Feature | Alta | ✅ Pasó |

### 3.5 Módulo Inventory (12 pruebas ✅)

| ID | Caso de Prueba | Tipo | Prioridad | Estado |
|----|----------------|------|-----------|--------|
| INV-001 | Listar inventario paginado | Feature | Alta | ✅ Pasó |
| INV-002 | Filtrar por almacén | Feature | Media | ✅ Pasó |
| INV-003 | Productos stock bajo | Feature | Alta | ✅ Pasó |
| INV-004 | Movimientos de inventario | Feature | Media | ✅ Pasó |
| INV-005 | Listar almacenes | Feature | Media | ✅ Pasó |
| INV-005b | Crear almacén | Feature | Media | ✅ Pasó |
| INV-005c | Obtener almacén individual | Feature | Media | ✅ Pasó |
| INV-005d | Actualizar almacén | Feature | Media | ✅ Pasó |
| INV-005e | Listar todos los almacenes | Feature | Baja | ✅ Pasó |
| INV-Auth | Requiere autenticación | Feature | Alta | ✅ Pasó |
| INV-Auth2 | Almacenes requiere auth | Feature | Alta | ✅ Pasó |
| INV-Valid | Validar campos almacén | Feature | Media | ✅ Pasó |

### 3.6 Módulo Purchase (12 pruebas ✅)

| ID | Caso de Prueba | Tipo | Prioridad | Estado |
|----|----------------|------|-----------|--------|
| COM-001 | Listar compras paginadas | Feature | Alta | ✅ Pasó |
| COM-001b | Filtrar compras por estado | Feature | Media | ✅ Pasó |
| COM-002 | Obtener detalle de compra | Feature | Alta | ✅ Pasó |
| COM-002b | Retorna 404 compra inexistente | Feature | Media | ✅ Pasó |
| COM-004 | Listar proveedores | Feature | Media | ✅ Pasó |
| COM-004b | Crear proveedor | Feature | Media | ✅ Pasó |
| COM-004c | Obtener proveedor individual | Feature | Media | ✅ Pasó |
| COM-004d | Actualizar proveedor | Feature | Media | ✅ Pasó |
| COM-004e | Buscar proveedores | Feature | Baja | ✅ Pasó |
| COM-Auth | Requiere autenticación | Feature | Alta | ✅ Pasó |
| COM-Auth2 | Proveedores requiere auth | Feature | Alta | ✅ Pasó |
| COM-Valid | Validar campos proveedor | Feature | Media | ✅ Pasó |

### 3.7 Pruebas de Seguridad OWASP (11 pruebas ✅)

| ID | OWASP | Caso de Prueba | Tipo | Estado |
|----|-------|----------------|------|--------|
| SEC-001 | A01:2021 | Endpoints protegidos requieren auth | Security | ✅ Pasó |
| SEC-001b | A01:2021 | Endpoints modificación requieren auth | Security | ✅ Pasó |
| SEC-002 | A03:2021 | SQL Injection en búsquedas | Security | ✅ Pasó |
| SEC-002b | A03:2021 | SQL Injection en login | Security | ✅ Pasó |
| SEC-003 | A03:2021 | XSS documentación manejo | Security | ✅ Pasó |
| SEC-004 | A07:2021 | Tokens inválidos rechazados | Security | ✅ Pasó |
| SEC-004b | A07:2021 | Protección fuerza bruta | Security | ✅ Pasó |
| SEC-005 | A05:2021 | Headers de seguridad | Security | ✅ Pasó |
| SEC-005b | A05:2021 | Exposición información en errores | Security | ✅ Pasó |
| SEC-006 | A08:2021 | Validación de entrada | Security | ✅ Pasó |
| SEC-006b | A08:2021 | Protección mass assignment | Security | ✅ Pasó |

---

## 4. Criterios de Aceptación

### 4.1 Criterios de Entrada
- ✅ Código fuente completo y compilable
- ✅ Base de datos configurada con datos de prueba
- ✅ Entorno Docker funcional
- ✅ Documentación de API disponible

### 4.2 Criterios de Salida
- ⏳ 100% de pruebas críticas ejecutadas
- ⏳ 0 defectos de prioridad alta sin resolver
- ⏳ Cobertura mínima de 70% en módulos críticos
- ⏳ Documentación de resultados completada

### 4.3 Criterios de Éxito/Fallo

| Métrica | Éxito | Fallo |
|---------|-------|-------|
| Pruebas pasadas | ≥ 95% | < 80% |
| Vulnerabilidades críticas | 0 | ≥ 1 |
| Tiempo de respuesta API | < 500ms | > 2000ms |

---

## 5. Estructura de Archivos de Prueba

```
backend/tests/
├── TestCase.php                    # Clase base con helpers
├── CreatesApplication.php          # Trait Laravel
│
├── Feature/                        # Pruebas de integración
│   ├── Auth/
│   │   └── AuthenticationTest.php  # AUTH-001 a AUTH-007
│   ├── Product/
│   │   ├── ProductoTest.php        # PROD-001 a PROD-007
│   │   └── CategoriaTest.php       # CAT-001
│   ├── Customer/
│   │   └── ClienteTest.php         # CLI-001 a CLI-007
│   ├── Sales/
│   │   └── VentaTest.php           # VTA-001 a VTA-008
│   ├── Inventory/
│   │   └── InventarioTest.php      # INV-001 a INV-005
│   ├── Purchase/
│   │   └── CompraTest.php          # COM-001 a COM-005
│   └── Security/
│       └── OWASPSecurityTest.php   # SEC-001 a SEC-006
│
└── Unit/                           # Pruebas unitarias
    └── Services/
        ├── VentaServiceTest.php
        └── InventarioServiceTest.php
```

---

## 6. Herramientas y Comandos

### 6.1 Ejecución de Pruebas

```bash
# Ejecutar todas las pruebas
php artisan test

# Ejecutar con cobertura
php artisan test --coverage

# Ejecutar suite específica
php artisan test --testsuite=Feature

# Ejecutar archivo específico
php artisan test tests/Feature/Auth/AuthenticationTest.php

# Ejecutar método específico
php artisan test --filter=test_user_can_login
```

### 6.2 Reportes

```bash
# Generar reporte HTML de cobertura
XDEBUG_MODE=coverage php artisan test --coverage-html=coverage-report

# Reporte XML para CI/CD
php artisan test --coverage-clover=coverage.xml
```

---

## 7. Cronograma de Ejecución

| Fase | Actividad | Duración Estimada |
|------|-----------|-------------------|
| 1 | Configuración de entorno | 30 min |
| 2 | Pruebas Auth Module | 1 hora |
| 3 | Pruebas Product Module | 1 hora |
| 4 | Pruebas Customer Module | 45 min |
| 5 | Pruebas Sales Module | 1.5 horas |
| 6 | Pruebas Inventory Module | 45 min |
| 7 | Pruebas Purchase Module | 1 hora |
| 8 | Pruebas Seguridad OWASP | 1 hora |
| 9 | Documentación de resultados | 30 min |

---

## 8. Riesgos y Mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| BD de prueba con datos inconsistentes | Media | Alto | Usar RefreshDatabase trait |
| Dependencias entre módulos | Alta | Medio | Ejecutar pruebas en orden |
| Tiempo insuficiente | Media | Alto | Priorizar pruebas críticas |
| Falsos positivos en seguridad | Baja | Bajo | Verificar manualmente |

---

## 9. Aprobaciones

| Rol | Nombre | Fecha | Firma |
|-----|--------|-------|-------|
| Desarrollador | Equipo FRENAD | 2025-01-22 | ✅ |
| QA Lead | - | - | ⏳ |
| Project Manager | - | - | ⏳ |

---

## 10. Historial de Cambios

| Versión | Fecha | Descripción | Autor |
|---------|-------|-------------|-------|
| 1.0 | 2025-01-22 | Creación inicial del documento | Equipo Dev |

