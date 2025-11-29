# 🎯 Visión General del Sistema - Ferretería Frenad

## 📖 Contexto del Negocio

### ¿Qué es Ferretería Frenad?
Ferretería Frenad es un comercio minorista ubicado en El Alto, La Paz - Bolivia, especializado en la venta de materiales de construcción, herramientas, y productos de ferretería general.

### Problema a Resolver
La ferretería actualmente opera con procesos manuales o sistemas básicos (Excel, cuadernos), lo que genera:
- ❌ Errores en inventario
- ❌ Pérdida de ventas por falta de stock
- ❌ Control deficiente de créditos a clientes
- ❌ Dificultad para generar reportes
- ❌ Arqueos de caja lentos y propensos a errores
- ❌ No hay trazabilidad de operaciones

### Solución Propuesta
Sistema POS (Point of Sale) completo e integrado que digitaliza y automatiza:
- ✅ Ventas en mostrador (POS)
- ✅ Control de inventario multi-almacén
- ✅ Gestión de compras a proveedores
- ✅ CRM de clientes con créditos
- ✅ Arqueo de caja diario
- ✅ Auditoría completa de operaciones

---

## 🎯 Objetivos del Sistema

### Objetivo General
Desarrollar un sistema POS integral que permita a Ferretería Frenad gestionar de manera eficiente sus operaciones de venta, inventario, compras y administración.

### Objetivos Específicos

#### 1. **Automatizar Ventas** 🛒
- Registro rápido de ventas (POS)
- Cálculo automático de totales
- Soporte para ventas al contado y crédito
- Generación de comprobantes
- **Tiempo objetivo:** < 2 minutos por venta

#### 2. **Controlar Inventario** 📦
- Registro de productos con múltiples unidades
- Control de stock en tiempo real
- Alertas de stock mínimo
- Kardex automatizado por producto
- Multi-almacén
- **Precisión objetivo:** > 98% inventario

#### 3. **Gestionar Compras** 🏭
- Órdenes de compra a proveedores
- Recepción de mercadería
- Actualización automática de inventario
- Control de costos
- **Tiempo objetivo:** < 5 minutos por compra

#### 4. **Administrar Clientes** 👥
- Base de datos de clientes
- Límites de crédito personalizados
- Seguimiento de pagos
- Historial de compras
- **Control:** 100% créditos registrados

#### 5. **Controlar Caja** 💰
- Apertura/cierre de caja
- Registro de movimientos
- Cuadre automático
- Reportes diarios
- **Precisión:** 100% cuadre diario

#### 6. **Auditar Operaciones** 📊
- Logs de todas las operaciones críticas
- Trazabilidad completa
- Reportes de auditoría
- **Cobertura:** 100% operaciones críticas

---

## 👥 Usuarios del Sistema

### 1. **Administrador** 👔
**Rol:** Dueño o gerente de la ferretería

**Permisos:**
- ✅ Acceso completo al sistema
- ✅ Gestión de usuarios y roles
- ✅ Configuración del sistema
- ✅ Todos los reportes
- ✅ Auditoría completa

**Casos de uso principales:**
- Ver reportes de ventas/compras
- Configurar parámetros del sistema
- Gestionar usuarios
- Revisar auditoría
- Definir límites de crédito
- Aprobar compras grandes

---

### 2. **Cajero/Vendedor** 💼
**Rol:** Empleado encargado de atender ventas

**Permisos:**
- ✅ Registrar ventas
- ✅ Buscar productos
- ✅ Consultar stock
- ✅ Registrar pagos de crédito
- ✅ Apertura/cierre de caja
- ❌ No puede modificar precios sin autorización
- ❌ No puede anular ventas antiguas

**Casos de uso principales:**
- Realizar ventas (contado/crédito)
- Consultar disponibilidad de productos
- Registrar pagos de clientes
- Hacer arqueo de caja
- Generar comprobantes

---

### 3. **Almacenero** 📦
**Rol:** Empleado encargado del inventario

**Permisos:**
- ✅ Registrar ingresos de mercadería
- ✅ Consultar stock
- ✅ Transferencias entre almacenes
- ✅ Ajustes de inventario (con justificación)
- ❌ No puede realizar ventas
- ❌ No puede ver información de caja

