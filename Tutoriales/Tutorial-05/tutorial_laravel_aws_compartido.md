# Tutorial 05: Laravel, MySQL y phpMyAdmin con Docker

## Organización del ejercicio

Realizará toda la práctica principal en **la instancia AWS de su grupo, con Amazon Linux 2023**. Construirá y ejecutará allí sus contenedores. Al final encontrará un bonus opcional para publicar una imagen desde un computador local con Docker ya instalado y descargarla en AWS.

El ejemplo está preparado para reducir consumo: solo PHP, sin Node/Vite, build antes de iniciar MySQL y MySQL con ajustes para poca memoria. Construyan una imagen a la vez y consulten `free -m`, `df -h /` y `docker stats --no-stream`. Validar un despliegue en una instancia pequeña no demuestra que pueda sostener los nueve contenedores del grupo; midan su carga antes de asignarle tres aplicaciones.

Cada instancia AWS tiene como máximo tres estudiantes. **Cada estudiante crea y administra sus propios tres contenedores: Laravel, MySQL y phpMyAdmin.** También tendrá una carpeta, una red Docker y volúmenes propios. La base de datos y phpMyAdmin se definen en un Compose independiente del de Laravel.

No hay una base de datos ni un phpMyAdmin compartidos. Solo se comparte el servidor y la instalación de Docker/Compose. Cualquier integrante puede instalar las herramientas si faltan; coordinen ese paso para no ejecutarlo simultáneamente. Si ya funcionan, no reinstale ni reinicie Docker mientras sus compañeros trabajan.

### Asignación de identificadores y puertos

Cada integrante elige una fila diferente dentro de su instancia. Reemplace el nombre de ejemplo por su nombre real, en minúsculas, sin espacios ni tildes.

| Integrante | Identificador de ejemplo | Base de datos / usuario | Laravel | phpMyAdmin | MySQL en el host |
|---|---|---|---|---|---|
| 1 | ana-perez | anaperez | 8081 | 9081 | 33061 |
| 2 | luis-gomez | luisgomez | 8082 | 9082 | 33062 |
| 3 | sara-ruiz | sararuiz | 8083 | 9083 | 33063 |

Las tres aplicaciones usan internamente 80; los tres phpMyAdmin usan internamente 80; las tres bases usan internamente 3306. Los números de la tabla son los puertos del **host**, que deben ser distintos. Pueden reutilizar esta misma tabla en otra instancia AWS.

MySQL publica su puerto únicamente en 127.0.0.1, para una eventual conexión local o por túnel SSH. Laravel y phpMyAdmin se conectan directamente a mysql:3306 dentro de la red individual; no utilizan 33061, 33062 ni 33063 para esa comunicación.

El ejemplo completo corresponde a Ana, integrante 1. Luis y Sara deben cambiar sus identificadores, base, usuario y los tres puertos en .env; los archivos YAML se mantienen iguales.

## Trabajo en Amazon Linux 2023 en EC2

### 1. Conectarse a su instancia del grupo

Desde una terminal de su computador, con la clave SSH:

```bash
chmod 400 "students.pem"
ssh -i "students.pem" ec2-user@100.54.107.127
cat /etc/os-release
```

La sección siguiente está preparada para **Amazon Linux 2023** (VERSION_ID=2023). AMI significa imagen de máquina, no una versión del sistema operativo. Si su instancia usa Amazon Linux 2, no aplique los comandos DNF de esta sección: esa versión necesita una ruta de instalación distinta.

### 2. Instalar Docker con DNF, si hace falta

Ejecute **dentro de AWS**, no en su computador local:

```bash
docker version
docker compose version
```

Si ambos funcionan y dispone de git, nano y openssl, pase al paso 4. Compruebe esas herramientas con `command -v git nano openssl`; si alguna falta, instale solo las faltantes con DNF. Si Docker falta, cualquier integrante puede ejecutar, coordinando una sola instalación por instancia:

```bash
sudo dnf install -y docker git nano openssl
sudo systemctl enable --now docker
sudo usermod -aG docker ec2-user
```

Cierre SSH con exit y conéctese nuevamente para aplicar el grupo docker. Si Docker ya está instalado pero docker version muestra un error de permisos, compruebe su grupo con `id`; si falta docker, ejecute `sudo usermod -aG docker ec2-user` y vuelva a conectarse. No reinicie un Docker ya operativo que utilizan sus compañeros.

### 3. Instalar Compose v2 como complemento de Docker

Omita este paso si `docker compose version` ya funciona con una versión v2 compatible con la práctica. Si ya existe otra versión funcional que utiliza el grupo, coordinen cualquier cambio antes de reemplazarla.

