# Glinche Véhicules

Petite application qui récupère les véhicules de l'**API partenaires Glinche (V1)**, les affiche sous forme de cartes et permet de les **filtrer par marque**.

- **Back-end** : Laravel 12 (PHP 8.2+)
- **Front-end** : Vue.js 3 (Composition API, `<script setup>`) servi par Vite
- **Base de données** : SQLite par défaut (MySQL / PostgreSQL possibles sans changer le code)

---

## Prérequis

| Outil | Version |
| --- | --- |
| PHP | 8.2 ou plus, avec les extensions `pdo_sqlite`, `mbstring`, `curl`, `xml` |
| Composer | 2.x |
| Node.js | 20.19+ ou 22.12+ (requis par Vite 7) |
| npm | 10+ |

## Installation

```bash
git clone <url-du-depot> glinche-vehicles
cd glinche-vehicles

# Dépendances
composer install
npm install

# Configuration
cp .env.example .env
php artisan key:generate

# Base de données (SQLite)
touch database/database.sqlite        # Windows PowerShell : New-Item database/database.sqlite
php artisan migrate
```

## Variables d'environnement

Toutes les variables propres au projet sont dans `.env` (modèle : `.env.example`).

| Variable | Rôle | Valeur par défaut |
| --- | --- | --- |
| `GLINCHE_API_URL` | URL de base de l'API (préfixe `/api` inclus) | `https://marketplace-dev.glinche-automobiles.com/api` |
| `GLINCHE_API_EMAIL` | Identifiant fourni pour l'exercice | *(à renseigner)* |
| `GLINCHE_API_PASSWORD` | Mot de passe fourni pour l'exercice | *(à renseigner)* |
| `GLINCHE_API_TIMEOUT` | Timeout HTTP en secondes | `15` |
| `GLINCHE_TOKEN_TTL` | Durée de réutilisation du token (minutes) | `50` |
| `GLINCHE_SYNC_TTL` | Re-synchronisation automatique si la dernière date de plus de N minutes (`0` = désactivée) | `15` |
| `DB_CONNECTION` | `sqlite`, `mysql` ou `pgsql` | `sqlite` |

> Les identifiants ne sont volontairement **pas** commités : ils sont à copier dans le `.env`.

Pour utiliser MySQL plutôt que SQLite :

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=glinche
DB_USERNAME=root
DB_PASSWORD=
```

## Lancer Laravel

```bash
# (optionnel) premier import des véhicules depuis l'API
php artisan vehicles:sync

# serveur PHP sur http://localhost:8000
php artisan serve
```

La synchronisation manuelle est optionnelle : si la base est vide au premier affichage, l'application va chercher les véhicules toute seule.

## Lancer Vue.js

Dans un **second terminal** :

```bash
npm run dev      # serveur Vite avec rechargement à chaud
```

Puis ouvrir **http://localhost:8000** (c'est Laravel qui sert la page ; Vite ne fait que fournir les assets).

Pour une version de production des assets : `npm run build` (il n'est alors plus nécessaire de lancer `npm run dev`).

## Tests

```bash
php artisan test   # back : mapping des annonces, synchro, filtre, marques, erreurs API (HTTP simulé)
npm test           # front : formatage, composable, rendu et filtre de l'application (Vitest)
```

Les tests back n'appellent jamais la vraie API (`Http::fake()` + `tests/Fixtures/vehicles.json`, construit à partir du schéma OpenAPI de la doc).

---

## Fonctionnement

```
Navigateur (Vue)                 Laravel                               API Glinche V1
─────────────────               ─────────────────────────             ──────────────────
GET /api/vehicles?brand=…  ───▶  VehicleController
GET /api/brands            ───▶    └─ VehicleSynchronizer::syncIfStale()
                                        │ (base vide ou synchro > 15 min)
                                        └─ GlincheClient ─────────▶  POST /partners/login
                                                                    GET  /partners/vehicles
                                        AdvertMapper → table `vehicles`
                                 ◀─── lecture SQL (filtre marque, liste des marques)
```

### Endpoints exposés

| Méthode | URL | Description |
| --- | --- | --- |
| `GET` | `/api/vehicles?brand=Renault` | Véhicules (filtre optionnel, insensible à la casse) |
| `GET` | `/api/vehicles/{id}` | Fiche détail : galerie, caractéristiques par section, équipements, options |
| `GET` | `/api/brands` | Marques présentes en stock + nombre de véhicules |
| `POST` | `/api/vehicles/sync` | Force une re-synchronisation (limitée à 5 appels/min) |

Commande Artisan : `php artisan vehicles:sync` (aussi planifiée toutes les 15 min dans `routes/console.php`, active via `php artisan schedule:work`).

### Arborescence utile

```
app/
├─ Console/Commands/SyncVehicles.php      commande vehicles:sync
├─ Http/Controllers/Api/VehicleController.php
├─ Http/Resources/VehicleResource.php     format JSON renvoyé au front
├─ Models/Vehicle.php
└─ Services/Glinche/
   ├─ GlincheClient.php                   appels HTTP + gestion du token
   ├─ AdvertMapper.php                    annonce API → colonnes de la table
   ├─ VehicleDetailPresenter.php          annonce API → fiche détail lisible (sections, unités, libellés)
   ├─ VehicleSynchronizer.php             upsert / suppression / synchro auto
   └─ GlincheApiException.php
