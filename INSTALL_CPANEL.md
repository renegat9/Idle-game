# Guide d'installation — Le Donjon des Incompétents
## Déploiement sur hébergement cPanel

---

## Prérequis serveur

| Composant | Version minimale | Notes |
|-----------|-----------------|-------|
| PHP | **8.3+** | Extensions requises ci-dessous |
| MariaDB | 10.4+ | MySQL 8+ compatible |
| Apache | 2.4+ | mod_rewrite activé |
| Node.js | 18+ | Uniquement pour builder le frontend en local |
| Composer | 2.x | Disponible en SSH ou via cPanel |

**Extensions PHP requises** (à activer dans cPanel → PHP Selector) :
`pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`, `fileinfo`, `intl`

---

## Architecture des fichiers sur le serveur

```
/home/votrecompte/
├── donjon/                      ← Dossier Laravel (HORS public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   └── ...
└── public_html/                 ← ou un sous-domaine, ex: jeu.mondomaine.fr/
    ├── index.php                ← Modifié pour pointer vers ../donjon
    ├── .htaccess
    └── assets/                  ← Build React (dist/)
```

> **Important :** Le dossier Laravel ne doit **jamais** être dans `public_html`. Seul le contenu de `laravel/public/` va dans `public_html`.

---

## Étape 1 — Préparer le build frontend (en local)

Exécuter sur votre machine locale :

```bash
cd frontend/
npm install
npm run build
```

Le dossier `frontend/dist/` contient les fichiers statiques à uploader.

---

## Étape 2 — Uploader les fichiers

### Via FTP/SFTP ou Gestionnaire de fichiers cPanel

**1. Uploader Laravel** (tout sauf `vendor/` et `node_modules/`) dans `/home/votrecompte/donjon/` :

```
laravel/ → /home/votrecompte/donjon/
```

**2. Uploader le contenu de `laravel/public/`** dans `public_html/` :

```
laravel/public/index.php    → public_html/index.php
laravel/public/.htaccess    → public_html/.htaccess
laravel/public/favicon.ico  → public_html/favicon.ico
laravel/public/robots.txt   → public_html/robots.txt
```

**3. Uploader le build React** dans `public_html/` :

```
frontend/dist/assets/       → public_html/assets/
frontend/dist/index.html    → public_html/index.html
```

---

## Étape 3 — Modifier `public_html/index.php`

Remplacer les chemins par défaut pour pointer vers Laravel hors `public_html` :

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Maintenance mode check
if (file_exists($maintenance = __DIR__.'/../donjon/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Autoloader
require __DIR__.'/../donjon/vendor/autoload.php';

// Bootstrap application
$app = require_once __DIR__.'/../donjon/bootstrap/app.php';

$app->run(Request::capture());
```

---

## Étape 4 — Configurer `.htaccess` dans `public_html/`

Remplacer le contenu de `public_html/.htaccess` par :

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Handle X-XSRF-Token Header
    RewriteCond %{HTTP:x-xsrf-token} .
    RewriteRule .* - [E=HTTP_X_XSRF_TOKEN:%{HTTP:X-XSRF-Token}]

    # Redirect Trailing Slashes
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Serve existing static files (React build, images, etc.) directly
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule ^ - [L]

    # Route React SPA — toutes les routes non-API vont sur index.html
    RewriteCond %{REQUEST_URI} !^/api/
    RewriteCond %{REQUEST_URI} !^/storage/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.html [L]

    # Route Laravel API
    RewriteCond %{REQUEST_URI} ^/api/
    RewriteRule ^ index.php [L]
</IfModule>
```

---

## Étape 5 — Créer la base de données

Dans **cPanel → Bases de données MySQL** :

1. Créer une base de données : `votrecompte_donjon`
2. Créer un utilisateur : `votrecompte_donjon_user`
3. Attribuer **tous les privilèges** à l'utilisateur sur la base
4. Noter le hostname (souvent `localhost`)

---

## Étape 6 — Configurer le fichier `.env`

Dans `/home/votrecompte/donjon/`, créer le fichier `.env` :

```env
APP_NAME="Le Donjon des Incompétents"
APP_ENV=production
APP_KEY=                          # Généré à l'étape 7
APP_DEBUG=false
APP_URL=https://jeu.mondomaine.fr

APP_LOCALE=fr
APP_FALLBACK_LOCALE=fr
APP_FAKER_LOCALE=fr_FR

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

# ─── Base de données ──────────────────────────────
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=votrecompte_donjon
DB_USERNAME=votrecompte_donjon_user
DB_PASSWORD=VotreMotDePasseSecurisé

# ─── Cache & Sessions ─────────────────────────────
# cPanel : pas de Redis → utiliser file ou database
CACHE_STORE=file
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=.mondomaine.fr

# ─── Queue (jobs asynchrones Gemini) ──────────────
QUEUE_CONNECTION=database

# ─── Filesystem ───────────────────────────────────
FILESYSTEM_DISK=local

# ─── API Gemini (Google AI) ───────────────────────
GEMINI_API_KEY=AIzaSy...VotreCléAPI

# ─── Sanctum (CSRF pour SPA React) ───────────────
SANCTUM_STATEFUL_DOMAINS=jeu.mondomaine.fr

# ─── CORS ─────────────────────────────────────────
# Laisser vide si frontend et API sont sur le même domaine
```

---

## Étape 7 — Installation via SSH

Se connecter en SSH (cPanel → Terminal ou client SSH) :

```bash
cd /home/votrecompte/donjon

# 1. Installer les dépendances PHP
composer install --no-dev --optimize-autoloader

# 2. Générer la clé applicative
php artisan key:generate

# 3. Créer les tables (51 migrations)
php artisan migrate --force

# 4. Peupler les données de référence
php artisan db:seed --force

# 5. Créer le lien symbolique storage → public
php artisan storage:link

# 6. Optimiser pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## Étape 8 — Ajouter `services.gemini` dans la config

Éditer `/home/votrecompte/donjon/config/services.php` et ajouter avant le `]` final :

```php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
],
```

Puis vider le cache de config :

```bash
php artisan config:cache
```

---

## Étape 9 — Configurer le Cron cPanel

Dans **cPanel → Tâches Cron**, ajouter une seule entrée (toutes les minutes) :

```
* * * * * /usr/local/bin/php /home/votrecompte/donjon/artisan schedule:run >> /dev/null 2>&1
```

> **Note :** Remplacer `/usr/local/bin/php` par le chemin PHP 8.3 de votre hébergeur. Pour le trouver :
> ```bash
> which php8.3
> # → /usr/local/bin/php8.3
> ```

Cette unique tâche cron déclenche automatiquement toutes les tâches planifiées :

| Tâche | Fréquence | Description |
|-------|-----------|-------------|
| `logs:cleanup` | Quotidien 00:00 | Purge des logs combat/économie/idle |
| `quests:generate` | Quotidien 00:05 | Génération des quêtes daily via Gemini |
| `shop:refresh` | Toutes les 6h | Rafraîchissement de la boutique |
| `world-boss:spawn` | Tous les 3 jours | Spawn d'un nouveau boss mondial |
| `world-boss:auto-attack` | Toutes les 2h | Attaques NPC simulées sur le boss |
| `zones:generate` | Lundi 02:00 | Génération d'une zone procédurale |

---

## Étape 10 — Configurer les jobs de queue (optionnel)

Les jobs asynchrones (génération d'images IA, quêtes daily) utilisent la queue database.

**Option A — Processeur de queue via Cron** (recommandé pour cPanel) :

Ajouter une deuxième tâche cron (toutes les 5 minutes) :

```
*/5 * * * * /usr/local/bin/php /home/votrecompte/donjon/artisan queue:work --stop-when-empty --max-time=240 >> /dev/null 2>&1
```

**Option B — Désactiver les jobs async** (mode simplifié) :

Si les images IA ne sont pas nécessaires, modifier `AI_ENABLED=0` dans `.env`.

---

## Étape 11 — Vérification finale

```bash
# Tester la configuration
php artisan about

