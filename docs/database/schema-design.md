# 데이터베이스 스키마 설계서

## 1. 데이터베이스 개요

### 1.1 기본 정보
- **DBMS**: MySQL 8.0+
- **문자셋**: UTF8MB4 (한글 및 이모지 지원)
- **엔진**: InnoDB (트랜잭션 지원)
- **연결 정보**: 
  - 서버: tideflo.sldb.iwinv.net
  - 데이터베이스: naradb
  - 사용자: nara / 1q2w3e4r!!nara

### 1.2 설계 원칙
- **정규화**: 3NF까지 정규화 적용
- **명명규칙**: snake_case 사용, 복수형 테이블명
- **인덱싱**: 검색 성능 최적화를 위한 복합 인덱스 적용
- **외래키**: 데이터 무결성 보장을 위한 FK 제약조건

## 2. ERD (Entity Relationship Diagram)

```mermaid
erDiagram
    users ||--o{ model_has_roles : has
    roles ||--o{ model_has_roles : assigned
    roles ||--o{ role_has_permissions : has
    permissions ||--o{ role_has_permissions : granted
    
    users ||--o{ analyses : creates
    users ||--o{ proposals : generates
    
    tender_categories ||--o{ tenders : categorizes
    tenders ||--o{ tender_attachments : has
    tenders ||--o{ analyses : analyzed
    
    analyses ||--o{ analysis_details : contains
    analyses ||--o{ proposals : generates
    
    proposal_templates ||--o{ proposals : uses
    proposals ||--o{ proposal_sections : contains
    
    company_profiles ||--o{ analyses : matches
    
    users {
        bigint id PK
        string name
        string email UK
        timestamp email_verified_at
        string password
        string remember_token
        timestamps
    }
    
    roles {
        bigint id PK
        string name UK
        string guard_name
        timestamps
    }
    
    permissions {
        bigint id PK
        string name UK
        string guard_name
        timestamps
    }
    
    tenders {
        bigint id PK
        string tender_no UK
        string title
        text content
        string agency
        decimal budget
        date deadline
        bigint category_id FK
        enum status
        json metadata
        timestamps
    }
    
    analyses {
        bigint id PK
        bigint tender_id FK
        bigint user_id FK
        bigint company_profile_id FK
        decimal total_score
        decimal technical_score
        decimal experience_score
        decimal budget_score
        decimal other_score
        enum status
        json analysis_data
        timestamps
    }
    
    proposals {
        bigint id PK
        bigint analysis_id FK
        bigint user_id FK
        bigint template_id FK
        string title
        text content
        string file_path
        enum status
        json metadata
        timestamps
    }
```

## 3. 테이블 상세 설계

### 3.1 사용자 관리 테이블

