# Rapport de Correction: Détection "상주" et Re-téléchargement

**Date**: 2025-11-06
**Session**: Correction des bugs de détection du mot-clé "상주" et de la fonctionnalité de re-téléchargement

## Problèmes Résolus

### 1. Détection du mot-clé "상주" pour le tender 1715 ❌ → ✅

**Problème Signalé par l'Utilisateur**:
- URL: https://nara.tideflo.work/admin/tenders/1715
- Le système ne détectait pas le mot "상주" dans le fichier `제안요청서.hwp`
- L'utilisateur a insisté que le mot était bien présent dans le fichier
- Citation: "여기 상주가 있다고 이 공고에 제안요청서.hwp에 상주가 나온다고 지금 너가 제안요청서.hwp를 자꾸 깨진 걸 읽으니까 그런 거라고"

**Cause Racine**:
- Le script Python `extract_hwp_text_multi.py` produisait du texte corrompu/illisible
- Les méthodes personnalisées d'extraction HWP ne fonctionnaient pas correctement
- Le texte extrait était "garbled" (brouillé), rendant la détection impossible

**Solution Implémentée**:
1. **Création d'un nouveau script**: `/home/tideflo/nara/public_html/scripts/extract_hwp_text_hwp5.py`
   - Utilise l'outil officiel `hwp5txt` de la bibliothèque `pyhwp`
   - Appelle `hwp5txt` via subprocess pour une extraction fiable
   - Path: `/home/tideflo/nara/public_html/storage/hwp_venv/bin/hwp5txt`

2. **Mise à jour de TenderController.php**:
   - Ligne 613: Changement de `extract_hwp_text.py` → `extract_hwp_text_hwp5.py`
   - Ligne 684: Même changement pour les fichiers attachés

3. **Mise à jour de SangjuCheckService.php**:
   - Ligne 128: Changement vers le nouveau script hwp5txt

**Résultat de Vérification**:
```
Tender: R25BK01136215 (ID: 1715)
File: 제안요청서.hwp

상주 keyword found: YES ✅

Matched lines:
  -> 나. 유지관리 인력을 도청에 상주시켜 사용자 요청에 즉각 대응
  -> - 상주인력(2명 이상)
  -> ◦ 본 사업 수행에 필요한 작업장소는 충청남도에서 제공하는 장소에서 상주하는 것을 원칙으로 하며,
```

**Fichiers Modifiés**:
- ✅ `/home/tideflo/nara/public_html/scripts/extract_hwp_text_hwp5.py` (Créé)
- ✅ `/home/tideflo/nara/public_html/app/Http/Controllers/Admin/TenderController.php` (2 emplacements)
- ✅ `/home/tideflo/nara/public_html/app/Services/SangjuCheckService.php` (1 emplacement)

---

### 2. Fonctionnalité de Re-téléchargement pour le tender 1769 ❌ → ✅

**Problème Signalé par l'Utilisateur**:
- URL: https://nara.tideflo.work/admin/tenders/1769
- La collecte initiale des fichiers fonctionne
- Le bouton "재다운로드" (re-télécharger) ne fonctionne pas
- Citation: "뭐야 분명 제안요청정보 파일 수집 잘 됐었는데 https://nara.tideflo.work/admin/tenders/1769 이 공고에서 가져오는 건 되는데 재다운로드가 안돼"

**Cause Racine**:
- Méthode `downloadAttachment()` manquante dans `AttachmentService`
- Méthode `downloadSingleFile()` manquante dans `ProposalFileCrawlerService`
- Aucune logique pour re-télécharger un fichier individuel

**Solution Implémentée**:

1. **AttachmentService.php** - Ajout de la méthode `downloadAttachment()`:
   ```php
   public function downloadAttachment(Attachment $attachment): void
   {
       // 1ère étape: Rafraîchir les métadonnées via ProposalFileCrawlerService
       $crawler = app(\App\Services\ProposalFileCrawlerService::class);
       $metaResult = $crawler->downloadSingleFile($attachment);

       // 2ème étape: Télécharger le fichier réel via ProposalFileDownloaderService
       $downloader = app(\App\Services\ProposalFileDownloaderService::class);
       $downloadResult = $downloader->downloadFile($attachment->fresh());
   }
   ```

2. **ProposalFileCrawlerService.php** - Ajout de la méthode `downloadSingleFile()`:
   ```php
   public function downloadSingleFile(Attachment $attachment): array
   {
       // Récupère la liste des fichiers via Playwright
       $files = $this->fetchProposalFilesWithPlaywright($detailUrl);

       // Trouve le fichier correspondant par nom
       $targetFile = null;
       foreach ($files as $file) {
           if ($file['file_name'] === $attachment->file_name ||
               $file['file_name'] === $attachment->original_name) {
               $targetFile = $file;
               break;
           }
       }

       // Met à jour les métadonnées d'attachment
       $attachment->update([
           'download_url' => $targetFile['download_url'] ?? null,
           'post_data' => $targetFile['post_data'] ?? null,
           'doc_name' => $targetFile['doc_name'] ?? $attachment->doc_name,
           'download_status' => 'pending'
       ]);
   }
   ```

