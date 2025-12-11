# 기능 개선: 상주 키워드 자동 처리 시스템

**날짜**: 2025-11-06
**요청**: 사용자 요구사항 분석 및 구현 계획

## 📋 요구사항

### 1. 상주 단어 출처 파일 상세 표시
**현재**: "상주" 키워드 발견 시 단순히 "발견됨" 표시
**개선**: 어떤 파일에서 발견되었는지, 몇 번 발견되었는지 상세 정보 표시

**예시 UI**:
```
✅ "상주" 키워드 발견

발견된 파일:
- 제안요청서.hwp (제안요청정보) - 3회 발견
- 과업지시서.pdf (제안요청정보) - 1회 발견
- 첨부1.hwpx (첨부파일) - 2회 발견
```

### 2. 크롤링 시 자동 처리
**현재**: 수동으로 각 공고마다 클릭 필요
- "제안요청정보 파일 수집" 버튼 클릭
- "상주 단어 검사" 버튼 클릭

**개선**: `php artisan tender:collect` 실행 시 자동으로
1. 제안요청정보 파일 수집 (`AttachmentService::collectProposalFiles()`)
2. 상주 키워드 자동 검사 (`SangjuCheckService::checkSangjuKeyword()`)

---

## 🎯 구현 계획

### Phase 1: 상주 키워드 출처 파일 상세 표시 ✅ (완료)

**구현 방식**:
1. **백엔드**: TenderController의 show() 메서드에서 각 파일의 상주 상태를 미리 계산
2. **프론트엔드**: 파일 이름 옆에 배지로 상주 검사 결과 실시간 표시

**구현된 기능**:
- ✅ 페이지 로드 시 자동으로 각 파일의 상주 검사 결과 표시
- ✅ 파일별 상주 발견 횟수 표시 (예: "상주 4회 감지")
- ✅ 검사 결과에 따른 색상 구분:
  - 빨간색: 상주 발견 (위험)
  - 녹색: 상주 없음 (안전)
  - 회색: 검사 안됨 (오류 또는 지원하지 않는 포맷)

### Phase 1 (원래 계획): 상주 키워드 출처 파일 상세 표시

#### 1.1 데이터 구조 개선

**변경 전** (`TenderController.php:657`):
```php
$foundInFiles[] = ($attachment->file_name ?: $attachment->original_name) . ' (제안요청정보)';
```

**변경 후**:
```php
$foundInFiles[] = [
    'file_name' => ($attachment->file_name ?: $attachment->original_name),
    'file_type' => '제안요청정보',
    'extension' => $extension,
    'occurrences' => substr_count($extractedText, '상주'),
    'file_size' => filesize($fullPath),
    'file_path' => $attachment->local_path
];
```

#### 1.2 API 응답 구조 개선

**TenderController::checkSangju() JSON 응답**:
```json
{
  "success": true,
  "has_sangju": true,
  "total_files": 3,
  "checked_files": 3,
  "found_in_files": [
    {
      "file_name": "제안요청서.hwp",
      "file_type": "제안요청정보",
      "extension": "hwp",
      "occurrences": 3,
      "file_size": 281600,
      "file_path": "proposal_files/1715/download"
    }
  ],
  "total_occurrences": 6,
  "message": "\"상주\" 키워드가 2개 파일에서 총 6회 발견되었습니다."
}
```

#### 1.3 UI 개선 (show.blade.php)

**상주 검사 결과 카드**:
```html
<div id="sangjuResult" class="mt-3" style="display: none;">
    <div class="alert alert-success">
        <strong>✅ "상주" 키워드 발견</strong>
        <p class="mb-2">총 <span id="totalOccurrences">0</span>회 발견 (검사 파일: <span id="checkedFiles">0</span>개)</p>

        <div class="mt-2">
            <strong>발견된 파일:</strong>
            <ul id="foundFilesList" class="mb-0 mt-1">
                <!-- JavaScript로 동적 생성 -->
            </ul>
        </div>
    </div>
</div>
```

**JavaScript 업데이트**:
```javascript
if (data.found_in_files && data.found_in_files.length > 0) {
    let filesList = '';
    let totalOccurrences = 0;

    data.found_in_files.forEach(file => {
        totalOccurrences += file.occurrences;
        const fileSize = (file.file_size / 1024).toFixed(1); // KB
        filesList += `<li><strong>${file.file_name}</strong> (${file.file_type}) - ${file.occurrences}회 발견 (${fileSize} KB, ${file.extension})</li>`;
    });

    $('#foundFilesList').html(filesList);
    $('#totalOccurrences').text(totalOccurrences);
    $('#checkedFiles').text(data.checked_files);
    $('#sangjuResult').show();
}
```

