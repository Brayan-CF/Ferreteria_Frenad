# Database Backups

Este directorio almacena respaldos de la base de datos.

## Uso

```bash
# Crear respaldo
docker exec ferreteria_postgres pg_dump -U postgres ferreteria_frenad > database/backups/backup_$(date +%Y%m%d_%H%M%S).sql

# Restaurar respaldo
docker exec -i ferreteria_postgres psql -U postgres ferreteria_frenad < database/backups/backup_20241215_143000.sql
```

## ⚠️ IMPORTANTE

**NO commitear respaldos con datos reales a Git.**

Los archivos `.sql` en este directorio están excluidos en `.gitignore`.
