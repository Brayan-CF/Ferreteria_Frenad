# SEEDERS - DATOS INICIALES

## Descripción General

Seeders son scripts SQL que insertan datos iniciales necesarios para que el sistema funcione correctamente.

**Ubicación:** `database/06_seeders/`

---

## Orden de Ejecución

Los seeders deben ejecutarse en este orden estricto debido a dependencias:

1. `01_roles.sql` - Roles de usuario (sin dependencias)
2. `02_unidades_medida.sql` - Unidades (sin dependencias)
3. `03_almacenes.sql` - Almacenes (sin dependencias)
4. `04_categorias.sql` - Categorías de productos (sin dependencias)
5. `05_marcas.sql` - Marcas de productos (sin dependencias)
6. `06_usuario_admin.sql` - Usuario administrador (depende de roles)
7. `07_datos_ejemplo.sql` - Datos de prueba (depende de todos)

---

## 1. Roles de Usuario

**Archivo:** `database/06_seeders/01_roles.sql`

**Datos insertados:**

| ID | Nombre | Descripción |
|----|--------|-------------|
| 1 | ADMINISTRADOR | Acceso total al sistema |
| 2 | VENDEDOR | Gestión de ventas y clientes |
| 3 | ALMACENERO | Gestión de inventario y compras |
| 4 | CAJERO | Operaciones de caja |

**Propósito:** Definir los 4 roles principales del sistema.

**Uso posterior:**
- Asignar rol al crear usuarios
- Control de permisos en la aplicación (backend)

---

## 2. Unidades de Medida

**Archivo:** `database/06_seeders/02_unidades_medida.sql`

**Datos insertados:**

| Símbolo | Nombre | Tipo |
|---------|--------|------|
| UN | Unidad | base |
| KG | Kilogramo | peso |
| LT | Litro | volumen |
| M | Metro | longitud |
| M² | Metro cuadrado | superficie |
| M³ | Metro cúbico | volumen |
| CM | Centímetro | longitud |
| PZ | Pieza | base |
| PQ | Paquete | conjunto |
| CJ | Caja | conjunto |
| GL | Galón | volumen |
| LB | Libra | peso |

**Propósito:** Unidades de medida comunes en ferretería.

**Uso posterior:**
- Asignar unidades a productos
- Conversión de unidades en ventas/compras

---

## 3. Almacenes

**Archivo:** `database/06_seeders/03_almacenes.sql`

**Datos insertados:**

| Nombre | Ubicación | Tipo | Activo |
|--------|-----------|------|--------|
| Principal | Av. Principal, El Alto | principal | TRUE |
| Sucursal Centro | Calle 16 de Julio, La Paz | sucursal | TRUE |

**Propósito:** Configurar almacenes iniciales del negocio.

**Uso posterior:**
- Gestión de inventario por ubicación
- Transferencias entre almacenes

---

## 4. Categorías de Productos

**Archivo:** `database/06_seeders/04_categorias.sql`

**Datos insertados:**

| Nombre | Descripción | Activo |
|--------|-------------|--------|
| Cemento y Agregados | Materiales de construcción base | TRUE |
| Herramientas Manuales | Martillos, destornilladores, llaves | TRUE |
| Herramientas Eléctricas | Taladros, amoladoras, sierras | TRUE |
| Ferretería General | Clavos, tornillos, tuercas | TRUE |
| Pinturas y Barnices | Productos de acabado | TRUE |
| Plomería | Tuberías, conexiones, grifería | TRUE |
| Electricidad | Cables, interruptores, tomacorrientes | TRUE |
| Maderas | Tablas, vigas, contrachapado | TRUE |

**Propósito:** Clasificar productos por tipo.

**Uso posterior:**
- Organizar catálogo de productos
- Filtrar productos en ventas
- Reportes por categoría

---

## 5. Marcas

**Archivo:** `database/06_seeders/05_marcas.sql`

**Datos insertados:**

| Nombre | País Origen | Activo |
|--------|-------------|--------|
| FANCESA | Bolivia | TRUE |
| EMISA | Bolivia | TRUE |
| Stanley | Estados Unidos | TRUE |
| Bosch | Alemania | TRUE |
| DeWalt | Estados Unidos | TRUE |
| Truper | México | TRUE |
| Black+Decker | Estados Unidos | TRUE |
| Generic | N/A | TRUE |

**Propósito:** Marcas comunes en ferreterías bolivianas.

**Uso posterior:**
- Asignar marca a productos
- Filtrar por marca en búsquedas
- Análisis de ventas por marca

---

## 6. Usuario Administrador

**Archivo:** `database/06_seeders/06_usuario_admin.sql`

**Datos insertados:**

