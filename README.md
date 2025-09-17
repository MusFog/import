# Запуск проєкту (Docker + Laravel + Vite + MySQL)

## Вимоги
- Встановлені **Docker** і **Docker Compose v2** (`docker compose`).
- Вільні порти на хості: **8000** (Laravel), **5173** (Vite), **3316** (MySQL).

## Структура
```
project-root/
├─ dockerfiles/
│  └─ php.dockerfile
├─ laravel/
└─ docker-compose.yml
```

## 1) Підготовка `.env`
Скопіюйте приклад і відредагуйте налаштування БД:
```bash
cp laravel/.env.example laravel/.env
```

У `laravel/.env` перевірте:
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=database_test_server
DB_USERNAME=root
DB_PASSWORD=root
```

## 2) Пуск контейнерів
```bash
docker compose up -d --build
```

## 3) Ініціалізація Laravel (у контейнері `app`)
```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan app:import-webmagic

```

> **Node/Vite** залежності ставляться автоматично у контейнері `node` через `npm ci`.

## URL-и
- Laravel: **http://localhost:8000**
- Vite dev server: **http://localhost:5173**
- MySQL (з хоста): **127.0.0.1:3316**  
  Користувач: `root`, Пароль: `root`, База: `database_test_server`