Ejecute dentro de **Amazon Linux** el procedimiento de instalación manual del complemento documentado por Docker. Se utiliza la versión v2.40.3 fijada para esta práctica y se instala una sola vez para todos los usuarios:

```bash
COMPOSE_VERSION=v2.40.3
COMPOSE_ARCH=$(uname -m)
sudo mkdir -p /usr/local/lib/docker/cli-plugins
sudo curl -fL "https://github.com/docker/compose/releases/download/${COMPOSE_VERSION}/docker-compose-linux-${COMPOSE_ARCH}" -o /usr/local/lib/docker/cli-plugins/docker-compose
sudo chmod +x /usr/local/lib/docker/cli-plugins/docker-compose
docker compose version
```

Compruebe que la salida indica Docker Compose version v2.40.3 y verifique Docker:

```bash
docker version
docker run --rm hello-world
```

No es necesario agregar repositorios CentOS/EL9 a Amazon Linux ni instalar un RPM de otra distribución. La instalación manual de Compose no se actualiza automáticamente: para actualizarla, cambie COMPOSE_VERSION y repita los comandos. No necesita instalar PHP, Composer, Node ni MySQL en el host Amazon Linux.

### 4. Preparar su carpeta y configuración en EC2

Antes de conectarse, asegúrese de que su proyecto Laravel y sus archivos estén en su repositorio Git, sin .env ni claves SSH. Ejecute en **AWS**:

```bash
mkdir -p ~/students/ana-perez
cd ~/students/ana-perez
git clone https://github.com/smartinezg2018/smartinezg-arquitectura-software.git app
cd app/laravelcourse
```

Estos comandos usan el proyecto de ejemplo de clase. Para su aplicación, cambie la URL y entre a la carpeta que contiene `artisan` y `composer.json`; si Laravel está en la raíz de su repositorio, use `cd app`. Todos los archivos del ejercicio se guardan en la raíz del proyecto Laravel, donde están artisan y composer.json. En el ejemplo es `~/students/ana-perez/app/laravelcourse`, no la raíz del repositorio. Los pasos 5, 6 y 8 muestran el contenido completo de los cuatro archivos Docker: créelos con esos nombres si faltan en su clon. Las correcciones de esta validación están en el workspace y en EC2; todavía no se han publicado en GitHub.

En Linux, los nombres de carpetas distinguen mayúsculas. Para el ejemplo de clase, corrija los nombres antes del build si el clon aún usa api y resources en minúsculas:

```bash
if [ -d app/Http/Controllers/api ] && [ ! -d app/Http/Controllers/Api ]; then
  mv app/Http/Controllers/api app/Http/Controllers/Api
fi
if [ -d app/Http/resources ] && [ ! -d app/Http/Resources ]; then
  mv app/Http/resources app/Http/Resources
fi
```

Esto alinea las carpetas con los namespaces `App\Http\Controllers\Api` y `App\Http\Resources`. Guarde también esa corrección en su propio repositorio para futuros clones.

Cree .env desde .env.example solo si todavía no existe. Si ya existe, edítelo sin sobrescribirlo:

```bash
test -f .env || cp .env.example .env
chmod 600 .env
nano .env
```

Si ya tiene un despliegue, conserve APP_KEY, DB_PASSWORD y MYSQL_ROOT_PASSWORD existentes; los marcadores siguientes son solo para la primera configuración. Configure una sola entrada de cada variable siguiente. Edite APP_NAME en su posición original, antes de MAIL_FROM_NAME y VITE_APP_NAME que pueden referenciarla; así Compose no advierte que falta al leer el archivo:

```dotenv
STUDENT_ID=ana-perez
APP_IMAGE=laravel-ana-perez:1.0
APP_PORT=8081
PMA_PORT=9081
MYSQL_PORT=33061

APP_NAME="Laravel Ana Perez"
APP_ENV=production
APP_DEBUG=false
APP_KEY=SE_CONFIGURA_EN_EL_PASO_5
APP_URL=http://100.54.107.127:8081

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=anaperez
DB_USERNAME=anaperez
DB_PASSWORD=REEMPLACE_CLAVE_APP
MYSQL_ROOT_PASSWORD=REEMPLACE_CLAVE_ROOT

SESSION_DRIVER=file
SESSION_SECURE_COOKIE=false
SESSION_COOKIE=ana-perez_session
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Genere **dos contraseñas diferentes** con `openssl rand -hex 24` y pegue los valores en DB_PASSWORD y MYSQL_ROOT_PASSWORD. La primera será de Laravel; la segunda se usa para administrar su MySQL desde phpMyAdmin. No utilice las contraseñas literales del ejemplo.

APP_IMAGE es el nombre de la imagen que construirá en esta instancia. Sustituya la IP de APP_URL por la IP actual de su grupo y utilice su puerto asignado. No suba .env ni claves SSH a Git.

### 5. Crear Dockerfile, .dockerignore y construir la imagen

La plantilla usa Laravel 12 y PHP 8.4. Si su aplicación exige otras extensiones o versiones, ajuste según composer.json/composer.lock. Si sus proveedores consultan MySQL durante el build, también debe adaptar ese comportamiento.

#### Dockerfile

Este Dockerfile instala únicamente dependencias PHP y sirve la carpeta public con Apache. El ejemplo usa vistas Blade, CSS estático en public/css y Bootstrap desde CDN; no necesita Node, npm ni Vite. Los comandos apt-get de este archivo se ejecutan **dentro de la imagen PHP basada en Debian**, no en el host Amazon Linux.

```dockerfile
FROM php:8.4-apache
RUN apt-get update && apt-get install -y --no-install-recommends \
      unzip \
    && docker-php-ext-install -j1 pdo_mysql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
    && composer check-platform-reqs --no-dev \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

