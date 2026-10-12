# Books API

Навчальний проєкт із двох частин:

1. **API на PHP** (Composer + Slim 4). Видає дані про книги української літератури з файлу `data/books.json`.
2. **Консольний клієнт на Python**, який отримує ці дані через локальний HTTP-запит.

## Структура

```
books-api/
├── composer.json         # залежності та автозавантаження (PSR-4)
├── public/index.php      # точка входу, маршрути API
├── src/BookRepository.php# читання та фільтрація даних із JSON
├── data/books.json       # дані
└── client/client.py      # консольний клієнт на Python
```

## Вимоги

- PHP 8.1+ з розширенням `mbstring` (у `php.ini` розкоментуйте `extension=mbstring`)
- Composer
- Python 3.10+

## Запуск API

```bash
composer install
composer start
```

Сервер працюватиме на `http://localhost:8000`.

### Ендпоінти

| Метод | Шлях | Опис |
|-------|------|------|
| GET | `/` | інформація про API |
| GET | `/api/books` | усі книги; фільтри `?genre=роман`, `?author=Франко` |
| GET | `/api/books/{id}` | одна книга (404, якщо не знайдено) |
| GET | `/api/genres` | список жанрів |

Приклад:

```bash
curl http://localhost:8000/api/books/1
```

## Запуск клієнта

В іншому терміналі (поки працює API):

```bash
python client/client.py
```

Інша адреса API: `python client/client.py --url http://127.0.0.1:8080`

Клієнт показує меню: усі книги, книга за ID, фільтр за жанром, пошук за автором, список жанрів.
