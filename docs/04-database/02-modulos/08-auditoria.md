# MÓDULO AUDIT - AUDITORÍA Y TRAZABILIDAD

## Descripción General

Módulo que registra todos los cambios críticos en el sistema para auditoría y trazabilidad completa.

---

## Tablas del Módulo

### 1. logs_auditoria

**Propósito:** Registro completo de cambios críticos (INSERT, UPDATE, DELETE).

**Estructura:**

Ver archivo: [database/01_tables/24_logs_auditoria.sql](../../../database/01_tables/24_logs_auditoria.sql)

**Columnas Clave:**
- `tabla_afectada`: Nombre de la tabla modificada
- `registro_id`: ID del registro afectado
- `accion`: INSERT, UPDATE o DELETE
- `valores_anteriores`: Estado anterior (JSONB) - para UPDATE/DELETE
- `valores_nuevos`: Estado nuevo (JSONB) - para INSERT/UPDATE
- `usuario_id`: Quién hizo el cambio
- `ip_address`: Dirección IP del cliente
- `user_agent`: Navegador/cliente usado

---

## Diagrama de Relaciones

```
┌─────────────┐
│  usuarios   │
└──────┬──────┘
       │
       │ usuario_id
       ▼
┌─────────────────────┐
│  logs_auditoria     │
│ (tabla_afectada,    │
│  accion, valores_   │
│  anteriores,        │
│  valores_nuevos)    │
└─────────────────────┘
       │
       │ (referencia lógica)
       ▼
┌─────────────────────┐
│  Todas las tablas   │
│  del sistema        │
└─────────────────────┘
```

---

## Reglas de Negocio

### Auditoría:
1. Se registran cambios en tablas críticas:
   - usuarios, ventas, compras
   - inventario, creditos_clientes
   - arqueos_caja
2. Los logs son **inmutables** (no se pueden modificar ni eliminar)
3. Se almacenan tanto valores anteriores como nuevos para comparación
4. Los valores se guardan en formato JSONB para flexibilidad

### Información Contextual:
1. `ip_address`: Para identificar origen de la operación
2. `user_agent`: Para identificar aplicación/navegador
3. `creado_en`: Timestamp exacto del cambio

---

## Operaciones Comunes

### Registrar Cambio Manual:

```sql
-- Ejemplo: Registrar cambio crítico en precio de producto
INSERT INTO logs_auditoria (
    tabla_afectada,
    registro_id,
    accion,
    valores_anteriores,
    valores_nuevos,
    usuario_id,
    ip_address
) VALUES (
    'productos',
    5,
    'UPDATE',
    '{"precio_venta": 55.00}'::JSONB,
    '{"precio_venta": 60.00}'::JSONB,
    1,
    '192.168.1.100'::INET
);
```

---

### Consultar Historial de un Registro:

```sql
-- Ver todos los cambios de un producto específico
SELECT 
    accion,
    valores_anteriores,
    valores_nuevos,
    u.nombre AS usuario,
    ip_address,
    creado_en
FROM logs_auditoria la
LEFT JOIN usuarios u ON la.usuario_id = u.id
WHERE tabla_afectada = 'productos'
AND registro_id = 5
ORDER BY creado_en DESC;
```

---

### Ver Cambios Recientes:

```sql
SELECT 
    tabla_afectada,
    registro_id,
    accion,
    u.nombre AS usuario,
    creado_en
FROM logs_auditoria la
LEFT JOIN usuarios u ON la.usuario_id = u.id
WHERE creado_en >= CURRENT_DATE
ORDER BY creado_en DESC
LIMIT 50;
```

---

### Auditar Cambios de Precios:

```sql
-- Detectar cambios en precios de productos
SELECT 
    registro_id AS producto_id,
    valores_anteriores->>'precio_venta' AS precio_anterior,
    valores_nuevos->>'precio_venta' AS precio_nuevo,
    u.nombre AS modificado_por,
    creado_en
FROM logs_auditoria la
JOIN usuarios u ON la.usuario_id = u.id
WHERE tabla_afectada = 'productos'
AND valores_anteriores ? 'precio_venta' -- Clave existe
AND valores_anteriores->>'precio_venta' != valores_nuevos->>'precio_venta'
ORDER BY creado_en DESC;
```

---

### Detectar Actividad Sospechosa:

```sql
-- Usuarios con muchos cambios en poco tiempo
SELECT 
    u.nombre,
    COUNT(*) AS cantidad_cambios,
    array_agg(DISTINCT tabla_afectada) AS tablas_modificadas
FROM logs_auditoria la
JOIN usuarios u ON la.usuario_id = u.id
WHERE creado_en >= CURRENT_TIMESTAMP - INTERVAL '1 hour'
GROUP BY u.id, u.nombre
HAVING COUNT(*) > 50
ORDER BY cantidad_cambios DESC;
```