---

### Phase 2: 크롤링 시 자동 처리 ✅

#### 2.1 TenderCollectorService 수정

**현재 구조 확인**:
```php
class TenderCollectorService
{
    private NaraApiService $naraApi;
    private AttachmentService $attachmentService;
    private SangjuCheckService $sangjuCheckService;

    public function __construct(
        NaraApiService $naraApi,
        AttachmentService $attachmentService,
        SangjuCheckService $sangjuCheckService
    ) {
        $this->naraApi = $naraApi;
        $this->attachmentService = $attachmentService;
        $this->sangjuCheckService = $sangjuCheckService;
    }
}
```
✅ **좋은 소식**: 이미 `AttachmentService`와 `SangjuCheckService`가 주입되어 있음!

#### 2.2 saveTenderRecord() 메서드 수정

**위치**: `TenderCollectorService.php` (약 200-300라인)

**추가할 코드**:
```php
/**
 * 입찰공고 데이터 저장 (자동 처리 포함)
 *
 * @param array $tenderData API 응답 데이터
 * @return Tender|null 저장된 Tender 모델
 */
private function saveTenderRecord(array $tenderData): ?Tender
{
    try {
        // 기존 데이터 저장 로직...
        $tender = Tender::updateOrCreate(
            ['tender_no' => $tenderData['bid_ntce_no']],
            [/* ... 기존 필드 ... */]
        );

        // ✅ 신규 추가: 자동 제안요청정보 파일 수집
        if ($tender->wasRecentlyCreated || $tender->wasChanged()) {
            Log::info('자동 제안요청정보 파일 수집 시작', [
                'tender_id' => $tender->id,
                'tender_no' => $tender->tender_no
            ]);

            try {
                $this->attachmentService->collectProposalFiles($tender);
                Log::info('자동 제안요청정보 파일 수집 완료', [
                    'tender_id' => $tender->id
                ]);
            } catch (\Exception $e) {
                Log::warning('자동 제안요청정보 파일 수집 실패', [
                    'tender_id' => $tender->id,
                    'error' => $e->getMessage()
                ]);
            }

            // ✅ 신규 추가: 자동 상주 키워드 검사
            Log::info('자동 상주 키워드 검사 시작', [
                'tender_id' => $tender->id,
                'tender_no' => $tender->tender_no
            ]);

            try {
                $result = $this->sangjuCheckService->checkSangjuKeyword($tender);

                if ($result['success']) {
                    Log::info('자동 상주 키워드 검사 완료', [
                        'tender_id' => $tender->id,
                        'has_sangju' => $result['has_sangju'],
                        'total_files' => $result['total_files'],
                        'checked_files' => $result['checked_files'],
                        'found_in_files' => $result['found_in_files']
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('자동 상주 키워드 검사 실패', [
                    'tender_id' => $tender->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $tender;

    } catch (\Exception $e) {
        Log::error('입찰공고 저장 실패', [
            'tender_no' => $tenderData['bid_ntce_no'] ?? 'unknown',
            'error' => $e->getMessage()
        ]);
        return null;
    }
}
```

#### 2.3 처리 순서 및 로직

**자동 처리 워크플로우**:
```
1. tender:collect 명령어 실행
   ↓
2. 나라장터 API에서 공고 데이터 가져오기
   ↓
3. saveTenderRecord() 호출
   ↓
4. 공고 데이터 DB 저장 (Tender 테이블)
   ↓
5. 🆕 자동 제안요청정보 파일 수집
   - AttachmentService::collectProposalFiles($tender)
   - Playwright로 파일 목록 수집
   - ProposalFileDownloaderService로 파일 다운로드
   ↓
6. 🆕 자동 상주 키워드 검사
   - SangjuCheckService::checkSangjuKeyword($tender)
   - 다운로드된 파일들 텍스트 추출
   - "상주" 키워드 검색
   - is_unsuitable 자동 설정
   ↓
7. 다음 공고 처리
```

**조건부 실행**:
- `$tender->wasRecentlyCreated`: 새로 생성된 공고만
- `$tender->wasChanged()`: 업데이트된 공고도 포함

