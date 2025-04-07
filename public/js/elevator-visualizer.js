/**
 * Elevator Visualization Component
 * Provides a visual representation of elevator movement and status
 */

class ElevatorVisualizer {
    constructor(elementId, config = {}) {
        this.container = document.getElementById(elementId);
        if (!this.container) {
            console.error(`Element with ID ${elementId} not found`);
            return;
        }

        this.config = Object.assign({
            minFloor: 0,
            maxFloor: 10,
            floorHeight: 30, // pixels per floor
            animationSpeed: 1000, // ms
            showFloorMarkers: true
        }, config);

        this.currentFloor = this.config.minFloor;
        this.doorOpen = false;
        this.moving = false;
        this.direction = null;

        this.init();
    }

    init() {
        // Create visualization structure
        this.container.innerHTML = `
            <div class="elevator-visualization">
                <div class="elevator-shaft">
                    <div class="elevator-car">
                        <div class="elevator-door elevator-door-left"></div>
                        <div class="elevator-door elevator-door-right"></div>
                        <div class="floor-indicator">0</div>
                    </div>
                </div>
                ${this.config.showFloorMarkers ? '<div class="floor-markers"></div>' : ''}
            </div>
        `;

        // Get elements
        this.elevatorCar = this.container.querySelector('.elevator-car');
        this.floorIndicator = this.container.querySelector('.floor-indicator');

        // Add floor markers if enabled
        if (this.config.showFloorMarkers) {
            this.addFloorMarkers();
        }

        // Set initial position
        this.updatePosition(this.currentFloor);
    }

    addFloorMarkers() {
        const markersContainer = this.container.querySelector('.floor-markers');
        const totalFloors = this.config.maxFloor - this.config.minFloor + 1;

        for (let i = this.config.minFloor; i <= this.config.maxFloor; i++) {
            const marker = document.createElement('div');
            marker.className = 'floor-marker';
            marker.innerHTML = `
                <div class="floor-marker-line"></div>
                <div class="floor-marker-label">${i}</div>
            `;
            markersContainer.appendChild(marker);
        }
    }

    updatePosition(floor) {
        if (floor < this.config.minFloor || floor > this.config.maxFloor) {
            console.error(`Floor ${floor} is out of range`);
            return;
        }

        const totalFloors = this.config.maxFloor - this.config.minFloor;
        const floorPosition = floor - this.config.minFloor;
        const positionPercentage = (floorPosition / totalFloors) * 100;

        // Update elevator car position
        this.elevatorCar.style.bottom = `${positionPercentage}%`;

        // Update floor indicator
        this.floorIndicator.textContent = floor;

        this.currentFloor = floor;
    }

    moveTo(floor) {
        if (this.moving) return;

        this.moving = true;
        this.direction = floor > this.currentFloor ? 'up' : 'down';

        // Close doors if open
        if (this.doorOpen) {
            this.closeDoors();
            setTimeout(() => {
                this.startMoving(floor);
            }, 500);
        } else {
            this.startMoving(floor);
        }
    }

    startMoving(floor) {
        // Update position with animation
        this.updatePosition(floor);

        // Set moving state during animation
        setTimeout(() => {
            this.moving = false;
            this.direction = null;
            this.openDoors();
        }, this.config.animationSpeed);
    }

    openDoors() {
        if (this.doorOpen) return;

        this.elevatorCar.classList.add('elevator-door-open');
        this.doorOpen = true;
    }

    closeDoors() {
        if (!this.doorOpen) return;

        this.elevatorCar.classList.remove('elevator-door-open');
        this.doorOpen = false;
    }

    updateStatus(status) {
        // Update based on elevator status object
        if (status.current_floor !== undefined) {
            if (this.currentFloor !== status.current_floor) {
                this.updatePosition(status.current_floor);
            }
        }

        if (status.door_open !== undefined) {
            if (this.doorOpen !== status.door_open) {
                if (status.door_open) {
                    this.openDoors();
                } else {
                    this.closeDoors();
                }
            }
        }

        this.moving = status.moving || false;
        this.direction = status.direction || null;
    }
}

// Initialize elevator visualizers when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Find all elevator visualization containers
    document.querySelectorAll('.elevator-visualizer').forEach(container => {
        const elevatorId = container.dataset.elevatorId;
        const minFloor = parseInt(container.dataset.minFloor || 0);
        const maxFloor = parseInt(container.dataset.maxFloor || 10);

        // Create visualizer instance
        const visualizer = new ElevatorVisualizer(container.id, {
            minFloor: minFloor,
            maxFloor: maxFloor
        });

        // Store instance in global object for later access
        if (!window.elevatorVisualizers) {
            window.elevatorVisualizers = {};
        }

        window.elevatorVisualizers[elevatorId] = visualizer;
    });

    // Connect visualizers to real-time updates
    if (window.elevatorVisualizers && typeof refreshElevatorStatuses === 'function') {
        const originalUpdateFunction = updateElevatorUI;

        // Override the update function to also update visualizers
        window.updateElevatorUI = function(data) {
            // Call original function
            originalUpdateFunction(data);

            // Update visualizers
            if (data.elevators) {
                data.elevators.forEach(elevator => {
                    const visualizer = window.elevatorVisualizers[elevator.id];
                    if (visualizer) {
                        visualizer.updateStatus(elevator);
                    }
                });
            }
        };
    }
});