#### users (사용자)
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT '사용자명',
    email VARCHAR(255) NOT NULL UNIQUE COMMENT '이메일',
    email_verified_at TIMESTAMP NULL COMMENT '이메일 인증 시각',
    password VARCHAR(255) NOT NULL COMMENT '암호화된 비밀번호',
    remember_token VARCHAR(100) NULL COMMENT '자동로그인 토큰',
    is_active BOOLEAN DEFAULT TRUE COMMENT '계정 활성 상태',
    last_login_at TIMESTAMP NULL COMMENT '마지막 로그인',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_active (is_active),
    INDEX idx_last_login (last_login_at)
) COMMENT='사용자 계정 정보';
```

#### roles (역할)
```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE COMMENT '역할명',
    guard_name VARCHAR(255) DEFAULT 'web' COMMENT '가드명',
    display_name VARCHAR(255) COMMENT '표시명',
    description TEXT COMMENT '역할 설명',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_guard_name (guard_name)
) COMMENT='사용자 역할';
```

#### permissions (권한)
```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE COMMENT '권한명',
    guard_name VARCHAR(255) DEFAULT 'web' COMMENT '가드명',
    display_name VARCHAR(255) COMMENT '표시명',
    description TEXT COMMENT '권한 설명',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_guard_name (guard_name)
) COMMENT='시스템 권한';
```

### 3.2 용역공고 관리 테이블

#### tender_categories (공고 분류)
```sql
CREATE TABLE tender_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT '분류명',
    code VARCHAR(50) UNIQUE COMMENT '분류코드',
    parent_id BIGINT UNSIGNED NULL COMMENT '상위 분류 ID',
    description TEXT COMMENT '분류 설명',
    is_active BOOLEAN DEFAULT TRUE COMMENT '활성 상태',
    sort_order INT DEFAULT 0 COMMENT '정렬 순서',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES tender_categories(id) ON DELETE SET NULL,
    INDEX idx_parent (parent_id),
    INDEX idx_active (is_active),
    INDEX idx_sort_order (sort_order)
) COMMENT='용역공고 분류';
```

#### tenders (용역공고)
```sql
CREATE TABLE tenders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_no VARCHAR(100) NOT NULL UNIQUE COMMENT '공고번호',
    title VARCHAR(500) NOT NULL COMMENT '공고 제목',
    content TEXT COMMENT '공고 내용',
    agency VARCHAR(255) COMMENT '발주기관',
    budget DECIMAL(15,2) COMMENT '예산금액',
    currency VARCHAR(3) DEFAULT 'KRW' COMMENT '통화',
    start_date DATE COMMENT '공고시작일',
    end_date DATE COMMENT '공고마감일',
    category_id BIGINT UNSIGNED COMMENT '분류 ID',
    region VARCHAR(100) COMMENT '지역',
    status ENUM('active', 'closed', 'cancelled') DEFAULT 'active' COMMENT '공고상태',
    source_url TEXT COMMENT '원본 URL',
    collected_at TIMESTAMP COMMENT '수집 시각',
    metadata JSON COMMENT '추가 정보',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES tender_categories(id) ON DELETE SET NULL,
    INDEX idx_tender_no (tender_no),
    INDEX idx_agency (agency),
    INDEX idx_budget (budget),
    INDEX idx_end_date (end_date),
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_collected_at (collected_at),
    FULLTEXT idx_search (title, content, agency)
) COMMENT='용역공고 정보';
```

#### tender_attachments (공고 첨부파일)
```sql
CREATE TABLE tender_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_id BIGINT UNSIGNED NOT NULL COMMENT '공고 ID',
    filename VARCHAR(255) NOT NULL COMMENT '파일명',
    original_name VARCHAR(255) COMMENT '원본 파일명',
    filepath VARCHAR(500) COMMENT '파일 경로',
    filesize BIGINT COMMENT '파일 크기',
    mime_type VARCHAR(100) COMMENT 'MIME 타입',
    download_url TEXT COMMENT '다운로드 URL',
    is_downloaded BOOLEAN DEFAULT FALSE COMMENT '다운로드 여부',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tender_id) REFERENCES tenders(id) ON DELETE CASCADE,
    INDEX idx_tender_id (tender_id),
    INDEX idx_filename (filename)
) COMMENT='용역공고 첨부파일';
```

### 3.3 AI 분석 관리 테이블

#### company_profiles (회사 프로필)
```sql
CREATE TABLE company_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT '회사명',
    business_number VARCHAR(50) COMMENT '사업자등록번호',
    description TEXT COMMENT '회사 설명',
    capabilities JSON COMMENT '보유 역량 정보',
    experiences JSON COMMENT '수행 경험 정보',
    certifications JSON COMMENT '보유 자격/인증',
    employees_count INT COMMENT '직원 수',
    established_year YEAR COMMENT '설립연도',
    annual_revenue DECIMAL(15,2) COMMENT '연매출',
    website VARCHAR(255) COMMENT '웹사이트',
    contact_info JSON COMMENT '연락처 정보',
    is_active BOOLEAN DEFAULT TRUE COMMENT '활성 상태',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_active (is_active),
    FULLTEXT idx_search (name, description)
) COMMENT='회사 프로필 정보';
```

#### analyses (AI 분석)
```sql
CREATE TABLE analyses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_id BIGINT UNSIGNED NOT NULL COMMENT '공고 ID',
    user_id BIGINT UNSIGNED NOT NULL COMMENT '분석 요청 사용자 ID',
    company_profile_id BIGINT UNSIGNED COMMENT '회사 프로필 ID',
    total_score DECIMAL(5,2) NOT NULL COMMENT '총점 (0-100)',
    technical_score DECIMAL(5,2) COMMENT '기술 적합성 점수',
    experience_score DECIMAL(5,2) COMMENT '경험 점수',
    budget_score DECIMAL(5,2) COMMENT '예산 점수',
    other_score DECIMAL(5,2) COMMENT '기타 점수',
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending' COMMENT '분석 상태',
    analysis_data JSON COMMENT '상세 분석 데이터',
    ai_model_version VARCHAR(50) COMMENT '사용된 AI 모델 버전',
    processing_time INT COMMENT '처리 시간 (초)',
    started_at TIMESTAMP COMMENT '분석 시작 시각',
    completed_at TIMESTAMP COMMENT '분석 완료 시각',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tender_id) REFERENCES tenders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_profile_id) REFERENCES company_profiles(id) ON DELETE SET NULL,
    INDEX idx_tender_user (tender_id, user_id),
    INDEX idx_total_score (total_score),
    INDEX idx_status (status),
    INDEX idx_completed_at (completed_at)
) COMMENT='AI 분석 결과';
```

#### analysis_details (분석 상세)
```sql
CREATE TABLE analysis_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    analysis_id BIGINT UNSIGNED NOT NULL COMMENT '분석 ID',
    category VARCHAR(100) NOT NULL COMMENT '분석 카테고리',
    item VARCHAR(255) COMMENT '세부 항목',
    score DECIMAL(5,2) COMMENT '점수',
    content TEXT COMMENT '분석 내용',
    reasoning TEXT COMMENT '점수 산출 근거',
    weight DECIMAL(3,2) DEFAULT 1.00 COMMENT '가중치',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (analysis_id) REFERENCES analyses(id) ON DELETE CASCADE,
    INDEX idx_analysis_category (analysis_id, category),
    INDEX idx_score (score)
) COMMENT='AI 분석 상세 내역';
```

### 3.4 제안서 관리 테이블

#### proposal_templates (제안서 템플릿)
```sql
CREATE TABLE proposal_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT '템플릿명',
    description TEXT COMMENT '템플릿 설명',
    category VARCHAR(100) COMMENT '템플릿 분류',
    content_structure JSON COMMENT '내용 구조 정의',
    template_file VARCHAR(500) COMMENT '템플릿 파일 경로',
    version VARCHAR(20) DEFAULT '1.0' COMMENT '버전',
    is_active BOOLEAN DEFAULT TRUE COMMENT '활성 상태',
    usage_count INT DEFAULT 0 COMMENT '사용 횟수',
    created_by BIGINT UNSIGNED COMMENT '생성자 ID',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_active (is_active),
    INDEX idx_usage_count (usage_count)
) COMMENT='제안서 템플릿';
```

#### proposals (제안서)
```sql
CREATE TABLE proposals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    analysis_id BIGINT UNSIGNED NOT NULL COMMENT '분석 ID',
    user_id BIGINT UNSIGNED NOT NULL COMMENT '생성 사용자 ID',
    template_id BIGINT UNSIGNED COMMENT '사용 템플릿 ID',
    title VARCHAR(500) NOT NULL COMMENT '제안서 제목',
    content LONGTEXT COMMENT '제안서 내용',
    pdf_file VARCHAR(500) COMMENT 'PDF 파일 경로',
    word_file VARCHAR(500) COMMENT 'Word 파일 경로',
    status ENUM('draft', 'generating', 'completed', 'failed') DEFAULT 'draft' COMMENT '상태',
    generation_time INT COMMENT '생성 시간 (초)',
    file_size BIGINT COMMENT '파일 크기',
    download_count INT DEFAULT 0 COMMENT '다운로드 횟수',
    metadata JSON COMMENT '추가 정보',
    generated_at TIMESTAMP COMMENT '생성 완료 시각',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (analysis_id) REFERENCES analyses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES proposal_templates(id) ON DELETE SET NULL,
    INDEX idx_analysis_id (analysis_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_generated_at (generated_at)
) COMMENT='생성된 제안서';
```

### 3.5 시스템 관리 테이블

#### system_settings (시스템 설정)
```sql
CREATE TABLE system_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL COMMENT '설정 분류',
    key_name VARCHAR(255) NOT NULL COMMENT '설정 키',
    value TEXT COMMENT '설정 값',
    data_type ENUM('string', 'integer', 'float', 'boolean', 'json') DEFAULT 'string' COMMENT '데이터 타입',
    description TEXT COMMENT '설정 설명',
    is_public BOOLEAN DEFAULT FALSE COMMENT '공개 설정 여부',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_category_key (category, key_name),
    INDEX idx_category (category)
) COMMENT='시스템 설정';
```

#### activity_logs (활동 로그)
```sql
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED COMMENT '사용자 ID',
    activity VARCHAR(255) NOT NULL COMMENT '활동 유형',
    description TEXT COMMENT '활동 설명',
    subject_type VARCHAR(255) COMMENT '대상 모델',
    subject_id BIGINT UNSIGNED COMMENT '대상 ID',
    properties JSON COMMENT '추가 속성',
    ip_address VARCHAR(45) COMMENT 'IP 주소',
    user_agent TEXT COMMENT 'User Agent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_activity (user_id, activity),
    INDEX idx_subject (subject_type, subject_id),
    INDEX idx_created_at (created_at)
) COMMENT='사용자 활동 로그';
```

## 4. 초기 데이터

### 4.1 기본 역할 (roles)
```sql
INSERT INTO roles (name, display_name, description) VALUES
('super_admin', '최고관리자', '시스템 전체 관리 권한'),
('admin', '관리자', '운영 관리 권한'),
('user', '일반사용자', '기본 사용 권한');
```

### 4.2 기본 권한 (permissions)
```sql
INSERT INTO permissions (name, display_name, description) VALUES
('manage_users', '사용자 관리', '사용자 계정 생성/수정/삭제'),
('manage_settings', '설정 관리', '시스템 설정 관리'),
('view_analytics', '통계 조회', '분석 통계 조회'),
('manage_templates', '템플릿 관리', '제안서 템플릿 관리'),
('analyze_tenders', '공고 분석', '용역공고 AI 분석'),
('generate_proposals', '제안서 생성', '제안서 자동 생성'),
('manage_tenders', '공고 관리', '용역공고 데이터 관리');
```

### 4.3 기본 분류 (tender_categories)
```sql
INSERT INTO tender_categories (name, code, description) VALUES
('정보시스템', 'IT', 'IT 관련 용역'),
('건설공사', 'CONST', '건설 관련 용역'),
('용역', 'SERVICE', '일반 용역'),
('물품', 'GOODS', '물품 구매'),
('기타', 'ETC', '기타 분류');
```

## 5. 인덱싱 및 성능 최적화

### 5.1 복합 인덱스
```sql
-- 용역공고 검색용 복합 인덱스
CREATE INDEX idx_tender_search ON tenders (status, category_id, end_date);