RUN printf 'ServerName localhost\n' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && printf '%s\n' \
    '<VirtualHost *:80>' \
    '    DocumentRoot /var/www/html/public' \
    '    <Directory /var/www/html/public>' \
    '        AllowOverride All' \
    '        Require all granted' \
    '    </Directory>' \
    '</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

EXPOSE 80
CMD ["apache2-foreground"]
```

La imagen PHP ya incluye mbstring, DOM y las demás extensiones base de este proyecto. Solo compilamos pdo_mysql, con un proceso (-j1). unzip permite a Composer extraer las descargas; no instalamos git, libonig-dev, libzip-dev, libicu-dev ni compilamos zip o intl, porque este ejemplo no los exige. composer check-platform-reqs confirma los requisitos PHP instalados.

Conserve composer.lock en Git para instalar las versiones PHP del proyecto. La presencia de package.json en la plantilla Laravel no obliga a compilar recursos: las rutas de este ejemplo no utilizan la vista welcome ni su directiva @vite.

#### .dockerignore

```text
.git
.env
.env.*
!.env.example
vendor
node_modules
*.pem
*.tar
*.tar.gz
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
bootstrap/cache/*.php
public/hot

public/build
._*
.DS_Store
```

La configuración real se entrega al contenedor al ejecutarlo. No se copia .env dentro de la imagen. Verifique también que .env, claves SSH y archivos de imagen exportados estén excluidos de Git.

Construya antes de iniciar MySQL y phpMyAdmin para que no compitan por memoria durante el build. Si ya tiene su infraestructura activa, detenga solo sus contenedores antes de compilar y vuelva a iniciarlos al terminar.

Guarde Dockerfile y .dockerignore en la carpeta que contiene artisan y composer.json. Desde esa carpeta, construya la imagen con su identificador:

```bash
docker build -t laravel-ana-perez:1.0 .
docker run --rm laravel-ana-perez:1.0 php artisan key:generate --show
```

Copie la clave base64 mostrada en APP_KEY del .env. Coordine las compilaciones con sus compañeros para ejecutar una a la vez. El punto final de docker build indica el contexto de construcción. La clave no se incluye en la imagen. Conserve APP_KEY estable cuando actualice un despliegue existente.

### 6. Crear compose.infra.yaml: su MySQL y su phpMyAdmin

Guarde el siguiente YAML en compose.infra.yaml, en la misma carpeta de Laravel:

```yaml
name: ${STUDENT_ID:?Defina STUDENT_ID}-infra

services:
  mysql:
    image: mysql:8.4
    command: ["--performance-schema=OFF", "--innodb-buffer-pool-size=64M", "--max-connections=30"]
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD:?Defina MYSQL_ROOT_PASSWORD}
      MYSQL_ROOT_HOST: "%"
    ports:
      - "127.0.0.1:${MYSQL_PORT:?Defina MYSQL_PORT}:3306"
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - student
    healthcheck:
      test: ["CMD-SHELL", "MYSQL_PWD=\"$$MYSQL_ROOT_PASSWORD\" mysql -h 127.0.0.1 -u root -e 'SELECT 1' >/dev/null 2>&1"]
      interval: 5s
      timeout: 5s
      retries: 30
      start_period: 30s

  phpmyadmin:
    image: phpmyadmin:5.2
    restart: unless-stopped
    ports:
      - "${PMA_PORT:?Defina PMA_PORT}:80"
    environment:
      PMA_HOST: mysql
      PMA_PORT: "3306"
    depends_on:
      mysql:
        condition: service_healthy
    networks:
      - student

volumes:
  mysql_data:

networks:
  student:
    name: ${STUDENT_ID:?Defina STUDENT_ID}-net
```

La red se llama ana-perez-net y solo contiene los contenedores de Ana. Luis y Sara generan sus propias redes a partir de STUDENT_ID. Compose crea nombres individuales de contenedor y un volumen como ana-perez-infra_mysql_data; no agregue container_name ni nombres de volumen fijos.

PMA_HOST configura el MySQL al que se conecta phpMyAdmin. PMA_PORT dentro del contenedor vale 3306; la variable PMA_PORT del .env controla el puerto web publicado y vale 9081 para Ana. Son dos usos diferentes, definidos explícitamente en el YAML.

Los argumentos command desactivan Performance Schema, reducen el buffer InnoDB a 64 MiB y limitan las conexiones a 30 para esta práctica pequeña. No requieren cambiar datos ni credenciales y no son una configuración general para cargas de producción.

MYSQL_ROOT_HOST permite que phpMyAdmin entre como administrador desde otro contenedor de esta red. La contraseña root se escribe en la pantalla de inicio de sesión y no se entrega automáticamente a phpMyAdmin.

Valide e inicie:

```bash
docker compose -f compose.infra.yaml config --quiet
docker compose -f compose.infra.yaml up -d --wait --wait-timeout 240
docker compose -f compose.infra.yaml ps
```

Continúe si MySQL está saludable. Si falla, revise:

```bash
docker compose -f compose.infra.yaml logs --tail=100 mysql phpmyadmin
```

### 7. Crear su base de datos y usuario desde phpMyAdmin

Antes de abrir el navegador, compruebe el acceso a 9081. Si no está habilitado, abra desde su computador local otra terminal y deje este túnel activo (también servirá para Laravel):

```bash
ssh -i "students.pem" -o ExitOnForwardFailure=yes -N -L 127.0.0.1:18081:127.0.0.1:8081 -L 127.0.0.1:19081:127.0.0.1:9081 ec2-user@100.54.107.127
```

En ese caso abra **http://localhost:19081** para phpMyAdmin, configure `APP_URL=http://localhost:18081` en el `.env` de AWS antes del paso 8 y use **http://localhost:18081** para Laravel. Continúe los comandos remotos en su terminal SSH original. No necesita esperar al paso 10 para abrir el túnel.

1. Abra **http://100.54.107.127:9081**, reemplazando la IP por la de su instancia y el puerto por el de su fila. Si no está habilitado el acceso público, use el túnel del paso 10.
2. Inicie sesión con usuario **root** y su MYSQL_ROOT_PASSWORD.
3. Abra la pestaña **SQL** y cree la base y el usuario que usará Laravel con este bloque:

```sql
CREATE DATABASE IF NOT EXISTS `anaperez` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'anaperez'@'%' IDENTIFIED BY 'PEGUE_AQUI_SU_DB_PASSWORD';
GRANT ALL PRIVILEGES ON `anaperez`.* TO 'anaperez'@'%';
```

Reemplace usuario, base y contraseña de acuerdo con su .env. Este es el procedimiento SQL utilizado en la validación mediante phpMyAdmin. IF NOT EXISTS permite repetir el bloque sin eliminar datos, pero no cambia la contraseña ni el cotejamiento de objetos ya existentes. Su aplicación no usará root. No se necesita FLUSH PRIVILEGES después de CREATE USER / GRANT.

Cierre sesión y vuelva a ingresar con anaperez y DB_PASSWORD para comprobar acceso a su base. No cree las tablas a mano: las migraciones Laravel lo harán.

Las credenciales iniciales se aplican al crear un volumen vacío; editar MYSQL_ROOT_PASSWORD no modifica automáticamente un root ya existente. No borre su volumen para solucionar una contraseña si necesita conservar datos.

### 8. Crear compose.app.yaml: su aplicación Laravel

Guarde el siguiente YAML en compose.app.yaml, en la misma carpeta de Laravel:

```yaml
name: ${STUDENT_ID:?Defina STUDENT_ID}-app

services:
  app:
    image: ${APP_IMAGE:?Defina APP_IMAGE}
    restart: unless-stopped
    ports:
      - "${APP_PORT:?Defina APP_PORT}:80"
    environment:
      APP_NAME: ${APP_NAME:-Laravel}
      APP_ENV: production
      APP_DEBUG: "false"
      APP_KEY: ${APP_KEY:?Defina APP_KEY}
      APP_URL: ${APP_URL:?Defina APP_URL}
      LOG_CHANNEL: stderr
      DB_CONNECTION: mysql
      DB_HOST: mysql
      DB_PORT: "3306"
      DB_DATABASE: ${DB_DATABASE:?Defina DB_DATABASE}
      DB_USERNAME: ${DB_USERNAME:?Defina DB_USERNAME}
      DB_PASSWORD: ${DB_PASSWORD:?Defina DB_PASSWORD}
      SESSION_DRIVER: file
      SESSION_SECURE_COOKIE: "false"
      SESSION_COOKIE: ${STUDENT_ID:?Defina STUDENT_ID}_session
      CACHE_STORE: file
      QUEUE_CONNECTION: sync
    volumes:
      - app_storage:/var/www/html/storage
    networks:
      - student

volumes:
  app_storage:

networks:
  student:
    external: true
    name: ${STUDENT_ID:?Defina STUDENT_ID}-net
```

Este Compose solo ejecuta Laravel. Utiliza la red creada por **su propio** Compose de infraestructura. DB_HOST=mysql es el nombre del servicio en esa red; no es localhost ni la IP de EC2. La aplicación no recibe la contraseña root.

El servidor Apache sirve Laravel desde public y entrega sus archivos estáticos directamente. No hace falta ejecutar comandos npm para esta aplicación. Esta plantilla utiliza sesiones y caché en archivos y colas síncronas. Si el proyecto necesita correo u otras variables, agréguelas explícitamente a environment.

Con MySQL saludable y la base y el usuario creados, ejecute:

```bash
docker compose -f compose.app.yaml config --quiet
docker compose -f compose.app.yaml up -d
docker compose -f compose.app.yaml exec --user www-data app php artisan migrate --force
docker compose -f compose.app.yaml ps
```

La infraestructura debe estar activa antes de Laravel. depends_on no coordina dos proyectos Compose independientes.

### 9. Verificar el funcionamiento en EC2

Abra **http://100.54.107.127:8081** para Laravel y **http://100.54.107.127:9081** para phpMyAdmin. Reemplace los puertos por los de su fila. No añada /public: Apache ya usa esa carpeta como raíz.

En el ejemplo de clase, abra **/products/create**, cree un producto con nombre `Prueba AWS` y precio `123`, y compruebe que aparece en **/products** y **/api/products**. Pruebe también **/api/v2/products**, **/api/v3/products** y el carrito **/cart/add/121** seguido de **/cart**; el carrito de este ejemplo usa productos simulados (ID 121 para Tv samsung e ID 11 para Iphone), mientras el formulario y las API sí utilizan MySQL. Compruebe en phpMyAdmin que las migraciones crearon las tablas y que su operación guardó información.