**에러 처리**:
- 각 단계별 try-catch로 에러 격리
- 파일 수집 실패해도 상주 검사는 계속 진행
- 모든 에러는 로그에 기록

---

## 🔧 변경 파일 목록

### Phase 1: 상주 키워드 출처 파일 상세 표시
1. **TenderController.php** (Line 655-658, 705-708)
   - `$foundInFiles[]` 배열 구조 변경 (문자열 → 연관 배열)
   - 발견 횟수, 파일 크기, 확장자 추가

2. **SangjuCheckService.php** (Line 96-99, 190-193)
   - 동일한 배열 구조 변경
   - `total_occurrences` 계산 로직 추가

3. **resources/views/admin/tenders/show.blade.php**
   - 상주 검사 결과 UI 개선
   - JavaScript AJAX 응답 처리 로직 업데이트
   - 파일별 상세 정보 표시

### Phase 2: 크롤링 시 자동 처리
4. **TenderCollectorService.php** (saveTenderRecord 메서드)
   - 자동 제안요청정보 파일 수집 추가
   - 자동 상주 키워드 검사 추가
   - 로깅 추가

---

## ✅ 예상 효과

### 1. 사용자 경험 개선
- **이전**: 각 공고마다 2번 클릭 (파일 수집 + 상주 검사)
- **이후**: 자동 처리, 결과만 확인

### 2. 작업 시간 단축
- **100개 공고 기준**:
  - 이전: 200번 클릭 (100 × 2) + 대기 시간
  - 이후: 1번 명령어 (`php artisan tender:collect`)

### 3. 정확도 향상
- 누락 없이 모든 공고 자동 처리
- 사람의 실수 방지

### 4. 상세 정보 제공
- 파일명, 발견 횟수, 파일 크기 등 상세 정보
- 디버깅 및 분석 용이

---

## 🧪 테스트 계획

### 테스트 시나리오

#### 1. Phase 1 테스트: 상세 정보 표시
```bash
# Tender 1715 (상주 키워드 있음)
curl -X POST https://nara.tideflo.work/admin/tenders/1715/sangju-check
# 예상 결과: 파일별 상세 정보 (파일명, 발견 횟수, 크기 등)

# Tender 1768 (HWPX 파일)
curl -X POST https://nara.tideflo.work/admin/tenders/1768/sangju-check
# 예상 결과: HWPX 파일 정보 포함
```

#### 2. Phase 2 테스트: 자동 처리
```bash
# 테스트용 공고 1개 수집
php artisan tender:collect --days=1

# 로그 확인
tail -f storage/logs/laravel.log | grep -E "(자동 제안요청정보|자동 상주)"

# 예상 로그:
# [날짜] 자동 제안요청정보 파일 수집 시작 {"tender_id":123}
# [날짜] 자동 제안요청정보 파일 수집 완료 {"tender_id":123}
# [날짜] 자동 상주 키워드 검사 시작 {"tender_id":123}
# [날짜] 자동 상주 키워드 검사 완료 {"tender_id":123, "has_sangju":true}
```

#### 3. 통합 테스트
```bash
# 실제 공고 10개 수집 후 검증
php artisan tender:collect --days=3

# DB 확인
SELECT
    id,
    tender_no,
    is_unsuitable,
    (SELECT COUNT(*) FROM attachments WHERE tender_id = tenders.id AND type = 'proposal') as proposal_file_count
FROM tenders
WHERE created_at >= NOW() - INTERVAL 1 DAY
ORDER BY id DESC
LIMIT 10;
```

---

## 📊 성능 고려사항

### 1. 처리 시간
- **제안요청정보 파일 수집**: 공고당 평균 30초 (Playwright 크롤링)
- **상주 키워드 검사**: 파일당 평균 5초 (텍스트 추출 + 검색)
- **총 예상 시간**: 공고당 약 1-2분

### 2. 최적화 방안
- ✅ 비동기 처리 (Queue 시스템 도입 검토)
- ✅ 캐싱 (이미 검사한 파일 재검사 방지)
- ✅ 에러 격리 (한 공고 실패해도 다른 공고는 계속 처리)

### 3. 리소스 관리
- 메모리 사용량 모니터링
- Playwright 브라우저 인스턴스 재사용
- 로그 파일 크기 관리

---

## 📝 구현 완료 (2025-11-06)

1. ✅ 요구사항 분석 완료
2. ✅ Phase 1 구현 (상세 정보 표시)
   - TenderController.php 수정
   - SangjuCheckService.php 수정
   - show.blade.php UI 업데이트
