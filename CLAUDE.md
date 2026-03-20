# Backend Steven

## Descripción
REST API backend para gestión de inventarios, ventas y automatización de mensajería WhatsApp. Construido con PHP Slim 4 y MySQL. Se integra con autowat-api (servicio WhatsApp) y Amazon S3.

## Stack principal
- PHP 7.4+
- Slim Framework 4
- MySQL (PDO)
- Firebase/PHP-JWT
- PHPOffice/PhpSpreadsheet
- Ramsey/UUID
- AWS SDK (Amazon S3)
- Apache (.htaccess)

## URLs e integraciones
- Frontend: steven-escaner (Vue 3) en https://steven.multimarcas.app
- AutoWhat API: http://localhost:3300
- Amazon S3 CDN: https://cdn.multimarcas.app

## Estructura del proyecto
```
backend-steven/
├── index.php                    # Punto de entrada, rutas y middlewares
├── composer.json
├── migration.sql
├── src/
│   ├── Controllers/             # 8 controllers (request/response)
│   └── Models/                  # 9 models (lógica + BD)
```

## Reglas generales
- Controllers solo manejan request y response
- Models contienen lógica de negocio y queries SQL
- Toda query debe usar prepared statements (PDO)
- Toda ruta sensible debe validar JWT
- Las rutas admin deben validar rol
- Toda respuesta debe ser JSON consistente
- No hardcodear credenciales ni secrets

## Autenticación
- JWT con HS256 via Firebase/PHP-JWT
- Middleware global en index.php valida Authorization: Bearer
- Roles: User, Admin, Editor
- Rutas sin JWT: /auth/register, /auth/login

## Base de datos
- Motor: MySQL
- Base: steven
- Charset: UTF-8 MB4
- Tablas: usuarios, productos, categoria_mensaje, mensajes_personalizados, base
- Todos los Models heredan de Database (conexión PDO)

## Convenciones
- Clases: PascalCase
- Métodos: camelCase (crear*, obtener*, listar*, editar*, eliminar*)
- Rutas API: kebab-case (/api/auth/register)
- Tablas/columnas: snake_case
- Respuestas: JSON con estructura consistente

## Endpoints principales
- /api/auth/* — registro y login
- /api/user/* — perfil y listado
- /api/admin/* — gestión de usuarios y proxy a autowat-api
- /api/document/* — inventario y escaneo
- /api/category/* — categorías de mensajes
- /api/messages/* — mensajes personalizados
- /api/base/* — base de clientes
- /api/files/* — upload a S3

## Riesgos a evitar
- SQL injection (siempre prepared statements)
- Credenciales hardcodeadas
- Respuestas inconsistentes (strings vs arrays)
- Variables mal nombradas en controllers
- Proxies sin manejo de errores de conexión
- Uploads sin validación completa
- Falta de validación de entrada en endpoints
