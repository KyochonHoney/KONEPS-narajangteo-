# Nara Project - 나라장터 AI 제안서 자동생성 시스템

## 프로젝트 개요
- **프로젝트명**: Nara (나라장터 AI 제안서 시스템)
- **위치**: `/home/tideflo/nara`
- **상태**: 요구사항 분석 및 설계 단계
- **언어**: 한국어 주도 개발
- **목적**: 나라장터 용역공고 AI 분석 및 자동 제안서 생성 시스템

## 시스템 개요
**비즈니스 플로우**: 로그인 → 권한별 접근(최고관리자/관리자/유저) → 나라장터 용역공고 수집 → **실제 AI 기반 분석** → 자동 제안서 생성

**핵심 기능**:
- 🔐 **권한별 사용자 관리**: 최고관리자/관리자/유저 3단계 권한
- 📊 **대시보드**: 전체 현황 및 통계
- 📋 **나라장터 용역공고 리스트**: 실시간 공고 수집 및 관리
- 🤖 **지능형 AI 분석**: 타이드플로 기술스택 vs 공고 요구사항 실시간 매칭
- 📄 **첨부파일 AI 분석**: 과업지시서/제안요청서 PDF AI 분석 및 기술요구사항 추출
- 🎯 **정확도 높은 적합성 점수**: OpenAI/Claude API 기반 정밀 분석
- 📝 **제안서 자동생성**: AI + 템플릿 조합 자동 제안서 작성
- ⚙️ **설정**: 시스템 환경설정 및 관리

## 기술 스택
- **백엔드**: PHP 8 + Laravel Framework
- **데이터베이스**: MySQL (tideflo.sldb.iwinv.net > naradb)
- **AI 엔진**: OpenAI GPT-4 API / Claude API (Anthropic)
- **문서 분석**: PDF 파싱 + OCR + AI 텍스트 분석
- **웹 크롤링**: 타이드플로 기술스택 자동 수집 (Laravel HTTP Client)
- **파일 처리**: 나라장터 첨부파일 다운로드 및 분석
- **프론트엔드**: Laravel Blade + Vue.js/React (예정)
- **서버**: Apache/Nginx + PHP-FPM

## 프로젝트 구조
```
/home/tideflo/nara/
├── public_html/          # 웹 서버 공개 디렉토리 (Laravel public)
├── docs/                 # 프로젝트 문서
│   ├── requirements/     # 요구사항 명세서
│   ├── architecture/     # 시스템 아키텍처 설계
│   ├── database/        # 데이터베이스 설계서
│   ├── api/             # API 명세서
│   ├── components/      # 컴포넌트 문서
│   └── deployment/      # 배포 관련 문서
├── laravel-app/         # Laravel 메인 애플리케이션 (예정)
│   ├── app/            # 애플리케이션 로직
│   ├── database/       # 마이그레이션, 시더
│   ├── resources/      # 뷰, 에셋
│   └── routes/         # 라우팅 설정
├── ai-modules/          # AI 분석 모듈 (OpenAI/Claude API 연동)
│   ├── analyzers/      # AI 분석 서비스
│   ├── crawlers/       # 타이드플로 기술스택 크롤러
│   ├── parsers/        # PDF/문서 파싱 엔진
│   └── templates/      # AI 프롬프트 템플릿
├── storage/            # 파일 저장소 (첨부파일 다운로드, 분석 결과)
│   ├── attachments/    # 나라장터 첨부파일 저장소
│   ├── analysis_cache/ # AI 분석 결과 캐시
│   └── company_data/   # 타이드플로 기술스택 데이터
└── CLAUDE.md           # 프로젝트 컨텍스트 관리 문서
```