**Casos de uso principales:**
- Recepcionar compras
- Hacer inventarios físicos
- Transferir productos entre almacenes
- Ajustar stock (mermas, roturas)
- Generar reportes de stock

---

### 4. **Contador/Auditor** 📈
**Rol:** Encargado de revisión contable (opcional)

**Permisos:**
- ✅ Ver todos los reportes
- ✅ Consultar auditoría
- ✅ Ver movimientos de caja
- ❌ No puede modificar datos
- ❌ Solo lectura

**Casos de uso principales:**
- Revisar cierre de caja
- Generar reportes contables
- Auditar operaciones
- Verificar conciliaciones

---

## 📊 Alcance Funcional

### ✅ Incluido en el Sistema (V1.0)

#### Módulo de Autenticación
- Login con usuario/contraseña
- Gestión de roles y permisos
- Sesiones con JWT
- Cambio de contraseña

#### Módulo de Productos
- Catálogo de productos
- Categorías y marcas
- Múltiples unidades de medida
- Precios por unidad
- Stock mínimo por producto

#### Módulo de Inventario
- Control multi-almacén
- Kardex automatizado
- Movimientos de entrada/salida
- Alertas de stock bajo
- Ajustes de inventario

#### Módulo de Compras
- Registro de proveedores
- Órdenes de compra
- Recepción de mercadería
- Actualización automática de stock
- Historial de compras

#### Módulo de Ventas
- POS (Punto de venta)
- Ventas al contado
- Ventas a crédito
- Devoluciones
- Generación de comprobantes
- Descuentos

#### Módulo de Clientes
- Registro de clientes
- Límites de crédito
- Créditos vigentes
- Historial de pagos
- Historial de compras

#### Módulo de Caja
- Apertura de caja
- Registro de movimientos (ingresos/egresos)
- Cierre de caja
- Arqueo diario
- Cuadre automático

#### Módulo de Auditoría
- Logs de operaciones críticas
- Trazabilidad de cambios
- Reportes de auditoría
- Consulta de históricos

---

### ❌ NO Incluido (Futuras Versiones)

#### V2.0 - Mejoras Planeadas
- 📱 Aplicación móvil
- 🖨️ Integración con impresora fiscal
- 📧 Notificaciones por email/SMS
- 📊 Dashboard con gráficos avanzados
- 🔗 Integración con sistemas contables
- 📦 Picking y packing para delivery

#### V3.0 - Expansión
- 🌐 Tienda online (e-commerce)
- 📱 App para clientes
- 🤖 Sugerencias de compra con IA
- 📈 Predicción de demanda
- 🔄 Sincronización multi-sucursal

---

## 🚀 Beneficios Esperados

### Para el Negocio
| Beneficio | Impacto Esperado |
|-----------|------------------|
| **Reducción de errores** | -80% errores de inventario |
| **Tiempo de venta** | -50% tiempo por transacción |
| **Control de créditos** | 100% créditos registrados |
| **Reportes** | De 2 horas a 5 minutos |
| **Auditoría** | Trazabilidad completa |
| **Satisfacción cliente** | +30% velocidad de atención |

### Para los Usuarios
- ✅ Interfaz intuitiva y fácil de usar
- ✅ Procesos más rápidos
- ✅ Menos errores manuales
- ✅ Información en tiempo real
- ✅ Reportes automáticos

### ROI (Return on Investment)
- **Inversión inicial:** Sistema + Hardware + Capacitación
- **Recuperación estimada:** 6-12 meses
- **Ahorros mensuales:** Reducción de pérdidas, tiempo de personal
- **Beneficios intangibles:** Mejor control, decisiones informadas

---

## 🎓 Contexto Académico

### Proyecto Integrador II
Este sistema forma parte del Proyecto Integrador II de la carrera de Ingeniería de Sistemas en UNIFRANZ - El Alto.