| Campo | Valor |
|-------|-------|
| Nombre | Administrador |
| Apellido | Sistema |
| Email | admin@ferreteria.com |
| Contraseña | admin123 (hasheada con bcrypt) |
| Activo | TRUE |
| Rol | ADMINISTRADOR (ID: 1) |

**⚠️ IMPORTANTE:**
- La contraseña está hasheada con bcrypt
- **Cambiar contraseña inmediatamente** después del primer login
- En producción, usar contraseña fuerte

**Propósito:** Acceso inicial al sistema.

**Uso posterior:**
- Primer login al sistema
- Crear otros usuarios
- Configurar permisos

---

## 7. Datos de Ejemplo (SOLO DESARROLLO)

**Archivo:** `database/06_seeders/07_datos_ejemplo.sql`

**⚠️ ADVERTENCIA:** NO ejecutar en producción.

**Datos insertados:**

### Usuarios de prueba:
- Vendedor de prueba
- Almacenero de prueba
- Cajero de prueba

### Productos de ejemplo:
- 10-15 productos en diferentes categorías
- Con precios, stock, SKUs

### Clientes de prueba:
- 5 clientes con diferentes perfiles
- Clientes frecuentes y ocasionales

### Proveedores de ejemplo:
- 3 proveedores principales

### Ventas de ejemplo:
- 2-3 ventas con detalle completo
- Diferentes tipos de venta

**Propósito:** Facilitar pruebas y desarrollo.

**Uso:**
```bash
# SOLO en desarrollo
psql -U postgres -d ferreteria_frenad_dev -f database/06_seeders/07_datos_ejemplo.sql
```

---

## Ejecución Manual de Seeders

### Ejecutar un seeder específico:

```bash
psql -U postgres -d ferreteria_frenad -f database/06_seeders/01_roles.sql
```

---

### Ejecutar todos los seeders:

```bash
for file in database/06_seeders/*.sql; do
  echo "Ejecutando: $file"
  psql -U postgres -d ferreteria_frenad -f "$file"
done
```

**Nota:** El script `migrate.sh` ya ejecuta todos los seeders automáticamente.

---

## Ejecución con Docker

### Usando docker-compose:

```bash
docker-compose exec postgres psql -U postgres -d ferreteria_frenad -f /docker-entrypoint-initdb.d/06_seeders/01_roles.sql
```

---

### Ejecutar todos dentro del contenedor:

```bash
docker-compose exec postgres bash -c "
for file in /docker-entrypoint-initdb.d/06_seeders/*.sql; do
  psql -U postgres -d ferreteria_frenad -f \$file
done
"
```

---

## Verificación de Seeders

### Verificar roles:

```sql
SELECT * FROM roles ORDER BY id;
```

**Resultado esperado:** 4 roles

---

### Verificar unidades de medida:

```sql
SELECT * FROM unidades_medida ORDER BY id;
```

**Resultado esperado:** 12 unidades

---

### Verificar almacenes:

```sql
SELECT * FROM almacenes ORDER BY id;
```

**Resultado esperado:** 2 almacenes

---

### Verificar categorías:

```sql
SELECT * FROM categorias ORDER BY id;
```

**Resultado esperado:** 8 categorías

---

### Verificar marcas:

```sql
SELECT * FROM marcas ORDER BY id;
```

**Resultado esperado:** 8 marcas

---

### Verificar usuario admin:

```sql
SELECT u.email, r.nombre AS rol 
FROM usuarios u
JOIN usuario_roles ur ON u.id = ur.usuario_id
JOIN roles r ON ur.rol_id = r.id
WHERE u.email = 'admin@ferreteria.com';
```

**Resultado esperado:** admin@ferreteria.com con rol ADMINISTRADOR

---

## Limpiar y Recargar Seeders

### Eliminar todos los datos (⚠️ CUIDADO):

```sql
TRUNCATE TABLE 
  usuario_roles,
  usuarios,
  roles,
  productos,
  categorias,
  marcas,
  unidades_medida,
  almacenes
CASCADE;
```

---

### Recargar seeders:

```bash
for file in database/06_seeders/01_*.sql database/06_seeders/02_*.sql database/06_seeders/03_*.sql database/06_seeders/04_*.sql database/06_seeders/05_*.sql database/06_seeders/06_*.sql; do
  psql -U postgres -d ferreteria_frenad -f "$file"
done
```

**Nota:** Excluye `07_datos_ejemplo.sql` intencionalmente.

---

## Seeders Personalizados

### Crear seeder adicional:

**Ejemplo:** Agregar más categorías específicas.

**Archivo:** `database/06_seeders/04_categorias_adicionales.sql`

