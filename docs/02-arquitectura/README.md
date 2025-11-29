# 🏗️ Arquitectura del Sistema - Ferretería Frenad

Documentación completa de la arquitectura del sistema POS Ferretería Frenad.

---

## 📋 Contenido

### 1. **01-vision-general.md**
Visión de alto nivel del sistema:
- ✅ Contexto del negocio
- ✅ Objetivos del sistema
- ✅ Usuarios del sistema
- ✅ Alcance funcional
- ✅ Restricciones y limitaciones

**Cuándo consultar:** Para entender el propósito global del sistema.

---

### 2. **02-arquitectura-general.md**
Arquitectura técnica completa:
- ✅ Arquitectura de 3 capas (Frontend, Backend, Database)
- ✅ Diagrama de componentes
- ✅ Flujo de datos
- ✅ Tecnologías utilizadas
- ✅ Patrones arquitectónicos

**Cuándo consultar:** Para entender cómo está construido el sistema.

---

### 3. **03-componentes.md**
Detalle de cada componente:
- ✅ Frontend (Nginx + HTML/CSS/JS)
- ✅ Backend (Laravel + PHP)
- ✅ Base de Datos (PostgreSQL)
- ✅ Docker (Containerización)
- ✅ Responsabilidades de cada capa

**Cuándo consultar:** Para trabajar en un componente específico.

---

### 4. **04-integracion.md**
Integración entre componentes:
- ✅ API REST (Frontend ↔ Backend)
- ✅ ORM Eloquent (Backend ↔ Database)
- ✅ Docker Compose (Orquestación)
- ✅ Autenticación JWT
- ✅ Manejo de errores

**Cuándo consultar:** Para integrar componentes o debuggear comunicación.

---

### 5. **05-seguridad.md**
Consideraciones de seguridad:
- ✅ Autenticación y autorización
- ✅ Encriptación de contraseñas
- ✅ Tokens JWT
- ✅ Validación de datos
- ✅ SQL Injection prevention
- ✅ XSS prevention

**Cuándo consultar:** Para implementar seguridad o auditar vulnerabilidades.

---

### 6. **06-escalabilidad.md**
Diseño para crecimiento:
- ✅ Índices de base de datos
- ✅ Cache strategies
- ✅ Load balancing
- ✅ Horizontal scaling
- ✅ Performance optimization

**Cuándo consultar:** Para optimizar performance o escalar el sistema.

---

## 🎯 Arquitectura en Una Imagen

```
┌─────────────────────────────────────────────────────────────┐
│                        USUARIOS                              │
│         (Cajeros, Administradores, Almaceneros)             │
└─────────────────────────────────────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    FRONTEND (Puerto 8080)                    │
│  ┌──────────────────────────────────────────────────────┐  │
│  │         Nginx Alpine (Web Server)                     │  │
│  │  • HTML5 + CSS3 + JavaScript (Vanilla)               │  │
│  │  • Bootstrap 5 (UI Framework)                        │  │
│  │  • Fetch API (HTTP Client)                           │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              ▼
                         HTTP/JSON
                         (API REST)
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    BACKEND (Puerto 8000)                     │
│  ┌──────────────────────────────────────────────────────┐  │
│  │         Laravel 10 + PHP 8.2 FPM                      │  │
│  │  • Controllers (Lógica de negocio)                   │  │
│  │  • Models (Eloquent ORM)                             │  │
│  │  • Middleware (Auth JWT, CORS)                       │  │
│  │  • Validation (Form Requests)                        │  │
│  │  • Services (Business Logic)                         │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              ▼
                         PDO/Eloquent
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                  BASE DE DATOS (Puerto 5432)                 │
│  ┌──────────────────────────────────────────────────────┐  │
│  │         PostgreSQL 15 Alpine                          │  │
│  │  • 25 Tablas (Normalización 3FN)                     │  │
│  │  • 43+ Índices (Performance)                         │  │
│  │  • 12 Triggers (Automatización)                      │  │
│  │  • 8 Vistas (Consultas complejas)                   │  │
│  │  • 11+ Funciones (Lógica de BD)                     │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    ALMACENAMIENTO                            │
│  • Docker Volumes (Persistencia de datos)                   │
│  • Backups automáticos (pg_dump)                            │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔄 Flujo de una Petición Típica

**Ejemplo: Usuario hace una venta**

```
1. USUARIO
   └─> Ingresa datos de venta en interfaz web

2. FRONTEND
   └─> Valida datos en cliente
   └─> POST /api/ventas (JSON)

3. BACKEND
   └─> Middleware: Verifica token JWT
   └─> Controller: VentaController@store
   └─> Validation: VentaRequest valida datos
   └─> Service: VentaService procesa lógica
   └─> Model: Venta, DetalleVenta (Eloquent)
   └─> Database: INSERT en tablas

4. BASE DE DATOS
   └─> Trigger: actualiza inventario automáticamente
   └─> Trigger: registra en movimientos_inventario
   └─> Trigger: actualiza timestamps
   └─> Function: calcula totales
   └─> COMMIT transaction