---

### Auditar Ventas Anuladas:

```sql
-- Ver quién anuló ventas y por qué
SELECT 
    registro_id AS venta_id,
    valores_anteriores->>'numero_venta' AS numero,
    valores_anteriores->>'total' AS monto,
    valores_nuevos->>'estado' AS nuevo_estado,
    u.nombre AS anulado_por,
    creado_en
FROM logs_auditoria la
JOIN usuarios u ON la.usuario_id = u.id
WHERE tabla_afectada = 'ventas'
AND accion = 'UPDATE'
AND valores_nuevos->>'estado' = 'anulada'
ORDER BY creado_en DESC;
```

---

## Triggers Automáticos (Futuro)

### Auditoría Automática con Triggers:

**Nota:** Actualmente los logs se registran manualmente desde la API. En el futuro se pueden implementar triggers automáticos.

```sql
-- Ejemplo de trigger para auditoría automática (NO implementado aún)
CREATE OR REPLACE FUNCTION registrar_auditoria()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        INSERT INTO logs_auditoria (
            tabla_afectada, registro_id, accion, valores_nuevos, usuario_id
        ) VALUES (
            TG_TABLE_NAME, NEW.id, 'INSERT', row_to_json(NEW)::JSONB, NEW.creado_por
        );
    ELSIF TG_OP = 'UPDATE' THEN
        INSERT INTO logs_auditoria (
            tabla_afectada, registro_id, accion, valores_anteriores, valores_nuevos, usuario_id
        ) VALUES (
            TG_TABLE_NAME, NEW.id, 'UPDATE', row_to_json(OLD)::JSONB, row_to_json(NEW)::JSONB, NEW.actualizado_por
        );
    ELSIF TG_OP = 'DELETE' THEN
        INSERT INTO logs_auditoria (
            tabla_afectada, registro_id, accion, valores_anteriores, usuario_id
        ) VALUES (
            TG_TABLE_NAME, OLD.id, 'DELETE', row_to_json(OLD)::JSONB, OLD.actualizado_por
        );
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Aplicar a tablas críticas
CREATE TRIGGER trigger_auditoria_productos
    AFTER INSERT OR UPDATE OR DELETE ON productos
    FOR EACH ROW
    EXECUTE FUNCTION registrar_auditoria();
```

---

## Índices Definidos

Ver archivo: [database/02_indexes/09_auditoria_indexes.sql](../../../database/02_indexes/09_auditoria_indexes.sql)

```sql
CREATE INDEX idx_logs_tabla ON logs_auditoria(tabla_afectada);
CREATE INDEX idx_logs_registro ON logs_auditoria(registro_id);
CREATE INDEX idx_logs_usuario ON logs_auditoria(usuario_id);
CREATE INDEX idx_logs_fecha ON logs_auditoria(creado_en);
CREATE INDEX idx_logs_accion ON logs_auditoria(accion);
```

---

## Integración con Laravel

### Modelos Eloquent:

```php
// app/Models/LogAuditoria.php
class LogAuditoria extends Model
{
    protected $table = 'logs_auditoria';
    
    protected $casts = [
        'valores_anteriores' => 'array',
        'valores_nuevos' => 'array',
        'creado_en' => 'datetime',
    ];
    
    // No permite modificaciones
    public $timestamps = false;
    protected $guarded = ['id'];
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
    
    // Método estático para registrar cambios
    public static function registrar($tabla, $registroId, $accion, $anterior = null, $nuevo = null)
    {
        return self::create([
            'tabla_afectada' => $tabla,
            'registro_id' => $registroId,
            'accion' => $accion,
            'valores_anteriores' => $anterior,
            'valores_nuevos' => $nuevo,
            'usuario_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
```

### Uso en Controladores:

```php
// Ejemplo: Auditar cambio de precio
public function actualizarPrecio(Request $request, $productoId)
{
    $producto = Producto::findOrFail($productoId);
    
    // Guardar estado anterior
    $anterior = [
        'precio_compra' => $producto->precio_compra,
        'precio_venta' => $producto->precio_venta,
    ];
    
    // Actualizar
    $producto->precio_compra = $request->precio_compra;
    $producto->precio_venta = $request->precio_venta;
    $producto->save();
    
    // Registrar en auditoría
    LogAuditoria::registrar(
        'productos',
        $producto->id,
        'UPDATE',
        $anterior,
        [
            'precio_compra' => $producto->precio_compra,
            'precio_venta' => $producto->precio_venta,
        ]
    );
    
    return response()->json(['success' => true]);
}
```

---

## Casos de Uso Prácticos

