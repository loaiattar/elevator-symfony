/**
 * real-time updates and interactive controls for the elevator system
 */

document.addEventListener('DOMContentLoaded', function() {
    // initialize the elevator system UI
    initElevatorSystem();
});

/**
 * initialize the elevator system UI components
 */
function initElevatorSystem() {
    // setup auto-refresh for elevator status
    setupAutoRefresh();

    // setup API-based interactions
    setupApiInteractions();

    // setup form validations
    setupFormValidations();

    console.log('Elevator System UI initialized');
}

/**
 * setup auto-refresh functionality for real-time updates
 */
function setupAutoRefresh() {
    // check if we're on the elevator system show page
    if (document.querySelector('.elevator-status-container')) {
        // auto-refresh the elevator statuses every 3 seconds
        setInterval(function() {
            refreshElevatorStatuses();
        }, 3000);
    }
}

/**
 * refresh elevator statuses by AJAX
 */
function refreshElevatorStatuses() {
    const systemId = document.querySelector('meta[name="elevator-system-id"]')?.content;

    if (!systemId) return;

    fetch(`/api/elevator-system/${systemId}/status`)
        .then(response => response.json())
        .then(data => {
            updateElevatorUI(data);
        })
        .catch(error => {
            console.error('Error fetching elevator statuses:', error);
        });
}

/**
 * update the elevator UI with the latest data
 */
function updateElevatorUI(data) {
    // update system status
    const systemStatusElement = document.querySelector('.system-status');
    if (systemStatusElement && data.system_info) {
        systemStatusElement.textContent = data.system_info.status;
        systemStatusElement.className = 'system-status badge ' +
            (data.system_info.status === 'operational' ? 'bg-success' : 'bg-danger');
    }

    // update each elevator
    if (data.elevators) {
        data.elevators.forEach(elevator => {
            const elevatorCard = document.querySelector(`.elevator-card[data-elevator-id="${elevator.id}"]`);
            if (!elevatorCard) return;

            // update status badge
            const statusBadge = elevatorCard.querySelector('.elevator-status');
            if (statusBadge) {
                let statusText = 'Idle';
                let statusClass = 'bg-success';

                if (elevator.moving) {
                    statusText = `Moving ${elevator.direction}`;
                    statusClass = 'bg-warning';
                } else if (elevator.door_open) {
                    statusText = 'Door Open';
                    statusClass = 'bg-info';
                }

                statusBadge.textContent = statusText;
                statusBadge.className = 'elevator-status badge ' + statusClass;
            }

            // update current floor
            const floorElement = elevatorCard.querySelector('.elevator-floor');
            if (floorElement) {
                floorElement.textContent = elevator.current_floor;
            }

            // update queue
            const queueElement = elevatorCard.querySelector('.elevator-queue');
            if (queueElement) {
                queueElement.textContent = elevator.queue.length > 0
                    ? elevator.queue.join(', ')
                    : 'Empty';
            }

            // update buttons state
            updateButtonStates(elevatorCard, elevator, data.system_info.status);
        });
    }
}

/**
 * update button states based on elevator status
 */
function updateButtonStates(elevatorCard, elevator, systemStatus) {
    // move up button
    const moveUpBtn = elevatorCard.querySelector('.btn-move-up');
    if (moveUpBtn) {
        moveUpBtn.disabled = elevator.moving || systemStatus === 'emergency' ||
            elevator.current_floor >= elevator.max_floor;
    }

    // move down button
    const moveDownBtn = elevatorCard.querySelector('.btn-move-down');
    if (moveDownBtn) {
        moveDownBtn.disabled = elevator.moving || systemStatus === 'emergency' ||
            elevator.current_floor <= elevator.min_floor;
    }

    // open door button
    const openDoorBtn = elevatorCard.querySelector('.btn-open-door');
    if (openDoorBtn) {
        openDoorBtn.disabled = elevator.door_open || elevator.moving || systemStatus === 'emergency';
    }

    // close door button
    const closeDoorBtn = elevatorCard.querySelector('.btn-close-door');
    if (closeDoorBtn) {
        closeDoorBtn.disabled = !elevator.door_open || elevator.moving || systemStatus === 'emergency';
    }
}

/**
 * setup API-based interactions for elevator controls
 */
function setupApiInteractions() {
    // add destination form
    document.querySelectorAll('.add-destination-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const elevatorId = this.dataset.elevatorId;
            const floorInput = this.querySelector('input[name="floor"]');
            const floor = floorInput.value;

            if (!floor) return;

            fetch(`/api/elevator/${elevatorId}/add-destination`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ floor: parseInt(floor) }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', data.message);
                        floorInput.value = '';
                        // Refresh elevator status
                        setTimeout(refreshElevatorStatuses, 500);
                    } else {
                        showNotification('danger', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error adding destination:', error);
                    showNotification('danger', 'Failed to add destination');
                });
        });
    });
}

/**
 * setup form validations
 */
function setupFormValidations() {
    // new elevator system form
    const newSystemForm = document.querySelector('#new-elevator-system-form');
    if (newSystemForm) {
        newSystemForm.addEventListener('submit', function(e) {
            const numElevators = parseInt(this.querySelector('#num_elevators').value);
            const maxFloor = parseInt(this.querySelector('#max_floor').value);
            const minFloor = parseInt(this.querySelector('#min_floor').value);

            if (numElevators < 1 || numElevators > 10) {
                e.preventDefault();
                showNotification('danger', 'Number of elevators must be between 1 and 10');
                return;
            }

            if (maxFloor <= minFloor) {
                e.preventDefault();
                showNotification('danger', 'Maximum floor must be greater than minimum floor');
                return;
            }
        });
    }

    // C
    // call elevator form
    const callElevatorForm = document.querySelector('#call-elevator-form');
    if (callElevatorForm) {
        callElevatorForm.addEventListener('submit', function(e) {
            const floor = parseInt(this.querySelector('#floor').value);
            const minFloor = parseInt(this.querySelector('#floor').min);
            const maxFloor = parseInt(this.querySelector('#floor').max);

            if (isNaN(floor) || floor < minFloor || floor > maxFloor) {
                e.preventDefault();
                showNotification('danger', `Floor must be between ${minFloor} and ${maxFloor}`);

            }
        });
    }
}

/**
 * show a notification message
 */
function showNotification(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);

    // auto-dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => alertDiv.remove(), 150);
    }, 5000);
}
