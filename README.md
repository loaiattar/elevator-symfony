# Elevator Simulation System

## Project Overview
This project is a Symfony 7.2 application that simulates an elevator system. It allows users to create, manage, and interact with virtual elevator systems through a web interface. The application is structured using standard Symfony MVC architecture with Doctrine ORM for database management.

## Features
- **Elevator Simulation**: Realistic elevator movement with door operations and queue management
- **Smart Allocation Algorithm**: Efficient elevator dispatch based on position, direction, and status
- **Real-time Monitoring**: System status updates every 5 seconds
- **Emergency Controls**: Safety functionality for system-wide and individual elevator control
- **Statistics Tracking**: Analytics on elevator usage patterns

## Tech Stack
- **Framework**: Symfony 7.2
- **PHP Version**: 8.2+
- **Database**: SQLite (configured in .env)
- **Front-end**: Bootstrap 5.3 with minimal JavaScript
- **ORM**: Doctrine 3.3

## System Requirements
- PHP 8.2 or higher
- Composer
- SQLite 3
- Web server (Apache/Nginx) or Symfony server for development

## Project Structure
```
elevator-symfony/
├── bin/                       # Symfony console and executables
├── config/                    # Configuration files
├── migrations/                # Database migrations
├── public/                    # Web root directory
│   ├── css/                   # CSS stylesheets
│   ├── js/                    # JavaScript files
│   └── index.php              # Front controller
├── src/                       # Application source code
│   ├── Controller/            # Controllers
│   │   ├── Api/               # API controllers
│   │   ├── ElevatorController.php
│   │   └── ElevatorSystemController.php
│   ├── Entity/                # Doctrine entities
│   │   ├── Elevator.php
│   │   └── ElevatorSystem.php
│   ├── Repository/            # Doctrine repositories
│   ├── Service/               # Business logic services
│   │   ├── ElevatorService.php
│   │   └── ElevatorSystemService.php
│   └── Kernel.php             # Application kernel
├── templates/                 # Twig templates
├── var/                       # Cache, logs, and database
├── vendor/                    # Dependencies
└── .env                       # Environment configuration
```

## Core Components

### Entities
1. **ElevatorSystem**: Represents a building with multiple elevators
   * Contains properties like min/max floors, system status
   * Manages a collection of Elevator instances
   * Tracks call history for statistics

2. **Elevator**: Represents an individual elevator
   * Tracks current floor, movement status, direction
   * Manages door status (open/closed)
   * Maintains a queue of destination floors

### Controllers
1. **ElevatorSystemController**: Manages elevator systems
   * Creating new systems with specified parameters
   * Viewing system status and statistics
   * Calling elevators to specific floors
   * Emergency control (stop all/resume)

2. **ElevatorController**: Controls individual elevators
   * Moving up/down
   * Opening/closing doors
   * Adding destinations to queue
   * Emergency stop for individual elevators

### Services
1. **ElevatorSystemService**: Business logic for elevator systems
   * Algorithm for selecting the best elevator to respond to a call
   * System-wide operations like emergency stop
   * Tracking call statistics

2. **ElevatorService**: Business logic for individual elevators
   * Movement control
   * Door operations
   * Queue management
3. **ElevatorControlService**:
* The ElevatorControlService is responsible for controlling
  the elevator operations. It interacts with the hardware layer
  through the adapter pattern, which makes it flexible enough
  to work with different types of elevator hardware systems.
* Control Movement
* Door Operations
* Simulation of Movement
* Integration with Multiple Hardware Types
  *Example:*
* // Assuming BasicHardwareAdapter or AdvancedHardwareAdapter has been initialized
  $elevatorService = new ElevatorControlService($hardwareAdapter);

// Move the elevator to floor 5
$elevatorService->moveToFloor(5);
* This service abstracts the complexity of controlling an elevator by interacting
  with hardware through a unified interface, making it easy to manage elevator
  operations regardless of the

## Installation & Setup

### Step 1: Clone the repository
```bash
git clone [repository-url] elevator-symfony
cd elevator-symfony
```

### Step 2: Install dependencies
```bash
composer install
```

### Step 3: Set up the database
```bash
# Create the database
php bin/console doctrine:database:create

# Run migrations
php bin/console doctrine:migrations:migrate
```

### Step 4: Start the server
```bash
# Using Symfony CLI
symfony serve

# OR using PHP's built-in server
php -S 127.0.0.1:8000 -t public/
```

## API Endpoints

The application provides a RESTful API for interacting with the elevator system:

### Elevator System Endpoints
- `GET /api/v1/systems` - List all systems
- `GET /api/v1/systems/{id}` - Get system details
- `POST /api/v1/systems` - Create a new system
- `POST /api/v1/systems/{id}/call` - Call an elevator

### Elevator Endpoints
- `GET /api/v1/elevators/{id}` - Get elevator details
- `POST /api/v1/elevators/{id}/move` - Move elevator
- `POST /api/v1/elevators/{id}/door` - Open/close doors

## Smart Allocation Algorithm

The elevator selection algorithm factors in:
1. Current distance to requested floor
2. Direction of travel relative to request
3. Queue length and current load
4. Operational status of the elevator

This ensures efficient distribution of elevator calls throughout the building.

## Design Decisions

- **SQLite Database**: Chosen for simplicity and ease of setup without external dependencies
- **MVC Architecture**: Clean separation of concerns following Symfony best practices
- **Service Layer**: Business logic extracted to services for better testability
- **API Support**: REST API endpoints for potential external integration
- **Real-time Updates**: Page refresh every 5 seconds provides near real-time status



## Testing

Run the test suite with:
```bash
php bin/phpunit
```



