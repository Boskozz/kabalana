# Guide de déploiement — Hébergement mutualisé (OVH, Infomaniak…)

Ce guide explique comment mettre le site en ligne sur un hébergement mutualisé classique (Apache + MySQL/MariaDB + PHP, avec phpMyAdmin et FTP).

---

## 0. Vérifications préalables chez l'hébergeur

Avant toute chose, assurez-vous que l'hébergement propose :

| Prérequis | Valeur |
|---|---|
| **PHP** | **≥ 8.4** (sinon le site ne s'installera pas) |
| Extensions PHP | `pdo_mysql`, `mbstring`, `xml`, `curl`, `ctype`, `iconv`, `zip` |
| Base de données | MySQL 8 ou MariaDB 10.6+ |
| Accès | FTP/SFTP + phpMyAdmin (SSH recommandé si dispo) |

> 💡 Chez OVH, la version PHP se règle dans le panel → « Hébergement → Onglet *Configuration* → *Configuration* → PHP ». Choisissez **PHP 8.4**.

---

## 1. Préparer le dossier du site en local

### 1.1 Récupérer le code source

Si le projet est dans un dépôt git, clonez-le sur votre machine. Sinon, copiez simplement le dossier du projet.

### 1.2 Fichier de configuration de production

Créez un fichier **`.env.prod.local`** à la racine du projet (à ne **jamais** mettre en ligne tel quel, il contient vos secrets) :

```dotenv
# .env.prod.local
APP_ENV=prod

# ⚠️ Remplacez par votre propre secret ou utilisez celui fourni ci-dessous
APP_SECRET=67414bfbe89368a773b90910861013313de2635ca6c2d1eba36e201692feb07b

# ⚠️ À adapter : identifiants MySQL/MariaDB de l'hébergeur
DATABASE_URL="mysql://UTILISATEUR:MOTDEPASSE@HOTE:3306/NOM_BASE?serverVersion=8.0&charset=utf8mb4"
```

> **Important** : si l'hébergeur utilise **MariaDB**, remplacez `serverVersion=8.0` par ex. par `serverVersion=mariadb-10.11.2`.

> **App Security (APP_SECRET)** : la valeur ci-dessus est temporaire et a été affichée. Pour un meilleur secret, générez-en un avec : `php -r "echo bin2hex(random_bytes(32));"` et copiez la sortie (64 caractères).

---

## 2. Créer la base de données

1. Ouvrez **phpMyAdmin** (lien fourni par l'hébergeur).
2. Créez une base de données (ex. `alexandrak_prod`), encodage **utf8mb4_general_ci**.
3. Notez le nom de la base, l'utilisateur et le mot de passe → reportez-les dans `.env.prod.local` (`DATABASE_URL`).

### Deux scénarios possibles :

**A. Vous voulez conserver tout le contenu actuel (recommandé)**
Dans phpMyAdmin sur votre **serveur local**, exportez la base `alexandrak` (format SQL, « Données + structure »), puis importez ce fichier dans la nouvelle base en ligne. Tout le contenu (pages, blocs, images, compte admin) sera copié tel quel.

**B. Base vide**
Après mise en ligne (voir plus bas), vous créerez les tables avec les migrations :
```bash
php bin/console doctrine:migrations:migrate --env=prod
```

---

## 3. Installer les dépendances (Composer)

**Si l'hébergement propose SSH + Composer (recommandé)** :
```bash
cd /chemin/vers/le/projet
composer install --no-dev --optimize-autoloader --no-interaction
```

**Sinon (FTP uniquement)** : exécutez ces commandes **en local**, puis uploadez le dossier `vendor/` avec le reste :
```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

---

## 4. Compiler les assets (CSS/JS)

Encore **en local** (ou sur le serveur si SSH) :
```bash
php bin/console importmap:install
php bin/console asset-map:compile --env=prod
```
Cette commande génère le dossier `public/assets/` (compilé) qu'il faudra aussi envoyer.

---

## 5. Envoyer les fichiers (FTP)

1. Ouvrez votre client FTP (FileZilla…).
2. Uploadez **tout le dossier du projet** vers l'hébergement.
3. Vérifiez que ces dossiers sont bien présents :
   - `vendor/` (dépendances)
   - `public/assets/` (assets compilés)
   - `public/uploads/` (images, documents, mandala)
   - `public/favicon.ico`, `public/mandala/mandala.webp`
   - `.env.prod.local` (créé en local à l'étape 1.2)
4. **Pointez la racine du site vers `public/`** (très important) :
   - **OVH** : panel → Hébergement → Multisite → « Racine du site » → indiquez `public`.
   - **Infomaniak** : Manager → Site → Réglages → Dossier racine → `public`.
   - Si l'hébergeur ne le permet pas, voir la section « Astuce sans réglage de la racine » ci-dessous.

### Astuce sans réglage de la racine (solution de secours)
Si la racine est fixée sur `www/` ou `public_html/`, uploadez le contenu de `public/` (index.php, .htaccess, bundles, uploads…) dans `public_html/`, et placez le **reste du projet** dans un dossier au même niveau (ex. `../app/`). Symfony fonctionne alors correctement car il est conçu pour être appelé depuis `public/index.php`.

---

## 6. Permissions (dossiers en écriture)

Assurez-vous que ces dossiers sont **accessibles en écriture** par PHP (droits `755` ou `775`) :
- `var/` (cache et logs)
- `public/uploads/` (pour les uploads depuis l'admin)

---

## 7. Passer en mode production

Sur le serveur, éditez le fichier **`.env`** et changez :
```dotenv
APP_ENV=dev
```
en :
```dotenv
APP_ENV=prod
```

Puis, toujours sur le serveur (SSH) ou en local avant upload :
```bash
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

---

## 8. Créer le compte administrateur

**Si vous avez importé la base existante (scénario A)** : le compte admin est déjà inclus, passez à l'étape 9.

**Sinon** (base vide), créez le compte :
```bash
php bin/console app:create-admin "email@exemple.com" "VotreMotDePasseFort" --env=prod
```
Pour changer le mot de passe plus tard :
```bash
php bin/console app:create-admin "email@exemple.com" "NouveauMotDePasse" --update --env=prod
```

---

## 9. HTTPS (obligatoire)

Activez le certificat SSL dans le panel de l'hébergeur (Let's Encrypt gratuit chez OVH/Infomaniak). Le formulaire de connexion admin (`/login`) doit absolument passer par HTTPS.

---

## 10. Sauvegardes

Mettez en place des sauvegardes régulières :
- **Base de données** : export phpMyAdmin chaque semaine (ou automatisation chez l'hébergeur).
- **`public/uploads/`** : téléchargement régulier via FTP (c'est là que vivent photos et documents).

---

## 11. Vérification finale

- [ ] `https://votredomaine.com/` affiche l'accueil
- [ ] La navigation (menus déroulants + mandala) fonctionne
- [ ] `/admin` redirige vers `/login` (pas d'accès direct)
- [ ] La connexion admin fonctionne avec le compte créé
- [ ] Les pages listées dans l'admin s'affichent correctement
- [ ] Les images/documents des blocs s'affichent (uploads OK)

---

## 🔧 Problèmes fréquents

| Symptôme | Cause probable | Solution |
|---|---|---|
| Erreur 500 au chargement | `APP_ENV=dev` encore actif ou `APP_SECRET` vide | Vérifiez `.env` (APP_ENV=prod) et `.env.prod.local` (APP_SECRET non vide) |
| Erreur 500 après upload | Cache non vidé / assets absents | Relancez `cache:clear --env=prod` et vérifiez `public/assets/` |
| « Unable to connect to the database » | Mauvais `DATABASE_URL` ou MariaDB mal déclaré | Vérifiez identifiants et `serverVersion` dans `.env.prod.local` |
| Connexion admin impossible | Sessions / HTTPS non configuré | Activez HTTPS et videz le cache |
| Images absentes | `public/uploads/` non transféré ou non accessible en écriture | Re-upload et vérifiez les permissions |
| Page blanche à l'accueil | Racine du site non pointée vers `public/` | Corrigez la « racine du site » dans le panel |

---

## Notes importantes

- **`.env.prod.local` ne doit jamais être versionné** (il est déjà ignoré par `.gitignore`). Il se crée directement sur le serveur.
- Ne partagez jamais `APP_SECRET` ni les mots de passe de base de données.
- Le mandala actuel (`public/mandala/mandala.webp`) pèse ~978 Ko : pensez à le compresser avant la mise en ligne pour un chargement plus rapide.
