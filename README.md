# Travel Booking System PHP Simple--- Launched app https://alonsosamuel.infinityfree.me

## Castellano

Aplicacion web de gestion y reserva de viajes compartidos desarrollada en PHP clasico. El proyecto permite publicar trayectos, reservar plazas, gestionar perfiles de usuario, procesar pagos y administrar la operativa general desde un panel interno.

Proyecto de clase realizado por Samuel B.A. como entrega academica orientada a demostrar el desarrollo de una aplicacion web funcional de reservas de viajes.

### Resumen del proyecto

- La plataforma cubre el flujo principal de una aplicacion de movilidad compartida, desde el registro de usuarios hasta la reserva y valoracion de viajes.
- Esta entrega esta orientada a un entorno local y academico-profesional, con una estructura sencilla de mantener y desplegar en XAMPP.
- El sistema incluye un area publica para usuarios y un area administrativa para supervisar viajes, reservas, usuarios, comisiones e informes.

### Funcionalidades principales

- Registro, inicio de sesion, recuperacion de cuenta y gestion de perfil de usuario.
- Publicacion, edicion y administracion de viajes con control de plazas disponibles.
- Busqueda de viajes, detalle de trayectos y flujo de reserva de plazas.
- Gestion de reservas activas, cancelaciones y seguimiento del historial del usuario.
- Valoraciones entre usuarios para reforzar la confianza dentro de la plataforma.
- Integracion con Stripe para simular un proceso de pago mas cercano a un escenario real.
- Panel de administracion con gestion de usuarios, viajes, reservas, comisiones e informes.

### Stack tecnico

- Backend y renderizado construidos en PHP con estructura clasica basada en archivos.
- Base de datos MySQL con volcado listo para importar desde `database/travel_booking_system_full.sql`.
- Integracion de pagos mediante la libreria oficial de Stripe incluida con Composer.
- Despliegue pensado para Apache/XAMPP, sin necesidad de Node.js ni de una arquitectura MVC.

### Estructura funcional

- `auth/` y `actions/` concentran autenticacion, formularios y acciones del negocio.
- `trips/`, `account/` y `payments/` agrupan la experiencia principal del usuario final.
- `admin/` y `reports/` recogen la operativa interna y la generacion de informes.
- `includes/` y `config/` centralizan utilidades compartidas, conexion y configuracion del sistema.

### Puesta en marcha

1. Copia el proyecto dentro de tu directorio de XAMPP o del servidor Apache local.
2. Importa `database/travel_booking_system_full.sql` en MySQL para cargar la estructura y los datos base.
3. Revisa `config/stripe.local.php` si necesitas configurar claves locales para el flujo de pagos.
4. Accede a la aplicacion desde `http://localhost/Travel_Booking_System_PHP_Simple/` o desde la ruta local equivalente.

### Alcance de la entrega

- Esta version representa una entrega funcional completa para demostracion, evaluacion academica o presentacion de portfolio.
- El proyecto prioriza claridad estructural, cobertura del flujo principal y facilidad de despliegue en entorno local.
- El archivo de referencia principal para la base de datos es `database/travel_booking_system_full.sql`.

## English

Travel Booking System PHP Simple is a classic PHP web application for managing and booking shared trips. The project allows users to publish rides, reserve seats, manage profiles, process payments, and handle operations through an internal admin panel.

This is a class project created by Samuel B.A. as an academic delivery focused on demonstrating the development of a functional trip booking web application.

### Project Summary

- The platform covers the core flow of a shared mobility application, from user registration to trip booking and post-trip reviews.
- This delivery is aimed at local and academic-professional environments, with a straightforward structure that is easy to maintain and run on XAMPP.
- The system includes a public user-facing area and an administrative area to supervise trips, reservations, users, commissions, and reports.

### Main Features

- User registration, sign-in, account recovery, and profile management.
- Trip publishing, editing, and management with seat availability control.
- Trip discovery, trip detail views, and seat reservation flow.
- Active reservation management, cancellations, and user booking history tracking.
- User reviews to strengthen trust and credibility within the platform.
- Stripe integration to simulate a payment process closer to a real-world scenario.
- Administration panel with user, trip, reservation, commission, and reporting management.

### Technical Stack

- Backend and rendering built in PHP using a classic file-based structure.
- MySQL database with an import-ready dump available at `database/travel_booking_system_full.sql`.
- Payment integration through the official Stripe library installed with Composer.
- Deployment designed for Apache/XAMPP, with no Node.js requirement and no MVC framework dependency.

### Functional Areas

- `auth/` and `actions/` contain authentication flows, form handlers, and core business actions.
- `trips/`, `account/`, and `payments/` cover the main end-user experience.
- `admin/` and `reports/` handle internal operations and report generation.
- `includes/` and `config/` centralize shared utilities, connectivity, and system configuration.

### Getting Started

1. Copy the project into your XAMPP directory or local Apache server workspace.
2. Import `database/travel_booking_system_full.sql` into MySQL to load the schema and baseline data.
3. Review `config/stripe.local.php` if you need to configure local keys for the payment flow.
4. Open the application at `http://localhost/Travel_Booking_System_PHP_Simple/` or the equivalent local route.

### Delivery Scope

- This version represents a complete functional delivery for demos, academic evaluation, or portfolio presentation.
- The project prioritizes structural clarity, coverage of the main user flow, and easy deployment in a local environment.
- The main reference database file is `database/travel_booking_system_full.sql`.