Para comprobar persistencia después de guardar su producto, retire y recree únicamente sus contenedores, sin -v:

```bash
docker compose -f compose.app.yaml down
docker compose -f compose.infra.yaml down
docker compose -f compose.infra.yaml up -d --wait --wait-timeout 240
docker compose -f compose.app.yaml up -d
```

Abra otra vez /products y compruebe el mismo producto en phpMyAdmin. No vuelva a crear la base ni ejecute migrate:fresh; los datos ya están en el volumen MySQL.

La instalación usa HTTP, sin certificados HTTPS. Cada aplicación tiene una cookie de sesión con su identificador: las cookies del navegador no se separan por puerto y no deben tener el mismo nombre cuando las tres aplicaciones comparten IP.

La instancia necesita permitir Laravel 8081-8083 y phpMyAdmin 9081-9083 en su Security Group para acceso por IP pública. Si no tiene acceso a la consola AWS o esos puertos no están abiertos, use el túnel del paso 10. No abra los puertos MySQL 33061-33063 en AWS: están enlazados solo a 127.0.0.1.

### 10. Acceder mediante túnel SSH si faltan reglas de entrada

Ana ejecuta en **su computador local**, desde la carpeta de la clave:

```bash
ssh -i "students.pem" -o ExitOnForwardFailure=yes -N -L 127.0.0.1:18081:127.0.0.1:8081 -L 127.0.0.1:19081:127.0.0.1:9081 ec2-user@100.54.107.127
```