resources/js/
├─ App.vue                                page : filtre, tri, grille, états
├─ components/BrandFilter.vue
├─ components/VehicleCard.vue
├─ components/VehicleModal.vue            fiche détail (<dialog> native)
├─ components/VehicleGallery.vue          galerie photos de la fiche
├─ composables/useVehicles.js             état + appels API + cache par marque
├─ api.js / format.js
```

---

## Choix techniques

**Les appels à l'API Glinche passent uniquement par Laravel.** Le navigateur ne voit jamais les identifiants ni le token. Le front ne parle qu'à l'API de l'application.

**Stockage en base plutôt que simple proxy.** Les véhicules sont synchronisés dans une table `vehicles` (upsert sur la référence de l'annonce, suppression de ce qui a disparu du flux, annonces avec `deleteDate` ignorées). Cela permet :
- de ne pas dépendre de la latence ou d'une indisponibilité ponctuelle de l'API (si une re-synchro échoue, on continue de servir les données en base) ;
- de faire le filtre par marque et la liste des marques en SQL (`GROUP BY brand`), l'API V1 ne proposant pas de filtre ;
- de justifier l'usage d'une base relationnelle demandé dans l'énoncé.

**Synchronisation « paresseuse ».** Pas besoin de penser à lancer une commande : la première requête remplit la base, puis les données sont rafraîchies au plus toutes les 15 minutes. Un verrou de cache empêche deux requêtes simultanées de lancer chacune une synchro.

**Token mis en cache.** Le login n'est fait qu'une fois toutes les 50 minutes. Si l'API répond `401` (token expiré ou révoqué), le client se reconnecte **une** fois et rejoue la requête.

**Liste des marques construite à partir des données.** Rien n'est écrit en dur. Les marques sont normalisées (`RENAULT`, `renault` → `Renault`, sigles comme `BMW` ou `DS` conservés) pour éviter les doublons dans le filtre.

**Mapping isolé et testable.** `AdvertMapper` ne dépend pas du framework. Il gère les écarts entre la doc et la réalité : prix envoyés en chaîne de caractères, prix public absent (repli sur le prix marchand), photo `MAIN` absente (repli sur la première photo), année absente (déduite de la date de mise en circulation). Les codes de l'API (`EH`, `GO`, `AUT`…) sont traduits en libellés lisibles.

**Fiche détail.** Les colonnes de la table ne servent qu'à ce qui est filtré, trié ou affiché dans la liste. L'annonce complète est conservée dans une colonne JSON `raw` (jamais renvoyée dans la liste) et transformée à la demande par `VehicleDetailPresenter` : sections thématiques (général, motorisation, consommation, électrique, couleurs, dimensions, garantie), équipements groupés par catégorie, options avec leur prix. Les codes de l'API sont traduits, les unités formatées à la française et les champs vides omis. Les informations internes ou sensibles ne sont pas exposées : VIN, immatriculation, prix marchand / partenaire, suivi documentaire.

**Front.**
- Cartes responsive (grille `auto-fill`), filtre en « chips » qui défilent horizontalement sur mobile.
- Le changement de marque annule la requête précédente (`AbortController`) et les résultats déjà chargés sont gardés en mémoire : revenir sur une marque est instantané.
- La marque choisie est reflétée dans l'URL (`?marque=Renault`) : lien partageable et filtre conservé au rechargement.
- États gérés : chargement (squelettes), liste vide, erreur avec bouton « Réessayer », image manquante ou cassée.
- Tri côté client (prix, kilométrage, année).
- Le bouton « Voir » (ou un clic sur la photo) ouvre une modale de détail basée sur l'élément natif `<dialog>` : piège du focus, touche Échap et fond assombri gérés par le navigateur ; plein écran sur mobile. Les infos de la carte s'affichent tout de suite, le détail se charge ensuite puis reste en mémoire.
- Accessibilité de base : `aria-pressed` sur les filtres, `aria-live` sur le compteur, focus visible, `prefers-reduced-motion` et thème sombre respectés.
- CSS écrit à la main (pas de framework) : l'interface est simple et cela évite une dépendance de plus.

## Difficultés rencontrées

- **Documentation rendue en JavaScript** : la page de doc est une interface Swagger chargée côté client. J'ai travaillé à partir du fichier OpenAPI (`/docs/openapi.json`) pour avoir le détail exact des schémas (`advert`, `vehicle`, `prices`, `pictures`).
- **Types peu homogènes** : les prix sont décrits comme des `string` (avec des exemples numériques), certains champs utiles sont optionnels (`price`, `year`, photos). Le mapper tolère ces variations plutôt que de supposer un format parfait.
- **Libellés codés** : énergie, boîte et type d'offre arrivent sous forme de codes ; il a fallu reprendre les tables de correspondance de la doc.
- **Doublons de marques potentiels** selon la casse renvoyée, d'où la normalisation.

## Avec plus de temps

- **Pagination** (côté API Laravel + « Charger plus » côté front) si le stock devient important.
- **Filtres supplémentaires** : énergie, fourchette de prix et de kilométrage, recherche texte — le schéma de base les permet déjà.
- **Lien direct vers une fiche** (`?vehicule=12` dans l'URL) pour partager un véhicule précis.
- **Synchronisation en file d'attente** (job Laravel) pour ne jamais faire attendre une requête utilisateur, avec un statut de dernière synchro affiché dans l'interface.
- Tests end-to-end (Playwright) et CI (GitHub Actions : Pint, PHPUnit, Vitest, build).
