# AI 분석 시스템 설계 문서

## 시스템 개요
나라장터 용역공고를 타이드플로 회사의 기술 역량과 매칭하여 적합성을 분석하고 점수화하는 AI 기반 시스템

## 회사 프로필 (타이드플로)

### 기술 역량
- **프로그래밍 언어**: Java, PHP, Python, JavaScript, TypeScript
- **웹 개발**: Laravel, React, Vue.js, Node.js, Spring Boot
- **데이터베이스**: MySQL, PostgreSQL, MongoDB, Redis
- **클라우드/인프라**: AWS, Docker, Kubernetes, CI/CD
- **AI/ML**: OpenAI API, Claude, TensorFlow, PyTorch
- **모바일**: React Native, Flutter
- **기타**: RESTful API, GraphQL, Microservices, DevOps

### 업무 영역
- 웹 애플리케이션 개발
- 모바일 앱 개발
- AI/ML 솔루션 구축
- 데이터 처리 및 분석
- 시스템 통합 및 API 개발
- UI/UX 개발
- 클라우드 인프라 구축
- 유지보수 및 운영

### 규모 및 역량
- **팀 규모**: 중소규모 (10-50명 추정)
- **프로젝트 규모**: 소규모-중규모 프로젝트 적합
- **예산 범위**: 1천만원 ~ 10억원 규모
- **기간**: 3개월 ~ 12개월 프로젝트 적합

## AI 분석 로직

### 분석 기준 (100점 만점)

#### 1. 기술적 적합성 (40점)
- **키워드 매칭** (20점)
  - 웹개발, 앱개발, 시스템개발, API, 데이터베이스 등
  - Java, PHP, Python 등 언어별 가중치
- **기술 스택 분석** (20점)
  - Laravel, React, Vue.js, Spring Boot 등 프레임워크
  - 클라우드, AI/ML, 빅데이터 관련 키워드

#### 2. 사업 영역 적합성 (25점)
- **업종코드 매칭** (15점)
  - 8111200201: 데이터처리서비스 (높은 적합성)
  - 8111200202: 빅데이터분석서비스 (높은 적합성)
  - 8111159901: 정보시스템개발서비스 (매우 높은 적합성)
- **사업 분야** (10점)
  - 소프트웨어개발, 시스템구축, 유지보수 등

#### 3. 프로젝트 규모 적합성 (20점)
- **예산 규모** (10점)
  - 1천만원-10억원: 높은 적합성
  - 10억원 초과: 중간 적합성
  - 1천만원 미만: 낮은 적합성
- **기간** (10점)
  - 3-12개월: 높은 적합성
  - 12개월 초과: 중간 적합성

#### 4. 경쟁 강도 및 기타 (15점)
- **지역 가점** (5점): 수도권 기반으로 접근성 고려
- **공고 유형** (5점): 기술제안, 협상계약 선호
- **특수 요구사항** (5점): 특허, 인증서 등 요구사항 분석

### 점수별 권고사항
- **80점 이상**: 적극 검토 권장 (높은 적합성)
- **60-79점**: 검토 권장 (중간 적합성)
- **40-59점**: 신중 검토 (낮은 적합성)
- **40점 미만**: 참여 비권장 (부적합)

## 데이터베이스 설계

### analyses 테이블
```sql
CREATE TABLE analyses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_id BIGINT UNSIGNED NOT NULL,
    analysis_score INT NOT NULL COMMENT '분석 점수 (0-100)',
    technical_score INT NOT NULL COMMENT '기술적 적합성 점수 (0-40)',
    business_score INT NOT NULL COMMENT '사업 영역 적합성 점수 (0-25)',
    scale_score INT NOT NULL COMMENT '프로젝트 규모 적합성 점수 (0-20)',
    competition_score INT NOT NULL COMMENT '경쟁강도 및 기타 점수 (0-15)',
    recommendation ENUM('highly_recommended', 'recommended', 'consider', 'not_recommended') NOT NULL,
    analysis_details JSON COMMENT '상세 분석 결과',
    analyzed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tender_id) REFERENCES tenders(id) ON DELETE CASCADE,
    INDEX idx_tender_analysis (tender_id),
    INDEX idx_score (analysis_score),
    INDEX idx_recommendation (recommendation)
);
```

### company_profiles 테이블
```sql
CREATE TABLE company_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL DEFAULT 'Tideflo',
    technical_keywords JSON COMMENT '기술 키워드 및 가중치',
    business_areas JSON COMMENT '사업 영역 정의',
    budget_range JSON COMMENT '적정 예산 범위',
    team_size_range JSON COMMENT '팀 규모 범위',
    preferred_duration_range JSON COMMENT '선호 프로젝트 기간',
    location_preferences JSON COMMENT '지역 선호도',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## AI 분석 프로세스

### 1. 텍스트 전처리
- 공고 제목, 내용에서 기술 키워드 추출
- 불용어 제거, 토큰화
- 업종코드, 예산, 기간 정보 구조화

### 2. 점수 계산
- 각 카테고리별 점수 계산
- 가중치 적용
- 총점 및 권고사항 결정

### 3. 결과 저장 및 표시
- 분석 결과 데이터베이스 저장
- 웹 인터페이스를 통한 결과 표시
- 상세 분석 내역 제공

## API 설계

### 분석 실행
- `POST /admin/tenders/{tender}/analyze`
- 특정 공고에 대한 AI 분석 실행

### 분석 결과 조회
- `GET /admin/analyses`
- 전체 분석 결과 목록 조회

### 분석 결과 상세
- `GET /admin/analyses/{analysis}`
- 특정 분석 결과 상세 조회

### 일괄 분석
- `POST /admin/analyses/bulk-analyze`
- 여러 공고 일괄 분석

## 확장 계획

### Phase 3.1: 기본 분석 시스템
- 키워드 기반 매칭
- 기본 점수 계산 로직
- 웹 인터페이스 구현

### Phase 3.2: 고도화
- OpenAI/Claude API 연동
- 자연어 처리 개선
- 머신러닝 모델 적용

### Phase 3.3: 지능화
- 과거 입찰 결과 학습
- 개인화된 추천
- 자동 알림 시스템