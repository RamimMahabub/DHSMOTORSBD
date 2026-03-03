document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('appointmentForm');
    const dateInput = document.getElementById('appointment_date');
    const mechanicSelect = document.getElementById('mechanic_id');
    const mechanicHelp = document.getElementById('mechanicHelp');

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    dateInput.setAttribute('min', today);

    // Fetch mechanics when date changes
    dateInput.addEventListener('change', function () {
        const selectedDate = this.value;
        if (!selectedDate) {
            mechanicSelect.innerHTML = '<option value="">-- Please select a date first --</option>';
            mechanicSelect.disabled = true;
            return;
        }

        // Fetch available mechanics for the selected date
        fetch(`get_mechanics.php?date=${selectedDate}`)
            .then(response => response.json())
            .then(data => {
                mechanicSelect.innerHTML = '<option value="">-- Select a Mechanic --</option>';

                if (data.error) {
                    mechanicHelp.innerHTML = `<span class="error">${data.error}</span>`;
                    mechanicSelect.disabled = true;
                    return;
                }

                let availableCount = 0;

                data.forEach(mechanic => {
                    const option = document.createElement('option');
                    option.value = mechanic.id;

                    const freeSpaces = parseInt(mechanic.free_places);

                    if (freeSpaces > 0) {
                        option.textContent = `${mechanic.name} (${freeSpaces} free places left)`;
                        availableCount++;
                    } else {
                        option.textContent = `${mechanic.name} (Fully booked)`;
                        option.disabled = true;
                    }

                    mechanicSelect.appendChild(option);
                });

                if (availableCount === 0) {
                    mechanicHelp.innerHTML = '<span class="error">All mechanics are fully booked on this date. Please select another date.</span>';
                    mechanicSelect.disabled = true;
                } else {
                    mechanicHelp.textContent = 'Choose your preferred available mechanic.';
                    mechanicSelect.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error fetching mechanics:', error);
                mechanicHelp.innerHTML = '<span class="error">Failed to load mechanics. Please try again.</span>';
            });
    });

    // Form Validation
    form.addEventListener('submit', function (e) {
        let isValid = true;

        // Reset errors
        document.querySelectorAll('.error:not(#mechanicHelp .error)').forEach(el => el.textContent = '');

        // Validate Name
        const name = document.getElementById('client_name').value.trim();
        if (name === '') {
            document.getElementById('nameError').textContent = 'Name is required.';
            isValid = false;
        }

        // Validate Phone (numbers only)
        const phone = document.getElementById('client_phone').value.trim();
        if (!/^\d+$/.test(phone)) {
            document.getElementById('phoneError').textContent = 'Phone must contain numbers only.';
            isValid = false;
        }

        // Validate License
        const license = document.getElementById('car_license_no').value.trim();
        if (license === '') {
            document.getElementById('licenseError').textContent = 'License number is required.';
            isValid = false;
        }

        // Validate Engine No (numbers only)
        const engine = document.getElementById('car_engine_no').value.trim();
        if (!/^\d+$/.test(engine)) {
            document.getElementById('engineError').textContent = 'Engine number must contain numbers only.';
            isValid = false;
        }

        // Validate Date
        const date = document.getElementById('appointment_date').value;
        if (date === '') {
            document.getElementById('dateError').textContent = 'Appointment date is required.';
            isValid = false;
        } else if (isNaN(Date.parse(date))) {
            document.getElementById('dateError').textContent = 'Invalid date format.';
            isValid = false;
        }

        // Validate Mechanic
        const mechanic = document.getElementById('mechanic_id').value;
        if (mechanic === '') {
            document.getElementById('mechanicError').textContent = 'Please select a mechanic.';
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
        }
    });
});
