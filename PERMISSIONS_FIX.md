# Rezolvarea problemelor de permisiuni pentru încărcarea APK-urilor

Dacă întâmpini erori de tipul "Permission denied" sau "Failed to open stream" la încărcarea fișierelor APK, urmează acești pași pentru a rezolva problema:

## Opțiunea 1: Folosește scriptul automat

1. Deschide un terminal și navighează la directorul proiectului:
   ```
   cd /Applications/XAMPP/xamppfiles/htdocs/SiteCatalog
   ```

2. Fă scriptul executabil:
   ```
   chmod +x fix_permissions.sh
   ```

3. Rulează scriptul ca administrator (va trebui să introduci parola):
   ```
   sudo ./fix_permissions.sh
   ```

## Opțiunea 2: Verifică permisiunile manual

1. Deschide un browser și accesează:
   ```
   http://localhost/SiteCatalog/check_permissions.php
   ```

2. Urmează instrucțiunile afișate pentru a rezolva problemele detectate.

## Opțiunea 3: Setează permisiunile manual

1. Deschide un terminal și execută următoarele comenzi:
   ```
   sudo mkdir -p /Applications/XAMPP/xamppfiles/htdocs/SiteCatalog/uploads/apk
   sudo chmod -R 777 /Applications/XAMPP/xamppfiles/htdocs/SiteCatalog/uploads
   ```

2. Dacă folosești macOS, setează și proprietarul corect:
   ```
   sudo chown -R _www:_www /Applications/XAMPP/xamppfiles/htdocs/SiteCatalog/uploads
   ```

## Notă importantă

Permisiunile 777 (scriere pentru toți utilizatorii) sunt riscante din punct de vedere al securității și ar trebui folosite doar în medii de dezvoltare. Pentru un server de producție, folosește permisiuni mai restrictive și asigură-te că doar utilizatorul serverului web are acces de scriere.

## Verificare după rezolvarea problemei

După ce ai aplicat una dintre soluțiile de mai sus, încearcă din nou să încarci un fișier APK prin panoul de administrare. Dacă problema persistă, verifică jurnalul de erori PHP pentru mai multe detalii:

```
tail -f /Applications/XAMPP/xamppfiles/logs/php_error_log
```
