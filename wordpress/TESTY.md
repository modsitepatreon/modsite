# Weryfikacja wersji testowej Modsite 0.2.0

Sprawdzono 28.10.2026 w prawdziwym WordPressie uruchomionym przez oficjalne WordPress Playground CLI: WordPress 7.1.4, PHP 8.3, Paid Memberships Pro 3.9, Nextend Social Login 3.1.26 i SQLite.

Testy zakończyły się poprawnie:

- Aktywacja motywu Modsite oraz obu wtyczek zewnętrznych.
- Osiem wpisów katalogu jako edytowalne wpisy WordPressa.
- Obszary widżetów: lewa kolumna, prawa kolumna i górna belka.
- Odmowa pobierania dla gościa, konta bez członkostwa i niewłaściwego poziomu.
- Dostęp dla administratora i aktywnego członka właściwego płatnego poziomu.
- Odmowa po wygaśnięciu terminu, także przed uruchomieniem zadania cron.
- Odmowa po anulowaniu członkostwa.
- Darmowy poziom nie odblokowuje płatnych plików.
- Walidacja URL: HTTPS dozwolone, HTTP, JavaScript i dane logowania w URL odrzucone.
- Wymuszenie testowej ręcznej bramki płatniczej.
- Poprawny eksport ZIP całej instalacji.

Kontrola eksportu potwierdziła: aktywny motyw Modsite, język WordPressa `pl_PL`, osiem modów, jeden domyślny administrator, brak narzuconego planu i brak wypełnionych prywatnych linków. Użytkownik, plany i grupa użyte przez testy zostały usunięte.

Nie podłączano rzeczywistego konta PayPal, aplikacji Google ani wysyłki poczty. Ich testy odbędą się po konfiguracji docelowej domeny i kont operatorów. Tłumaczenia części zewnętrznych rozszerzeń mogą być niepełne; własny panel Modsite i WordPress są ustawione po polsku.
