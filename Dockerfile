# ==============================================================================
# DOCKERFILE POUR GOOGLE CLOUD RUN - CIRA SAS POINTAGE
# ==============================================================================

# 1. Utilise l'image Apache + PHP 8.2 officielle de Google/Docker
FROM php:8.2-apache

# 2. Installe et active l'extension mysqli nécessaire pour connecter MySQL/MariaDB
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# 3. Configure Apache pour écouter sur le port injecté dynamiquement par Cloud Run ($PORT)
# Par défaut, Apache écoute sur le port 80, mais Cloud Run requiert d'écouter sur 8080 (ou $PORT)
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 4. Copie le code source de l'application dans le répertoire web par défaut du conteneur
COPY . /var/www/html/

# 5. Attribue les bonnes permissions aux fichiers pour le serveur Apache (www-data)
RUN chown -R www-data:www-data /var/www/html

# 6. Active le module de réécriture Apache (si vous souhaitez ajouter du routage d'URL plus tard)
RUN a2enmod rewrite

# 7. Expose le port par défaut (facultatif mais recommandé pour la clarté)
EXPOSE 8080
