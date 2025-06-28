#!/bin/bash

# Script pentru a seta permisiunile corecte pentru directoarele de upload
# Execută acest script cu drepturi de administrator (sudo)

# Definește calea către directorul web
WEB_DIR="/Applications/XAMPP/xamppfiles/htdocs/SiteCatalog"

# Verifică dacă directorul există
if [ ! -d "$WEB_DIR" ]; then
  echo "Directorul $WEB_DIR nu există!"
  exit 1
fi

# Creează directoarele de upload dacă nu există
echo "Creez directoarele de upload..."
mkdir -p "$WEB_DIR/uploads/apk"

# Setează permisiunile
echo "Setez permisiunile..."
chmod -R 777 "$WEB_DIR/uploads"

# Afișează permisiunile
echo "Permisiuni setate:"
ls -la "$WEB_DIR/uploads"
ls -la "$WEB_DIR/uploads/apk"

echo "Verifică dacă utilizatorul serverului web are acces la directoare..."
# În macOS, utilizatorul serverului web este de obicei _www
USER=$(ps aux | grep httpd | grep -v grep | head -1 | awk '{print $1}')
if [ -z "$USER" ]; then
  USER="_www" # Default pentru macOS
fi
echo "Utilizatorul serverului web este: $USER"

# Setează proprietarul
echo "Schimb proprietarul la $USER..."
chown -R $USER:$USER "$WEB_DIR/uploads"

echo "Permisiuni actualizate:"
ls -la "$WEB_DIR/uploads"
ls -la "$WEB_DIR/uploads/apk"

echo "Gata! Directoarele ar trebui să aibă acum permisiunile corecte."
