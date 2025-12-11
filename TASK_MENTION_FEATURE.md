# 공고 멘션(메모) 기능 구현 작업 문서

## 작업 개요
- **작업명**: 공고별 개인 멘션(메모) 시스템 구축
- **시작일**: 2025-10-31
- **담당**: Claude Code SuperClaude
- **우선순위**: 중

## 요구사항
1. 상세 공고 페이지에서 개인 메모 작성 기능
2. 작성한 메모 수정/삭제 기능
3. 공고를 어디까지 봤는지, 중요 포인트 등 자유롭게 기록
4. 사용자별로 독립적인 메모 관리

## 구현 계획

### 1. 데이터베이스 마이그레이션 ⏳
- **파일**: `database/migrations/YYYY_MM_DD_create_tender_mentions_table.php`
- **변경사항**: `tender_mentions` 테이블 생성
  - id (PK)
  - tender_id (FK to tenders)
  - user_id (FK to users)
  - mention (text) - 멘션 내용
  - created_at, updated_at
- **인덱스**: tender_id + user_id 복합 인덱스
- **상태**: 대기중

### 2. TenderMention 모델 생성 ⏳
- **파일**: `app/Models/TenderMention.php`
- **변경사항**:
  - Tender, User 관계 설정
  - fillable 배열 정의
  - 타임스탬프 활성화
- **상태**: 대기중

### 3. Tender 모델 관계 추가 ⏳
- **파일**: `app/Models/Tender.php`
- **변경사항**: `mentions()` hasMany 관계 메서드 추가
- **상태**: 대기중

### 4. TenderController 메서드 추가 ⏳
- **파일**: `app/Http/Controllers/Admin/TenderController.php`
- **변경사항**:
  - `storeMention(Request $request, Tender $tender)` - 멘션 저장
  - `updateMention(Request $request, Tender $tender)` - 멘션 수정
  - `destroyMention(Tender $tender)` - 멘션 삭제
- **기능**: 사용자별 멘션 CRUD 관리
- **상태**: 대기중

### 5. 라우트 등록 ⏳
- **파일**: `routes/web.php`
- **변경사항**:
  - `POST /admin/tenders/{tender}/mention` - 멘션 저장
  - `PATCH /admin/tenders/{tender}/mention` - 멘션 수정
  - `DELETE /admin/tenders/{tender}/mention` - 멘션 삭제
- **상태**: 대기중

### 6. Blade 뷰 UI 수정 ⏳
- **파일**: `resources/views/admin/tenders/show.blade.php`
- **변경사항**:
  - 멘션 작성 텍스트 영역 추가
  - 저장/수정/삭제 버튼
  - JavaScript AJAX 멘션 관리 함수
  - 실시간 저장 상태 표시
- **상태**: 대기중

## 작업 진행 상황

### ⏳ 대기중
- 없음

### ✅ 완료
- 데이터베이스 마이그레이션 생성 및 실행 (tender_mentions 테이블)
- TenderMention 모델 생성 및 관계 설정
- Tender 모델에 mentions() 관계 추가
- TenderController에 멘션 CRUD 메서드 추가 (storeMention, destroyMention)
- show() 메서드 수정 (사용자 멘션 로드)
- 라우트 등록 (POST /mention, DELETE /mention)
- show.blade.php에 멘션 UI 추가 (텍스트 영역, 저장/삭제 버튼, 글자 수 카운터)
- JavaScript AJAX 멘션 관리 기능 구현

## 구현 세부사항

### 데이터베이스 스키마
```php
Schema::create('tender_mentions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tender_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->text('mention')->nullable()->comment('사용자 메모');
    $table->timestamps();

    // 복합 유니크 인덱스: 한 사용자당 공고 하나에 하나의 멘션만
    $table->unique(['tender_id', 'user_id']);
});
```

### API 엔드포인트
- **URL**: `POST /admin/tenders/{tender}/mention`
- **응답 형식**:
```json
{
  "success": true,
  "mention": "어디까지 봤는지 메모...",
  "message": "메모가 저장되었습니다."
}
```

