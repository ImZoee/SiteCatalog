# Catalog Electronic - Platformă de Distribuție APK

Aceasta este o platformă web pentru distribuția de aplicații APK pe bază de invitații. Aplicația permite administratorilor să încarce fișiere APK și să genereze coduri de invitație pentru utilizatorii care pot descărca aceste aplicații. Utilizatorii normali pot doar descărca aplicațiile, fără a avea posibilitatea de a încărca fișiere.

## Caracteristici principale

- Sistem de autentificare securizat
- Înregistrare bazată pe coduri de invitație
- Încărcare și gestionare fișiere APK
- Panou de administrare pentru gestionarea utilizatorilor și fișierelor
- Monitorizarea descărcărilor
- Panou de control pentru utilizatori

## Instalare

1. Copiați fișierele în directorul web (ex: `/Applications/XAMPP/xamppfiles/htdocs/SiteCatalog/`)
2. Creați o bază de date MySQL numită `db_restaurant` (sau modificați numele în `connect/config.php`)
3. Importați structura bazei de date din fișierul `database_setup.sql`
4. Configurați conexiunea la baza de date în fișierul `connect/config.php`
5. Asigurați-vă că aveți permisiuni de scriere pentru directorul `uploads/apk/`

## Configurație bază de date

Editați fișierul `connect/config.php` pentru a configura conexiunea la baza de date:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_catalog');
define('DB_USER', 'SuperUser');
define('DB_PASS', 'superuser');
```

## Utilizare

### Administrator

- Utilizatorul implicit: `admin` / Parola: `admin123`
- După autentificare, navigați la "Administrare" pentru a:
  - Încărca fișiere APK noi
  - Genera coduri de invitație
  - Gestiona utilizatorii și aplicațiile

### Utilizator

- Utilizatorii noi au nevoie de un cod de invitație pentru a se înregistra
- După înregistrare, utilizatorii pot:
  - Descărca aplicațiile APK disponibile
  - Vizualiza istoricul descărcărilor
  - Accesa panoul de control personal
- Utilizatorii obișnuiți NU pot încărca fișiere APK - această funcționalitate este restricționată doar pentru administratori

## Structura fișierelor

```
/
├── admin.php                  # Panou de administrare
├── admin-functions.php        # Funcții pentru administrare
├── connect/                   # Fișiere conexiune bază de date
│   ├── config.php
│   ├── connect.php
│   ├── footer.php
│   └── header.php
├── dashboard.php              # Panou de control utilizator
├── database_setup.sql         # Structura bazei de date
├── includes/                  # Funcții și clase
│   ├── apk/
│   │   └── apk-functions.php  # Funcții pentru gestionarea APK
│   └── auth/                  # Funcții pentru autentificare
│       ├── auth-functions.php
│       ├── invitation-functions.php
│       └── session-functions.php
├── index.php                  # Pagina principală
├── login.php                  # Pagina de autentificare
├── register.php               # Pagina de înregistrare
└── uploads/                   # Director pentru fișiere încărcate
    └── apk/                   # Fișiere APK
```

## Cerințe sistem

- PHP 7.4 sau mai recent
- MySQL 5.7 sau mai recent
- Extensii PHP: mysqli, fileinfo, session
- Apache sau Nginx cu mod_rewrite activat

## Notă de securitate

- Schimbați numele de utilizator și parola implicite pentru contul admin
- Asigurați-vă că directorul `uploads/apk/` nu permite executarea script-urilor
- Actualizați regulat aplicația și dependențele
- Folosiți HTTPS pentru a proteja datele utilizatorilor

## Licență

Acest proiect este destinat utilizării interne și nu poate fi redistribuit fără permisiune.
