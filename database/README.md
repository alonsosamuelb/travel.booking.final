# Base de datos del proyecto

Este directorio contiene el volcado completo actual de la base de datos del proyecto:

- `travel_booking_system_full.sql`

Ese archivo incluye la estructura y los datos esenciales de la base `travel_booking_system` y debe considerarse la referencia principal para restaurar el proyecto en otra instalacion.

La version actual del SQL se ha simplificado para entrega:

- sin comentarios generados por `mysqldump`
- sin datos de `activity_logs`
- sin tabla de migraciones
- manteniendo usuarios, viajes, reservas, opiniones y tablas auxiliares necesarias para el funcionamiento

Los antiguos scripts incrementales se han retirado para evitar duplicidades y confusiones en la entrega.
