# 나라장터 API 연동 문서

## API 기본 정보

### 서비스 개요
- **서비스명**: 나라장터 입찰공고 정보 서비스
- **서비스 제공기관**: 조달청
- **API 유형**: REST API (XML 응답)
- **인증 방식**: 공공데이터포털 서비스키

### 기본 URL 정보
```
Base URL: http://apis.data.go.kr/1230000/ad/BidPublicInfoService
```

## 주요 API 메서드

### 1. 공사(건설공사) 조회 API
```
Method: getBidPblancListInfoCnstwkPPSSrch
URL: http://apis.data.go.kr/1230000/ad/BidPublicInfoService/getBidPblancListInfoCnstwkPPSSrch
설명: 나라장터 검색조건에 의한 입찰공고 공사 조회
```

### 2. 용역 조회 API ⭐ (현재 사용 중)
```
Method: getBidPblancListInfoServcPPSSrch
URL: http://apis.data.go.kr/1230000/ad/BidPublicInfoService/getBidPblancListInfoServcPPSSrch
설명: 나라장터 검색조건에 의한 입찰공고 용역 조회
```

## Operation #12: 나라장터검색조건에 의한 입찰공고용역조회

### 요청(Request) 명세

#### HTTP Method
```
GET
```

#### 필수 파라미터
| 파라미터명 | 타입 | 필수여부 | 설명 | 예시값 |
|-----------|------|----------|------|--------|
| serviceKey | String | 필수 | 공공데이터포털 발급 서비스키 | 인증키 |
| pageNo | Integer | 선택 | 페이지번호 (기본값: 1) | 1 |
| numOfRows | Integer | 선택 | 한 페이지 결과수 (기본값: 10, 최대: 1000) | 10 |

#### 선택 파라미터
| 파라미터명 | 타입 | 필수여부 | 설명 | 예시값 | 비고 |
|-----------|------|----------|------|--------|------|
| inqryDiv | String | 선택 | 조회구분 | 11 | 11:용역 |
| inqryBgnDt | String | 선택 | 조회시작일자 | 20231201 | YYYYMMDD |
| inqryEndDt | String | 선택 | 조회종료일자 | 20231231 | YYYYMMDD |
| area | String | 선택 | 지역코드 | 11 | 11:서울 |
| type | String | 선택 | 응답형식 | xml | xml 또는 json |

### 응답(Response) 명세

#### 성공 응답 구조 (XML)
```xml
<response>
    <cmmMsgHeader>
        <returnReasonCode>00</returnReasonCode>
        <returnAuthMsg>정상</returnAuthMsg>
        <errMsg></errMsg>
    </cmmMsgHeader>
    <body>
        <totalCount>100</totalCount>
        <items>
            <item>
                <bidNtceNo>공고번호</bidNtceNo>
                <bidNtceNm>공고명</bidNtceNm>
                <ntceDt>공고일자</ntceDt>
                <ntceKndNm>공고종류명</ntceKndNm>
                <demndOrgNm>수요기관명</demndOrgNm>
                <cntrctCnclsMthdNm>계약체결방법명</cntrctCnclsMthdNm>
                <rcptBgnDt>접수시작일시</rcptBgnDt>
                <rcptEndDt>접수종료일시</rcptEndDt>
                <opengDt>개찰일시</opengDt>
                <presmptPrce>추정가격</presmptPrce>
                <!-- 추가 필드들... -->
            </item>
        </items>
    </body>
</response>
```

#### 오류 응답 구조 (XML)
```xml
<response>
    <cmmMsgHeader>
        <returnReasonCode>07</returnReasonCode>
        <returnAuthMsg>입력범위값 초과 에러</returnAuthMsg>
        <errMsg>요청변수 점검필요</errMsg>
    </cmmMsgHeader>
</response>
```

#### 용역 조회 특화 응답 구조 (inqryDiv=11 사용시)
```xml
<response>
    <header>
        <resultCode>00</resultCode>
        <resultMsg>정상</resultMsg>
    </header>
    <body>
        <totalCount>50</totalCount>
        <items>
            <!-- 용역 공고 항목들 -->
        </items>
    </body>
</response>
```

### 응답 코드 정의

#### 성공 코드
| 코드 | 메시지 | 설명 |
|------|--------|------|
| 00 | 정상 | 정상적인 응답 |