### Caso 1: Investigar Cambio No Autorizado

**Escenario:** El precio de un producto cambió sin autorización.

```sql
-- 1. Buscar el cambio
SELECT 
    valores_anteriores->>'precio_venta' AS precio_anterior,
    valores_nuevos->>'precio_venta' AS precio_nuevo,
    u.nombre AS modificado_por,
    ip_address,
    creado_en
FROM logs_auditoria la
JOIN usuarios u ON la.usuario_id = u.id
WHERE tabla_afectada = 'productos'
AND registro_id = 15
AND valores_anteriores ? 'precio_venta'
ORDER BY creado_en DESC
LIMIT 1;

-- Resultado: Usuario "Juan Pérez" cambió el precio de Bs. 55 a Bs. 45
-- IP: 192.168.1.50, Fecha: 2024-12-15 14:30:00
```

---

### Caso 2: Auditoría Mensual

```sql
-- Reporte de cambios del mes
SELECT 
    tabla_afectada,
    accion,
    COUNT(*) AS cantidad
FROM logs_auditoria
WHERE DATE_TRUNC('month', creado_en) = DATE_TRUNC('month', CURRENT_DATE)
GROUP BY tabla_afectada, accion
ORDER BY cantidad DESC;
```

---

### Caso 3: Recuperar Valor Anterior

**Escenario:** Se necesita saber el precio de un producto hace 3 meses.

```sql
SELECT 
    valores_anteriores->>'precio_venta' AS precio_hace_3_meses,
    creado_en
FROM logs_auditoria
WHERE tabla_afectada = 'productos'
AND registro_id = 5
AND accion = 'UPDATE'
AND valores_anteriores ? 'precio_venta'
AND creado_en >= CURRENT_DATE - INTERVAL '3 months'
ORDER BY creado_en ASC
LIMIT 1;
```

---

### Caso 4: Actividad por Usuario

```sql
-- Ver qué ha hecho un usuario específico hoy
SELECT 
    tabla_afectada,
    registro_id,
    accion,
    CASE 
        WHEN valores_nuevos IS NOT NULL THEN 
            jsonb_pretty(valores_nuevos)
        ELSE 
            jsonb_pretty(valores_anteriores)
    END AS datos,
    creado_en
FROM logs_auditoria
WHERE usuario_id = 2
AND DATE(creado_en) = CURRENT_DATE
ORDER BY creado_en DESC;
```

---

## Mejores Prácticas

### Qué Auditar:

**Siempre:**
- Cambios en precios de productos
- Ventas anuladas
- Ajustes de inventario
- Modificación de roles de usuarios
- Cierre de caja con diferencias

**Opcional:**
- Todos los INSERT en ventas/compras
- Cambios en datos de clientes
- Modificaciones en proveedores

### Qué NO Auditar:

- Timestamps automáticos (creado_en, actualizado_en)
- Lecturas de datos (SELECT)
- Cambios frecuentes sin importancia

---

## Mantenimiento

### Limpieza de Logs Antiguos:

```sql
-- Eliminar logs de más de 2 años (ejecutar anualmente)
-- PRECAUCIÓN: Revisar con el equipo legal antes
DELETE FROM logs_auditoria
WHERE creado_en < CURRENT_DATE - INTERVAL '2 years';

-- Alternativa: Archivar en tabla de históricos
INSERT INTO logs_auditoria_archivo
SELECT * FROM logs_auditoria
WHERE creado_en < CURRENT_DATE - INTERVAL '1 year';
```

### Particionamiento (Futuro):

Para mejorar performance con millones de registros:

```sql
-- Particionar por mes (implementar en producción)
CREATE TABLE logs_auditoria_2024_12 PARTITION OF logs_auditoria
FOR VALUES FROM ('2024-12-01') TO ('2025-01-01');
```

---

## Testing

### Casos de Prueba:

1. **Registrar log sin usuario** → Debe permitir (usuario puede ser NULL)
2. **Modificar log existente** → Debe rechazar (política de aplicación)
3. **Consultar logs por fecha** → Índice debe optimizar
4. **JSONB query performance** → Verificar con datos reales
5. **Logs no afectan transacciones** → Deben ser asíncronos idealmente

---

## Consideraciones de Performance

### Volumen Esperado:
- ~500 logs/día en operación normal
- ~180,000 logs/año
- Con 5 años: ~900,000 registros

### Optimizaciones:
1. Índices en columnas más consultadas
2. Particionamiento por fecha (futuro)
3. Archivado de logs antiguos
4. Considerar logging asíncrono para no afectar performance

---

**Documentación Completa de Módulos Finalizada**

**Siguiente:** [Funciones Transaccionales](../03-funciones/01-transaccionales.md)