### UI 디자인
- 공고 상세 페이지 작업 섹션 하단에 "내 메모" 카드 추가
- 텍스트 영역 (최대 5000자)
- 실시간 글자 수 표시 (0 / 5000자)
- 저장 버튼 (AJAX로 즉시 저장)
- 삭제 버튼 (메모가 있을 때만 표시, 확인 후 삭제)
- 마지막 수정 시간 표시

## 테스트 계획
1. 멘션 생성 테스트
2. 멘션 수정 테스트
3. 멘션 삭제 테스트
4. 사용자별 독립성 테스트 (다른 사용자의 멘션 안 보임)
5. 공고 삭제 시 멘션도 함께 삭제 (cascade) 테스트

## 참고사항
- 멘션은 사용자별로 독립적 (다른 사용자가 볼 수 없음)
- 공고 하나당 사용자 하나당 멘션 하나
- AJAX 방식으로 페이지 새로고침 없이 저장
- 자동 저장 기능 (선택사항)

## 작업 로그

### 2025-10-31 (작업 시작)
- ✅ 요구사항 분석 완료
- ✅ 작업 계획 수립 완료
- ✅ TASK_MENTION_FEATURE.md 문서 생성 완료

### 2025-10-31 (구현 완료)
- ✅ 데이터베이스 마이그레이션 생성: `2025_10_31_183709_create_tender_mentions_table.php`
- ✅ 마이그레이션 실행 성공 (523.20ms 소요)
- ✅ TenderMention 모델 생성 및 관계 설정:
  - `tender_id`, `user_id`, `mention` fillable 필드
  - tender(), user() belongsTo 관계 메서드
- ✅ Tender 모델 수정:
  - `mentions()` hasMany 관계 메서드 추가
- ✅ TenderController 수정:
  - `storeMention(Request $request, Tender $tender)` 메서드 추가 (저장/수정)
  - `destroyMention(Tender $tender)` 메서드 추가 (삭제)
  - `show()` 메서드 수정 (현재 사용자 멘션 로드)
- ✅ 라우트 등록: `routes/web.php`에 mention 라우트 추가
  - POST /admin/tenders/{tender}/mention (저장)
  - DELETE /admin/tenders/{tender}/mention (삭제)
- ✅ Blade 뷰 수정 (show.blade.php):
  - "내 메모" 카드 섹션 추가 (작업 섹션 하단)
  - 텍스트 영역 (5000자 제한)
  - 실시간 글자 수 카운터
  - 저장/삭제 버튼
  - 마지막 수정 시간 표시
  - JavaScript AJAX 멘션 관리 기능 구현

## 구현 결과

### 변경된 파일 목록
1. **데이터베이스**:
   - `database/migrations/2025_10_31_183709_create_tender_mentions_table.php` (신규)

2. **백엔드**:
   - `app/Models/TenderMention.php` (신규)
   - `app/Models/Tender.php` (수정 - mentions 관계 추가)
   - `app/Http/Controllers/Admin/TenderController.php` (수정 - 3개 메서드)
   - `routes/web.php` (수정 - 2개 라우트 추가)

3. **프론트엔드**:
   - `resources/views/admin/tenders/show.blade.php` (수정 - UI + JavaScript)

### 주요 기능
1. 💬 **개인 메모 작성** (공고별로 사용자가 자유롭게 메모 작성)
2. ✏️ **메모 수정** (저장 버튼으로 언제든 수정 가능)
3. 🗑️ **메모 삭제** (확인 후 완전 삭제)
4. 📊 **실시간 글자 수 카운터** (5000자 제한)
5. 🔒 **사용자별 독립 메모** (다른 사용자 메모는 볼 수 없음)
6. ⏰ **마지막 수정 시간 표시** (자동 업데이트)
7. ⚡ **AJAX 방식** (페이지 새로고침 없이 즉시 저장/삭제)
8. 🔔 **토스트 알림** (저장/삭제 성공 시 우측 상단 알림)

### 테스트 상태
- ⏳ 수동 테스트 대기중 (브라우저 확인 필요)

---
**최종 수정**: 2025-10-31 (구현 완료)
