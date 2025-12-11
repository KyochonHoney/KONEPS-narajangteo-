# Playwright 기반 제안요청정보 파일 크롤링 완료 (Proof Mode)

## 📋 구현 개요
**목적**: JavaScript 동적 렌더링 페이지에서 제안요청정보 파일을 크롤링하기 위한 Playwright 기반 시스템 구축
**날짜**: 2025-11-03
**상태**: ✅ 완료 및 검증 완료

## 🎯 문제 상황

### 기존 문제점
> "그렇게 해줘 그러면" (Playwright 사용 요청)

**문제**:
- 나라장터 페이지는 JavaScript로 동적 렌더링
- 단순 HTTP 요청(curl, Http::get)으로는 제안요청정보 섹션 HTML을 가져올 수 없음
- 제안요청정보는 사용자가 탭을 클릭할 때 동적으로 로드됨

**해결 방법**:
- Playwright를 사용하여 실제 브라우저로 페이지 접근
- JavaScript 렌더링 완료 후 HTML 추출
- 제안요청정보 테이블에서 파일 정보 파싱

## 🏗️ 구현 내용

### 1. Node.js Playwright 설치 ✅

```bash
# Playwright 패키지 설치
$ npm install playwright
added 87 packages, and audited 88 packages in 6s

# Chromium 브라우저 설치
$ npx playwright install chromium
Chromium 141.0.7390.37 (playwright build v1194) downloaded
```

**설치 위치**:
- Playwright: `/home/tideflo/nara/public_html/node_modules/playwright`
- Chromium: `/home/tideflo/.cache/ms-playwright/chromium-1194`

### 2. ProposalFileCrawlerService 전면 개편 ✅

**파일**: `app/Services/ProposalFileCrawlerService.php`

**주요 변경사항**:
- ❌ 제거: `Http::get()` (정적 HTML 요청)
- ❌ 제거: `DOMDocument`, `DOMXPath` (서버측 파싱)
- ✅ 추가: Playwright 기반 브라우저 자동화
- ✅ 추가: JavaScript 실행 후 HTML 추출

### 3. Playwright 크롤링 로직 ✅

**핵심 메서드**: `fetchProposalFilesWithPlaywright(string $url)`

**동작 방식**:
1. Node.js 스크립트를 임시 파일로 생성 (`.cjs` 확장자)
2. Playwright로 Chromium 브라우저 실행
3. 나라장터 페이지 접속 및 대기 (3초)
4. JavaScript로 제안요청정보 테이블 파싱
5. JSON 형태로 결과 반환
6. PHP에서 JSON 파싱 후 DB 저장
7. 임시 스크립트 파일 삭제

**Node.js 스크립트**:
```javascript
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    await page.goto(process.argv[2], { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForTimeout(3000);

    const files = await page.evaluate(() => {
      const rows = document.querySelectorAll('#mf_wfm_container_mainWframe_grdPrpsDmndInfoView_body_tbody tr');
      const result = [];

      rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 3) {
          const docName = cells[1]?.textContent?.trim() || '';
          const fileName = cells[2]?.textContent?.trim() || '';

          if (fileName && !row.style.display.includes('none')) {
            result.push({
              doc_name: docName,
              file_name: fileName
            });
          }
        }
      });

      return result;
    });

    console.log(JSON.stringify(files));

  } catch (error) {
    console.error('Error:', error.message);
    console.log('[]');
  } finally {
    await browser.close();
  }
})();
```

**PHP 실행 로직**:
```php
// 임시 스크립트 파일 생성 (.cjs 확장자 사용)
$scriptPath = storage_path('app/temp/playwright_crawler_' . uniqid() . '.cjs');
file_put_contents($scriptPath, $nodeScript);

// Node.js로 Playwright 스크립트 실행
$result = Process::timeout(60)->run("node {$scriptPath} " . escapeshellarg($url));

// JSON 파싱
$files = json_decode($result->output(), true);

// 임시 파일 삭제
unlink($scriptPath);
```

### 4. CommonJS vs ES Module 이슈 해결 ✅

**문제**:
```
ReferenceError: require is not defined in ES module scope
This file is being treated as an ES module because it has a '.js' file extension
and '/home/tideflo/nara/public_html/package.json' contains "type": "module"
```

**원인**: Laravel Vite 설정이 `"type": "module"`로 되어 있음

**해결**: 스크립트 파일 확장자를 `.cjs`로 변경
```php
// Before
$scriptPath = storage_path('app/temp/playwright_crawler_' . uniqid() . '.js');

// After
$scriptPath = storage_path('app/temp/playwright_crawler_' . uniqid() . '.cjs');
```

## 🧪 테스트 및 검증

### Test 1: Playwright로 페이지 접근 ✅

**대상**: Tender #1441 (공고번호: R25BK01122952)

```bash
$ php artisan tender:crawl-proposal-files 1441
공고 #1441 (R25BK01122952) 제안요청정보 파일 크롤링 시작...
✅ 1개 파일 저장 완료
   발견: 1개, 다운로드: 1개
```

**결과**: ✅ 성공

### Test 2: 데이터베이스 확인 ✅

```sql
SELECT id, tender_id, type, doc_name, file_name, file_type, mime_type, download_status
FROM attachments
WHERE tender_id = 1441 AND type = 'proposal';
```