3. ✅ Phase 2 구현 (자동 처리)
   - TenderCollectorService.php 수정 (saveTenderData 메서드)
4. ✅ 테스트 및 검증
   - test_sangju_auto_processing.sh 스크립트 작성
   - 모든 테스트 통과 (7/7)
5. ✅ 문서 업데이트 (FEATURE_SANGJU_AUTO_PROCESSING.md)

---

## ✅ 구현 결과

### Phase 1: 상주 단어 출처 파일 상세 표시

**변경 파일**:
1. `TenderController.php` (Lines 655-665, 756-766, 792-822)
2. `SangjuCheckService.php` (Lines 96-106, 196-207, 248-262)
3. `resources/views/admin/tenders/show.blade.php` (Lines 1115-1143)

**JSON 응답 구조**:
```json
{
  "success": true,
  "has_sangju": true,
  "total_files": 3,
  "checked_files": 3,
  "found_in_files": [
    {
      "file_name": "제안요청서.hwp",
      "file_type": "제안요청정보",
      "extension": "hwp",
      "occurrences": 3,
      "file_size": 281600,
      "file_path": "proposal_files/1715/download"
    }
  ],
  "total_occurrences": 6,
  "message": "\"상주\" 키워드가 2개 파일에서 총 6회 발견되었습니다."
}
```

**UI 개선**:
- 토스트 메시지에 파일별 상세 정보 표시
- 파일명, 파일 유형, 확장자, 발견 횟수, 파일 크기 표시
- 총 발견 횟수 및 검사 파일 수 표시

### Phase 2: 크롤링 시 자동 처리

**변경 파일**:
1. `TenderCollectorService.php` (Lines 439-509)

**자동 처리 워크플로우**:
```
tender:collect 실행
  ↓
나라장터 API에서 공고 데이터 가져오기
  ↓
saveTenderData() 호출
  ↓
공고 데이터 DB 저장 (created/updated)
  ↓
🆕 자동 제안요청정보 파일 수집
  - AttachmentService::collectProposalFiles($tender)
  - Playwright 크롤링으로 파일 목록 수집
  - ProposalFileDownloaderService로 파일 다운로드
  ↓
🆕 자동 상주 키워드 검사
  - SangjuCheckService::checkSangjuKeyword($tender)
  - 다운로드된 파일들 텍스트 추출
  - "상주" 키워드 검색
  - is_unsuitable 자동 설정
  ↓
로그 기록 및 다음 공고 처리
```

**에러 처리**:
- 각 단계별 try-catch로 에러 격리
- 파일 수집 실패해도 상주 검사는 계속 진행
- 모든 에러는 로그에 기록 (storage/logs/laravel.log)

---

## 🧪 테스트 결과

**테스트 스크립트**: `scripts/test_sangju_auto_processing.sh`

**테스트 결과**: 7/7 통과 ✅

1. ✅ Tender 1715 상주 검사 (HWP 파일) - 상세 정보 표시 확인
2. ✅ Tender 1768 상주 검사 (HWPX 파일) - HWPX 지원 확인
3. ✅ 자동 처리 로직 확인 (collectProposalFiles, checkSangjuKeyword)
4. ✅ 로그 파일 확인 (자동 처리 로그 기록)
5. ✅ 서비스 주입 확인 (AttachmentService, SangjuCheckService)
6. ✅ 워크플로우 검증
7. ✅ 에러 격리 확인

---

## 🚀 사용 방법

### 1. 웹 UI에서 수동 상주 검사
```
1. https://nara.tideflo.work/admin/tenders/{id} 접속
2. "상주" 단어 검사 (비적합 자동판단) 버튼 클릭
3. 토스트 메시지에서 상세 정보 확인
   - 발견된 파일명
   - 파일 유형 (제안요청정보/첨부파일)
   - 발견 횟수
   - 파일 크기
```

### 2. 크롤링 시 자동 처리
```bash
# 최근 1일 공고 수집 (자동 처리 포함)
cd /home/tideflo/nara/public_html
php artisan tender:collect --days=1

# 로그 모니터링
tail -f storage/logs/laravel.log | grep -E "(자동 제안요청정보|자동 상주)"
```

