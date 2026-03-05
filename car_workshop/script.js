document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('appointmentForm');
    const container = document.getElementById('vehicles_container');
    const addBtn = document.getElementById('add_vehicle_btn');
    let vehicleIndex = 1;

    
    const today = new Date().toISOString().split('T')[0];

    function setupMechanicFetch(box) {
        const dateInput = box.querySelector('.appointment-date');
        const mechanicSelect = box.querySelector('.mechanic-select');
        const mechanicHelp = box.querySelector('.help-mechanic');

        if (dateInput) {
            dateInput.setAttribute('min', today);
            dateInput.addEventListener('change', function () {
                const selectedDate = this.value;
                if (!selectedDate) {
                    mechanicSelect.innerHTML = '<option value="">-- Please select a date first --</option>';
                    mechanicSelect.disabled = true;
                    return;
                }

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
                            mechanicHelp.innerHTML = '<span class="error">All mechanics booked.</span>';
                            mechanicSelect.disabled = true;
                        } else {
                            mechanicHelp.textContent = 'Choose an available mechanic.';
                            mechanicSelect.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching mechanics:', error);
                        mechanicHelp.innerHTML = '<span class="error">Failed to load mechanics.</span>';
                    });
            });
        }
    }

    setupMechanicFetch(document.getElementById('vehicle_box_1'));


    addBtn.addEventListener('click', function () {
        vehicleIndex++;

        const newBox = document.createElement('div');
        newBox.className = 'vehicle-box';
        newBox.id = `vehicle_box_${vehicleIndex}`;
        newBox.dataset.index = vehicleIndex;
        newBox.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); margin-bottom: 1rem; padding-bottom: 0.5rem;">
                <h3 style="margin-bottom: 0; border: none; padding: 0; color: var(--primary-color);">Vehicle ${vehicleIndex}</h3>
                <button type="button" class="btn btn-secondary remove-vehicle-btn" style="width: auto; padding: 0.25rem 0.75rem; font-size: 0.85rem; background-color: var(--error-color); color: white; border: none;">Remove</button>
            </div>
            <div class="form-group">
                <label for="car_license_no_${vehicleIndex}">Car License Number <span class="required">*</span></label>
                <input type="text" id="car_license_no_${vehicleIndex}" name="car_license_no[]"
                    placeholder="e.g. Dhaka Metro-Ga 12-3456" required>
                <span class="error error-license"></span>
            </div>

            <div class="form-group">
                <label for="car_engine_no_${vehicleIndex}">Car Engine Number (Digits only) <span
                        class="required">*</span></label>
                <input type="text" id="car_engine_no_${vehicleIndex}" name="car_engine_no[]" placeholder="e.g. 987654321"
                    required>
                <span class="error error-engine"></span>
            </div>

            <div class="form-group">
                <label for="appointment_date_${vehicleIndex}">Appointment Date <span class="required">*</span></label>
                <input type="date" id="appointment_date_${vehicleIndex}" name="appointment_date[]" class="appointment-date" required>
                <span class="error error-date"></span>
            </div>

            <div class="form-group mb-0">
                <label for="mechanic_id_${vehicleIndex}">Select Mechanic <span class="required">*</span></label>
                <select id="mechanic_id_${vehicleIndex}" name="mechanic_id[]" class="mechanic-select" required disabled>
                    <option value="">-- Please select a date first --</option>
                </select>
                <div class="help-text help-mechanic">Choose a date to see available mechanics. Limits can vary by mechanic and date.</div>
                <span class="error error-mechanic"></span>
            </div>
        `;

        container.appendChild(newBox);

        setupMechanicFetch(newBox);

        newBox.querySelector('.remove-vehicle-btn').addEventListener('click', function () {
            newBox.remove();

            const allBoxes = container.querySelectorAll('.vehicle-box');
            allBoxes.forEach((box, idx) => {
                const displayIndex = idx + 1;
                box.dataset.index = displayIndex;
                box.id = `vehicle_box_${displayIndex}`;
                box.querySelector('h3').textContent = `Vehicle ${displayIndex}`;
            });
            vehicleIndex = allBoxes.length;
        });
    });

    form.addEventListener('submit', function (e) {
        let isValid = true;

        document.querySelectorAll('.error:not(.help-mechanic .error)').forEach(el => el.textContent = '');

        const name = document.getElementById('client_name').value.trim();
        if (name === '') {
            document.getElementById('nameError').textContent = 'Name is required.';
            isValid = false;
        }

        const phone = document.getElementById('client_phone').value.trim();
        if (!/^\d+$/.test(phone)) {
            document.getElementById('phoneError').textContent = 'Phone must contain numbers only.';
            isValid = false;
        }

        const boxes = document.querySelectorAll('.vehicle-box');
        boxes.forEach(box => {
            const licenseInput = box.querySelector('input[name="car_license_no[]"]');
            const engineInput = box.querySelector('input[name="car_engine_no[]"]');
            const dateInput = box.querySelector('input[name="appointment_date[]"]');
            const mechanicSelect = box.querySelector('select[name="mechanic_id[]"]');

            if (licenseInput.value.trim() === '') {
                box.querySelector('.error-license').textContent = 'License number is required.';
                isValid = false;
            }

            const engine = engineInput.value.trim();
            if (engine === '') {
                box.querySelector('.error-engine').textContent = 'Engine number is required.';
                isValid = false;
            } else if (!/^\d+$/.test(engine)) {
                box.querySelector('.error-engine').textContent = 'Numbers only.';
                isValid = false;
            }

            const date = dateInput.value;
            if (date === '') {
                box.querySelector('.error-date').textContent = 'Date is required.';
                isValid = false;
            } else if (isNaN(Date.parse(date))) {
                box.querySelector('.error-date').textContent = 'Invalid date.';
                isValid = false;
            }

            if (mechanicSelect.value === '') {
                box.querySelector('.error-mechanic').textContent = 'Mechanic is required.';
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
        }
    });
});
