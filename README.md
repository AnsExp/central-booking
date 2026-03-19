# Central Booking

Sistema web para la gestion operativa de una red ferroviaria: estaciones, trenes, servicios y administracion basica desde dashboard.

## Nota de producto

Central Booking esta pensado como base evolutiva para operaciones ferroviarias. El producto hoy cubre administracion interna y catalogos; su siguiente fase recomendada es venta de tickets, reportes y gestion avanzada de rutas.

## Tecnologias utilizadas

- PHP 8.2+
- Laravel 12
- MySQL o SQLite (segun entorno)
- Blade (vistas servidor)
- Vite
- Tailwind CSS v4
- PHPUnit 11
- Blade Heroicons

## Requisitos previos

- PHP 8.2 o superior
- Composer
- Node.js 18+ y npm
- MySQL 8+ (recomendado para desarrollo actual) o SQLite

## Instalacion local

1. Clonar el repositorio.
2. Instalar dependencias de PHP:

```bash
composer install
```

3. Crear archivo de entorno:

```bash
cp .env.example .env
```

En Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

4. Configurar variables de base de datos en `.env`.

Ejemplo MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=central-booking
DB_USERNAME=root
DB_PASSWORD=tu_password
```

5. Generar llave de aplicacion:

```bash
php artisan key:generate
```

6. Ejecutar migraciones:

```bash
php artisan migrate --force
```

7. Sembrar usuario administrador por defecto:

```bash
php artisan db:seed
```

8. Instalar dependencias front:

```bash
npm install
```

9. Levantar entorno de desarrollo:

```bash
composer run dev
```

## Credenciales por defecto

- Email: `admin@mail.com`
- Password: `admin`

## Como probar la aplicacion

### Pruebas automaticas

```bash
composer test
```

o

```bash
php artisan test
```

### Prueba manual basica

1. Iniciar sesion con el usuario admin.
2. Ir al dashboard.
3. Crear, editar y eliminar una estacion.
4. Crear, editar y eliminar un servicio.
5. Crear un tren y validar campos condicionales por tipo.

## Despliegue a produccion

## Opcion A: servidor Linux con Nginx

1. Subir codigo al servidor.
2. Instalar dependencias sin paquetes de desarrollo:

```bash
composer install --no-dev --optimize-autoloader
```

3. Configurar `.env` de produccion (`APP_ENV=production`, `APP_DEBUG=false`).
4. Generar llave si no existe:

```bash
php artisan key:generate
```

5. Ejecutar migraciones:

```bash
php artisan migrate --force
```

6. Cachear configuraciones:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

7. Compilar assets:

```bash
npm ci
npm run build
```

8. Configurar Nginx apuntando a `public/index.php`.
9. Permisos para escritura en `storage` y `bootstrap/cache`.

## Opcion B: hosting con Apache

1. Repetir pasos 1 a 7 de la opcion A.
2. Asegurar que el DocumentRoot apunte a `public`.
3. Verificar `mod_rewrite` habilitado.

## Variables de entorno clave

- `APP_ENV`
- `APP_DEBUG`
- `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `SESSION_DRIVER`
- `CACHE_STORE`
- `QUEUE_CONNECTION`

## Notas tecnicas

- La base actual usa nombres de tabla en singular (`station`, `train`, `service`, `meta`).
- El modulo de trenes usa enums de dominio:
	- Tipo: `passengers`, `cargo`, `mixed`
	- Estado: `active`, `under_maintenance`, `decommissioned`, `out_of_service`
- La tabla `meta` usa la columna `meta_id` para relacion con tren.
- Para evitar errores de autoload e IntelliSense, mantener convencion PSR-4 (nombre de archivo == nombre de clase).

## Estructura funcional principal

- Dashboard administrativo
- Gestion de estaciones
- Gestion de servicios
- Gestion de trenes
- Login y logout

## Solucion de problemas comunes

1. Error de clase no encontrada en editor:
- Ejecutar `composer dump-autoload`.
- Recargar ventana de VS Code.

2. El formulario vuelve a cargarse al enviar:
- Revisar validaciones en controlador.
- Verificar que los valores enviados coincidan con enums esperados.

3. Error SQL por columnas en `meta`:
- Confirmar que las relaciones usen `meta_id` y no `train_id`.

## Licencia

Proyecto academico/interno. Definir licencia comercial u open source segun politica del equipo.