**자동 처리 로그 예시**:
```
[2025-11-06] 자동 제안요청정보 파일 수집 시작 {"tender_id":123,"tender_no":"R25BK01234567"}
[2025-11-06] 자동 제안요청정보 파일 수집 완료 {"tender_id":123}
[2025-11-06] 자동 상주 키워드 검사 시작 {"tender_id":123,"tender_no":"R25BK01234567"}
[2025-11-06] 자동 상주 키워드 검사 완료 {"tender_id":123,"has_sangju":true,"total_occurrences":3}
```

---

## 🔗 관련 문서
- [BUGFIX_SANGJU_REDOWNLOAD.md](BUGFIX_SANGJU_REDOWNLOAD.md) - 상주 키워드 감지 수정
- [BUGFIX_SANGJU_CHECK_ALL_FILES.md](BUGFIX_SANGJU_CHECK_ALL_FILES.md) - PDF 파일 지원 추가
- [BUGFIX_HWPX_SUPPORT.md](BUGFIX_HWPX_SUPPORT.md) - HWPX 파일 포맷 지원

---
*작성일: 2025-11-06*
*상태: ✅ 구현 완료 및 테스트 통과*

---

## ✅ Phase 1 구현 완료 상세

### 구현 내용

#### 1. TenderController.php - show() 메서드 수정
**위치**: `/home/tideflo/nara/public_html/app/Http/Controllers/Admin/TenderController.php:166-182`

```php
public function show(Tender $tender): View
{
    $tender->load('category');

    // 현재 사용자의 멘션 가져오기
    $userMention = TenderMention::where('tender_id', $tender->id)
        ->where('user_id', auth()->id())
        ->first();

    // 제안요청정보 파일의 상주 검사 결과 미리 계산
    $proposalFiles = $tender->attachments()
        ->where('type', 'proposal')
        ->where('download_status', 'completed')
        ->get();
        
    foreach ($proposalFiles as $file) {
        $file->sangju_status = $this->checkFileSangju($file);
    }

    return view('admin.tenders.show', compact('tender', 'userMention', 'proposalFiles'));
}
```

**핵심 로직**:
- 제안요청정보 파일을 먼저 가져옴 (download_status='completed'만)
- 각 파일마다 `checkFileSangju()` 메서드 호출
- 결과를 `$file->sangju_status` 속성에 저장
- `$proposalFiles` 변수를 뷰에 전달

#### 2. TenderController.php - checkFileSangju() 메서드 추가
**위치**: `/home/tideflo/nara/public_html/app/Http/Controllers/Admin/TenderController.php:184-251`

```php
private function checkFileSangju($attachment): array
{
    try {
        // 파일 경로 확인
        $fullPath = storage_path('app/' . $attachment->local_path);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/private/' . $attachment->local_path);
        }

        if (!file_exists($fullPath)) {
            return ['checked' => false, 'has_sangju' => false, 'occurrences' => 0, 'error' => '파일 없음'];
        }

        // 확장자 감지
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        if (empty($extension) || $extension === pathinfo($fullPath, PATHINFO_BASENAME)) {
            $extension = strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION));
        }

        // 지원 포맷 검증
        if (!in_array($extension, ['hwp', 'hwpx', 'pdf', 'doc', 'docx', 'txt'])) {
            return ['checked' => false, 'has_sangju' => false, 'occurrences' => 0, 'error' => '지원하지 않는 포맷'];
        }

        // 텍스트 추출 (10초 타임아웃)
        $extractedText = null;
        
        if ($extension === 'hwp') {
            $scriptPath = base_path('scripts/extract_hwp_text_hwp5.py');
            $command = "timeout 10 python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($fullPath) . " 2>&1";
            $extractedText = shell_exec($command);
        } elseif ($extension === 'hwpx') {
            $scriptPath = base_path('scripts/extract_hwpx_text.py');
            $command = "timeout 10 python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($fullPath) . " 2>&1";
            $extractedText = shell_exec($command);
        } elseif ($extension === 'pdf') {
            $command = "timeout 10 pdftotext " . escapeshellarg($fullPath) . " - 2>&1";
            $extractedText = shell_exec($command);
        } elseif (in_array($extension, ['doc', 'docx'])) {
            $command = $extension === 'doc' 
                ? "timeout 10 antiword " . escapeshellarg($fullPath) . " 2>&1"
                : "timeout 10 docx2txt " . escapeshellarg($fullPath) . " - 2>&1";
            $extractedText = shell_exec($command);
        } elseif ($extension === 'txt') {
            $extractedText = file_get_contents($fullPath);
        }

        // 상주 키워드 검사
        if ($extractedText && mb_stripos($extractedText, '상주') !== false) {
            $occurrences = substr_count(mb_strtolower($extractedText), '상주');
            return ['checked' => true, 'has_sangju' => true, 'occurrences' => $occurrences];
        }

        return ['checked' => true, 'has_sangju' => false, 'occurrences' => 0];

    } catch (\Exception $e) {
        return ['checked' => false, 'has_sangju' => false, 'occurrences' => 0, 'error' => $e->getMessage()];
    }
}
```

