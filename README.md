# Simple AI Bot 

---

Tech Stack:

Frontend.
HTMX, CSS (Bootstrap 5), a little Vanilla JS

Backend:
PHP, MySQL

AI:
OpenAI

---

Was macht der Bot?

Chate direkt mit ChatGPT. Zusätzlich können Parameter mitgeben werden, welche der ChatBot bei der Generierung seiner Antwort beachtet.
Alle Anfragen werden in einer mySQL-DB gespeichert und können von registrierten Usern jederzeit abgerufen und wiederverwendet werden, sofern sie eingeloggt sind.

Generelle Info

Authentication & simples Backend

Da unser "old-school" Apache Webserver immer noch läuft, war es einfacher auf das LAMP-Stack zuzugreifen. Für einen simplen Protoytp reicht das und HTMX vereinfacht wenigstens das Frontend. Security scheint soweit in Ordnung (Basic Checks durchgeführt, allen voran Prüfung der Sanitation). Genauere Security-Prüfung sicher nicht schlecht.

Frontend

Sehr zufrieden mit HTMX. Simpel und schnell. Aktuelles JS müsste separiert werden bzw. Teile können allenfalls mit HTMX ersetzt werden (Idealfall).
Mobile Responsiveness ist ganz anständig.

Was könnte man besser machen? (Bugs, Unschönheiten, etc.)

- Font des Timestamp wird nicht weiss, wenn selecteed
- Bei Page Reload zuletzt ausgewählte Chat-Konversation anzeigen (?)
- Bugs bei removeParameters
- "..." in "Meine Fragen" dynamisieren
- DRY könnte besser angewendet werden (Front- und Backend)
- konsequenter Objekt-orientieres PHP im Backend schreiben






