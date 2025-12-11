# API 문서

## 개요
Nara 프로젝트의 API 명세서 및 인터페이스 문서를 관리합니다.

## 문서 목록
*아직 생성된 문서 없음*

## API 문서 템플릿

```markdown
# [API명] 명세서

## 기본 정보
- Base URL: 
- Version: 
- Authentication: 

## 엔드포인트

### GET /resource
**설명**: 리소스 조회

**Parameters**:
- `id` (required): 리소스 ID
- `limit` (optional): 조회 제한 수

**Response**:
```json
{
  "status": "success",
  "data": {}
}
```

**Error Codes**:
- 400: Bad Request
- 404: Not Found
- 500: Internal Server Error

## 데이터 스키마
### Resource Object
### Request/Response Models

## 사용 예제
### cURL
### JavaScript
### Python
```

---
*상위 문서: [../README.md](../README.md)*