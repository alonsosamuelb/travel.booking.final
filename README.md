# Travel Booking System

An academic PHP and MySQL application for shared trips. It includes trip publication and search, seat reservations and cancellations, user profiles and reviews, and an administration area for trips, users, and bookings.

## Code walkthrough

- [Trip pages](trips/) and [account pages](account/) are the main user interfaces.
- [Reservation creation](actions/create_reservation.php) connects the booking form to database writes.
- [Database configuration](includes/database.php) centralizes the PDO connection.
- [Payment configuration](includes/payments.php) reads a local Stripe configuration. The project includes a Stripe Checkout integration for sandbox demonstrations.

## Local setup

1. Place the repository in a local Apache document root (for example, XAMPP's `htdocs`). Enable PHP with PDO MySQL, start MySQL, and run `composer install` in the project directory.
2. Review the [SQL export](database/travel_booking_system_full.sql). Import its schema into a fresh, disposable MySQL instance and use synthetic records for your demo. The export includes table drops and account seed data.
3. Configure `DB_HOST`, `DB_PORT`, `DB_DATABASE` (normally `travel_booking_system`), `DB_USERNAME`, and `DB_PASSWORD` in the PHP process environment.
4. Open the repository's local URL in your browser. For checkout testing, copy `config/stripe.example.php` to the ignored `config/stripe.local.php` and supply your own test-mode configuration locally.

## Demo walkthrough

With synthetic accounts, publish a trip as one user and look it up as another. Follow the reservation form and expected seat-count change, then review the reservation in the user account. Keep payments in Stripe test mode. This project is coursework intended for local evaluation.
