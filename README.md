# Ncloud Outbound Mailer for WordPress

[![WordPress Plugin](https://img.shields.io/badge/WordPress.org-Plugin-blue.svg)](https://wordpress.org/plugins/ncloud-outbound-mailer/)
[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net)
[![WordPress](https://img.shields.io/badge/WordPress-5.6%2B-blue.svg)](https://wordpress.org)

**[WordPress.org Plugin Page](https://wordpress.org/plugins/ncloud-outbound-mailer/)** | [GitHub](https://github.com/Daworks/wp-plugins-ncloud-outbound-mailer)

[English](#english) | [한국어](#한국어)

---

## English

Send WordPress emails through Ncloud Cloud Outbound Mailer API.

**Developed by [Design Arete](https://daworks.io)** - Professional WordPress Development

### Features

- Easy configuration through WordPress admin
- Support for multiple regions (Korea, Singapore, Japan)
- HTML and plain text email support
- CC and BCC support
- Email logging with last 100 entries
- Test connection and send test email functionality
- Compatible with popular plugins (Contact Form 7, WooCommerce, etc.)

### Requirements

- WordPress 5.6 or higher
- PHP 7.4 or higher
- Ncloud Cloud Outbound Mailer subscription
- Ncloud API Access Key and Secret Key

### Installation

1. Upload the `ncloud-outbound-mailer` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu
3. Go to **Settings > Ncloud Mailer** to configure

### Setup

1. Sign up for [Ncloud Cloud Outbound Mailer](https://www.ncloud.com/product/applicationService/cloudOutboundMailer)
2. Get your API credentials from [Ncloud Console](https://console.ncloud.com/)
3. Register and verify your sending domain (see Domain Setup below)
4. Enter your Access Key, Secret Key, and sender information
5. Enable the mailer and test with the test email feature

### Domain Setup

Before sending emails, you must register and verify your domain in Ncloud Console.

#### Step 1: Register Domain
1. Go to [Ncloud Console](https://console.ncloud.com/) > Cloud Outbound Mailer > Domain Management
2. Click **"+ 도메인 등록"** (Add Domain)
3. Enter your domain name (e.g., example.com)

#### Step 2: Domain Verification Token
Add a TXT record to verify domain ownership:

| DNS Record | Value |
|------------|-------|
| Host | `@` |
| Type | `TXT` |
| Value | (Copy from "인증 토큰" > "보기") |

#### Step 3: SPF Record
SPF authorizes Ncloud to send emails on your behalf:

| DNS Record | Value |
|------------|-------|
| Host | `@` |
| Type | `TXT` |
| Value | `v=spf1 include:_spfblocka.ncloud.com ~all` |

#### Step 4: DKIM Record
DKIM adds a digital signature to your emails:

| DNS Record | Value |
|------------|-------|
| Host | `ncloud._domainkey` |
| Type | `TXT` |
| Value | (Copy DKIM public key from console) |

#### Step 5: DMARC Record (Recommended)
DMARC provides instructions for handling authentication failures:

| DNS Record | Value |
|------------|-------|
| Host | `_dmarc` |
| Type | `TXT` |
| Value | `v=DMARC1; p=none; rua=mailto:dmarc@yourdomain.com` |

> **Note**: DNS propagation may take 24-48 hours. Status will show "인증 완료" when verified.

---

## 한국어

Ncloud Cloud Outbound Mailer API를 통해 WordPress 이메일을 발송하는 플러그인입니다.

**개발: [Design Arete](https://daworks.io)** - 전문 WordPress 개발

### 주요 기능

- WordPress 관리자 페이지에서 간편한 설정
- 다중 리전 지원 (한국, 싱가포르, 일본)
- HTML 및 일반 텍스트 이메일 지원
- CC/BCC 지원
- 최근 100개 이메일 발송 로그
- 연결 테스트 및 테스트 이메일 발송 기능
- 주요 플러그인과 호환 (Contact Form 7, WooCommerce 등)

### 요구 사항

- WordPress 5.6 이상
- PHP 7.4 이상
- Ncloud Cloud Outbound Mailer 서비스 가입
- Ncloud API Access Key 및 Secret Key

### 설치 방법

1. `ncloud-outbound-mailer` 폴더를 `/wp-content/plugins/`에 업로드
2. '플러그인' 메뉴에서 플러그인 활성화
3. **설정 > Ncloud Mailer**에서 설정

### 설정 방법

1. [Ncloud Cloud Outbound Mailer](https://www.ncloud.com/product/applicationService/cloudOutboundMailer) 서비스 가입
2. [Ncloud 콘솔](https://console.ncloud.com/)에서 API 인증키 발급
3. 발신 도메인 등록 및 인증 (아래 도메인 설정 참조)
4. Access Key, Secret Key, 발신자 정보 입력
5. 활성화 후 테스트 이메일로 동작 확인

### 도메인 설정

이메일을 발송하기 전에 Ncloud 콘솔에서 도메인을 등록하고 인증해야 합니다.

#### 1단계: 도메인 등록
1. [Ncloud 콘솔](https://console.ncloud.com/) > Cloud Outbound Mailer > Domain Management 이동
2. **"+ 도메인 등록"** 버튼 클릭
3. 도메인명 입력 (예: example.com)

#### 2단계: 도메인 인증 토큰
도메인 소유권 확인을 위해 TXT 레코드를 추가합니다:

| DNS 설정 | 값 |
|----------|-----|
| 호스트 | `@` |
| 타입 | `TXT` |
| 값 | (인증 토큰 > "보기"에서 복사) |

#### 3단계: SPF 레코드
SPF는 Ncloud가 도메인을 대신하여 이메일을 보낼 수 있도록 권한을 부여합니다:

| DNS 설정 | 값 |
|----------|-----|
| 호스트 | `@` |
| 타입 | `TXT` |
| 값 | `v=spf1 include:_spfblocka.ncloud.com ~all` |

등록 후 콘솔에서 **"사용"** 버튼을 클릭하여 활성화합니다.

#### 4단계: DKIM 레코드
DKIM은 이메일에 디지털 서명을 추가하여 위변조를 방지합니다:

| DNS 설정 | 값 |
|----------|-----|
| 호스트 | `ncloud._domainkey` |
| 타입 | `TXT` |
| 값 | (콘솔에서 DKIM 공개키 복사) |

등록 후 콘솔에서 **"사용"** 버튼을 클릭하여 활성화합니다.

#### 5단계: DMARC 레코드 (권장)
DMARC는 인증 실패 시 이메일 처리 방법을 지정합니다:

| DNS 설정 | 값 |
|----------|-----|
| 호스트 | `_dmarc` |
| 타입 | `TXT` |
| 값 | `v=DMARC1; p=none; rua=mailto:dmarc@yourdomain.com` |

> **참고**: DNS 전파에 최대 24-48시간이 소요될 수 있습니다. 인증이 완료되면 "인증 완료" 상태가 표시됩니다.

#### DNS 레코드 요약

| 타입 | 호스트 | 값 |
|------|--------|-----|
| TXT | @ | (인증 토큰) |
| TXT | @ | `v=spf1 include:_spfblocka.ncloud.com ~all` |
| TXT | ncloud._domainkey | (DKIM 공개키) |
| TXT | _dmarc | `v=DMARC1; p=none; rua=mailto:you@domain.com` |

### 스크린샷

#### 설정 페이지
API 인증 정보와 발신자 정보를 설정합니다.

#### 테스트 기능
- **연결 테스트**: API 인증키가 올바른지 확인
- **테스트 이메일**: 실제 이메일 발송 테스트

#### 발송 로그
최근 발송된 이메일의 성공/실패 여부를 확인할 수 있습니다.

### 자주 묻는 질문

#### API 인증키는 어디서 발급받나요?
[Ncloud 콘솔](https://console.ncloud.com/) > 마이페이지 > 계정 관리 > 인증키 관리에서 발급받을 수 있습니다.

#### 어떤 리전을 선택해야 하나요?
한국에서 사용하는 경우 **Korea (KR)**를 선택하세요. 해외 서버를 사용하는 경우 가까운 리전을 선택하면 됩니다.

#### WooCommerce와 호환되나요?
네, 이 플러그인은 WordPress의 `wp_mail()` 함수를 대체하므로 wp_mail을 사용하는 모든 플러그인과 호환됩니다.

#### API 오류가 발생하면 어떻게 되나요?
기본적으로 Ncloud API 오류 시 이메일이 발송되지 않습니다. 로그에서 오류 내용을 확인할 수 있습니다. 기본 PHP mail로 폴백하려면 다음 필터를 사용하세요:

```php
add_filter( 'ncloud_mailer_fallback_on_error', '__return_true' );
```

### 개발자 문서

#### 필터

**ncloud_mailer_before_send**
발송 전 메일 데이터를 수정합니다.

```php
add_filter( 'ncloud_mailer_before_send', function( $body, $mail_data ) {
    // $body 배열을 수정
    return $body;
}, 10, 2 );
```

**ncloud_mailer_fallback_on_error**
오류 시 기본 wp_mail로 폴백합니다.

```php
add_filter( 'ncloud_mailer_fallback_on_error', '__return_true' );
```

**ncloud_mailer_enable_logging**
이메일 로깅을 비활성화합니다.

```php
add_filter( 'ncloud_mailer_enable_logging', '__return_false' );
```

#### 액션

**ncloud_mailer_init**
플러그인 초기화 완료 후 실행됩니다.

**ncloud_mailer_after_send**
이메일 발송 성공 후 실행됩니다.

**ncloud_mailer_error**
이메일 발송 오류 시 실행됩니다.

### 변경 이력

#### 1.0.0
- 최초 릴리스
- Ncloud API를 통한 기본 이메일 발송
- 관리자 설정 페이지
- 연결 테스트 및 테스트 이메일 기능
- 이메일 로깅

### 라이선스

GPL v2 or later

---

## Author / 개발자

**Design Arete**
- Website: [https://daworks.io](https://daworks.io)
- GitHub: [https://github.com/Daworks](https://github.com/Daworks)

Copyright (c) 2024 Design Arete. All rights reserved.