```sql
INSERT INTO categorias (nombre, descripcion, activo) VALUES
('Jardinería', 'Herramientas y accesorios de jardín', TRUE),
('Seguridad', 'Candados, cerraduras, alarmas', TRUE),
('Limpieza', 'Productos y herramientas de limpieza', TRUE)
ON CONFLICT (nombre) DO NOTHING;
```

**Ejecutar:**
```bash
psql -U postgres -d ferreteria_frenad -f database/06_seeders/04_categorias_adicionales.sql
```

---

## Seeders por Entorno

### Desarrollo:

```bash
# Todos los seeders incluyendo datos de ejemplo
./migrate.sh ferreteria_frenad_dev postgres localhost 5432
psql -U postgres -d ferreteria_frenad_dev -f database/06_seeders/07_datos_ejemplo.sql
```

---

### Testing:

```bash
# Solo seeders esenciales (sin datos de ejemplo)
./migrate.sh ferreteria_frenad_test postgres localhost 5433
# NO ejecutar 07_datos_ejemplo.sql
```

---

### Producción:

```bash
# Solo seeders esenciales
./migrate.sh ferreteria_frenad_prod postgres db.example.com 5432

# DESPUÉS del despliegue, cambiar contraseña de admin:
psql -U postgres -d ferreteria_frenad_prod -c "
UPDATE usuarios 
SET password_hash = '$2b$10$nuevo_hash_seguro_aqui' 
WHERE email = 'admin@ferreteria.com';
"
```

**⚠️ NUNCA ejecutar `07_datos_ejemplo.sql` en producción.**

---

## Buenas Prácticas

### Al crear seeders:

✅ Usar `ON CONFLICT DO NOTHING` para evitar duplicados
✅ Mantener datos mínimos necesarios
✅ Documentar propósito de cada seeder
✅ Numerar archivos en orden de dependencias

### Al ejecutar seeders:

✅ Verificar que tablas estén creadas primero
✅ Ejecutar en orden correcto
✅ Verificar datos después de ejecutar
✅ NO ejecutar datos de ejemplo en producción

### En producción:

✅ Solo ejecutar seeders esenciales (01-06)
✅ Cambiar contraseña de admin inmediatamente
✅ Revisar datos insertados
✅ Hacer respaldo después de seeders
❌ NUNCA ejecutar 07_datos_ejemplo.sql

---

## Mantenimiento de Seeders

### Actualizar datos de seeders:

**Opción 1:** Crear nuevo seeder con UPDATE.

**Ejemplo:** `08_actualizar_categorias.sql`
```sql
UPDATE categorias 
SET descripcion = 'Materiales de construcción base y agregados' 
WHERE nombre = 'Cemento y Agregados';
```

---

**Opción 2:** Eliminar y reinsertar.

```sql
DELETE FROM categorias WHERE nombre = 'Cemento y Agregados';
INSERT INTO categorias (nombre, descripcion, activo) 
VALUES ('Cemento y Agregados', 'Nueva descripción', TRUE);
```

---

### Agregar nuevos datos de referencia:

```sql
-- Agregar nueva unidad de medida
INSERT INTO unidades_medida (simbolo, nombre, tipo) 
VALUES ('BOL', 'Bolsa', 'conjunto')
ON CONFLICT (simbolo) DO NOTHING;
```

---

## Troubleshooting

### Error: "Violación de clave única"

**Causa:** Seeder ejecutado múltiples veces sin `ON CONFLICT`.

**Solución:**
```sql
-- Verificar datos duplicados
SELECT nombre, COUNT(*) FROM roles GROUP BY nombre HAVING COUNT(*) > 1;

-- Eliminar duplicados manualmente
DELETE FROM roles WHERE id IN (SELECT id FROM roles WHERE nombre = 'ADMINISTRADOR' ORDER BY id DESC LIMIT 1);
```

---

### Error: "Violación de foreign key"

**Causa:** Seeders ejecutados en orden incorrecto.

**Solución:** Ejecutar en orden correcto (01 → 02 → 03 → ... → 07).

---

### Contraseña de admin no funciona:

**Causa:** Hash de bcrypt no coincide.

**Solución:**
```bash
# Generar nuevo hash de bcrypt
node -e "console.log(require('bcrypt').hashSync('admin123', 10))"

# Actualizar en base de datos
psql -U postgres -d ferreteria_frenad -c "
UPDATE usuarios SET password_hash = '\$2b\$10\$nuevo_hash' 
WHERE email = 'admin@ferreteria.com';
"
```

---

## Referencias

- **Código fuente:** `database/06_seeders/`
- **Script de migración:** `database/migrate.sh` (ejecuta seeders automáticamente)
- **Instalación:** `docs/04-database/07-instalacion.md`
- **Testing:** `docs/04-database/10-testing.md` (NIVEL 4: Seeders)
