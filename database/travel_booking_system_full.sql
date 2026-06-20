CREATE DATABASE IF NOT EXISTS `travel_booking_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `travel_booking_system`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `trip_reviews`;
DROP TABLE IF EXISTS `reservations`;
DROP TABLE IF EXISTS `trips`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `api_token` varchar(64) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_deleted_at` (`deleted_at`),
  KEY `idx_users_api_token` (`api_token`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` VALUES (2,'Paco Soler','paco@gmail.com','$2y$10$0ZlWoGOF4mJZFJujb92w3./oPrF0avtUS4MFiM1bYL.ZwfVlg9wWq',NULL,'+34 600 333 444','user',NULL,'2026-05-10 16:56:06','2026-06-18 21:56:18'),(5,'samuel alonso','samuel@gmail.com','$2y$10$0ZlWoGOF4mJZFJujb92w3./oPrF0avtUS4MFiM1bYL.ZwfVlg9wWq',NULL,'633165791','admin',NULL,'2026-05-10 18:00:26','2026-06-18 21:56:18'),(6,'viajero','viajero@gmail.com','$2y$10$0ZlWoGOF4mJZFJujb92w3./oPrF0avtUS4MFiM1bYL.ZwfVlg9wWq',NULL,'722663272','user',NULL,'2026-06-15 22:27:40','2026-06-18 21:56:18'),(7,'usuario','usuario@gmail.com','$2y$10$0ZlWoGOF4mJZFJujb92w3./oPrF0avtUS4MFiM1bYL.ZwfVlg9wWq',NULL,NULL,'user',NULL,'2026-06-16 05:22:20','2026-06-18 21:56:18');

CREATE TABLE `trips` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `origin` varchar(120) NOT NULL,
  `destination` varchar(120) NOT NULL,
  `departure_at` datetime NOT NULL,
  `vehicle` varchar(100) NOT NULL,
  `available_seats` int(10) unsigned NOT NULL,
  `price_per_seat` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'published',
  `creator_user_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_trips_departure_at` (`departure_at`),
  KEY `idx_trips_route` (`origin`,`destination`),
  KEY `idx_trips_creator_user_id` (`creator_user_id`),
  CONSTRAINT `fk_trip_creator_user` FOREIGN KEY (`creator_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `trips` VALUES (1,'Madrid to Valencia Weekend Ride','Shared intercity trip with luggage space and direct route.','Madrid','Valencia','2026-05-13 16:56:00','Toyota Corolla',4,20.00,'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80','published',2,'2026-05-10 16:56:06','2026-06-16 05:20:53'),(2,'Barcelona Coastal Escape','Morning departure with scenic route and one stop.','Barcelona','Tarragona','2026-05-15 16:56:00','Seat Leon',3,20.00,'https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=900&q=80','published',2,'2026-05-10 16:56:06','2026-06-16 05:21:08'),(6,'Road to Albacete','increíble viaje de la mano de los mejores','elche','Albacete','2026-05-30 14:19:00','coche',5,15.00,'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=900&q=80','published',7,'2026-05-23 14:19:47','2026-06-19 05:47:22'),(7,'road to Sweden','a fantastic trip with your Friends','elche','Sorsele','2026-07-31 17:49:00','car',4,100.00,NULL,'published',7,'2026-05-23 17:50:08','2026-06-19 05:47:22'),(8,'road to Switzerland','Fantastic trip and views','elche','lauterbrunnen','2026-06-12 08:45:00','van',7,150.00,NULL,'published',7,'2026-05-23 17:51:12','2026-06-19 05:47:22'),(10,'Costa Brava en coche','Escapada comoda para dos o tres personas con equipaje ligero.','Barcelona','Girona','2026-07-18 08:30:00','Seat Ibiza',3,10.00,'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=1200&q=80','published',2,'2026-06-11 17:35:34','2026-06-16 05:20:44'),(14,'Ruta Alicante-Madrid en coche','Viaje directo con parada breve para cafe y descanso.','Alicante','Madrid','2026-06-11 09:30:00','Toyota Corolla',4,30.00,'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=80','published',2,'2026-06-11 17:37:17','2026-06-19 05:19:37'),(15,'Van a Valencia para grupo','Trayecto para grupo pequeno con espacio extra para maletas.','Murcia','Valencia','2026-07-18 09:00:00','Mercedes Van',6,20.00,'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=1200&q=80','published',2,'2026-06-11 17:37:17','2026-06-16 05:20:09'),(16,'Van al Pirineo','Salida temprana para viaje de senderismo con material.','Zaragoza','Jaca','2026-07-18 07:45:00','Ford Transit Van',7,34.00,'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=1200&q=80','published',2,'2026-06-11 17:37:17','2026-06-16 05:20:20'),(17,'Van a la costa del sol','Viaje amplio para familia o amigos con equipaje grande.','Sevilla','Malaga','2026-07-18 12:00:00','Renault Van',8,20.00,'https://images.unsplash.com/photo-1489824904134-891ab64532f1?auto=format&fit=crop&w=1200&q=80','published',2,'2026-06-11 17:37:17','2026-06-16 05:19:49'),(18,'viaje de prueba','viaje a la  provincia y ciudad de valencia','elche','valencia','2026-06-10 18:00:00','coche',3,12.00,NULL,'published',6,'2026-06-16 00:45:57','2026-06-19 05:19:37');

CREATE TABLE `reservations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `trip_id` int(10) unsigned NOT NULL,
  `reservation_date` datetime NOT NULL,
  `seats_reserved` int(10) unsigned NOT NULL,
  `price_per_seat` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `travel_role` enum('passenger','driver') NOT NULL DEFAULT 'passenger',
  `notes` varchar(255) DEFAULT NULL,
  `status` enum('active','cancelled','completed') NOT NULL DEFAULT 'active',
  `payment_status` enum('pending','paid','refunded') NOT NULL DEFAULT 'pending',
  `stripe_checkout_session_id` varchar(255) DEFAULT NULL,
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_reservation_trip` (`trip_id`),
  KEY `idx_reservations_status` (`status`),
  KEY `idx_reservations_user_trip` (`user_id`,`trip_id`),
  CONSTRAINT `fk_reservation_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`),
  CONSTRAINT `fk_reservation_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `reservations` VALUES (1,2,1,'2026-05-10 16:56:06',1,20.00,1.00,'passenger','Window seat preferred','cancelled','refunded',NULL,NULL,NULL,'2026-05-10 16:56:06','2026-06-18 17:15:17'),(2,2,2,'2026-05-10 16:56:06',1,20.00,1.00,'driver','Will bring small luggage','cancelled','refunded',NULL,NULL,NULL,'2026-05-10 16:56:06','2026-06-18 17:15:17'),(4,2,2,'2026-05-10 17:37:00',3,6.00,1.00,'passenger',NULL,'completed','paid',NULL,NULL,'2026-06-16 13:32:43','2026-05-10 17:37:51','2026-06-18 15:24:21'),(5,5,10,'2026-06-12 16:41:01',1,10.00,1.00,'passenger',NULL,'cancelled','refunded',NULL,NULL,NULL,'2026-06-12 16:41:01','2026-06-18 17:15:17'),(6,6,15,'2026-06-15 22:29:38',2,20.00,1.00,'passenger','llevo a mi perro','active','pending',NULL,NULL,NULL,'2026-06-15 22:29:38','2026-06-18 17:15:17'),(7,2,18,'2026-06-16 04:52:51',1,12.00,1.00,'passenger',NULL,'completed','paid',NULL,NULL,'2026-06-19 05:19:37','2026-06-16 04:52:51','2026-06-19 05:19:37'),(8,6,8,'2026-06-16 05:16:17',1,150.00,1.00,'passenger',NULL,'completed','paid','cs_test_a1NYJTtE29ItTvGHCDKqYVNQKgFGaTIC5aV9EZbh3pG8BwPFFLUysFqB93','pi_3Tjm3uCjyQcHFWHW0hE46AbS','2026-06-18 21:57:32','2026-06-16 05:16:17','2026-06-19 05:19:37'),(9,7,18,'2026-06-16 05:22:51',1,12.00,1.00,'passenger',NULL,'cancelled','pending',NULL,NULL,NULL,'2026-06-16 05:22:51','2026-06-18 15:24:21'),(10,6,14,'2026-06-18 22:12:56',1,30.00,1.00,'passenger',NULL,'completed','paid','cs_test_a1T8aCYxMXTHbzoGX1DjbzKK0S7xK1Rsa9EEItn6OzIOlUspGqguHx2cHK',NULL,'2026-06-19 05:19:37','2026-06-18 22:12:56','2026-06-19 05:19:37'),(11,2,7,'2026-06-19 13:51:04',1,100.00,1.00,'passenger',NULL,'active','paid','cs_test_a1S02wwq774xihc1H6aGPL3qgVIV6pmBrs7aiuVRAfchRXeR7Z52CPzzF1','pi_3Tk0xLCjyQcHFWHW1YZeTLJH','2026-06-19 13:51:32','2026-06-19 13:51:04','2026-06-19 13:51:44');

CREATE TABLE `trip_reviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` int(10) unsigned NOT NULL,
  `trip_id` int(10) unsigned NOT NULL,
  `reviewer_user_id` int(10) unsigned NOT NULL,
  `reviewee_user_id` int(10) unsigned NOT NULL,
  `reviewer_role` enum('passenger','driver') NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `comment` varchar(600) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_reservation_reviewer` (`reservation_id`,`reviewer_user_id`),
  KEY `idx_reviewee_user` (`reviewee_user_id`),
  KEY `idx_trip_reviews_trip` (`trip_id`),
  KEY `fk_trip_reviews_reviewer` (`reviewer_user_id`),
  CONSTRAINT `fk_trip_reviews_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_trip_reviews_reviewee` FOREIGN KEY (`reviewee_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_trip_reviews_reviewer` FOREIGN KEY (`reviewer_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_trip_reviews_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `trip_reviews` VALUES (1,7,18,2,6,'passenger',5,'Todo claro y puntual. El viaje fue muy comodo y bien organizado.','2026-06-19 05:19:37','2026-06-19 05:19:37'),(2,7,18,6,2,'driver',5,'Pasajero puntual y respetuoso durante todo el trayecto.','2026-06-19 05:19:37','2026-06-19 05:19:37'),(3,8,8,6,5,'passenger',4,'Buen conductor y comunicacion facil. Repetiria sin problema.','2026-06-19 05:19:37','2026-06-19 05:19:37'),(4,8,8,5,6,'driver',5,'Viajero muy correcto y facil de coordinar para la salida.','2026-06-19 05:19:37','2026-06-19 05:19:37'),(5,10,14,6,2,'passenger',5,'Trayecto muy agradable. Buena comunicacion y hora cumplida.','2026-06-19 05:19:37','2026-06-19 05:19:37'),(6,10,14,2,6,'driver',4,'Pasajero amable y puntual. Todo fue segun lo previsto.','2026-06-19 05:19:37','2026-06-19 05:19:37');

CREATE TABLE `activity_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(10) unsigned DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_activity_log_user` (`user_id`),
  KEY `idx_activity_logs_created_at` (`created_at`),
  KEY `idx_activity_logs_action` (`action`),
  CONSTRAINT `fk_activity_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_resets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `token` varchar(120) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