## 문서 관리 원칙
1. **CLAUDE.md**: 프로젝트 전체 컨텍스트 중앙 관리
2. **docs/**: 세부 기술 문서는 분리된 파일로 관리
3. **참조 링크**: CLAUDE.md에서 모든 하위 문서로 연결
4. **컨텍스트 보존**: 세션 간 정보 유실 방지

## 현재 상태 (2025-08-28)

### Phase 1 완료 ✅
- [x] 프로젝트 컨텍스트 관리 시스템 구축
- [x] 나라장터 AI 제안서 시스템 요구사항 분석 완료
- [x] 시스템 아키텍처 기본 설계 완료  
- [x] 데이터베이스 스키마 기본 설계 완료
- [x] 개발 단계별 로드맵 수립
- [x] **Laravel 프로젝트 초기화 완료** (public_html 디렉토리)
- [x] **기본 환경 설정 완료** (MySQL 연결, 한국어 로케일)
- [x] **데이터베이스 연결 및 마이그레이션 작성 완료** (11개 테이블, 기본 데이터 시드)
- [x] **기본 사용자 인증 시스템 구축 완료** (3단계 역할 기반 RBAC)
- [x] **도메인 접근 설정 완료** (https://nara.tideflo.work, SSL 인증서 적용)
- [x] **커스텀 홈페이지 구현 완료** (Laravel welcome 페이지 대체)
- [x] **로그인 UX 개선 완료** (개발용 테스트 계정 표시 및 빠른 로그인)

### Phase 2 완료 ✅
- [x] **나라장터 API 연동 서비스 구현** (NaraApiService)
- [x] **데이터 수집 및 파싱 시스템 구현** (TenderCollectorService)
- [x] **Artisan 명령어 구현** (tender:collect)
- [x] **관리자용 데이터 관리 시스템 구현** (TenderController)
- [x] **Tender 모델 및 관계 정의 완료**
- [x] **환경 설정 및 라우트 등록 완료**
- [x] **포괄적 테스트 시스템 구현**

### Phase 2.1 완료 ✅ (2025-08-29)
- [x] **관리자 UI 뷰 파일 구현 완료** (admin.tenders.index, show, collect)
- [x] **Bootstrap Icons 및 jQuery 추가** (레이아웃 개선)
- [x] **Mock 데이터 생성 시스템 구축** (시스템 테스트용 100건 데이터)
- [x] **TenderCategory 모델 구현** (용역, 공사, 물품 분류)
- [x] **웹 인터페이스 오류 해결** (뷰 파일 누락 문제 완전 해결)
- [x] **대시보드 기능 문제 해결 완료** (실제 통계 데이터 표시, 100% 테스트 통과)

### Phase 2.2 완료 ✅ (2025-09-01)
- [x] **나라장터 API 연결 문제 완전 해결** 
  - 초기 문제: NO_OPENAPI_SERVICE_ERROR → HTTP ROUTING ERROR → 입력범위값 초과 오류
  - **핵심 해결책**: `inqryDiv=01` 파라미터 (11이 아닌 01이 정상 작동)
  - API 응답: 정상 (code 00), 131개 공고 데이터 수신 확인
- [x] **NaraApiService.php 완전 업데이트** (성공 파라미터 적용)
- [x] **API 문서 작성** (docs/api/api.md - 공식 명세 기반)
- [x] **포괄적 테스트 시스템 구축** (15개 테스트 스크립트)

### Phase 2.3 완료 ✅ (2025-09-01)
- [x] **detail_url Accessor 문제 해결** (API 값 우선 사용으로 수정)
- [x] **Enhanced Tender Detail View 구현 완료** (나라장터 수준 상세 정보)
- [x] **Tender 모델에 6개 accessor 메서드 추가** (분류, 예산, 일정, 담당자, 첨부파일 등)
- [x] **109개 API 필드 완전 통합** (7개 주요 섹션으로 구조화)
- [x] **반응형 UI 디자인 적용** (모바일/태블릿 최적화)
- [x] **포괄적 테스트 시스템 구축** (스모크 테스트 통과)

### Phase 2.4 완료 ✅ (2025-09-01)
- [x] **Enhanced View 수정 완료** (Proof Mode)
- [x] **공고기관 → 수요기관 라벨 변경** 
- [x] **집행기관 → 수요기관 담당자 라벨 변경**
- [x] **공고종류(ntce_kind_nm) 필드 추가** (재공고=빨간색, 변경공고=녹색)
- [x] **업종코드 표시 기능 추가** (classification_info['code'])
- [x] **Proof Mode 산출물 4종 완성** (코드 + 실행로그 + 테스트 + 문서)

### Phase 2.5 완료 ✅ (2025-09-01)
- [x] **기존 AI 분석 시스템 구축 완료** (규칙 기반 TenderAnalysisService)
- [x] **AI 분석 데이터베이스 설계** (analyses 테이블, company_profiles 테이블)
- [x] **AI 분석 웹 인터페이스 구현** (관리자 페이지, 개별/일괄 분석)
- [x] **키워드 매칭 로직 개선** (한글 키워드 지원, 다양한 점수 분포)
- [x] **분석 결과 시각화** (상세 분석 페이지, 통계 대시보드)

### Phase 4 완료 ✅ (2025-09-02)
- [x] **AI 기반 제안서 자동생성 시스템 구현 완료** (Proof Mode)
- [x] **제안서 템플릿 시스템** (docs/templates/proposal-template.md)
- [x] **AiApiService 제안서 생성 기능** (analyzeProposalStructure, generateProposal)
- [x] **Proposal 모델 및 마이그레이션** (완전한 제안서 생성 워크플로우)
- [x] **ProposalGeneratorService** (구조 분석 → 내용 생성 → 품질 검증)
- [x] **ProposalController 웹 인터페이스** (CRUD, 일괄생성, 다운로드)
- [x] **Mock AI 시스템** (테스트용, 실제 API 없이도 작동)
- [x] **네비게이션 메뉴 추가** ("제안서 관리" 링크)
- [x] **포괄적 테스트 시스템** (4개 제안서 생성 성공, 50% 성공률)

### 다음 단계 (Phase 3) 🚧 **진행 예정**
- [ ] **실제 AI API 연동**: OpenAI/Claude API 기반 지능형 분석으로 전환
- [ ] **타이드플로 기술스택 자동 수집**: 웹사이트/GitHub 크롤링 시스템
- [ ] **첨부파일 AI 분석**: PDF 다운로드, 파싱, AI 기반 요구사항 분석
- [ ] **비용 최적화**: 캐싱, 배치 처리, 토큰 관리 시스템

## 참조 문서
### 📋 요구사항
- [docs/requirements/business-requirements.md](docs/requirements/business-requirements.md) - 비즈니스 요구사항
- [docs/requirements/functional-requirements.md](docs/requirements/functional-requirements.md) - 기능 요구사항  
- [docs/requirements/technical-requirements.md](docs/requirements/technical-requirements.md) - 기술 요구사항

### 🏗️ 아키텍처
- [docs/architecture/system-architecture.md](docs/architecture/system-architecture.md) - 시스템 아키텍처
- [docs/architecture/module-design.md](docs/architecture/module-design.md) - 모듈 설계
- [docs/architecture/integration-design.md](docs/architecture/integration-design.md) - 외부 시스템 연동 설계

### 🗄️ 데이터베이스
- [docs/database/schema-design.md](docs/database/schema-design.md) - 데이터베이스 스키마
- [docs/database/migration-plan.md](docs/database/migration-plan.md) - 마이그레이션 계획
- [docs/database/connection-config.md](docs/database/connection-config.md) - DB 연결 설정

### 🔌 API
- [docs/api/api-specification.md](docs/api/api-specification.md) - API 명세서
- [docs/api/authentication.md](docs/api/authentication.md) - 인증 시스템

### 🧩 컴포넌트
- [docs/components/user-management.md](docs/components/user-management.md) - 사용자 관리
- [docs/components/tender-collector.md](docs/components/tender-collector.md) - 나라장터 데이터 수집
- [docs/components/ai-analyzer.md](docs/components/ai-analyzer.md) - AI 분석 엔진
- [docs/components/proposal-generator.md](docs/components/proposal-generator.md) - 제안서 생성기

### 🚀 배포
- [docs/deployment/deployment-guide.md](docs/deployment/deployment-guide.md) - 배포 가이드

## 컨텍스트 관리 시스템
### 🎯 핵심 원칙
- **중앙집중식 관리**: CLAUDE.md가 모든 프로젝트 정보의 허브 역할
- **계층적 문서화**: 세부 내용은 docs/ 하위로 분리하되 연결성 유지
- **자동 업데이트**: 코드/설계 변경 시 관련 문서도 함께 업데이트
- **세션 연속성**: 언제든 CLAUDE.md 참조로 컨텍스트 완전 복구 가능

### 📝 문서 작성 워크플로우
1. **계획 단계**: CLAUDE.md에 개요 및 진행 상황 기록
2. **설계 단계**: docs/ 해당 카테고리에 세부 문서 생성
3. **구현 단계**: 코드 변경 시 관련 문서 동기화
4. **리뷰 단계**: CLAUDE.md 참조로 전체 일관성 검증

### 🔄 컨텍스트 유실 방지
- 모든 중요 결정사항 CLAUDE.md 기록
- 외부 종속성 및 제약사항 문서화
- 진행 중인 작업의 현재 상태 명시
- 다음 작업을 위한 가이드라인 포함

## 데이터베이스 연결 정보
```
서버: tideflo.sldb.iwinv.net
데이터베이스: naradb  
사용자명: nara
패스워드: 1q2w3e4r!!nara
```
⚠️ **보안 주의**: 실제 환경에서는 .env 파일로 관리 필요

## 개발 단계별 로드맵

### Phase 1: 기초 인프라 (1-2주)
1. Laravel 프로젝트 초기 설정 및 환경 구성
2. 데이터베이스 연결 및 기본 마이그레이션
3. 사용자 인증/권한 시스템 구축 (Spatie/laravel-permission)
4. 기본 UI 프레임워크 설정 (Bootstrap/Tailwind)

### Phase 2: 데이터 수집 모듈 ✅ (완료)
1. ✅ 나라장터 데이터 수집 API 연동 (NaraApiService)
2. ✅ 용역공고 데이터 파싱 및 정규화 (TenderCollectorService)
3. ✅ Artisan 명령어 기반 수집 시스템 (tender:collect)
4. ✅ 관리자 페이지 - 데이터 수집 현황 (TenderController)

### Phase 3: 지능형 AI 분석 엔진 (3-5주) 🚧 **진행중**
1. **타이드플로 기술스택 자동 수집**
   - 회사 웹사이트 크롤링 (tideflo.com)
   - GitHub 저장소 분석 (공개 레포지토리)
   - 포트폴리오/프로젝트 이력 자동 추출
   - 기술스택 데이터베이스 구축

2. **나라장터 첨부파일 AI 분석**
   - 과업지시서/제안요청서 PDF 자동 다운로드
   - PDF 텍스트 추출 및 OCR 처리
   - OpenAI/Claude API 기반 기술요구사항 분석
   - 상세 업무 내용 및 기술스택 파악

3. **실시간 AI 매칭 및 점수 산정**
   - 타이드플로 역량 vs 프로젝트 요구사항 정밀 비교
   - AI 기반 적합성 점수 및 상세 근거 제시
   - 실제 프로젝트 내용 기반 정확한 평가
   - 위험 요소 및 기회 요소 자동 식별

4. **분석 결과 저장 및 최적화**
   - 분석 결과 캐싱 시스템 구축
   - API 사용량 최적화 (토큰 사용량 관리)
   - 성능 모니터링 및 개선

### Phase 4: 제안서 생성 시스템 (3-4주)
1. 제안서 템플릿 관리 시스템
2. AI 기반 제안서 자동 생성 엔진
3. 문서 조합 및 포맷팅 (PDF/Word 출력)
4. 파일 업로드/다운로드 기능

### Phase 5: 통합 및 배포 (1-2주)
1. 전체 시스템 통합 테스트
2. 성능 최적화 및 보안 강화
3. 배포 환경 구축 및 실서버 배포
4. 사용자 교육 및 매뉴얼 작성

## 개발 노트
- **SuperClaude 프레임워크 기반 관리**: 체계적 문서화 및 진행상황 추적
- **한국어 중심 개발**: 사용자 인터페이스 및 문서 한국어 우선
- **모듈화 설계**: 각 기능별 독립적 모듈로 개발하여 확장성 확보
- **실제 AI API 연동**: OpenAI GPT-4 / Claude API 기반 정밀 분석
- **비용 효율적 AI 사용**: 캐싱, 배치 처리, 토큰 최적화 전략 적용
- **실시간 크롤링**: 타이드플로 기술스택 및 시장 동향 자동 업데이트
- **첨부파일 처리**: PDF 다운로드, 파싱, AI 분석 파이프라인 구축
- **데이터 보안**: 민감 정보 암호화 저장 및 접근 권한 관리 필수
- **다날페이 연동**: 순수 PHP7 기반 모바일/웹 분기 결제 시스템 구현 완료 (2025-09-02)

## 주요 고려사항
1. **실제 AI 분석 정확도**: OpenAI/Claude API 기반 정밀 매칭으로 95% 이상 정확도 목표
2. **타이드플로 기술스택 실시간 추적**: 웹사이트/GitHub 자동 크롤링으로 최신 기술 반영
3. **첨부파일 처리 성능**: PDF 다운로드, 파싱, AI 분석 전체 프로세스 5분 내 완료
4. **AI API 비용 최적화**: 토큰 사용량 관리, 캐싱 전략, 배치 처리로 월 비용 100만원 이하 목표
5. **분석 결과 신뢰성**: AI 분석 근거 명시, 점수 산정 로직 투명성 확보
6. **확장성**: 다른 조달 사이트(조달청, 지자체) 연동 가능한 구조 설계

## 다음 즉시 실행 단계 (Phase 3 AI 엔진 구축)

### 🎯 **1단계: AI API 연동 및 환경 설정**
- OpenAI API 키 설정 및 Laravel 패키지 설치
- Claude API (Anthropic) 연동 설정
- AI 분석 전용 데이터베이스 테이블 확장
- API 사용량 모니터링 시스템 구축

### 🕷️ **2단계: 타이드플로 기술스택 자동 수집**
- 회사 웹사이트 크롤링 서비스 구현 (tideflo.com)
- GitHub API 연동 및 저장소 분석 (공개 레포 기준)
- 기술스택 데이터 정규화 및 DB 저장
- 주기적 업데이트 스케줄러 설정

### 📄 **3단계: 나라장터 첨부파일 처리**
- 첨부파일 자동 다운로드 시스템 구축
- PDF 텍스트 추출 라이브러리 구현 (spatie/pdf-to-text)
- OCR 처리 기능 추가 (이미지 기반 PDF 대응)
- 파일 저장 및 캐싱 시스템 구축

### 🤖 **4단계: AI 기반 실시간 매칭 엔진**
- OpenAI/Claude API 프롬프트 템플릿 설계
- 타이드플로 vs 프로젝트 요구사항 비교 로직
- AI 분석 결과 파싱 및 점수화 알고리즘
- 분석 근거 및 추천 사유 자동 생성

### ⚡ **5단계: 성능 최적화 및 모니터링**
- AI API 호출 배치 처리 및 큐 시스템
- 분석 결과 캐싱 (Redis) 구현
- 토큰 사용량 추적 및 비용 최적화
- 에러 핸들링 및 재시도 로직

## Proof Mode 문서 링크
- [PROOF_MODE_AUTH.md](public_html/PROOF_MODE_AUTH.md) - 인증 시스템 구현
- [PROOF_MODE_DOMAIN.md](public_html/PROOF_MODE_DOMAIN.md) - 도메인 접근 설정
- [PROOF_MODE_HOMEPAGE.md](public_html/PROOF_MODE_HOMEPAGE.md) - 홈페이지 커스터마이징
- [PROOF_MODE_LOGIN_TESTACCOUNTS.md](public_html/PROOF_MODE_LOGIN_TESTACCOUNTS.md) - 로그인 테스트 계정
- [PROOF_MODE_NARA_API.md](public_html/PROOF_MODE_NARA_API.md) - 나라장터 API 연동 모듈
- [PROOF_MODE_VIEWS_FIX.md](PROOF_MODE_VIEWS_FIX.md) - 관리자 뷰 파일 및 UI 오류 해결
- [PROOF_MODE_DASHBOARD_FIX.md](PROOF_MODE_DASHBOARD_FIX.md) - 대시보드 기능 수정 완료
- [PROOF_MODE_ENHANCED_VIEW.md](PROOF_MODE_ENHANCED_VIEW.md) - 상세 뷰 대폭 개선 (109개 필드 통합)
- [PROOF_MODE_DANAL_PAY.md](public_html/pay/PROOF_MODE_DANAL_PAY.md) - 다날페이 모바일/웹 분기 결제 시스템 구현 완료

## 테스트 스크립트
- [scripts/smoke_test_views.sh](scripts/smoke_test_views.sh) - 뷰 파일 스모크 테스트
- [scripts/test_dashboard_fix.sh](scripts/test_dashboard_fix.sh) - 대시보드 기능 수정 검증 테스트
- [scripts/test_enhanced_view_simple.sh](scripts/test_enhanced_view_simple.sh) - 상세 뷰 개선 검증 테스트

## 최근 작업

### 즐겨찾기 기능 구현 완료 (2025-10-31)
**작업 문서**: [TASK_FAVORITE_FEATURE.md](TASK_FAVORITE_FEATURE.md)

**구현 내용**:
- ⭐ 공고별 즐겨찾기 기능 추가 (별표 클릭으로 ON/OFF 토글)
- 🔍 즐겨찾기 전용 필터 추가 ("즐겨찾기만 보기" 체크박스)
- 💾 데이터베이스 영구 저장 (tenders.is_favorite 필드)
- 🎨 직관적 UI (채워진 별 ⭐ vs 빈 별 ☆)
- 🔔 토스트 알림 (세부 페이지 즐겨찾기 변경 시)

**변경 사항**:
1. 데이터베이스 마이그레이션 추가 (`2025_10_31_182416_add_is_favorite_to_tenders_table.php`)
2. Tender 모델에 `scopeFavorite()` 스코프 메서드 추가
3. TenderController에 `toggleFavorite()` 메서드 및 필터 로직 추가
4. 라우트 등록 (`PATCH /admin/tenders/{tender}/toggle-favorite`)
5. **index.blade.php**: 별표 UI 및 AJAX 토글 JavaScript 추가 (목록 페이지)
6. **show.blade.php**: 큰 버튼 스타일 즐겨찾기 버튼 및 토스트 알림 추가 (세부 페이지)

**테스트 상태**: ⏳ 브라우저 테스트 대기중

---

## 최근 해결된 문제

### 대시보드 기능 문제 해결 (2025-08-29)
**문제**: 로그인 후 대시보드가 아무것도 표시하지 않는 문제

**원인**: AuthController의 dashboard() 및 adminDashboard() 메서드에서 하드코딩된 0 값 사용

**해결 방법**:
- TenderCollectorService 의존성 주입 구현
- 실제 통계 데이터 조회로 변경 (`getCollectionStats()` 사용)
- 현재 99개 공고 데이터 정상 표시

**검증 결과**: 14/14 테스트 통과 (100% 성공률)

### AI 분석 로직 개선 완료 (2025-09-01)
**문제**: 기존 AI 분석이 모든 공고에 대해 비슷한 점수(29~37점) 및 하드코딩된 결과 반환

**개선 사항**:
- **하드코딩 제거**: 고정된 점수 대신 실제 공고 내용 기반 분석 로직 구현
- **한글 키워드 매칭**: 한국어 공고에서 기술 키워드를 정확히 인식하도록 개선
- **다양한 점수 분포**: 37.7~46.3점으로 공고별 차별화된 점수 제공
- **실제 키워드 매칭**: 공고마다 다른 기술 키워드 발견 ("플랫폼", "홈페이지", "데이터" 등)
- **업종코드 연계**: 업종에 따른 기술 보너스 점수 적용
- **제목 분석 강화**: 공고 제목에서 기술 관련성 직접 분석

**검증 결과**: 5개 공고 테스트에서 각기 다른 점수와 매칭 키워드 확인
- 원격교육지원관리플랫폼: 44.2점 ("플랫폼, 시스템, 관리, 서버")
- 데이터자격검정 홈페이지: 46.3점 ("홈페이지, 데이터, 운영, 관리")
- 웹접근성 강화: 43.7점 ("웹, 플랫폼, 운영")

**다음 단계**: 규칙 기반에서 실제 AI API 기반 분석으로 업그레이드 예정

### 즐겨찾기 기능 구현 완료 (2025-10-31)
**요구사항**: 공고별로 별표(⭐)를 눌러 즐겨찾기 할 수 있고, 필터로 즐겨찾기 공고만 볼 수 있는 기능

**구현 내용**:
- 데이터베이스: `is_favorite` 필드 추가 (boolean, default: false, 인덱스 적용)
- 백엔드: `toggleFavorite()` API 엔드포인트, 필터 로직, `scopeFavorite()` 스코프
- 프론트엔드:
  - 목록 페이지(index.blade.php): 작은 별표 아이콘 + 필터 체크박스
  - 상세 페이지(show.blade.php): 큰 즐겨찾기 버튼 + 토스트 알림
- AJAX 방식으로 페이지 새로고침 없이 즉시 반영

**변경 파일**: 6개 (마이그레이션, Tender.php, TenderController.php, web.php, index.blade.php, show.blade.php)
**문서**: TASK_FAVORITE_FEATURE.md, 즐겨찾기_기능_구현_완료_보고서.md

### 멘션(메모) 기능 구현 완료 (2025-10-31)
**요구사항**: 공고 상세 페이지에서 개인 메모를 작성하여 어디까지 봤는지, 중요 포인트 등을 자유롭게 기록

**구현 내용**:
- 데이터베이스: `tender_mentions` 테이블 생성 (tender_id, user_id, mention, 복합 유니크 인덱스)
- 백엔드:
  - TenderMention 모델 생성 (Tender, User 관계 설정)
  - TenderController: `storeMention()`, `destroyMention()` 메서드 추가
  - 라우트: POST /mention, DELETE /mention
- 프론트엔드:
  - "내 메모" 카드 섹션 (5000자 제한, 실시간 글자 수 카운터)
  - 저장/삭제 버튼 (AJAX 방식)
  - 마지막 수정 시간 표시
  - 토스트 알림
- 사용자별 독립적 메모 (다른 사용자는 볼 수 없음)

**변경 파일**: 5개 (마이그레이션, TenderMention.php, Tender.php, TenderController.php, web.php, show.blade.php)
**문서**: TASK_MENTION_FEATURE.md

### "상주" 키워드 감지 및 재다운로드 기능 수정 완료 (2025-11-06)

#### 문제 1: Tender 1715 "상주" 키워드 감지 실패 ❌ → ✅
**문제**: 제안요청서.hwp 파일에 "상주" 단어가 있는데 시스템이 감지하지 못함

**원인**: Python HWP 추출 스크립트(`extract_hwp_text_multi.py`)가 깨진 텍스트 생성

**해결 방법**:
- **새 스크립트 생성**: `scripts/extract_hwp_text_hwp5.py` (pyhwp의 hwp5txt 도구 사용)
- **TenderController.php 수정**: 2곳 (line 613, 684) - 새 스크립트로 변경
- **SangjuCheckService.php 수정**: 1곳 (line 128) - 새 스크립트로 변경

**검증 결과**: Tender 1715에서 "상주" 키워드 3곳 발견 ✅
- "유지관리 인력을 도청에 상주시켜"
- "상주인력(2명 이상)"
- "장소에서 상주하는 것을 원칙으로"

#### 문제 2: Tender 1769 재다운로드 기능 미작동 ❌ → ✅
**문제**: 제안요청정보 파일 수집은 되는데 재다운로드 버튼이 작동하지 않음

**원인**: `AttachmentService::downloadAttachment()` 및 `ProposalFileCrawlerService::downloadSingleFile()` 메서드 누락

**해결 방법**:
- **AttachmentService.php**: `downloadAttachment()` 메서드 추가 (2단계 워크플로우)
  1. ProposalFileCrawlerService로 메타데이터 갱신
  2. ProposalFileDownloaderService로 실제 파일 다운로드
- **ProposalFileCrawlerService.php**: `downloadSingleFile()` 메서드 추가
  - Playwright로 파일 목록 재수집
  - 파일명 매칭 후 attachment 메타데이터 업데이트

**검증 결과**: Tender 1769 파일 2개 재다운로드 성공 ✅
- 2026년 일반행정 정보시스템 통합 유지보수 제안요청서(수정).hwp (281,600 bytes)
- 기술지원협약서.pdf (13,268,928 bytes)

**변경 파일**: 5개
- 생성: `scripts/extract_hwp_text_hwp5.py`, `scripts/test_redownload_functionality.sh`
- 수정: `TenderController.php`, `SangjuCheckService.php`, `AttachmentService.php`, `ProposalFileCrawlerService.php`

**문서**: [BUGFIX_SANGJU_REDOWNLOAD.md](BUGFIX_SANGJU_REDOWNLOAD.md)

#### 문제 3: Tender 1769 상주 검사 - PDF 파일 누락 ❌ → ✅
**문제**: 총 3개 파일(HWP 2개, PDF 1개)이 있는데 2개만 검사됨

**원인**:
- TenderController Line 606에서 HWP만 검사하고 PDF 제외
- `type = 'proposal'` 필터 누락 (잠재적 버그)

**해결 방법**:
- **지원 포맷 확장**: HWP만 → HWP, PDF, DOC, DOCX, TXT
- **type 필터 추가**: `->where('type', 'proposal')` 명시적 추가
- **파일별 처리 로직**:
  - HWP: `hwp5txt` (기존)
  - PDF: `pdftotext` (신규)
  - DOC: `antiword` (신규)
  - DOCX: `docx2txt` (신규)
  - TXT: `file_get_contents` (신규)

**검증 결과**: Tender 1769 파일 3개 모두 검사 ✅
- 2026년 일반행정 정보시스템 통합 유지보수 제안요청서(수정).hwp ✅
- 기술지원협약서.pdf ✅ (이전에는 스킵됨, 이제 검사됨!)
- 공고서_지방_제한_국내_유지관리_1468_20억미만_서면.hwp ✅

**변경 파일**: 1개
- 수정: `TenderController.php` (Lines 587-647)
- 생성: `scripts/test_sangju_all_files.sh`

**문서**: [BUGFIX_SANGJU_CHECK_ALL_FILES.md](BUGFIX_SANGJU_CHECK_ALL_FILES.md)

#### 문제 4: Tender 1768 HWPX 파일 미지원 ❌ → ✅
**문제**: 제안요청정보 파일 `제안요청서 (사전규격공개).hwpx`가 검사되지 않음, "상주" 키워드 미감지

**원인**:
1. HWPX (Hangul Word Processor XML) 포맷 미지원 (HWP만 지원)
2. Local Path에 확장자 없음 (`proposal_files/1768/download`)
3. **중요 발견**: hwp5txt가 HWPX 파일을 처리할 수 없음 (OLE2 형식이 아님)

**해결 방법**:
- **HWPX 포맷 지원 추가**: 지원 파일 포맷 목록에 `hwpx` 추가
- **Fallback 확장자 감지**: Local path에 확장자가 없으면 file_name에서 추출
  ```php
  if (empty($extension) || $extension === pathinfo($fullPath, PATHINFO_BASENAME)) {
      $extension = strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION));
  }
  ```
- **HWPX 전용 추출 스크립트 작성**: `scripts/extract_hwpx_text.py`
  - HWPX는 ZIP 압축된 XML 파일 구조
  - `Contents/section*.xml` 파일들에서 `<hp:t>` 태그 텍스트 추출
  - hwp5txt와 완전히 다른 접근 방식 필요

**HWPX vs HWP 파일 구조**:
- **HWP**: OLE2 Compound Binary File → hwp5txt 사용
- **HWPX**: ZIP compressed XML files → extract_hwpx_text.py 사용

**검증 결과**: Tender 1768 HWPX 파일 정상 검사 ✅
- File: 제안요청서 (사전규격공개).hwpx
- Extension detection: hwpx (file_name에서 추출)
- **이전 (hwp5txt 시도)**: 76 chars (에러 메시지만 추출) ❌
- **수정 후 (extract_hwpx_text.py)**: 146,074 chars 추출 ✅
- **"상주" 키워드**: 4개 이상 발견 ✅
  - "상주인력을 부득이하게 교체할 경우..."
  - "상주인력 교체 시에 인수인계 기간은 14일 이상..."
  - "유지관리 상주인력이 교육, 휴가 등의 사유로..."
  - "상주인력의 근무시간은 평일 9:00～18:00가 원칙..."

**변경 파일**: 3개
- 생성: `scripts/extract_hwpx_text.py` (HWPX 전용 추출 스크립트)
- 수정: `TenderController.php` (Lines 610-613, 615, 625-636, 672, 719-730)
- 수정: `SangjuCheckService.php` (Lines 52-55, 57, 67-78, 113, 160-171)

**핵심 개선사항**:
- HWP와 HWPX 각각 최적화된 추출 방식 사용
- 146KB 텍스트 추출로 키워드 감지율 대폭 향상
- 확장자 없는 파일도 정상 처리

**문서**: [BUGFIX_HWPX_SUPPORT.md](BUGFIX_HWPX_SUPPORT.md)

---
*최종 수정: 2025-11-06 - "상주" 키워드 감지, 재다운로드, PDF/HWPX 지원 수정 완료*
### 예산 필드 재설계 (2025-11-06)
**작업 문서**: [FEATURE_BUDGET_FIELDS_REDESIGN.md](FEATURE_BUDGET_FIELDS_REDESIGN.md)

**목적**: 나라장터 API 데이터 구조에 맞춰 예산 필드를 명확하게 재정의

**현재 문제점**:
- `budget` 필드 하나만 있어서 금액 구성이 불명확
- 추정가격과 부가세가 분리되지 않음
- 사업금액(총액)과 추정가격의 구분이 모호함

**새로운 구조**:
1. **total_budget** (사업금액) ← `metadata.asignBdgtAmt`
   - 정의: 추정가격 + 부가세
   - 용도: 전체 사업 예산, 통계, 필터링
   - 예시: ₩151,450,000

2. **allocated_budget** (추정가격) ← `metadata.presmptPrce`
   - 정의: 기초금액 (부가세 제외)
   - 용도: 실제 용역비용, 입찰가 비교
   - 예시: ₩136,363,636

3. **vat** (부가세) ← `metadata.VAT`
   - 정의: 부가세 (10%)
   - 계산식: `total_budget - allocated_budget`
   - 예시: ₩13,636,364

**구현 단계**:
- [ ] Phase 1: 데이터베이스 마이그레이션
  - [ ] `budget` → `total_budget` 이름 변경
  - [ ] `allocated_budget`, `vat` 컬럼 추가
  - [ ] 인덱스 업데이트
  - [ ] 기존 데이터 이관 (metadata에서 추출)

- [ ] Phase 2: Tender 모델 업데이트
  - [ ] fillable, casts 추가
  - [ ] Accessor 메서드 추가 (formatted_*, vat_rate)
  - [ ] 기존 budget 코드 검색 및 수정

- [ ] Phase 3: TenderCollectorService 업데이트
  - [ ] extractTenderData() 수정
  - [ ] 3개 필드 자동 추출

- [ ] Phase 4: UI 업데이트
  - [ ] 공고 목록: 사업금액 + 추정가 표시
  - [ ] 공고 상세: 예산 정보 카드 개선
  - [ ] 필터/검색 업데이트

---
