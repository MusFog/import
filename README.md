# Запуск проєкту (Docker + Laravel + Vite + MySQL)

## Вимоги
- Встановлені **Docker** і **Docker Compose v2** (`docker compose`)
- Вільні порти: **8000** (Laravel), **5173** (Vite), **3316** (MySQL)

## Структура
```
project-root/
├─ dockerfiles/
│  └─ php.dockerfile
├─ laravel/
└─ docker-compose.yml
```

## 1) Підготовка `.env`
```bash
cp laravel/.env.example laravel/.env
```
У `laravel/.env` перевірте:
```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=database_test_server
DB_USERNAME=root
DB_PASSWORD=root
```

## 2) Підняти контейнери
```bash
docker compose up -d --build
```
> Сервіс **app** автоматично виконає `composer install` і запустить `php artisan serve` (див. `command` у compose).

## 3) Ініціалізація Laravel
```bash
docker compose exec app sh -lc 'php artisan key:generate && php artisan migrate'
```

## 4) Команда імпорту статей

За замовчуванням імпорт охоплює **останні 4 місяці** (у тестах — ≈ 1 стаття).  
Щоб завантажити більший архів для тестування, тимчасово **збільште період** (наприклад, до 400 місяців) в імпорту/коді.

```bash
docker compose exec app php artisan app:import-webmagic
```

## URL-и
- Laravel Vue: **http://localhost:8000**
- MySQL (з хоста): **127.0.0.1:3316** (user: `root`, pass: `root`, db: `database_test_server`)