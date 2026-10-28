# Modsite — wersja demonstracyjna

Czarno-złoty projekt katalogu modyfikacji do gier. Repozytorium zawiera statyczny podgląd przeznaczony do dopracowywania strony, bez aktywnych płatności ani rejestracji.

## Funkcje podglądu

- Lista modyfikacji z grafikami, wyszukiwanie, filtry gier i kategorii, sortowanie.
- Zapisane pozycje i tekstowe panele przechowywane lokalnie w przeglądarce.
- Stała górna belka, boczne kolumny oraz układ na telefon.
- Formularze kont i subskrypcji są demonstracyjne. Pola haseł są wyłączone.

Wpisy są przykładowe. Nie umieszczaj tu haseł, kluczy API ani rzeczywistych chronionych linków do plików. Docelową rejestrację i płatności obsłuży osobny hosting WordPress. Oryginalny plik logo czeka na podmianę.

## GitHub Pages

W ustawieniach repozytorium: Settings → Pages → Deploy from a branch → main → / (root) → Save. Plik `.nojekyll` wyłącza przetwarzanie Jekyll.

## Pliki

- `index.html`: układ strony.
- `style.css`: wygląd i układ responsywny.
- `app.js`: działanie podglądu.
- `assets/`: ilustracje i ikona.

Grafiki gier pochodzą z oficjalnych kart Steam; ich źródła są dostępne w stopce podglądu. Materiały należą do ich właścicieli. Przed docelowym uruchomieniem zastąp je własnymi materiałami modów.

Kontrola składni: `node --check app.js`.
