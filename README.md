# Viteo — Сервис асинхронной конвертации видео

Production-ready backend для приложения видеомонтажа с GraphQL API.

## Технологический стек

- **Laravel 12** — PHP фреймворк
- **Lighthouse** — GraphQL для Laravel
- **FFmpeg** — конвертация видео
- **Redis** — очереди и кеш
- **MySQL** — реляционная БД
- **Docker** — контейнеризация

## Структура проекта

```
viteo/
├── backend/           # Laravel приложение
│   ├── app/
│   │   ├── GraphQL/   # GraphQL резолверы
│   │   ├── Jobs/      # Очередные задачи
│   │   ├── Models/
│   │   ├── Repositories/
│   │   └── Services/
│   ├── graphql/       # GraphQL схема
│   └── ...
├── infra/             # Docker инфраструктура
│   ├── docker/
│   └── docker-compose.yml
└── README.md
```

## GraphQL API

### Endpoint

```
POST /graphql
```

Для загрузки файлов используйте Content-Type: `multipart/form-data` согласно [GraphQL multipart request spec](https://github.com/jaydenseric/graphql-multipart-request-spec).

### Типы

#### VideoConversionTaskStatus (Enum)

Статусы задачи конвертации:

- `queued` — в очереди
- `processing` — выполняется
- `completed` — завершена
- `failed` — ошибка

#### VideoConversionTask

| Поле | Тип | Описание |
|------|-----|----------|
| id | ID! | UUID задачи |
| status | VideoConversionTaskStatus! | Текущий статус |
| progress | Int! | Прогресс 0–100 |
| downloadUrl | String | URL для скачивания (при completed) |
| errorMessage | String | Сообщение об ошибке (при failed) |
| createdAt | DateTime! | Время создания |
| updatedAt | DateTime! | Время обновления |

### Запросы (Queries)

#### videoConversionTask

Получить статус задачи по ID.

**Аргументы:**
- `id` (ID!, обязательно) — UUID задачи

**Пример запроса:**
```graphql
query GetTask($id: ID!) {
  videoConversionTask(id: $id) {
    id
    status
    progress
    downloadUrl
    errorMessage
    createdAt
    updatedAt
  }
}
```

**Variables:**
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000"
}
```

### Мутации (Mutations)

#### uploadVideo

Загрузить видео и создать задачу на конвертацию.

**Аргументы:**
- `video` (Upload!, обязательно) — видеофайл (mp4, mov, avi, mkv, wmv, webm, до 2 ГБ)

**Пример запроса (multipart/form-data):**
```graphql
mutation UploadVideo($video: Upload!) {
  uploadVideo(video: $video) {
    id
    status
    progress
    downloadUrl
  }
}
```

**Multipart структура:**
- `operations`: `{"query":"mutation UploadVideo($video: Upload!) { uploadVideo(video: $video) { id status progress } }","variables":{"video":null}}`
- `map`: `{"0":["variables.video"]}`
- `0`: файл

**cURL пример:**
```bash
curl -X POST http://localhost:8080/graphql \
  -F operations='{"query":"mutation UploadVideo($video: Upload!) { uploadVideo(video: $video) { id status progress } }","variables":{"video":null}}' \
  -F map='{"0":["variables.video"]}' \
  -F 0=@video.mp4
```

## Хранилище файлов (S3-совместимые)

Загрузка и выдача файлов идут через абстракцию `App\Contracts\VideoStorageInterface`. Реализация по умолчанию — локальные диски (`config/filesystems.php`).

Переход на S3-совместимое хранилище (AWS S3, MinIO и т.п.):

1. Установите пакет: `composer require league/flysystem-aws-s3-v3 "^3.0"`
2. В `.env` задайте:
   - `VIDEO_UPLOAD_DISK=s3`
   - `VIDEO_CONVERTED_DISK=s3`
   - переменные `AWS_*` (или `AWS_ENDPOINT` для MinIO)
3. Добавьте/настройте диск `s3` в `config/filesystems.php` при необходимости.

Текущая реализация (`App\Services\Storage\FilesystemVideoStorage`) для облачного диска скачивает исходник во временный файл для FFmpeg и заливает результат конвертации в выбранный диск.

## Конвертация видео

- **Входные форматы:** mp4, mov, avi, mkv, wmv, webm
- **Выходной формат:** MP4 (H.264, AAC)
- **Максимальное разрешение:** 720p
- **Оптимизация:** faststart для стриминга в браузере

## Запуск

### Через Docker (рекомендуется)

```bash
# 1. Сгенерируйте ключ приложения
cd backend
composer install
php artisan key:generate --show
# Скопируйте вывод (base64:...)

# 2. Настройте Docker
cd ../infra
cp .env.example .env
# Добавьте в .env: APP_KEY=base64:ваш_сгенерированный_ключ

# 3. Запуск
docker compose up -d
make install
```

API доступен на http://localhost:8080

### Локальная разработка

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# Настройте MySQL и Redis в .env
php artisan migrate
php artisan storage:link

# Терминал 1: API
php artisan serve

# Терминал 2: Queue Worker (с FFmpeg)
php artisan queue:work redis --queue=video-conversion,default
```

## Архитектура

- **nginx** — веб-сервер
- **php** — API (Laravel), не выполняет конвертацию
- **php-worker** — воркер очереди с FFmpeg, выполняет конвертацию
- **redis** — очереди и кеш
- **mysql** — БД

Общее хранилище файлов между API и worker обеспечивается Docker volume `shared-storage`.

## Тестирование

```bash
cd backend
php artisan test
```

## Линтинг

```bash
cd backend
composer cs-fix
```

## Масштабирование

Для горизонтального масштабирования:
- Запускайте несколько инстансов `php-worker`
- Используйте Redis для распределённой очереди
- Рассмотрите отдельное файловое хранилище (S3) для production
