"""Консольний клієнт для Books API.

Запуск:  python client/client.py [--url http://localhost:8000]
Використовує лише стандартну бібліотеку Python.
"""

import argparse
import json
import sys
import urllib.error
import urllib.parse
import urllib.request


class BooksApiClient:
    def __init__(self, base_url: str):
        self.base_url = base_url.rstrip("/")

    def _get(self, path: str, params: dict | None = None):
        url = self.base_url + path
        if params:
            url += "?" + urllib.parse.urlencode(params)
        with urllib.request.urlopen(url, timeout=5) as response:
            return json.loads(response.read().decode("utf-8"))

    def books(self, genre: str | None = None, author: str | None = None):
        params = {}
        if genre:
            params["genre"] = genre
        if author:
            params["author"] = author
        return self._get("/api/books", params)

    def book(self, book_id: int):
        return self._get(f"/api/books/{book_id}")

    def genres(self):
        return self._get("/api/genres")


def print_books(books: list) -> None:
    if not books:
        print("Нічого не знайдено.")
        return
    print(f"{'ID':>3}  {'Назва':<25} {'Автор':<25} {'Рік':>4}  Жанр")
    print("-" * 75)
    for b in books:
        print(f"{b['id']:>3}  {b['title']:<25} {b['author']:<25} {b['year']:>4}  {b['genre']}")


def print_book(b: dict) -> None:
    print(f"Назва:   {b['title']}")
    print(f"Автор:   {b['author']}")
    print(f"Рік:     {b['year']}")
    print(f"Жанр:    {b['genre']}")
    print(f"Сторінок: {b['pages']}")


MENU = """
===== Books API client =====
1. Усі книги
2. Книга за ID
3. Книги за жанром
4. Пошук за автором
5. Список жанрів
0. Вихід
"""


def main() -> None:
    if hasattr(sys.stdout, "reconfigure"):
        sys.stdout.reconfigure(encoding="utf-8")

    parser = argparse.ArgumentParser(description="Консольний клієнт для Books API")
    parser.add_argument("--url", default="http://localhost:8000", help="адреса API")
    client = BooksApiClient(parser.parse_args().url)

    while True:
        print(MENU)
        choice = input("Ваш вибір: ").strip().lstrip("﻿")
        try:
            if choice == "1":
                print_books(client.books())
            elif choice == "2":
                book_id = input("Введіть ID: ").strip()
                if not book_id.isdigit():
                    print("ID має бути числом.")
                    continue
                print_book(client.book(int(book_id)))
            elif choice == "3":
                print_books(client.books(genre=input("Жанр: ").strip()))
            elif choice == "4":
                print_books(client.books(author=input("Автор (або частина імені): ").strip()))
            elif choice == "5":
                for genre in client.genres():
                    print(f"- {genre}")
            elif choice == "0":
                print("До побачення!")
                break
            else:
                print("Невідомий пункт меню.")
        except urllib.error.HTTPError as e:
            body = e.read().decode("utf-8", errors="replace")
            try:
                message = json.loads(body).get("error", body)
            except json.JSONDecodeError:
                message = body
            print(f"Помилка {e.code}: {message}")
        except urllib.error.URLError as e:
            print(f"Не вдалося підключитися до API ({client.base_url}): {e.reason}")
            print("Перевірте, що сервер запущено командою: composer start")


if __name__ == "__main__":
    main()