**Workflow Complet**:
1. `AttachmentController::redownload()` appelle `AttachmentService::downloadAttachment()`
2. `downloadAttachment()` appelle `ProposalFileCrawlerService::downloadSingleFile()` pour rafraîchir les métadonnées
3. `downloadAttachment()` appelle ensuite `ProposalFileDownloaderService::downloadFile()` pour télécharger le fichier réel
4. Le fichier est sauvegardé dans `storage/app/proposal_files/{tender_id}/`
5. Les métadonnées de l'attachment sont mises à jour (`download_status`, `local_path`, `file_size`)

**Résultat de Vérification**:
```
Tender 1769: R25BK01137191

Fichier 1 (ID: 38):
  File: 2026년 일반행정 정보시스템 통합 유지보수 제안요청서(수정).hwp
  Status: completed ✅
  Local path: proposal_files/1769/2026년 일반행정 정보시스템 통합 유지보수 제안요청서(수정).hwp
  File size: 281,600 bytes

Fichier 2 (ID: 39):
  File: 기술지원협약서.pdf
  Status: completed ✅
  Local path: proposal_files/1769/기술지원협약서.pdf
  File size: 13,268,928 bytes

Vérification disque:
-rw-rw-r-- 1 tideflo tideflo 275K Nov  6 13:58 2026년 일반행정 정보시스템 통합 유지보수 제안요청서(수정).hwp
-rw-rw-r-- 1 tideflo tideflo 13M  Nov  6 13:59 기술지원협약서.pdf
```

**Fichiers Modifiés**:
- ✅ `/home/tideflo/nara/public_html/app/Services/AttachmentService.php` (méthode `downloadAttachment()` ajoutée)
- ✅ `/home/tideflo/nara/public_html/app/Services/ProposalFileCrawlerService.php` (méthode `downloadSingleFile()` ajoutée)

---

## Tests Créés

### Script de Test: `test_redownload_functionality.sh`
**Emplacement**: `/home/tideflo/nara/public_html/scripts/test_redownload_functionality.sh`

**Tests Inclus**:
1. ✅ Vérification du statut des attachments du tender 1769
2. ✅ Test de re-téléchargement fichier HWP (attachment 38)
3. ✅ Test de re-téléchargement fichier PDF (attachment 39)
4. ✅ Vérification des fichiers sur le disque
5. ✅ Test de détection du mot-clé "상주" sur les fichiers téléchargés

**Commande**:
```bash
bash /home/tideflo/nara/public_html/scripts/test_redownload_functionality.sh
```

---

## Résumé des Corrections

### Problème 1: Détection "상주"
- **Avant**: Script Python personnalisé produisant du texte corrompu
- **Après**: Utilisation de l'outil officiel `hwp5txt` de pyhwp
- **Résultat**: Détection réussie de "상주" dans tender 1715 ✅

### Problème 2: Re-téléchargement
- **Avant**: Méthodes manquantes, fonctionnalité non implémentée
- **Après**: Workflow complet avec rafraîchissement des métadonnées + téléchargement réel
- **Résultat**: Re-téléchargement fonctionnel pour HWP et PDF ✅

### Technologies Utilisées
- **pyhwp**: Bibliothèque officielle pour l'extraction de texte HWP
- **hwp5txt**: Outil CLI de pyhwp pour extraction fiable
- **Playwright**: Automatisation de navigateur pour téléchargement de fichiers
- **Laravel Services**: Architecture propre avec séparation des responsabilités

### Points Clés
1. **L'utilisateur avait raison**: Le mot "상주" était bien présent dans le fichier
2. **Mes scripts étaient le problème**: Les méthodes personnalisées d'extraction HWP ne fonctionnaient pas
3. **Solution officielle**: L'outil `hwp5txt` de pyhwp fonctionne parfaitement
4. **Architecture propre**: Séparation claire entre crawling (métadonnées) et downloading (fichiers réels)

---

**Statut Final**: ✅ TOUS LES PROBLÈMES RÉSOLUS

**Fichiers Créés**:
- `/home/tideflo/nara/public_html/scripts/extract_hwp_text_hwp5.py`
- `/home/tideflo/nara/public_html/scripts/test_redownload_functionality.sh`

**Fichiers Modifiés**:
- `/home/tideflo/nara/public_html/app/Http/Controllers/Admin/TenderController.php`
- `/home/tideflo/nara/public_html/app/Services/SangjuCheckService.php`
- `/home/tideflo/nara/public_html/app/Services/AttachmentService.php`
- `/home/tideflo/nara/public_html/app/Services/ProposalFileCrawlerService.php`
