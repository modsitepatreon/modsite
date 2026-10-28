# Modsite — testowy WordPress

Ta część projektu uruchamia prawdziwego WordPressa w WordPress Playground. Podgląd HTML na głównej stronie GitHub Pages jest osobny.

Otwórz [stronę panelu Modsite](https://modsitepatreon.github.io/modsite/wordpress/) i kliknij „Otwórz mój WordPress”. Pierwsze uruchomienie pobiera WordPressa i instaluje motyw, Modsite Core, Paid Memberships Pro oraz Nextend Social Login. Trafisz do menu Modsite w `/wp-admin`.

## Twoje zmiany

- Instancja działa lokalnie w przeglądarce. Każdy odwiedzający otrzymuje własną kopię.
- Link korzysta z zapisanego środowiska `modsite-dev`. Wracaj z tej samej przeglądarki i urządzenia.
- W Playground wybierz zapis w przeglądarce. Nie pracuj w trybie incognito, jeśli chcesz zachować zmiany.
- Po zakończeniu pracy wybierz **Export / Eksport → Download as ZIP / Pobierz ZIP**. Ten plik zachowuje aktualne treści, obrazy, pliki i bazę lokalnej kopii.
- Zmiany nie publikują się w statycznym podglądzie GitHub Pages i nie zapisują się same do repozytorium.
- Czyszczenie danych przeglądarki może usunąć lokalną kopię; przechowuj eksport poza przeglądarką.
- Aby zastosować aktualizację kodu w istniejącej kopii, wgraj nowe ZIP-y motywu i wtyczki przez panel WordPress. Nie resetuj działającej kopii, jeśli zawiera już Twoje zmiany.

## Gdzie konfigurować

| Element | Panel |
|---|---|
| Opisy, zdjęcia, gry i prywatne linki | Modsite → Modyfikacje |
| Gry | Modsite → Gry i kategorie |
| Kategorie | Link „Kategorie modów” w panelu Modsite |
| Panele lewe, prawe i górny | Modsite → Panele strony |
| Logo | Modsite → Logo i wygląd |
| Plan i cena | Modsite → Subskrypcje |
| Plan wymagany do pobierania | Modsite → Dostęp do plików |
| Operator płatności | Modsite → Płatności |
| Konfiguracja aplikacji Google | Modsite → Logowanie Google |

Zestaw startowy ma 8 przykładowych modów, 6 gier i 3 obszary widżetów. Linki do pobrania są puste. Cena nie została narzucona. Bez wskazania płatnego planu zwykli użytkownicy nie mają dostępu do pobierania; administrator może sprawdzać konfigurację.

## Tryb testowy

`playground-only.php` jest instalowany jako MU plugin wyłącznie w środowisku testowym. Blokuje wysyłkę e-maili i wymusza ręczną bramkę testową bez automatycznych obciążeń. Dostosowuje też wyrażenie strefy czasowej PMPro do bazy SQLite w UTC, zachowując warunki sprawdzania członkostwa. Tego pliku nie ma w paczce Modsite Core przeznaczonej na zwykły hosting.

Na docelowym hostingu wymagane będą: prawdziwy operator PayPal, jego klucze i webhooki, konfiguracja Google dla domeny, poczta oraz sprawdzenie zakupów i odnowień w Sandbox. BLIK nie jest częścią obecnej integracji.

## Przeniesienie na hosting

Motyw i Modsite Core są zwykłymi rozszerzeniami WordPressa. Można je zainstalować przez standardowy panel z plików w `packages/`.

Eksport Playground zawiera bazę SQLite. Hosting z MySQL/MariaDB wymaga migracji danych lub importu treści i ustawień — samo skopiowanie pliku bazy SQLite do MySQL nie wystarczy. Przy wdrożeniu przenosimy motyw, wtyczki, treści, media, widżety i ustawienia, usuwamy MU plugin trybu testowego oraz ustawiamy docelową domenę i własne dane logowania. Kopia testowa nie służy do obsługi rzeczywistych klientów.

## Źródła i wersje

- WordPress: aktualna stabilna wersja dostępna w Playground; PHP 8.3.
- Paid Memberships Pro: oficjalne wydanie 3.9 z repozytorium `strangerstudios/paid-memberships-pro`.
- Nextend Social Login: aktualna wersja z oficjalnego katalogu WordPress.org.
- Modsite / Modsite Core: 0.2.0.
- Grafiki startowe: oficjalne karty gier Steam, wykorzystane tak jak w demonstracji HTML. Przed publiczną sprzedażą zastąp je własnymi materiałami modów.

Dokumentacja: [WordPress Playground](https://developer.wordpress.org/playground/), [zapisywanie i eksport](https://wordpress.github.io/wordpress-playground/start-using/).