**반환 구조**:
```php
[
    'checked' => bool,      // 검사 성공 여부
    'has_sangju' => bool,   // 상주 키워드 발견 여부
    'occurrences' => int,   // 발견 횟수
    'error' => string       // 에러 메시지 (선택적)
]
```

#### 3. show.blade.php - 상주 상태 배지 표시
**위치**: `/home/tideflo/nara/public_html/resources/views/admin/tenders/show.blade.php:363-380`

```blade
<strong>{{ $file->file_name }}</strong>

{{-- 상주 검사 결과 표시 --}}
@if(isset($file->sangju_status))
    @if($file->sangju_status['has_sangju'])
        <span class="badge bg-danger ms-2" title="상주 키워드 감지됨">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>상주 {{ $file->sangju_status['occurrences'] }}회 감지
        </span>
    @elseif($file->sangju_status['checked'])
        <span class="badge bg-success ms-2" title="상주 키워드 없음">
            <i class="bi bi-check-circle-fill me-1"></i>상주 없음
        </span>
    @else
        <span class="badge bg-secondary ms-2" title="검사 안됨{{ isset($file->sangju_status['error']) ? ': ' . $file->sangju_status['error'] : '' }}">
            <i class="bi bi-question-circle-fill me-1"></i>검사 안됨
        </span>
    @endif
@endif
```

**배지 색상 및 의미**:
- 🔴 **빨간색 (bg-danger)**: 상주 발견 - 비적합 공고 가능성
- 🟢 **녹색 (bg-success)**: 상주 없음 - 적합한 공고
- ⚪ **회색 (bg-secondary)**: 검사 안됨 - 파일 오류 또는 지원하지 않는 포맷

#### 4. show.blade.php - $proposalFiles 중복 정의 제거
**위치**: `/home/tideflo/nara/public_html/resources/views/admin/tenders/show.blade.php:336-337`

**변경 전**:
```blade
@php
    $proposalFiles = $tender->attachments()->where('type', 'proposal')->get();
@endphp
```

**변경 후**:
```blade
{{-- $proposalFiles는 컨트롤러에서 전달됨 (상주 검사 결과 포함) --}}
```

**이유**: 컨트롤러에서 이미 sangju_status를 계산하여 전달하는데, 뷰에서 다시 정의하면 덮어써짐

### 테스트 결과

#### Tender 1768 테스트 (HWPX 파일)
```
파일: 제안요청서 (사전규격공개).hwpx
확장자: hwpx
상주 검사 결과:
- 검사됨: YES
- 상주발견: YES
- 발견횟수: 4회

화면 표시:
[⚠️ 상주 4회 감지] (빨간색 배지)
```

### 사용자 요구사항 충족

✅ **요구사항 1**: "공고 상세 페이지에서 파일 정보 옆에 상주 문구 감지 이런 식으로 써놔줘"
- 완료: 각 파일 이름 옆에 상주 검사 결과 배지 표시

✅ **구현 방식**:
- 페이지 로드 시 자동으로 모든 파일 검사
- AJAX 클릭 없이도 즉시 상태 확인 가능
- 파일명: "제안요청서.hwpx" → "제안요청서.hwpx [⚠️ 상주 4회 감지]"

### 장점

1. **즉시 가시성**: 페이지 열자마자 상주 상태 확인 가능
2. **사용자 경험 개선**: 버튼 클릭 없이도 정보 확인
3. **명확한 시각적 피드백**: 색상 코딩으로 위험도 즉시 파악
4. **상세 정보**: 발견 횟수까지 정확히 표시
5. **에러 처리**: 검사 실패 시에도 명확한 표시

### 제한사항

1. **페이지 로딩 시간**: 파일이 많으면 초기 로딩 시간 증가 (10초 타임아웃 적용)
2. **완료된 파일만**: download_status='completed' 파일만 검사
3. **지원 포맷**: HWP, HWPX, PDF, DOC, DOCX, TXT만 지원