Mantenga la terminal abierta. Abra http://localhost:18081 para Laravel y http://localhost:19081 para phpMyAdmin. Estos números pertenecen a su computador y deben estar libres allí.

| Integrante | Puerto local del túnel Laravel | Destino en AWS | Puerto local del túnel phpMyAdmin | Destino en AWS |
|---|---|---|---|---|
| 1 | 18081 | 8081 | 19081 | 9081 |
| 2 | 18082 | 8082 | 19082 | 9082 |
| 3 | 18083 | 8083 | 19083 | 9083 |

Luis y Sara cambian ambos pares -L según su fila. Para probar exclusivamente por túnel, establezca APP_URL=http://localhost:18081 en el .env **de AWS** (o su puerto de túnel), y aplique el cambio con `docker compose -f compose.app.yaml up -d`.

-N abre el túnel sin mostrar una consola. Utilice otra terminal SSH normal para los comandos de AWS. Los puertos de túnel pertenecen a su computador, no agregan contenedores ni puertos al servidor.

## Administración de su despliegue

### 11. Actualizar una versión construida en AWS

Suba sus cambios a Git. En su carpeta individual de AWS:

```bash
git pull --ff-only
docker compose -f compose.app.yaml stop
docker compose -f compose.infra.yaml stop
docker build -t laravel-ana-perez:1.0 .
docker compose -f compose.infra.yaml up -d --wait --wait-timeout 240
docker compose -f compose.app.yaml up -d --force-recreate
docker compose -f compose.app.yaml exec --user www-data app php artisan migrate --force
```

