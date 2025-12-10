# Documentación de API - Scribe

> **Última actualización**: 10 de diciembre de 2025  
> **Herramienta**: Scribe 5.6.0

## Acceso a la Documentación

La API está documentada automáticamente usando **Scribe**. Puedes acceder a la documentación en:

| Formato | URL |
|---------|-----|
| **Documentación HTML** | http://localhost:8000/docs |
| **Colección Postman** | http://localhost:8000/docs.postman |
| **OpenAPI/Swagger** | http://localhost:8000/docs.openapi |

---

## Características

- ✅ **95+ endpoints** documentados
- ✅ **Try It Out** - Prueba endpoints desde el navegador
- ✅ **Ejemplos en Bash y JavaScript**
- ✅ **Autenticación Bearer Token** configurada
- ✅ **Exportable a Postman**
- ✅ **OpenAPI 3.0.3** compatible

---

## Regenerar Documentación

Si realizas cambios en los endpoints, regenera la documentación:

```bash
docker exec ferreteria_backend php artisan scribe:generate
```

---

## Configuración

La configuración de Scribe está en `config/scribe.php`:

```php
'title' => 'Ferretería Frenat - API Documentation',
'description' => 'API REST para el sistema de gestión de Ferretería Frenat',
'auth' => [
    'enabled' => true,
    'default' => true,
    'in' => 'bearer',
    'name' => 'Authorization',
    'placeholder' => 'Bearer {YOUR_AUTH_TOKEN}',
],
```

---

## Archivos Generados

| Archivo | Descripción |
|---------|-------------|
| `resources/views/scribe/` | Vistas Blade de documentación |
| `public/vendor/scribe/` | Assets CSS/JS |
| `storage/app/scribe/collection.json` | Colección Postman |
| `storage/app/scribe/openapi.yaml` | Especificación OpenAPI |

---

## Módulos Documentados

1. **Auth** - Login, Logout, Profile, Usuarios
2. **Product** - Productos, Categorías, Marcas, Unidades
3. **Inventory** - Inventario, Almacenes, Movimientos
4. **Sales** - Ventas, Estadísticas
5. **Purchase** - Compras, Proveedores
6. **Customer** - Clientes, Créditos
7. **Reports** - Reportes de Ventas, Compras, Inventario, Financiero
