    </main>
    <footer class="site-footer">
        <div class="footer-top">
            <div class="shell">
                <div class="footer-top-inner">
                    <span class="footer-eyebrow">Travel Booking</span>
                    <div class="footer-kicker">Plataforma de reservas de viajes compartidos</div>
                    <h2 class="footer-heading">Planificar y reservar viajes, mucho mas sencillo.</h2>
                    <p class="footer-lead">Explora rutas, reserva plazas y gestiona tus trayectos con una experiencia clara, rapida y pensada para viajeros y conductores.</p>
                </div>
            </div>
        </div>
        <div class="footer-main">
            <div class="shell">
                <div class="footer-grid">
                    <div>
                        <div class="footer-brand">Travel Booking System</div>
                        <p class="footer-text">Una plataforma moderna para publicar viajes, descubrir trayectos y gestionar reservas de forma sencilla tanto en entorno local como en un despliegue real.</p>
                    </div>
                    <div>
                        <div class="footer-title">Empresa</div>
                        <ul class="footer-list">
                            <li>Travel Booking</li>
                            <li>Reservas y publicacion de viajes</li>
                            <li>Gran Via 128, Madrid</li>
                            <li>Lun - Vie, 09:00 - 18:00</li>
                        </ul>
                    </div>
                    <div>
                        <div class="footer-title">Contacto</div>
                        <ul class="footer-list">
                            <li>soporte@travelbooking.local</li>
                            <li>+34 900 123 456</li>
                            <li>Atencion al viajero y al conductor</li>
                            <li>Colaboraciones y consultas</li>
                        </ul>
                    </div>
                    <div>
                        <div class="footer-title">Proyecto</div>
                        <p class="footer-text">Desarrollado por Samuel Buitrago Alonso como proyecto intermodular de reservas adaptado al dominio de viajes compartidos.</p>
                    </div>
                </div>
                <div class="footer-divider"></div>
                <div class="footer-bottom">
                    <span>© <?= date('Y') ?> Travel Booking. Todos los derechos reservados.</span>
                    <div class="footer-bottom-links">
                        <a href="<?= app_url('/pages/sobre-nosotros.php') ?>">Sobre nosotros</a>
                        <a href="<?= app_url('/pages/contacto.php') ?>">Contacto</a>
                        <a href="<?= app_url('/pages/privacidad.php') ?>">Privacidad</a>
                        <a href="<?= app_url('/pages/condiciones.php') ?>">Condiciones</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navUserMenu = document.querySelector('.nav-user-menu');
            const steppers = document.querySelectorAll('[data-stepper]');
            const passwordToggles = document.querySelectorAll('[data-password-toggle]');
            const reservationForms = document.querySelectorAll('[data-reservation-form]');

            if (navUserMenu) {
                document.addEventListener('click', function (event) {
                    if (!navUserMenu.open) {
                        return;
                    }

                    if (!navUserMenu.contains(event.target)) {
                        navUserMenu.open = false;
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && navUserMenu.open) {
                        navUserMenu.open = false;
                    }
                });
            }

            steppers.forEach(function (stepper) {
                const input = stepper.querySelector('.seat-stepper-input');
                const decrease = stepper.querySelector('[data-stepper-decrease]');
                const increase = stepper.querySelector('[data-stepper-increase]');

                if (!input || !decrease || !increase) {
                    return;
                }

                const syncButtons = function () {
                    const min = Number(input.min || 1);
                    const max = Number(input.max || 99);
                    const value = Number(input.value || min);

                    decrease.disabled = value <= min;
                    increase.disabled = value >= max;
                };

                decrease.addEventListener('click', function () {
                    const min = Number(input.min || 1);
                    const value = Math.max(min, Number(input.value || min) - 1);
                    input.value = String(value);
                    syncButtons();
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });

                increase.addEventListener('click', function () {
                    const min = Number(input.min || 1);
                    const max = Number(input.max || 99);
                    const value = Math.min(max, Number(input.value || min) + 1);
                    input.value = String(value);
                    syncButtons();
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });

                syncButtons();
            });

            passwordToggles.forEach(function (toggle) {
                const wrapper = toggle.closest('.password-input-wrap');
                const input = wrapper ? wrapper.querySelector('[data-password-input]') : null;

                if (!input) {
                    return;
                }

                toggle.addEventListener('click', function () {
                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';
                    toggle.textContent = isPassword ? 'Ocultar' : 'Ver';
                });
            });

            reservationForms.forEach(function (form) {
                const input = form.querySelector('.seat-stepper-input');
                const feedback = form.parentElement ? form.parentElement.querySelector('.trip-booking-feedback') : null;

                if (!input || !feedback) {
                    return;
                }

                const validateReservation = function () {
                    if (form.dataset.admin === '1') {
                        feedback.hidden = true;
                        feedback.textContent = '';
                        return true;
                    }

                    const seats = Number(input.value || 1);
                    const sameDayCount = Number(form.dataset.sameDayCount || 0);
                    const dailyLimit = Number(form.dataset.dailyLimit || 2);
                    const sameVehicleWeekCount = Number(form.dataset.sameVehicleWeekCount || 0);
                    const weeklySeats = Number(form.dataset.weeklySeats || 0);
                    const weeklySeatLimit = Number(form.dataset.weeklySeatLimit || 4);
                    let message = '';

                    if (sameDayCount >= dailyLimit) {
                        message = 'Ya has alcanzado el maximo de reservas activas para ese dia.';
                    } else if (sameVehicleWeekCount >= 1) {
                        message = 'Ya tienes un viaje del mismo tipo reservado en esa semana.';
                    } else if (weeklySeats + seats > weeklySeatLimit) {
                        message = 'Con esta reserva superarias el limite semanal de plazas.';
                    }

                    if (message !== '') {
                        feedback.hidden = false;
                        feedback.textContent = message;
                        return false;
                    }

                    feedback.hidden = true;
                    feedback.textContent = '';
                    return true;
                };

                form.addEventListener('submit', function (event) {
                    if (!validateReservation()) {
                        event.preventDefault();
                    }
                });

                input.addEventListener('input', validateReservation);
                input.addEventListener('change', validateReservation);
                validateReservation();
            });

            const adminReservationForms = document.querySelectorAll('[data-admin-reservation-form]');

            adminReservationForms.forEach(function (form) {
                const tripSelect = form.querySelector('[data-admin-trip-select]');
                const seatsInput = form.querySelector('[data-admin-seats-input]');
                const priceInput = form.querySelector('[data-admin-price-input]');
                const totalOutput = form.querySelector('[data-admin-total-output]');

                if (!tripSelect || !seatsInput || !priceInput || !totalOutput) {
                    return;
                }

                const formatMoney = function (amount) {
                    return amount.toLocaleString('es-ES', { style: 'currency', currency: 'EUR' });
                };

                const syncAdminReservationPricing = function (refreshPriceFromTrip) {
                    const selectedOption = tripSelect.options[tripSelect.selectedIndex];
                    const tripPrice = Number(selectedOption ? (selectedOption.dataset.price || 0) : 0);

                    if (refreshPriceFromTrip && tripPrice > 0) {
                        priceInput.value = tripPrice.toFixed(2);
                    }

                    totalOutput.value = formatMoney(1);
                };

                tripSelect.addEventListener('change', function () {
                    syncAdminReservationPricing(true);
                });

                seatsInput.addEventListener('input', function () {
                    syncAdminReservationPricing(false);
                });

                priceInput.addEventListener('input', function () {
                    syncAdminReservationPricing(false);
                });

                syncAdminReservationPricing(priceInput.value === '');
            });
        });
    </script>
</body>
</html>