Esta secuencia detiene únicamente su despliegue durante la actualización, para liberar memoria antes del build. Si el build falla, conserve la etiqueta previa y vuelva a iniciar infra y app para recuperar su versión anterior.

Mantenga APP_KEY y sus credenciales. No ejecute migrate:fresh para actualizar: elimina tablas y datos. El volumen MySQL permanece independiente de la imagen Laravel. Si completó el bonus Docker Hub, puede actualizar publicando y descargando una etiqueta nueva en lugar de reconstruir en AWS.

### 12. Diagnosticar y detener solo sus contenedores

Ejecute dentro de **su carpeta de AWS**, con **su .env**:

```bash
docker compose -f compose.app.yaml logs --tail=100 app
docker compose -f compose.infra.yaml logs --tail=100 mysql phpmyadmin
docker stats --no-stream
```

Si un puerto está ocupado, confirme su fila y su .env; no detenga contenedores de otro estudiante. Si no resuelve mysql, compruebe su STUDENT_ID y que ambos Compose utilizan su red individual. Si rechaza credenciales, pruebe su usuario en su phpMyAdmin y revise DB_PASSWORD y DB_DATABASE.

Detener temporalmente sus tres contenedores:

```bash
docker compose -f compose.app.yaml stop
docker compose -f compose.infra.yaml stop
```

Iniciar otra vez, en orden:

```bash
docker compose -f compose.infra.yaml up -d --wait --wait-timeout 240
docker compose -f compose.app.yaml up -d
```

Retirar sus contenedores conservando datos:

```bash
docker compose -f compose.app.yaml down
docker compose -f compose.infra.yaml down
```

Down sin -v conserva sus volúmenes. Su infraestructura crea la red: retire primero app y luego infra. No elimine volúmenes si necesita conservar datos. El contenido de storage (sesiones, logs y archivos guardados allí) se conserva en app_storage; el código de la aplicación está en la imagen y en su clon Git; si su proyecto muestra archivos subidos, configure y verifique storage:link para ese despliegue.

No use docker system prune -a, docker volume prune ni comandos que eliminen todos los contenedores en la instancia compartida. Administre únicamente sus dos proyectos Compose.

Los nueve contenedores del grupo comparten CPU, memoria y disco; consulte docker stats y coordine las tareas si falta memoria. Cada uno tiene recursos Docker propios, pero compartir ec2-user y acceso al daemon permite afectar otros proyectos: los nombres evitan conflictos, no constituyen aislamiento de permisos del host.

restart: unless-stopped permite reiniciar contenedores que estaban activos cuando se reinicia el host. No coordina el arranque entre dos proyectos Compose: compruebe su MySQL antes de probar Laravel. Detener sus contenedores no detiene EC2. No apague la instancia mientras sus compañeros la usan. Si la IP cambia al detener e iniciar EC2, actualice conexiones y APP_URL recreando su aplicación.

## Entrega

Cada estudiante entrega:

- Repositorio Git con Dockerfile, .dockerignore, compose.infra.yaml y compose.app.yaml; sin .env ni claves.
- URL AWS y puertos propios de Laravel y phpMyAdmin, o evidencia del acceso por túnel si los puertos públicos no están habilitados.
- Evidencia de su base y usuario en su MySQL y de datos guardados por Laravel.
- Evidencia de que los datos sobreviven a down seguido de up sin -v.

El grupo demuestra que sus tres despliegues individuales pueden coexistir en la misma instancia.

Preguntas:

1. ¿Qué diferencia hay entre imagen, contenedor y volumen?
2. ¿Por qué se repite el puerto interno 3306, pero cada puerto publicado debe ser distinto?
3. ¿Por qué DB_HOST es mysql y no localhost?
4. ¿Cómo conectan sus dos proyectos Compose?
5. ¿Qué diferencia hay entre GitHub y Docker Hub?
6. ¿Por qué importa la arquitectura de una imagen?
7. ¿Qué ocurre si elimina su volumen MySQL?

