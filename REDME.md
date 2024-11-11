
# WhatNow

WhatNow es una aplicación web construida con Laravel 5.8 y Vue.js, diseñada para proporcionar una guía de preparación para desastres. Este documento te guiará a través de los pasos para instalar y configurar el proyecto en tu entorno de desarrollo.

## Prerrequisitos

Asegúrate de tener las siguientes herramientas instaladas en tu sistema:

- **PHP (versión 7.4):**
  ```bash
  php -v
  ```
- **Composer (administrador de dependencias para PHP):**
  ```bash
  composer --version
  ```
- **Node.js (versión recomendada 12 o superior):**
  ```bash
  node -v
  ```
- **npm (administrador de paquetes de Node.js):**
  ```bash
  npm -v
  ```
- **MySQL (versión 5.7 o superior):**
  ```bash
  mysql --version
  ```

## Instalación del Proyecto

### Clona el Repositorio

```bash
git clone https://github.com/YourUsername/nombre.git
cd nombre
```

### Instala las Dependencias de PHP

```bash
composer install
```

### Instala las Dependencias de JavaScript

```bash
npm install
```

### Configuración del Entorno

1. Copia el archivo de ejemplo `.env` y configúralo como tu archivo de entorno:
   ```bash
   cp .env.example .env
   ```

2. Genera una clave de aplicación para Laravel:
   ```bash
   php artisan key:generate
   ```

### Configuración de la Base de Datos

1. Crea una base de datos MySQL, por ejemplo `redcross-local` y `redcross_log`.

2. Abre el archivo `.env` y actualiza tus credenciales de base de datos según el entorno:

3. Ejecuta las migraciones de base de datos:
   ```bash
   php artisan migrate
   ```

4. Opcional: Para poblar la base de datos con datos de muestra iniciales, puedes ejecutar el siguiente comando:
   ```bash
   php artisan db:seed
   ```

### Iniciar el Servidor de Desarrollo

Ejecuta el siguiente comando para iniciar el servidor de desarrollo de Laravel:
```bash
php artisan serve --port=8001
```

## Configuración Adicional

1. Si en la carpeta `storage` falta la subcarpeta `framework` o las carpetas `cache` y `sessions` dentro de ella, créalas para asegurar que Laravel funcione correctamente.

2. Dirígete al archivo `.env` y ajusta las siguientes configuraciones para conectar correctamente la API y el backoffice:

   ```dotenv
   RCN_API_VERSION=v1
   RCN_API_URL=http://127.0.0.1:8001
   RCN_API_USER=admin
   RCN_API_PASSWORD=password
   ```

3. En el backoffice, ejecuta los siguientes comandos para compilar y servir la aplicación:

   ```bash
   npm run hot
   php artisan serve
   ```

Ahora, la API y el backoffice estarán conectados y listos para usar.
