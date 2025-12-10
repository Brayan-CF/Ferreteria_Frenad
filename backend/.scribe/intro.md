# Introduction

API REST para el sistema de gestión de Ferretería Frenat. Incluye módulos de autenticación, productos, inventario, ventas, compras, clientes y reportes.

<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>

    Bienvenido a la documentación de la API de Ferretería Frenat.

    ## Autenticación
    La API utiliza **Bearer Token** para autenticación. Obtenga su token mediante el endpoint `/api/auth/auth/login`.

    ## Formato de respuesta
    Todas las respuestas están en formato JSON con la siguiente estructura:
    - `success`: boolean indicando si la operación fue exitosa
    - `data`: datos de la respuesta
    - `message`: mensaje descriptivo (en caso de error)

    <aside>Los ejemplos de código se muestran en el panel derecho. Puede cambiar entre Bash y JavaScript.</aside>