#### 오류 코드
| 코드 | 메시지 | 설명 | 해결방안 |
|------|--------|------|---------|
| 01 | 서비스키 오류 | 인증키가 유효하지 않음 | 서비스키 확인 |
| 02 | 요청 메시지 파싱 오류 | 요청 파라미터 오류 | 파라미터 형식 확인 |
| 03 | HTTP 오류 | HTTP 통신 오류 | 네트워크 상태 확인 |
| 04 | HTTP 라우팅 오류 | URL 경로 오류 | API URL 확인 |
| 07 | 입력범위값 초과 에러 | 파라미터 값이 허용 범위 초과 | 파라미터 값 조정 |
| 12 | NO_OPENAPI_SERVICE_ERROR | 서비스가 존재하지 않음 | API 경로 확인 |

## 실제 사용 예제

### 기본 용역 공고 조회
```bash
curl "http://apis.data.go.kr/1230000/ad/BidPublicInfoService/getBidPblancListInfoServcPPSSrch?serviceKey=YOUR_SERVICE_KEY&pageNo=1&numOfRows=10"
```

### 날짜 조건 포함 조회
```bash
curl "http://apis.data.go.kr/1230000/ad/BidPublicInfoService/getBidPblancListInfoServcPPSSrch?serviceKey=YOUR_SERVICE_KEY&pageNo=1&numOfRows=10&inqryBgnDt=20231201&inqryEndDt=20231231"
```

### 용역 분류 지정 조회
```bash
curl "http://apis.data.go.kr/1230000/ad/BidPublicInfoService/getBidPblancListInfoServcPPSSrch?serviceKey=YOUR_SERVICE_KEY&pageNo=1&numOfRows=10&inqryDiv=11"
```

## API 연동 시 주의사항

### 1. 파라미터 제한사항
- `numOfRows`: 최대 1000까지 설정 가능
- `inqryBgnDt`, `inqryEndDt`: YYYYMMDD 형식 필수
- 날짜 범위가 너무 크면 "입력범위값 초과 에러" 발생 가능

### 2. 응답 구조 변화
- `inqryDiv=11` 파라미터 사용시 응답 구조가 변경됨
  - 기본: `cmmMsgHeader.returnReasonCode`
  - 용역: `header.resultCode`

### 3. 성능 최적화
- 불필요한 파라미터는 제외하여 "입력범위값 초과" 오류 방지
- 페이지 크기는 적절히 설정 (권장: 10-100)
- 날짜 범위는 1개월 이내로 제한 권장

### 4. 오류 처리
- 오류 코드별 적절한 재시도 로직 구현
- 특히 코드 07 (입력범위값 초과)의 경우 파라미터 조정 필요

## Laravel 서비스 클래스 구현

```php
// app/Services/NaraApiService.php에서 구현됨
public function getBidPblancListInfoServcPPSSrch(array $params = []): array
{
    $defaultParams = [
        'serviceKey' => $this->serviceKey,
        'pageNo' => 1,
        'numOfRows' => 100,
    ];
    
    $queryParams = array_merge($defaultParams, $params);
    
    $response = Http::timeout($this->timeout)
        ->get(self::BASE_URL . '/getBidPblancListInfoServcPPSSrch', $queryParams);
    
    // XML 응답 처리 및 검증 로직...
}
```

## 문제 해결 기록

### 현재 해결된 문제
1. ✅ **URL 경로 수정**: `BidPublicInfoService` 올바른 base URL 적용
2. ✅ **메서드명 수정**: `getBidPblancListInfoServcPPSSrch` (용역 조회) 적용
3. ✅ **응답 구조 개선**: 다중 응답 구조 지원 (`cmmMsgHeader` + `header`)

### 현재 진행 중인 문제
- ⏳ **입력범위값 초과 오류**: 파라미터 최적화로 해결 시도 중
- 성공 시나리오: 오류 코드 04 → 07 변화는 올바른 엔드포인트 접근 증명

### 향후 개선 사항
- [ ] 공식 API 문서 기반 파라미터 범위 정의
- [ ] 오류별 자동 재시도 로직 구현
- [ ] API 응답 캐싱 시스템 도입

---
*최종 수정: 2025-08-29*
*작성자: SuperClaude Framework*