**Objetivos académicos:**
- ✅ Aplicar metodologías de desarrollo (BDD/TDD)
- ✅ Implementar arquitectura de software profesional
- ✅ Diseñar base de datos normalizada (3FN)
- ✅ Desarrollar sistema full-stack
- ✅ Documentar según estándares de la industria
- ✅ Usar control de versiones (Git)
- ✅ Implementar buenas prácticas de código

**Tecnologías requeridas:**
- Backend: Laravel/PHP
- Frontend: HTML/CSS/JavaScript
- Base de datos: PostgreSQL
- Containerización: Docker

---

## 🔒 Restricciones y Limitaciones

### Técnicas
- ✅ Debe funcionar en Docker
- ✅ Base de datos PostgreSQL (requerimiento académico)
- ✅ Backend en PHP/Laravel (requerimiento académico)
- ✅ Debe ser responsive (móvil/desktop)
- ⚠️ No requiere alta disponibilidad (99.999%)
- ⚠️ Usuario único por sesión (no multi-usuario concurrente inicial)

### Operativas
- 🏪 Sistema para una sola sucursal (V1.0)
- 💻 Acceso desde red local
- 📊 Reportes básicos (no BI complejo)
- 🖨️ Impresión de comprobantes simples
- ⏰ Horario de operación: 8:00 - 20:00

### Legales
- 📄 Comprobantes según normativa boliviana
- 💼 Cumplimiento de ley de facturación
- 🔐 Protección de datos personales
- 📋 Auditoría según normativa contable

### Presupuestarias
- 💰 Proyecto académico (presupuesto limitado)
- 🖥️ Hardware existente
- 🆓 Uso de tecnologías open source
- 👨‍💻 Desarrollo por estudiantes

---

## 📅 Cronograma del Proyecto

### Fase 1: Análisis y Diseño (Completado)
- ✅ Requerimientos funcionales
- ✅ Diseño de base de datos
- ✅ Normalización 3FN
- ✅ Diagramas ER
- ✅ Documentación inicial

### Fase 2: Implementación de BD (Completado)
- ✅ Creación de 25 tablas
- ✅ Implementación de triggers (12)
- ✅ Creación de vistas (8)
- ✅ Funciones de negocio (11+)
- ✅ Índices de optimización (43+)
- ✅ Datos de prueba (seeders)

### Fase 3: Backend API (En Progreso)
- 🔄 Implementación de modelos
- 🔄 Controladores REST
- 🔄 Validaciones
- 🔄 Autenticación JWT
- 🔄 Tests automatizados

### Fase 4: Frontend (Pendiente)
- ⏳ Interfaz POS
- ⏳ Módulos administrativos
- ⏳ Dashboard
- ⏳ Reportes
- ⏳ Responsive design

### Fase 5: Testing y Deploy (Pendiente)
- ⏳ Tests de integración
- ⏳ Tests de usuario
- ⏳ Deploy en producción
- ⏳ Capacitación

### Fase 6: Entrega (Pendiente)
- ⏳ Documentación final
- ⏳ Manual de usuario
- ⏳ Presentación
- ⏳ Defensa del proyecto

---

## 🎯 Criterios de Éxito

### Técnicos
- ✅ Sistema funcional en Docker
- ✅ Base de datos normalizada 3FN
- ✅ API REST completa
- ✅ Cobertura de tests > 80%
- ✅ Documentación completa

### Funcionales
- ✅ Todos los módulos implementados
- ✅ Interfaz intuitiva
- ✅ Tiempo de respuesta < 2 segundos
- ✅ Sin errores críticos

### Académicos
- ✅ Cumple requerimientos del proyecto
- ✅ Aplicación de metodologías
- ✅ Código limpio y documentado
- ✅ Presentación profesional

---

## 📚 Documentos Relacionados

- **[02-arquitectura-general.md](02-arquitectura-general.md)** - Arquitectura técnica
- **[03-componentes.md](03-componentes.md)** - Detalle de componentes
- **[docs/04-database/](../04-database/)** - Documentación de BD
- **[docs/05-testing/](../05-testing/)** - Metodología BDD/TDD

---

**Última actualización:** 29 de noviembre de 2025  
**Versión:** 1.0.0  
**Autores:** Equipo Ferretería Frenad - UNIFRANZ
