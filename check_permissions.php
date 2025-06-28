<?php
// Acest script verifică și setează permisiunile pentru directorul de upload
// Rulați-l cu permisiuni de administrator pentru a rezolva problemele de permisiuni

// Calea către directorul principal al aplicației
$baseDir = realpath(dirname(__FILE__));
echo "Director de bază: " . $baseDir . "\n";

// Calea către directorul de upload
$uploadsDir = $baseDir . '/uploads';
$apkDir = $uploadsDir . '/apk';

// Creează directoarele dacă nu există
if (!file_exists($uploadsDir)) {
    echo "Creez directorul uploads...\n";
    if (mkdir($uploadsDir, 0777, true)) {
        echo "Director uploads creat cu succes.\n";
    } else {
        echo "EROARE: Nu s-a putut crea directorul uploads.\n";
    }
}

if (!file_exists($apkDir)) {
    echo "Creez directorul apk...\n";
    if (mkdir($apkDir, 0777, true)) {
        echo "Director apk creat cu succes.\n";
    } else {
        echo "EROARE: Nu s-a putut crea directorul apk.\n";
    }
}

// Setează permisiunile pentru directoarele de upload
echo "Setez permisiunile pentru directoarele de upload...\n";

// Setează permisiunile pentru directorul uploads
chmod($uploadsDir, 0777);
echo "Permisiuni director uploads: " . substr(sprintf('%o', fileperms($uploadsDir)), -4) . "\n";

// Setează permisiunile pentru directorul apk
chmod($apkDir, 0777);
echo "Permisiuni director apk: " . substr(sprintf('%o', fileperms($apkDir)), -4) . "\n";

// Verifică dacă directoarele sunt inscriptibile
if (is_writable($uploadsDir)) {
    echo "Directorul uploads este inscriptibil.\n";
} else {
    echo "EROARE: Directorul uploads NU este inscriptibil.\n";
}

if (is_writable($apkDir)) {
    echo "Directorul apk este inscriptibil.\n";
} else {
    echo "EROARE: Directorul apk NU este inscriptibil.\n";
}

echo "\nPentru a rezolva problemele de permisiuni, rulați următoarele comenzi în terminal:\n";
echo "chmod -R 777 " . $uploadsDir . "\n";
echo "Sau, în cazul în care serverul web rulează ca utilizator diferit (ex: www-data):\n";
echo "chown -R www-data:www-data " . $uploadsDir . "\n";

echo "\nVerificare completă.\n";
?>
