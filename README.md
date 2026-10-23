# Featbrands

Panel de administración con Laravel 13, Filament 5 y Livewire 4, configurado en español. Utiliza SQLite para desarrollo local.

## Requisitos

- PHP 8.4 con las extensiones habituales de Laravel, `intl`, `zip`, `pdo_sqlite` y `sqlite3`.
- Composer 2.
- Node.js 22.12 o superior y npm 10 o superior.
- Git y conexión a Internet para descargar dependencias y las fuentes del frontend.

## Instalar en otro dispositivo

```bash
git clone https://github.com/deevfer/featbrands.git
cd featbrands
composer setup
php artisan serve
```

`composer setup` instala las versiones fijadas en `composer.lock` y `package-lock.json`, copia `.env.example` cuando no existe `.env`, genera una clave propia, crea la base SQLite, aplica las migraciones, publica los recursos de Filament, enlaza el almacenamiento público y compila el frontend.

En la primera instalación te pedirá nombre, correo y contraseña para crear tu administrador. Abre **http://127.0.0.1:8000/admin** y entra con esas credenciales.

Puedes repetir `composer setup`: conserva la clave, la base de datos y los usuarios existentes. El administrador original de otro equipo no se copia. Para crear otro usuario:

```bash
php artisan make:filament-user --panel=admin
```

Para instalar sin preguntas, por ejemplo en una automatización:

```bash
composer --no-interaction setup
```

En ese caso crea el administrador después, con el comando anterior. Si utilizas MySQL, configura primero sus credenciales en tu `.env` y crea la base de datos antes de ejecutar el instalador.

## Desarrollo

```bash
composer run dev
```

Inicia Laravel, Vite y el procesador de colas. También puedes usar `php artisan serve` para trabajar con los recursos ya compilados.

```bash
composer test
npm run build
```

## Archivos locales

Git incluye el código, `.env.example`, las migraciones y los archivos de versiones de dependencias. Cada equipo genera su propio `.env`, clave, base de datos, dependencias y archivos compilados. Los archivos subidos, las sesiones y los registros tampoco se sincronizan por Git.

El instalador está preparado para desarrollo local. Para producción configura el dominio, `APP_ENV=production`, `APP_DEBUG=false` y el acceso de usuarios al panel antes de desplegarlo.
