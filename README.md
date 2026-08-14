
# Polar Yii - интеграция с Polar для выгрузки и анализа личных тренировок [WIP]

Yii2 application with Docker (PHP + PostgreSQL).

## Requirements

- Docker & Docker Compose

## Quick start

Copy environment config and adjust if needed:

```bash
cp .env.example .env
```

```bash
docker compose up --build
```

Open [http://localhost:8082/login](http://localhost:8082/login) (default `APP_PORT=8082`)

## Environment

Configuration lives in `[.env](.env)` at the project root. Docker Compose loads it automatically.


| Variable                                                    | Description                                                                          |
| ----------------------------------------------------------- | ------------------------------------------------------------------------------------ |
| `APP_PORT`                                                  | Host port for the PHP/nginx container                                                |
| `COOKIE_VALIDATION_KEY`                                     | Yii CSRF/cookie signing key                                                          |
| `RATE_LIMIT_MAX_ATTEMPTS`                                   | Max failed login/signup attempts per IP (default: 5)                                 |
| `RATE_LIMIT_WINDOW`                                         | Rate limit window in seconds (default: 900)                                          |
| `DB_*`                                                      | Database connection for Yii2 and PostgreSQL container                                |
| `POSTGRES_HOST_PORT`                                        | Host port for PostgreSQL                                                             |
| `POLAR_CLIENT_ID` / `POLAR_CLIENT_SECRET`                   | Polar AccessLink OAuth client credentials                                            |
| `POLAR_REDIRECT_URI`                                        | OAuth callback, e.g. `http://localhost:8082/polar/callback` (must match Polar admin) |
| `POLAR_AUTH_URL` / `POLAR_TOKEN_URL` / `POLAR_API_BASE_URL` | Polar OAuth and AccessLink endpoints                                                 |


Use `[.env.example](.env.example)` as a template. `.env` is gitignored.

## Project structure

```
polar-yii/
├── docker-compose.yaml
├── docker/
│   ├── db/
│   └── php/          # Dockerfile, nginx.conf, entrypoint
└── app/              # Yii2 application (bind-mounted)
    ├── controllers/  # thin HTTP layer
    ├── services/     # business logic (AuthService, ProfileService, PasswordValidatorService)
    ├── repositories/ # data access (UserRepository)
    ├── models/       # AR entities & form models
    └── migrations/
```



## Polar Documentation

[https://www.polar.com/accesslink-api/](https://www.polar.com/accesslink-api/)

## Polar API Flow
1) GET https://flow.polar.com/oauth2/authorization - receive code 
2) POST https://polarremote.com/v2/oauth2/token - receive access token 
3) POST /v3/users register the user before being able to access its data
4) access data (e.g. GET /v3/exercises)