# Vérifier les routes
php artisan route:list | grep api | head -20

# Tester la BDD
php artisan tinker --execute="echo DB::connection()->getPdo() ? 'BDD OK' : 'ERREUR';"

# Tester la commande cron manuellement
php artisan schedule:run
```

Accéder à `https://jeu.mondomaine.fr` — la page React doit s'afficher.
Tester `https://jeu.mondomaine.fr/api/reference/races` — doit retourner du JSON.

---

## Permissions des dossiers

```bash
chmod -R 775 /home/votrecompte/donjon/storage
chmod -R 775 /home/votrecompte/donjon/bootstrap/cache
```

---

## Variables d'environnement — Référence complète

| Variable | Valeur production | Description |
|----------|-------------------|-------------|
| `APP_ENV` | `production` | |
| `APP_DEBUG` | `false` | Ne jamais mettre `true` en prod |
| `APP_URL` | `https://jeu.mondomaine.fr` | URL complète avec HTTPS |
| `DB_CONNECTION` | `mysql` | |
| `DB_DATABASE` | `votrecompte_donjon` | Nom exact créé dans cPanel |
| `CACHE_STORE` | `file` | `database` aussi possible |
| `SESSION_DRIVER` | `database` | |
| `QUEUE_CONNECTION` | `database` | |
| `GEMINI_API_KEY` | `AIzaSy...` | Obtenir sur [Google AI Studio](https://aistudio.google.com/) |
| `AI_ENABLED` | `1` | `0` = fallbacks statiques uniquement |
| `AI_DAILY_BUDGET_LIMIT` | `500` | Limite d'appels IA par jour |
| `SANCTUM_STATEFUL_DOMAINS` | `jeu.mondomaine.fr` | Domaine(s) autorisés pour Sanctum |

---

## Mise à jour du jeu (déploiements suivants)

```bash
cd /home/votrecompte/donjon

# 1. Uploader les nouveaux fichiers (hors vendor/)
# 2. Installer/mettre à jour les dépendances
composer install --no-dev --optimize-autoloader

# 3. Appliquer les nouvelles migrations
php artisan migrate --force

# 4. Vider et reconstruire les caches
php artisan optimize:clear
php artisan optimize

# 5. Uploader le nouveau build React dans public_html/
# (npm run build en local, puis FTP)
```

---

## Résolution de problèmes courants

**Erreur 500 au chargement**
- Vérifier `APP_KEY` est définie dans `.env`
- Vérifier les permissions de `storage/` et `bootstrap/cache/`
- Consulter `/home/votrecompte/donjon/storage/logs/laravel.log`

**Page blanche React (pas d'erreur API)**
- Vérifier que `index.html` est présent dans `public_html/`
- Vérifier que le `.htaccess` redirige les routes non-API vers `index.html`

**Les appels `/api/...` retournent HTML au lieu de JSON**
- Vérifier que `RewriteRule` pour `/api/` pointe bien vers `index.php` dans `.htaccess`
- Vérifier que `index.php` pointe vers le bon chemin `donjon/`

**Cron ne s'exécute pas**
- Tester manuellement : `php artisan schedule:run --verbose`
- Vérifier le chemin PHP dans la tâche cron cPanel

**Images IA non générées**
- Vérifier `GEMINI_API_KEY` dans `.env`
- Vérifier `AI_ENABLED=1` dans la table `game_settings`
- Lancer manuellement : `php artisan queue:work --once`
