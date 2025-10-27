# Online Bookstore

This is a full-stack online bookstore application built with a React frontend, FastAPI backend, and PostgreSQL database. The application allows users to browse, search, and purchase books online.

## Project Structure

```
online-bookstore
├── backend          # FastAPI backend
│   ├── app         # Application code
│   ├── requirements.txt  # Python dependencies
│   └── Dockerfile   # Dockerfile for backend
├── frontend         # React frontend
│   ├── public       # Public assets
│   ├── src          # Source code
│   ├── package.json  # npm dependencies
│   ├── tsconfig.json # TypeScript configuration
│   └── Dockerfile    # Dockerfile for frontend
├── nginx            # Nginx configuration
│   └── nginx.conf   # Nginx configuration file
├── db               # Database initialization
│   └── init.sql     # SQL commands to set up the database
├── docker-compose.yml # Docker Compose configuration
└── README.md        # Project documentation
```

## Setup Instructions

1. **Clone the repository:**
   ```
   git clone <repository-url>
   cd online-bookstore
   ```

2. **Build and run the application:**
   ```
   docker-compose up --build
   ```

3. **Access the application:**
   - Frontend: `http://localhost:3000`
   - Backend: `http://localhost:8000`
   - Admin interface (if applicable): `http://localhost:8000/admin`

## Default Admin Credentials

- **Username:** admin
- **Password:** password

## Commands

- To stop the application, run:
  ```
  docker-compose down
  ```

- To view logs, run:
  ```
  docker-compose logs
  ```

## Technologies Used

- **Frontend:** React, TypeScript
- **Backend:** FastAPI, SQLAlchemy
- **Database:** PostgreSQL
- **Containerization:** Docker, Docker Compose
- **Reverse Proxy:** Nginx (optional)

## License

This project is licensed under the MIT License.