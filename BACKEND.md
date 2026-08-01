# ClubHub v1.0.0 — Backend PHP/MySQL

## Stack
- PHP 7.4+ (PDO, JSON, password_hash, random_bytes)
- MySQL 5.7+ / MariaDB 10.3+
- Apache (`.htaccess`) ou tout serveur supportant PHP

## Installation locale (XAMPP / WAMP / MAMP)

```bash
# 1. Copier le projet dans le repertoire web
cp -r clubhub-v2/ /xampp/htdocs/clubhub

# 2. Demarrer Apache + MySQL depuis XAMPP

# 3. Creer la base
mysql -u root -p < database/schema.sql
mysql -u root -p clubhub < database/seed.sql

# 4. Configurer la connexion
cp api/config/db.php api/config/db.local.php
# editer db.local.php avec vos identifiants MySQL
# OU definir les variables d'environnement :
export CLUBHUB_DB_HOST=localhost
export CLUBHUB_DB_NAME=clubhub
export CLUBHUB_DB_USER=root
export CLUBHUB_DB_PASS=
export CLUBHUB_JWT_SECRET=your-secret-key

# 5. Tester l'API
curl http://localhost/clubhub/api/health
# -> {"ok":true,"data":{"service":"ClubHub API","version":"1.0.0","status":"ok"}}
```

## Comptes de demonstration

| Role  | Matricule | Mot de passe | Email        |
|-------|-----------|--------------|--------------|
| Etudiant   | MA  | demo1234 | mika@u.fr   |
| Manager    | SA  | demo1234 | sarah@u.fr  |
| Admin      | HR  | demo1234 | hery@u.fr   |

## Endpoints

| Methode | URL                              | Description                          | Auth |
|---------|----------------------------------|--------------------------------------|------|
| GET     | /api/health                      | Health check                         | -    |
| POST    | /api/login                       | Connexion (matricule + password)     | -    |
| POST    | /api/logout                      | Deconnexion                          | -    |
| GET     | /api/me                          | Utilisateur courant                  | x    |
| GET     | /api/clubs                       | Liste clubs (?q=&category=&sort=)   | -    |
| GET     | /api/clubs?id=2                  | Detail d'un club                     | -    |
| POST    | /api/clubs                       | Creer un club                        | admin |
| PUT     | /api/clubs?id=2                  | Modifier un club                     | admin |
| DELETE  | /api/clubs?id=2                  | Archiver un club                     | admin |
| GET     | /api/events                      | Liste events (?from=&to=&club_id=)   | -    |
| POST    | /api/events                      | Creer un event                       | manager+ |
| DELETE  | /api/events?id=5                 | Supprimer un event                   | manager+ |
| GET     | /api/inscriptions                | Mes clubs inscrits                   | x    |
| GET     | /api/inscriptions?type=events    | Mes events inscrits                  | x    |
| POST    | /api/inscriptions                | Rejoindre un club (body: club_id)    | x    |
| DELETE  | /api/inscriptions                | Quitter un club (body: club_id)      | x    |
| GET     | /api/notifications               | Mes notifications                    | x    |
| POST    | /api/notifications?read_all=1    | Tout marquer comme lu                | x    |
| GET     | /api/stats                       | Statistiques globales / par club     | x    |

## Securite

- Mots de passe : `password_hash` (bcrypt)
- Tokens : 64 chars hex stockes en BDD, expiration 24h
- Cookie httpOnly + SameSite=Lax (anti-XSS / CSRF)
- Toutes les requetes SQL en requetes preparees (anti-injection)
- Headers CORS restrictifs (a configurer en prod)
- Audit log de toutes les actions sensibles

## Branchement cote front

Le fichier `js/api.js` detecte automatiquement le mode :
- Mode **mock** (defaut) : utilise `data.js` (statique, 0 serveur)
- Mode **API** : parle au backend PHP

Pour basculer en mode API :
```js
localStorage.setItem('clubhub_api_mode', 'live');
// ou ajouter ?api=live dans l'URL
```

Le code existant (login, dashboard, clubs, etc.) continue de marcher
grace au fallback mock. Pour brancher les actions reelles
(joindre un club, creer un event), il suffit d'appeler
`ClubHubAPI.joinClub(id)` au lieu de muter `CLUBHUB.CLUBS`.

## Schema BDD

8 tables :
- `users` — comptes (matricule, role, filiere, points, managed_club_id)
- `clubs` — clubs (slug, name, category, color, tags JSON, status)
- `events` — evenements (club_id, date, time, capacity, registered)
- `inscriptions` — liaison user/club/an + role
- `presences` — liaison user/event + statut
- `notifications` — alertes par user
- `audit_log` — journal immutable
- `auth_tokens` — tokens de session

## Algorithmes implementes

- Detection de conflits d'horaires (`/api/events` : tableau `conflicts`)
- Pagination (LIMIT/OFFSET)
- Compteurs agreges (membres par club, par filiere)
- Hash bcrypt pour mots de passe
- Tokens aleatoires 64 chars (random_bytes)
- Audit log automatique sur chaque action

## Production checklist

- [ ] HTTPS obligatoire (Let's Encrypt)
- [ ] Changer `JWT_SECRET` et `DB_PASS`
- [ ] Limiter `ALLOWED_ORIGIN` au domaine frontend
- [ ] Activer le rate limiting (ex: fail2ban)
- [ ] Sauvegardes automatiques (mysqldump cron)
- [ ] Monitoring (Prometheus + Grafana)
- [ ] Logs centralises (Loki)
- [ ] RGPD : duree de retention 3 ans, anonymisation