## Referencias y alcance de la verificación

- AWS, Docker en Amazon Linux: https://docs.aws.amazon.com/serverless-application-model/latest/developerguide/install-docker.html
- Docker, instalación del complemento Compose: https://docs.docker.com/compose/install/linux/
- Docker, redes entre proyectos Compose: https://docs.docker.com/compose/how-tos/networking/
- phpMyAdmin, imagen y variables: https://github.com/phpmyadmin/docker
- Docker Hub, publicar imágenes: https://docs.docker.com/docker-hub/repos/manage/hub-images/push/
- Laravel 12, despliegue: https://laravel.com/docs/12.x/deployment

Validación realizada en EC2, IP 100.54.107.127, Amazon Linux 2023, x86_64, 913 MiB de RAM utilizable y disco de 8 GiB, sin swap. Docker 25.0.16 y Compose v2.40.3. Se clonó el repositorio y se aplicaron los archivos corregidos de este ejercicio a la copia remota. El .env se preparó mediante automatización: las dos contraseñas tienen 48 caracteres hexadecimales, equivalentes a openssl rand -hex 24, y APP_KEY se generó con el comando artisan mostrado. Los pasos manuales de edición y phpMyAdmin describen cómo obtener esa misma configuración.

Se verificaron el build PHP mínimo y composer check-platform-reqs, la salud de MySQL, el acceso público a Laravel y phpMyAdmin, el login root y de la cuenta de aplicación en phpMyAdmin, la creación de base y usuario mediante su formulario SQL, las seis migraciones y el guardado de un producto mediante POST con token CSRF. Las páginas /, /about, /contact y /products, las API de productos v1/v2/v3 y el carrito con sesión respondieron correctamente. El producto se consultó también desde phpMyAdmin y permaneció después de down/up sin -v. También se comprobó acceso HTTP por el túnel SSH a 18081 y 19081. Se verificó el ciclo de actualización desde docker compose stop hasta build, up y migrate --force, sin nuevas migraciones. No se verificó git pull de las correcciones: se aplicaron directamente al clon remoto y aún no están publicadas en GitHub. Se validó la sintaxis Compose de las tres filas sin iniciar los despliegues de Luis o Sara, y se comprobó que .env no está en la imagen y que Laravel no recibe MYSQL_ROOT_PASSWORD. En la auditoría posterior se compararon los cuatro archivos del tutorial, el workspace y EC2 por SHA-256; coincidieron. Se cotejaron el .env y las variables reales del contenedor, los permisos del usuario MySQL, el cotejamiento de la base y los ajustes de memoria. Los comandos de corrección de carpetas se ejecutaron dos veces sobre un snapshot nuevo de Git HEAD en Linux. La automatización verificó HTTP y datos; no se realizó una revisión visual en navegador.

Después de esas pruebas, el consumo observado fue aproximadamente 216 MiB para MySQL, 53 MiB para phpMyAdmin y 56 MiB para Laravel, con unos 230 MiB disponibles en el host y 3,2 GiB libres en disco. Son mediciones de un despliegue con carga ligera, no una garantía para tres estudiantes simultáneos. No se reprodujo el bonus Docker Hub.


## Bonus opcional. Publicar en Docker Hub y desplegar la imagen en AWS

Este bonus supone que Docker ya está instalado y funciona en su computador local. No es necesario para completar la práctica principal. Su objetivo es construir un artefacto local, publicarlo en un registro y ejecutar ese mismo artefacto en otra máquina.

### B.1. Crear su cuenta y repositorio

Cree su cuenta en https://hub.docker.com y un repositorio público, por ejemplo **laravel-ana-perez**. Utilice su usuario real Docker Hub, en minúsculas, donde aparece TU_USUARIO_DOCKERHUB. Cada estudiante publica en su propia cuenta.

Una imagen pública contiene el código copiado por el Dockerfile. Compruebe que .dockerignore excluye .env y claves SSH y que el código no contiene contraseñas. Solo publique proyectos que pueda compartir.

### B.2. Construir para la arquitectura de AWS, desde su computador local

Desde su computador local, consulte la arquitectura remota:

```bash
ssh -i /RUTA/A/students.pem ec2-user@100.54.107.127 uname -m
```

x86_64 corresponde a linux/amd64; aarch64 corresponde a linux/arm64. Obtenga el proyecto en su computador local desde Git y entre a la carpeta que contiene el Dockerfile. Si su computador y AWS tienen la misma arquitectura, construya allí la imagen:

