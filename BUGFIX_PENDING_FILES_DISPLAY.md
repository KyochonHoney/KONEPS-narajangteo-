# 🐛 버그 수정: 제안요청정보 파일 수집 후 리스트에 안 보이는 문제

**날짜**: 2025-11-06
**상태**: ✅ 수정 완료 및 테스트 통과 (3/3)

---

## 📋 문제 상황

### 사용자 보고
> "제안요청정보 파일 수집 버튼 누르니까 잘 됐었는데 지금은 또 찾았다고 alert만 뜨고 제안요청정보 파일 리스트에 안 떠"

### 증상
1. "제안요청정보 파일 수집" 버튼 클릭
2. "✅ 성공! 발견: 1개, 저장: 1개" alert 표시
3. 페이지 새로고침 후에도 리스트에 파일 없음

---

## 🔍 원인 분석

### 근본 원인
**TenderController.php의 show() 메서드**에서 `download_status='completed'` 필터를 사용하여 제안요청정보 파일을 가져옴:

```php
// Line 176 (수정 전)
$proposalFiles = $tender->attachments()
    ->where('type', 'proposal')
    ->where('download_status', 'completed')  // ❌ 문제!
    ->get();
```

### 상세 분석

#### 파일 상태별 통계
```
전체 제안요청정보 파일: 9개
- completed: 7개 ✅ (표시됨)
- pending: 2개 ❌ (숨겨짐)
- failed: 0개
```

#### Pending 파일
1. **Tender 1759**: `붙임2. 제안요청서(의견반영).hwp` (pending)
2. **Tender 1717**: `제안요청서(수정본).hwp` (pending)

#### 왜 pending인가?
파일 크롤링 시 파일 정보는 데이터베이스에 저장되었으나, 실제 파일 다운로드가 완료되지 않음:
- `download_url`: `https://www.g2b.go.kr/fs/fsc/fsca/fileUpload.do` (잘못된 URL)
- `local_path`: (비어있음)
- `download_status`: `pending`

---

## ✅ 해결 방법

### 수정 내용

**TenderController.php의 show() 메서드**를 수정하여:
1. **모든 상태**의 제안요청정보 파일 표시 (completed, pending, failed)
2. **completed 파일만** 상주 검사 수행
3. **pending/failed 파일**은 "검사 안됨" 상태로 표시

### 수정된 코드

**위치**: `/home/tideflo/nara/public_html/app/Http/Controllers/Admin/TenderController.php:175-191`

```php
// 제안요청정보 파일의 상주 검사 결과 미리 계산
// 모든 상태의 파일을 표시하되, completed 파일만 상주 검사 수행
$proposalFiles = $tender->attachments()->where('type', 'proposal')->get();

foreach ($proposalFiles as $file) {
    if ($file->download_status === 'completed') {
        // 다운로드 완료된 파일만 상주 검사
        $file->sangju_status = $this->checkFileSangju($file);
    } else {
        // pending/failed 파일은 검사 안 함
        $file->sangju_status = [
            'checked' => false,
            'has_sangju' => false,
            'occurrences' => 0,
            'error' => '다운로드 ' . ($file->download_status === 'pending' ? '대기중' : '실패')
        ];
    }
}

return view('admin.tenders.show', compact('tender', 'userMention', 'proposalFiles'));
```

### 변경 전 vs 변경 후

| 항목 | 변경 전 | 변경 후 |
|------|---------|---------|
| 표시되는 파일 | completed만 (7개) | 모든 상태 (9개) |
| pending 파일 | ❌ 숨김 | ✅ 표시 |
| failed 파일 | ❌ 숨김 | ✅ 표시 |
| 상주 검사 | completed만 | completed만 (동일) |
| 배지 표시 | 상주 검사 결과만 | 상주 검사 + 다운로드 상태 |

---

## 🧪 테스트 결과

### 전체 테스트: 3/3 통과 (100%)

#### Test 1: Tender 1759 (pending 파일)
```
공고: 2026년도 지방세정보시스템 운영관리
제안요청정보 파일: 1개

파일: 붙임2. 제안요청서(의견반영).hwp
  - 다운로드 상태: pending
  - 상주 검사 상태: 검사 안됨
  - 에러: 다운로드 대기중
  - 화면 표시: [검사 안됨: 다운로드 대기중] (회색)

✅ pending 파일이 리스트에 표시됨
```

#### Test 2: 상태별 통계
```
제안요청정보 파일 통계:
  - 전체: 9개
  - 완료: 7개
  - 대기: 2개
  - 실패: 0개

수정 전: completed만 표시 (7개)
수정 후: 모든 상태 표시 (9개)

차이: +2개 (pending + failed)
```

#### Test 3: Tender 1768 (completed 파일, 기존 기능 유지)
```
파일: 제안요청서 (사전규격공개).hwpx
  상태: completed
  ✅ 상주 검사: 발견 (4회)
```

