# FilleaGO Next

Una riscrittura moderna dell'applicazione legacy FilleaGO, costruita con Laravel e Vue.js per gestire e visualizzare i cantieri edili.

## Informazioni sul Progetto

Questo progetto è un refactoring completo di una vecchia applicazione originariamente costruita con CodeIgniter. L'obiettivo è fornire una piattaforma moderna, responsive e manutenibile per esplorare i cantieri e le aziende che vi operano.

L'applicazione presenta due viste principali:
*   Una mappa interattiva che visualizza i cantieri con icone personalizzate.
*   Una vista a elenco per sfogliare gli stessi cantieri in un formato tabellare.

## Funzionalità Principali

*   **Mappa Interattiva**: Visualizza i cantieri utilizzando Leaflet.js, con icone SVG personalizzate a forma di gru.
*   **Elenco Cantieri**: Una tabella chiara e responsive che mostra i dettagli dei cantieri e le aziende associate.
*   **Design Responsive**: Una barra di navigazione e un layout che si adattano a dispositivi desktop e mobili, realizzati con Bootstrap 5.
*   **Stack Moderno**: Basato su Laravel 10, Vue 3 (Composition API) e Vite per un rapido bundling degli asset.
*   **Integrazione con Database Legacy**: Si connette alla struttura del database esistente, preservando i dati storici.

## Stack Tecnologico

*   **Backend**: PHP / Laravel
*   **Frontend**: Vue.js 3 (Composition API)
*   **Asset Bundling**: Vite
*   **Mappe**: Leaflet.js & Vue-Leaflet
*   **Stile**: Bootstrap 5 & CSS personalizzato

## Come Iniziare

Per ottenere una copia locale funzionante, segui questi semplici passaggi.

### Prerequisiti

*   PHP (versione compatibile con Laravel 10)
*   Composer
*   Node.js & npm
*   Un ambiente di sviluppo web locale (es. Laravel Herd, Valet, Laragon)
*   Accesso al database legacy.

### Installazione

1.  **Clona il repository**
    ```sh
    git clone <url-del-tuo-repository>
    cd FilleaGoNext
    ```

2.  **Installa le dipendenze PHP**
    ```sh
    composer install
    ```

3.  **Installa le dipendenze NPM**
    ```sh
    npm install
    ```

4.  **Configura il file d'ambiente**
    Copia il file d'ambiente di esempio e genera una chiave per l'applicazione.
    ```sh
    cp .env.example .env
    php artisan key:generate
    ```

5.  **Configura il Database**
    Apri il file `.env` e aggiorna le variabili `DB_*` per connetterti al tuo database legacy esistente.
    ```ini
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=nome_db_legacy
    DB_USERNAME=utente_db
    DB_PASSWORD=password_db
    ```

6.  **Avvia i server di sviluppo**
    Devi avviare due processi in terminali separati:
    *   Il server di sviluppo Vite per gli asset frontend:
        ```sh
        npm run dev
        ```
    *   Il server di sviluppo PHP (se non usi Herd/Valet):
        ```sh
        php artisan serve
        ```

7.  **Accedi all'applicazione**
    Apri il browser e naviga all'URL fornito dal tuo server locale (es. `http://filleagonext.test` o `http://127.0.0.1:8000`).