```bash
docker build -t TU_USUARIO_DOCKERHUB/laravel-ana-perez:1.0 .
```

Si necesita construir explícitamente para una instancia x86_64:

```bash
docker build --platform linux/amd64 -t TU_USUARIO_DOCKERHUB/laravel-ana-perez:1.0 .
```

Para una instancia ARM use linux/arm64. Una arquitectura distinta a la de su computador requiere soporte de compilación cruzada/emulación; --platform no lo instala. Compruebe que el build termina y que la imagen funciona antes de publicarla.

### B.3. Hacer login y push en su computador local

Utilice una carpeta Docker de configuración propia, fuera del proyecto. Ana ejecuta:

```bash
mkdir -p ~/docker-config-ana-perez
chmod 700 ~/docker-config-ana-perez
docker --config ~/docker-config-ana-perez login --username TU_USUARIO_DOCKERHUB
docker --config ~/docker-config-ana-perez push TU_USUARIO_DOCKERHUB/laravel-ana-perez:1.0
```

Cuando solicite contraseña, ingrese un token personal de acceso de Docker Hub con permiso de publicación, creado desde la configuración de su cuenta. No escriba el token en comandos, .env ni Git. Verifique la etiqueta 1.0 en el repositorio Docker Hub.

--config indica dónde Docker guarda y lee su configuración de autenticación. Use la misma carpeta en login y push. En computadores separados puede usar simplemente docker login y docker push; la carpeta explícita también funciona y muestra cómo separar configuraciones.

### B.4. Hacer pull y exponer su puerto en AWS

En AWS, mantenga su MySQL y phpMyAdmin activos y configure el .env de su aplicación:

```dotenv
APP_IMAGE=TU_USUARIO_DOCKERHUB/laravel-ana-perez:1.0
APP_PORT=8081
APP_URL=http://100.54.107.127:8081
```

Mantenga STUDENT_ID, APP_KEY y las credenciales de su base AWS. Luis y Sara utilizan sus puertos y nombres asignados. Si accede por túnel, conserve APP_URL con su URL local del túnel.

Desde su carpeta individual de aplicación en AWS:

```bash
docker pull TU_USUARIO_DOCKERHUB/laravel-ana-perez:1.0
docker compose -f compose.app.yaml up -d
docker compose -f compose.app.yaml exec --user www-data app php artisan migrate --force
docker compose -f compose.app.yaml ps
```

Una imagen pública puede descargarse sin login en AWS. La aplicación se ejecuta a partir de la imagen descargada; no necesita reconstruirla allí. El Compose existente expone **8081:80** mediante APP_PORT. Verifique http://100.54.107.127:8081, o su URL de túnel del paso 10. No cree otro contenedor con docker run en el mismo puerto mientras el Compose esté activo.

### B.5. Opcional: credenciales diferentes en el mismo ec2-user

Si usa una imagen privada o necesita autenticar sus descargas, utilice una configuración remota propia:

```bash
mkdir -p ~/docker-config-ana-perez
chmod 700 ~/docker-config-ana-perez
docker --config ~/docker-config-ana-perez login --username TU_USUARIO_DOCKERHUB
docker --config ~/docker-config-ana-perez pull TU_USUARIO_DOCKERHUB/laravel-ana-perez:1.0
docker --config ~/docker-config-ana-perez compose -f compose.app.yaml up -d
```

Cada estudiante utiliza su carpeta y repite --config en los comandos que necesitan esas credenciales, incluidos los pull de Compose:

```bash
docker --config ~/docker-config-ana-perez compose -f compose.app.yaml pull
```

Si todos usan ec2-user y ejecutan login sin --config, comparten ~/.docker/config.json y pueden reemplazar el login de otro estudiante para Docker Hub. Las carpetas separadas evitan ese reemplazo accidental, pero no protegen las credenciales frente a personas que comparten el mismo usuario Linux. No copie su configuración local de credenciales al repositorio ni a la imagen.

### B.6. Publicar una actualización

En su computador local, construya una etiqueta nueva para la arquitectura correcta y publíquela:

```bash
docker build --platform linux/amd64 -t TU_USUARIO_DOCKERHUB/laravel-ana-perez:1.1 .
docker --config ~/docker-config-ana-perez push TU_USUARIO_DOCKERHUB/laravel-ana-perez:1.1
```

En AWS cambie APP_IMAGE a la etiqueta 1.1 y ejecute pull, up y migrate como antes. Use --config si autentica las descargas. Conserve APP_KEY y los datos MySQL. Como evidencia opcional, entregue el enlace Docker Hub, la etiqueta y el despliegue AWS desde esa imagen.

Referencia de configuración Docker CLI: https://docs.docker.com/reference/cli/docker/