**결과**:
```
id    tender_id  type      doc_name      file_name                                                                  file_type  mime_type           download_status
23    1441       proposal  제안요청서    제안요청서(2026년 영사콜센터 상담시스템 운영 및 유지관리)_구매업무협의 반영_최종.hwpx  hwpx       application/x-hwp   pending
```

**검증 항목**:
- ✅ `type` = 'proposal' 정확히 저장
- ✅ `doc_name` = '제안요청서' 정확히 추출
- ✅ `file_name` 전체 파일명 정확히 저장
- ✅ `file_type` = 'hwpx' 확장자 정확히 파싱
- ✅ `mime_type` = 'application/x-hwp' HWPX 타입 지원
- ✅ `download_status` = 'pending' 기본값 정상

### Test 3: 웹 UI 확인 ✅

**URL**: `https://nara.tideflo.work/admin/tenders/1441`

**화면 표시**:
- ✅ **제안요청정보 파일 카드** (청록색 테두리)
- ✅ 파일 정보:
  - 아이콘: HWP (녹색)
  - 문서명: 제안요청서
  - 파일명: 제안요청서(2026년 영사콜센터 상담시스템 운영 및 유지관리)_구매업무협의 반영_최종.hwpx
  - 상태: 대기중 (노란색 배지)

## 📊 시스템 동작 플로우

```
1. 웹 UI에서 "제안요청정보 파일 수집" 버튼 클릭
   ↓
2. TenderController::crawlProposalFiles() 호출
   ↓
3. ProposalFileCrawlerService::crawlProposalFiles($tender)
   ↓
4. fetchProposalFilesWithPlaywright($url)
   - Node.js 스크립트 생성 (.cjs)
   - Playwright로 Chromium 실행
   - 페이지 접속 및 JavaScript 렌더링 대기
   - 제안요청정보 테이블 파싱
   - JSON 결과 반환
   ↓
5. downloadAndSaveFile($tender, $fileInfo)
   - Attachment::create() 호출
   - type='proposal'로 저장
   ↓
6. 결과 JSON 반환
   ↓
7. 웹 UI에서 알림 표시 및 페이지 새로고침
   ↓
8. 제안요청정보 파일 카드에 파일 표시
```

## 📂 변경된 파일 목록

### 수정된 파일 (1개)
1. `app/Services/ProposalFileCrawlerService.php`
   - HTTP 기반 → Playwright 기반 전환
   - fetchProposalFilesWithPlaywright() 메서드 추가
   - .cjs 스크립트 파일 생성 및 실행
   - HWPX 파일 타입 지원 추가

### 설치된 패키지 (2개)
1. `playwright` (npm package)
2. `chromium` (Playwright 브라우저)

## 🔍 기술적 특징

### 1. 동적 렌더링 대응 ✅
- JavaScript 실행 후 HTML 추출
- networkidle 상태까지 대기
- 추가 3초 대기로 안정성 확보

### 2. 헤드리스 브라우저 ✅
- Chromium 헤드리스 모드 사용
- 서버 환경에서 GUI 없이 실행
- 메모리 효율적 운영

### 3. 에러 처리 ✅
- 브라우저 실행 실패 시 빈 배열 반환
- 타임아웃 설정 (60초)
- 임시 파일 자동 정리

### 4. 성능 최적화 ✅
- 필요한 경우에만 브라우저 실행
- 단일 페이지만 로드
- 즉시 종료 및 리소스 해제

## ⚙️ 환경 요구사항

### 필수 환경
- ✅ Node.js v18+ 설치 (현재: v18.19.1)
- ✅ npm 패키지 관리자
- ✅ Playwright 패키지
- ✅ Chromium 브라우저 (자동 설치)

### 디렉토리 권한
- ✅ `storage/app/temp/` 쓰기 권한
- ✅ `~/.cache/ms-playwright/` 읽기 권한

## 🎉 결론

**✅ Playwright 기반 제안요청정보 파일 크롤링 시스템 구현 완료**

### 주요 성과
1. ✅ JavaScript 동적 렌더링 페이지 크롤링 가능
2. ✅ 실제 공고(R25BK01122952)에서 제안요청서 파일 성공적으로 수집
3. ✅ 웹 UI에서 제안요청정보 파일 정상 표시
4. ✅ HWPX 파일 형식 지원 추가
5. ✅ CommonJS/ES Module 호환성 문제 해결

### 실제 테스트 결과
- **공고**: 2026년 영사콜센터 상담시스템 운영 및 유지관리 (R25BK01122952)
- **발견 파일**: 1개
- **저장 성공**: 1개
- **파일명**: 제안요청서(2026년 영사콜센터 상담시스템 운영 및 유지관리)_구매업무협의 반영_최종.hwpx
- **문서명**: 제안요청서
- **파일 형식**: HWPX

### 다음 단계 (선택사항)
- 실제 파일 다운로드 기능 추가 (나라장터 로그인 세션 필요)
- 다운로드된 파일 로컬 저장소 관리
- PDF/Word 변환 기능 추가

---
*작성일: 2025-11-03*
*작성자: AI Assistant (Claude)*
*프로젝트: Nara - 나라장터 AI 제안서 시스템*
