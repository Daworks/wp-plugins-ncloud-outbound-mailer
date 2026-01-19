# Ncloud Outbound Mailer for WordPress

[English](#english) | [한국어](#한국어)

---

## English

Send WordPress emails through Ncloud Cloud Outbound Mailer API.

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
3. Enter your Access Key, Secret Key, and sender information
4. Enable the mailer and test with the test email feature

---

## 한국어

Ncloud Cloud Outbound Mailer API를 통해 WordPress 이메일을 발송하는 플러그인입니다.

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
3. Access Key, Secret Key, 발신자 정보 입력
4. 활성화 후 테스트 이메일로 동작 확인

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
