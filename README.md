# DolicraftS3

**Connectez Dolibarr à n'importe quel stockage cloud compatible S3.**

[![Dolibarr 16+](https://img.shields.io/badge/Dolibarr-16.0%2B-blue)](https://www.dolibarr.org)
[![PHP 7.4+](https://img.shields.io/badge/PHP-7.4%2B-8892BF)](https://www.php.net)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-green.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![Version](https://img.shields.io/badge/Version-1.0.0-orange)](https://github.com/Dolicraft/DolicraftS3/releases)
[![DoliStore](https://img.shields.io/badge/DoliStore-Gratuit-brightgreen)](https://www.dolistore.com/product.php?id=2904&title=dolicrafts3-stockage-s3-multi-cloud&l=fr)

---

## Pourquoi ce module ?

Dolibarr stocke tous les fichiers en local sur le serveur. Pas de redondance, pas de CDN, pas de scalabilité. Si le disque tombe, les fichiers sont perdus.

**DolicraftS3** permet de connecter Dolibarr à n'importe quel service de stockage compatible S3 : AWS, OVH, Scaleway, Wasabi, MinIO, Cloudflare R2, et bien d'autres. Vos fichiers sont dans le cloud, accessibles partout, avec la sécurité et la redondance que le stockage local ne peut pas offrir.

Le module est gratuit parce que le stockage cloud devrait être une fonctionnalité de base de tout ERP moderne.

---

## Fournisseurs supportés

| Fournisseur | Type | Région |
|-------------|------|--------|
| **Amazon Web Services (AWS S3)** | Cloud public | Mondial |
| **OVHcloud Object Storage** | Cloud européen | EU |
| **Scaleway Object Storage** | Cloud européen | FR/NL/PL |
| **Wasabi** | Stockage hot | Mondial |
| **MinIO** | Auto-hébergé | Local |
| **DigitalOcean Spaces** | Cloud public | Mondial |
| **Backblaze B2** | Stockage froid | US/EU |
| **Cloudflare R2** | Edge storage | Mondial |
| Tout endpoint compatible S3 | Custom | - |

---

## Fonctionnalités

### Navigateur de fichiers S3

Parcourez le contenu de votre bucket directement dans l'interface Dolibarr. Navigation par dossiers, affichage des tailles et dates de modification.

### Upload de fichiers

Envoyez des fichiers vers votre bucket S3 depuis Dolibarr. Upload simple ou multiple.

### Liens temporaires (Presigned URLs)

Générez des URLs temporaires sécurisées pour partager des fichiers avec des tiers sans leur donner accès au bucket.

### Modes de synchronisation

| Mode | Description |
|------|-------------|
| **Manuel** | Upload et téléchargement à la demande |
| **Auto-upload** | Les nouveaux fichiers Dolibarr sont automatiquement envoyés vers S3 |
| **Synchronisation complète** | Synchronisation bidirectionnelle entre Dolibarr et S3 |

### Permissions granulaires

4 niveaux de permissions indépendants :

- **Lire** - Naviguer et télécharger les fichiers S3
- **Uploader** - Envoyer des fichiers vers le bucket
- **Supprimer** - Supprimer des fichiers du bucket
- **Configurer** - Accéder aux paramètres du module

### Journaux d'activité

Chaque opération S3 (upload, download, suppression) est tracée avec l'utilisateur, la date et le fichier concerné.

---

## Installation

### Depuis GitHub

```bash
cd /path/to/dolibarr/htdocs/custom/
git clone https://github.com/Dolicraft/DolicraftS3.git dolicrafts3
```

Puis activez le module dans **Configuration > Modules**.

### Depuis le DoliStore

1. Télécharger le module sur le [DoliStore](https://www.dolistore.com/product.php?id=2904&title=dolicrafts3-stockage-s3-multi-cloud&l=fr)
2. Extraire le dossier `dolicrafts3` dans `htdocs/custom/`
3. Activer le module dans **Configuration > Modules**

---

## Configuration

Après activation, allez dans **DolicraftS3 > Configuration** :

| Paramètre | Description | Obligatoire |
|-----------|-------------|:-----------:|
| Fournisseur | Choix du provider S3 (pré-remplit l'endpoint) | Non |
| Endpoint URL | URL du service S3 | Oui |
| Région | Région du bucket (ex: eu-west-1) | Non |
| Access Key | Clé d'accès | Oui |
| Secret Key | Clé secrète (stockée chiffrée) | Oui |
| Bucket | Nom du bucket | Oui |
| Préfixe | Sous-dossier dans le bucket | Non |
| Path-Style | Activer pour MinIO / auto-hébergé | Non |
| Mode sync | Manuel, auto-upload ou complet | Non |

### Configuration rapide par fournisseur

Sélectionnez votre fournisseur dans le dropdown : l'endpoint et la région sont pré-remplis automatiquement. Il ne reste qu'à saisir vos clés d'accès et le nom du bucket.

---

## Compatibilité

| Composant | Version |
|-----------|---------|
| Dolibarr | 16.0 et supérieur |
| PHP | 7.4 et supérieur |
| Base de données | MySQL 5.7+ / MariaDB 10.3+ |

Aucune dépendance externe.

---

## Langues supportées

- Français (fr_FR)
- English (en_US)
- Español (es_ES)
- Deutsch (de_DE)
- Italiano (it_IT)
- Português Brasil (pt_BR)

---

## Contribuer

Les contributions sont les bienvenues !

1. Fork le projet
2. Créez une branche (`git checkout -b feature/ma-feature`)
3. Committez vos changements (`git commit -m 'feat: ma feature'`)
4. Pushez (`git push origin feature/ma-feature`)
5. Ouvrez une Pull Request

### Signaler un bug

Ouvrez une [issue](https://github.com/Dolicraft/DolicraftS3/issues) avec :
- Votre version de Dolibarr et PHP
- Le fournisseur S3 utilisé
- Les étapes pour reproduire le bug
- Le message d'erreur

---

## À propos de Dolicraft

**Dolicraft** développe des modules professionnels pour Dolibarr ERP/CRM.

- Site : [dolicraft.com](https://dolicraft.com)
- Email : contact@dolicraft.com
- DoliStore : [Modules Dolicraft](https://www.dolistore.com)

---

## Licence

Ce module est distribué sous licence [GNU General Public License v3.0](LICENSE).

Copyright (C) 2024-2026 [Dolicraft](https://dolicraft.com) - contact@dolicraft.com

---

---

# DolicraftS3 (English)

**Connect Dolibarr to any S3-compatible cloud storage.**

## Why this module?

Dolibarr stores all files locally on the server. No redundancy, no CDN, no scalability. If the disk fails, files are lost.

**DolicraftS3** connects Dolibarr to any S3-compatible storage service: AWS, OVH, Scaleway, Wasabi, MinIO, Cloudflare R2, and more. Your files are in the cloud, accessible everywhere, with the security and redundancy that local storage cannot provide.

This module is free because cloud storage should be a basic feature of any modern ERP.

## Supported Providers

- Amazon Web Services (AWS S3)
- OVHcloud Object Storage
- Scaleway Object Storage
- Wasabi
- MinIO (self-hosted)
- DigitalOcean Spaces
- Backblaze B2
- Cloudflare R2
- Any S3-compatible endpoint

## Features

- **S3 File Browser** - Browse your bucket contents directly in Dolibarr
- **File Upload** - Single and multi-file upload to S3
- **Presigned URLs** - Generate secure temporary links for file sharing
- **Sync Modes** - Manual, auto-upload, or full bidirectional sync
- **Granular Permissions** - Read, upload, delete, configure (4 levels)
- **Activity Logging** - Track every S3 operation
- **Quick Provider Setup** - Select your provider and endpoint/region auto-fills
- **6 Languages** - French, English, Spanish, German, Italian, Portuguese

## Installation

### From GitHub

```bash
cd /path/to/dolibarr/htdocs/custom/
git clone https://github.com/Dolicraft/DolicraftS3.git dolicrafts3
```

Then activate the module in **Setup > Modules**.

### From DoliStore

Download from the [DoliStore](https://www.dolistore.com/product.php?id=2904&title=dolicrafts3-stockage-s3-multi-cloud&l=fr), extract into `htdocs/custom/`, and activate.

## Requirements

- Dolibarr 16.0+
- PHP 7.4+
- No external dependencies

## About Dolicraft

**Dolicraft** builds professional modules for Dolibarr ERP/CRM.

- Website: [dolicraft.com](https://dolicraft.com)
- Email: contact@dolicraft.com

## License

[GNU General Public License v3.0](LICENSE) - Copyright (C) 2024-2026 [Dolicraft](https://dolicraft.com)