5. BACKEND
   └─> Response: JSON con venta creada

6. FRONTEND
   └─> Muestra confirmación
   └─> Actualiza UI
   └─> Imprime ticket (opcional)

Tiempo total: ~200-500ms
```

---

## 🛠️ Stack Tecnológico Completo

### Frontend
| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| **Nginx** | Alpine | Web server, proxy reverso |
| **HTML5** | - | Estructura de páginas |
| **CSS3** | - | Estilos y diseño |
| **JavaScript** | ES6+ | Interactividad, API calls |
| **Bootstrap** | 5.3 | Framework UI responsivo |

### Backend
| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| **PHP** | 8.2 | Lenguaje de programación |
| **Laravel** | 10.x | Framework MVC |
| **Composer** | 2.x | Gestor de dependencias |
| **JWT Auth** | - | Autenticación stateless |
| **Eloquent ORM** | - | Mapeo objeto-relacional |

### Base de Datos
| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| **PostgreSQL** | 15 | Base de datos relacional |
| **pg_dump** | - | Backups |
| **pgAdmin** | 4 | Administración (dev) |

### DevOps
| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| **Docker** | 20.10+ | Containerización |
| **Docker Compose** | 2.0+ | Orquestación |
| **Git** | 2.x | Control de versiones |

---

## 📦 Módulos del Sistema

### 1. **Autenticación** (`auth`)
- Login/Logout
- Gestión de roles
- Permisos

### 2. **Productos** (`productos`)
- Catálogo de productos
- Categorías y marcas
- Unidades de medida

### 3. **Inventario** (`inventario`)
- Multi-almacén
- Kardex automatizado
- Stock mínimo

### 4. **Compras** (`compras`)
- Órdenes de compra
- Recepción de mercadería
- Proveedores

### 5. **Ventas** (`ventas`)
- POS (Punto de venta)
- Ventas al contado
- Ventas a crédito

### 6. **Clientes** (`clientes`)
- CRM básico
- Límites de crédito
- Historial de compras

### 7. **Caja** (`caja`)
- Arqueo de caja
- Movimientos (ingresos/egresos)
- Cuadre diario

### 8. **Auditoría** (`auditoria`)
- Logs de operaciones
- Trazabilidad
- Reportes

---

## 🔗 Documentación Relacionada

### Desarrollo
- **[docs/01-git/](../01-git/)** - Workflow Git + BDD/TDD
- **[docs/05-testing/](../05-testing/)** - Metodología de testing
- **[docs/06-features/](../06-features/)** - Especificaciones BDD

### Base de Datos
- **[docs/04-database/](../04-database/)** - Documentación completa de BD
- **[docs/04-database/01-arquitectura.md](../04-database/01-arquitectura.md)** - Arquitectura de BD

### API
- **[docs/03-api/](../03-api/)** - Documentación de API REST (próximamente)

### Deployment
- **[docs/05-deployment/](../05-deployment/)** - Guías de despliegue

---

## 🎯 Principios Arquitectónicos

### 1. **Separation of Concerns**
- Frontend maneja presentación
- Backend maneja lógica de negocio
- Database maneja persistencia

### 2. **Single Responsibility**
- Cada componente tiene una responsabilidad clara
- Módulos independientes
- Bajo acoplamiento

### 3. **DRY (Don't Repeat Yourself)**
- Funciones de BD reutilizables
- Services para lógica compartida
- Componentes de UI reutilizables

### 4. **SOLID**
- Clases con responsabilidad única
- Abierto para extensión, cerrado para modificación
- Inyección de dependencias

### 5. **Clean Code**
- Nombres descriptivos
- Funciones pequeñas
- Comentarios solo cuando es necesario
- Tests automatizados

---

## 📊 Métricas del Sistema

### Base de Datos
- **Tablas:** 25
- **Índices:** 43+
- **Triggers:** 12
- **Vistas:** 8
- **Funciones:** 11+
- **Normalización:** 3FN

### Código (Estimado)
- **Modelos:** ~25 (uno por tabla)
- **Controllers:** ~15 (uno por módulo)
- **Requests:** ~30 (validaciones)
- **Services:** ~10 (lógica compleja)
- **Tests:** (a implementar)

### Documentación
- **Archivos:** 40+
- **Líneas:** ~30,000+
- **Cobertura:** 100% base de datos, 80% sistema

---

## 🚀 Próximos Pasos

1. **Leer visión general** → `01-vision-general.md`
2. **Entender arquitectura** → `02-arquitectura-general.md`
3. **Revisar componentes** → `03-componentes.md`
4. **Ver integración** → `04-integracion.md`
5. **Implementar seguridad** → `05-seguridad.md`
6. **Optimizar** → `06-escalabilidad.md`

---

**Última actualización:** 29 de noviembre de 2025  
**Versión:** 1.0.0