---

## 🎨 UI 표시

### 파일 상태에 따른 배지

#### 1. Completed 파일 (상주 발견)
```
제안요청서.hwpx
[다운로드 완료] [⚠️ 상주 4회 감지]
```
- 다운로드 상태: 녹색 "다운로드 완료"
- 상주 검사: 빨간색 "상주 4회 감지"

#### 2. Completed 파일 (상주 없음)
```
과업지시서.pdf
[다운로드 완료] [✅ 상주 없음]
```
- 다운로드 상태: 녹색 "다운로드 완료"
- 상주 검사: 녹색 "상주 없음"

#### 3. Pending 파일 (NEW!)
```
제안요청서(의견반영).hwp
[대기중] [⚠️ 검사 안됨: 다운로드 대기중]
```
- 다운로드 상태: 노란색 "대기중"
- 상주 검사: 회색 "검사 안됨: 다운로드 대기중"

#### 4. Failed 파일 (NEW!)
```
파일명.hwp
[실패] [⚠️ 검사 안됨: 다운로드 실패]
```
- 다운로드 상태: 빨간색 "실패"
- 상주 검사: 회색 "검사 안됨: 다운로드 실패"

---

## ✨ 개선 사항

### 1. 투명성 향상
- ✅ 모든 파일 상태가 명확히 표시됨
- ✅ pending 파일도 즉시 확인 가능
- ✅ 다운로드 대기/실패 상태 구분

### 2. 사용자 경험 개선
- ✅ 파일 수집 후 즉시 리스트에 표시
- ✅ 재다운로드 버튼으로 pending 파일 다시 시도 가능
- ✅ 혼란 방지 (alert는 떴는데 파일이 안 보이는 문제 해결)

### 3. 일관성 유지
- ✅ completed 파일의 상주 검사 기능은 이전과 동일
- ✅ 기존 기능에 영향 없음
- ✅ 점진적 기능 확장

---

## 📊 영향 범위

### 변경된 파일 (2개)
1. **TenderController.php**: show() 메서드 수정
2. **test_pending_files_display.sh**: 테스트 스크립트 추가

### 영향받는 화면
- ✅ 공고 상세 페이지 (제안요청정보 파일 섹션)

### 영향받지 않는 부분
- ✅ 파일 수집 기능
- ✅ 상주 검사 기능 (completed 파일만)
- ✅ 다른 페이지들

---

## 🚀 사용 방법

### 1. 파일 수집
1. 공고 상세 페이지 접속
2. "제안요청정보 파일 수집" 버튼 클릭
3. "✅ 성공!" alert 확인

### 2. 결과 확인
- **수정 전**: 리스트에 파일 안 보임 (pending이면)
- **수정 후**: 리스트에 파일 표시 + "대기중" 배지

### 3. Pending 파일 다운로드
- "재다운로드" 버튼 클릭하여 다시 시도
- 또는 크롤링 다시 실행

---

## ⚠️ 알려진 제한사항

### 1. Pending 파일의 잘못된 URL
**문제**: 일부 pending 파일이 잘못된 download_url을 가짐
```
URL: https://www.g2b.go.kr/fs/fsc/fsca/fileUpload.do
```
→ 실제 파일 다운로드 불가능

**원인**: ProposalFileCrawlerService의 URL 추출 로직 이슈

**해결 방법**: 별도 이슈로 처리 예정
- ProposalFileCrawlerService 크롤링 로직 개선
- 정확한 파일 다운로드 URL 추출

### 2. 재다운로드 기능의 제한
현재 "재다운로드" 버튼은 잘못된 URL에서 다시 시도하므로 실패할 가능성 높음.

---

## 🔄 향후 개선 계획

1. **ProposalFileCrawlerService 개선**
   - 정확한 파일 다운로드 URL 추출
   - 나라장터 파일 다운로드 메커니즘 분석

2. **자동 재시도 기능**
   - pending 파일 자동 다운로드 시도
   - 실패 시 재시도 로직

3. **배치 다운로드**
   - 여러 pending 파일 한 번에 다운로드
   - 진행 상황 표시

---

## 📚 관련 문서

- [IMPLEMENTATION_SANGJU_FILE_DISPLAY.md](IMPLEMENTATION_SANGJU_FILE_DISPLAY.md) - 상주 파일별 표시 기능
- [FEATURE_SANGJU_AUTO_PROCESSING.md](FEATURE_SANGJU_AUTO_PROCESSING.md) - 상주 자동 처리 시스템
- [scripts/test_pending_files_display.sh](public_html/scripts/test_pending_files_display.sh) - 테스트 스크립트

---

**수정 완료일**: 2025-11-06
**테스트 성공률**: 3/3 (100%)
**상태**: ✅ 프로덕션 배포 완료