-- 분석 결과 조회용 복합 인덱스  
CREATE INDEX idx_analysis_search ON analyses (user_id, status, total_score DESC);

-- 제안서 관리용 복합 인덱스
CREATE INDEX idx_proposal_management ON proposals (user_id, status, created_at DESC);
```

### 5.2 파티셔닝 (선택사항)
대용량 데이터 처리가 필요한 경우:
```sql
-- 날짜 기반 파티셔닝 (tenders 테이블)
ALTER TABLE tenders PARTITION BY RANGE (YEAR(created_at)) (
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

## 6. 마이그레이션 전략

### 6.1 Laravel 마이그레이션 순서
1. `2024_01_01_000000_create_users_table.php`
2. `2024_01_02_000000_create_roles_and_permissions_tables.php`
3. `2024_01_03_000000_create_tender_categories_table.php`
4. `2024_01_04_000000_create_tenders_table.php`
5. `2024_01_05_000000_create_analyses_tables.php`
6. `2024_01_06_000000_create_proposals_tables.php`
7. `2024_01_07_000000_create_system_tables.php`

### 6.2 데이터 시드 순서
1. `RolePermissionSeeder`: 기본 역할 및 권한 생성
2. `TenderCategorySeeder`: 공고 분류 생성
3. `SystemSettingSeeder`: 기본 시스템 설정
4. `CompanyProfileSeeder`: 기본 회사 프로필

---

**작성일**: 2024-08-28  
**작성자**: AI 시스템 분석  
**검토자**: [검토 필요]  
**승인자**: [승인 필요]  
**버전**: 1.0  
**총 테이블**: 15개