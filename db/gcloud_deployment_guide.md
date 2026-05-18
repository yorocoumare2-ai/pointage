# Guide de Déploiement : Google Cloud Platform (GCP)

Ce guide vous explique étape par étape comment déployer votre application de pointage **CIRA SAS** sur **Google Cloud App Engine** ou **Google Cloud Run** en la connectant à une base de données managée **Google Cloud SQL (MySQL)**.

---

## 🛠️ Étape Préalable : Installer Google Cloud CLI
Pour déployer depuis votre terminal local, vous devez disposer du Google Cloud SDK.
1. Téléchargez et installez l'outil `gcloud` depuis : [cloud.google.com/sdk](https://cloud.google.com/sdk)
2. Connectez-vous à votre compte Google Cloud :
   ```bash
   gcloud auth login
   ```
3. Créez ou sélectionnez votre projet Google Cloud :
   ```bash
   gcloud config set project ID_DE_VOTRE_PROJET
   ```

---

## 💾 Étape 1 : Créer la Base de Données sur Google Cloud SQL

Puisque votre application s'exécute sur des serveurs cloud éphémères (Serverless), vous devez héberger vos données sur un serveur SQL indépendant et permanent.

### 1. Créer une instance Cloud SQL MySQL
Exécutez la commande suivante (ou créez-la via la console web de GCP) :
```bash
gcloud sql instances create cira-mysql-db \
    --database-version=MYSQL_8_0 \
    --tier=db-f1-micro \
    --region=europe-west9
```
*(Remarque : la tier `db-f1-micro` est très économique et parfaite pour débuter).*

### 2. Créer l'utilisateur et la base de données
Créez la base de données `cira_pointage` :
```bash
gcloud sql databases create cira_pointage --instance=cira-mysql-db
```
Créez un utilisateur administrateur avec un mot de passe sécurisé :
```bash
gcloud sql users create cira_admin \
    --instance=cira-mysql-db \
    --password="MOT_DE_PASSE_TRES_SECURISE"
```

### 3. Importer votre schéma SQL (`cira_pointage.sql`)
1. Activez le stockage Cloud Storage (créez un bucket éphémère) :
   ```bash
   gcloud storage buckets create gs://cira-sql-import-bucket --location=europe-west9
   ```
2. Envoyez votre fichier SQL local dans le bucket :
   ```bash
   gcloud storage cp cira_pointage.sql gs://cira-sql-import-bucket/schema.sql
   ```
3. Donnez à votre instance SQL les droits de lecture sur le bucket, puis lancez l'importation via la console GCP, ou exécutez la commande d'import :
   ```bash
   gcloud sql import sql cira-mysql-db gs://cira-sql-import-bucket/schema.sql --database=cira_pointage
   ```

---

## 🚀 Option A : Déploiement sur Google Cloud App Engine

**App Engine (Standard Environment)** est la méthode la plus rapide et simple pour héberger une application PHP classique.

### 1. Configurer `app.yaml`
Ouvrez le fichier [app.yaml](file:///opt/lampp/htdocs/cira_pointage/app.yaml) que nous venons de créer. Décommentez la ligne `DB_SOCKET` et remplacez les valeurs par vos identifiants Cloud SQL :
```yaml
env_variables:
  DB_NAME: 'cira_pointage'
  DB_USER: 'cira_admin'
  DB_PASS: 'MOT_DE_PASSE_TRES_SECURISE'
  # Le socket se trouve dans les infos de votre instance Cloud SQL MySQL sur la console GCP
  DB_SOCKET: '/cloudsql/ID_DE_VOTRE_PROJET:europe-west9:cira-mysql-db'
```

### 2. Lancer le déploiement
Exécutez simplement cette commande dans le dossier de votre projet :
```bash
gcloud app deploy
```
*Choisissez la région de votre choix (ex: `europe-west9` pour Paris) et confirmez par `Y`.*

À la fin du déploiement, ouvrez votre application en ligne avec :
```bash
gcloud app browse
```

---

## 🐳 Option B : Déploiement sur Google Cloud Run

**Cloud Run** est l'approche moderne basée sur les conteneurs (Docker). Elle offre plus de flexibilité de configuration et une mise à l'échelle automatique jusqu'à zéro (très économique).

### 1. Déployer directement depuis le code source
Grâce au [Dockerfile](file:///opt/lampp/htdocs/cira_pointage/Dockerfile) et au [.dockerignore](file:///opt/lampp/htdocs/cira_pointage/.dockerignore) créés, Cloud Run compilera automatiquement votre conteneur dans le cloud (Cloud Build) et le déploiera.

Exécutez la commande suivante :
```bash
gcloud run deploy cira-pointage-app \
    --source . \
    --region europe-west9 \
    --allow-unauthenticated \
    --set-env-vars="DB_NAME=cira_pointage,DB_USER=cira_admin,DB_PASS=MOT_DE_PASSE_TRES_SECURISE,DB_SOCKET=/cloudsql/ID_DE_VOTRE_PROJET:europe-west9:cira-mysql-db" \
    --add-cloudsql-instances ID_DE_VOTRE_PROJET:europe-west9:cira-mysql-db
```

### Explication des paramètres :
* `--source .` : Compile le code local directement via Buildpacks.
* `--allow-unauthenticated` : Rend votre application accessible publiquement sur internet.
* `--set-env-vars` : Transmet les variables d'accès à la base de données.
* `--add-cloudsql-instances` : Établit un tunnel sécurisé crypté entre le conteneur Cloud Run et votre instance Cloud SQL.

À la fin de la commande, Cloud Run vous fournira un lien unique sécurisé de type :
`https://cira-pointage-app-xxxxxx-ew.a.run.app`

Votre application de pointage est désormais disponible dans le monde entier !
