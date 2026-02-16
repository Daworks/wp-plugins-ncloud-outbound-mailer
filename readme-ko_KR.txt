=== Daworks Outbound Mailer for Ncloud ===
Contributors: dhlee7
Donate link: https://daworks.io
Tags: email, smtp, ncloud, naver cloud, mail
Requires at least: 5.6
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.3
License: MIT
License URI: https://opensource.org/licenses/MIT

Ncloud Cloud Outbound Mailer API를 통해 워드프레스 이메일을 발송합니다.

== Description ==

Daworks Outbound Mailer for Ncloud는 워드프레스의 기본 PHP mail 함수 대신 Ncloud Cloud Outbound Mailer API를 통해 모든 이메일을 발송하는 플러그인입니다.

[Daworks](https://daworks.io) 개발 - Professional WordPress Development.

**참고:** 이 플러그인은 Daworks가 개발했으며, NAVER Cloud Platform 또는 Ncloud와 제휴, 보증, 공식 연계되어 있지 않습니다.

= 주요 기능 =

* 워드프레스 관리자 페이지에서 간편하게 설정
* 다중 리전 지원 (한국, 싱가포르, 일본)
* HTML 및 일반 텍스트 이메일 지원
* 참조(CC) 및 숨은 참조(BCC) 지원
* 최근 100건의 이메일 로그 기록
* 연결 테스트 및 테스트 이메일 발송 기능
* 주요 플러그인과 호환 (Contact Form 7, WooCommerce 등)

= 요구 사항 =

* WordPress 5.6 이상
* PHP 7.4 이상
* Ncloud Cloud Outbound Mailer 서비스 구독
* Ncloud API Access Key 및 Secret Key

= 초기 설정 =

1. Ncloud Cloud Outbound Mailer 서비스에 가입
2. Ncloud 콘솔에서 API Access Key와 Secret Key 발급
3. 발신 도메인 등록 및 인증 (아래 도메인 설정 참조)
4. 워드프레스 관리자 > 설정 > Ncloud 메일러 이동
5. API 인증 정보 및 발신자 정보 입력
6. 메일러를 활성화하고 테스트 이메일로 확인

= 도메인 설정 =

이메일을 발송하기 전에 Ncloud 콘솔에서 도메인을 등록하고 인증해야 합니다.

**1단계: 도메인 등록**

1. [Ncloud 콘솔](https://console.ncloud.com/) > Cloud Outbound Mailer > 도메인 관리로 이동
2. "+ 도메인 등록" 클릭
3. 도메인명 입력 (예: example.com)

**2단계: 도메인 인증 토큰**

도메인 소유권 확인을 위한 TXT 레코드를 추가합니다:

1. 도메인 관리에서 "인증 토큰" 옆의 "보기" 클릭
2. 인증 토큰 값 복사
3. DNS에 TXT 레코드 추가:
   * Host: @ (또는 도메인)
   * Type: TXT
   * Value: (인증 토큰 값 붙여넣기)
4. "새로 고침"을 클릭하여 인증 확인

**3단계: SPF 레코드**

SPF (Sender Policy Framework)는 Ncloud가 도메인을 대신하여 이메일을 보낼 수 있도록 승인합니다:

1. "SPF 레코드" 옆의 "보기" 클릭
2. SPF 레코드 값 복사
3. DNS에 TXT 레코드 추가:
   * Host: @
   * Type: TXT
   * Value: `v=spf1 include:_spfblocka.ncloud.com ~all`
4. "사용"을 클릭하여 SPF 활성화

**4단계: DKIM 레코드**

DKIM (DomainKeys Identified Mail)은 이메일에 디지털 서명을 추가합니다:

1. "DKIM" 옆의 "보기" 클릭
2. DKIM 레코드 값 복사
3. DNS에 TXT 레코드 추가:
   * Host: (제공된 선택자, 예: `ncloud._domainkey`)
   * Type: TXT
   * Value: (DKIM 공개 키 붙여넣기)
4. "사용"을 클릭하여 DKIM 활성화

**5단계: DMARC 레코드 (권장)**

DMARC는 인증 실패 시 처리 방법을 지정합니다:

1. DNS에 TXT 레코드 추가:
   * Host: `_dmarc`
   * Type: TXT
   * Value: `v=DMARC1; p=none; rua=mailto:dmarc@yourdomain.com`
2. 인증 확인 후 정책을 `p=quarantine` 또는 `p=reject`로 변경 권장

**DNS 레코드 요약**

| 유형 | 호스트 | 값 |
| --- | --- | --- |
| TXT | @ | (인증 토큰) |
| TXT | @ | v=spf1 include:_spfblocka.ncloud.com ~all |
| TXT | ncloud._domainkey | (DKIM 공개 키) |
| TXT | _dmarc | v=DMARC1; p=none; rua=mailto:you@domain.com |

참고: DNS 전파에는 최대 24~48시간이 소요될 수 있습니다. 인증이 완료되면 "인증 완료" 상태로 표시됩니다.

== Installation ==

1. `daworks-outbound-mailer-for-ncloud` 폴더를 `/wp-content/plugins/` 디렉토리에 업로드
2. 워드프레스 '플러그인' 메뉴에서 플러그인 활성화
3. 설정 > Daworks 메일러에서 플러그인 설정

== Frequently Asked Questions ==

= API 인증 정보는 어디서 받을 수 있나요? =

[Ncloud 콘솔](https://console.ncloud.com/)에서 API Access Key와 Secret Key를 발급받을 수 있습니다.

= 어떤 리전을 지원하나요? =

한국 (KR), 싱가포르 (SGN), 일본 (JPN) 리전을 지원합니다.

= WooCommerce와 호환되나요? =

네, 이 플러그인은 워드프레스 기본 wp_mail 함수를 대체하므로 wp_mail을 사용하는 모든 플러그인에서 동작합니다.

= API 오류 발생 시 어떻게 되나요? =

기본 설정에서는 Ncloud API가 실패하면 이메일이 발송되지 않습니다. `ncloud_mailer_fallback_on_error` 필터를 사용하여 기본 PHP mail 함수로 대체 발송을 활성화할 수 있습니다.

== Screenshots ==

1. 설정 페이지 - API 인증 정보 및 발신자 정보 설정
2. 연결 테스트 및 테스트 이메일 발송
3. 최근 발송 이력을 보여주는 이메일 로그

== External services ==

이 플러그인은 이메일 발송을 위해 NAVER Cloud Platform의 Cloud Outbound Mailer API를 외부 서비스로 사용합니다. 이 서비스 없이는 이메일을 발송할 수 없습니다.

= NAVER Cloud Platform - Cloud Outbound Mailer =

**서비스 제공자:** 네이버클라우드 주식회사
**서비스 웹사이트:** [https://www.ncloud.com/product/applicationService/cloudOutboundMailer](https://www.ncloud.com/product/applicationService/cloudOutboundMailer)
**이용약관:** [https://www.ncloud.com/policy/terms/service](https://www.ncloud.com/policy/terms/service)
**개인정보처리방침:** [https://www.ncloud.com/policy/privacy/privacy](https://www.ncloud.com/policy/privacy/privacy)

**이 서비스의 역할:**
이 플러그인은 워드프레스의 모든 이메일(회원 가입, 비밀번호 재설정, 문의 양식 제출, WooCommerce 알림 등 wp_mail()을 통해 발송되는 모든 이메일)을 기본 PHP mail 함수 대신 NAVER Cloud Platform Cloud Outbound Mailer API를 통해 발송합니다.

**이 서비스로 전송되는 데이터:**
워드프레스에서 이메일이 발송될 때마다 다음 데이터가 NAVER Cloud Platform API로 전송됩니다:

* 발신자 이메일 주소 및 이름 (플러그인 설정에서 구성)
* 수신자 이메일 주소 (받는 사람, 참조, 숨은 참조)
* 이메일 제목
* 이메일 본문 (HTML 또는 일반 텍스트)
* 회신 주소 (설정된 경우)

**데이터 전송 시점:**
플러그인이 활성화된 상태에서 워드프레스가 wp_mail() 함수를 통해 이메일을 보낼 때마다 데이터가 전송됩니다. 여기에는 회원 가입 이메일, 비밀번호 재설정 이메일, 댓글 알림, 플러그인/테마 업데이트 알림, WooCommerce 주문 이메일, Contact Form 7 제출 등이 포함됩니다.

**API 엔드포인트 (NAVER Cloud Platform의 API 게이트웨이 도메인인 ntruss.com에서 호스팅):**

* 한국: [https://mail.apigw.ntruss.com/api/v1](https://mail.apigw.ntruss.com/api/v1)
* 싱가포르: [https://mail.apigw.ntruss.com/api/v1-sgn](https://mail.apigw.ntruss.com/api/v1-sgn)
* 일본: [https://mail.apigw.ntruss.com/api/v1-jpn](https://mail.apigw.ntruss.com/api/v1-jpn)

이 플러그인을 사용함으로써 NAVER Cloud Platform의 [이용약관](https://www.ncloud.com/policy/terms/service) 및 [개인정보처리방침](https://www.ncloud.com/policy/privacy/privacy)에 동의하게 됩니다.

== Changelog ==

= 1.0.3 =
* WordPress.org 규정 준수를 위해 플러그인명을 "Daworks Outbound Mailer for Ncloud"로 변경
* External Services 문서 섹션 추가
* 서드파티 서비스 면책 조항 추가
* 새 슬러그에 맞춰 Text Domain 업데이트

= 1.0.2 =
* 라이선스를 MIT로 변경
* LICENSE 파일 추가
* GitHub Wiki 문서 추가

= 1.0.1 =
* 한국어 (ko_KR) 번역 추가
* 다국어 지원을 위한 load_plugin_textdomain 추가
* POT 파일의 번역 문자열 업데이트

= 1.0.0 =
* 최초 릴리스
* Ncloud API를 통한 기본 이메일 발송
* 관리자 설정 페이지
* 연결 테스트 및 테스트 이메일 기능
* 이메일 로그

== Upgrade Notice ==

= 1.0.0 =
최초 릴리스.

== Developer Documentation ==

= 필터 =

**ncloud_mailer_before_send**
발송 전 메일 데이터를 수정합니다.

`add_filter( 'ncloud_mailer_before_send', function( $body, $mail_data ) {
    // 발송 전 $body 배열 수정
    return $body;
}, 10, 2 );`

**ncloud_mailer_fallback_on_error**
오류 발생 시 기본 wp_mail로 대체 발송을 활성화합니다.

`add_filter( 'ncloud_mailer_fallback_on_error', '__return_true' );`

**ncloud_mailer_enable_logging**
이메일 로그를 비활성화합니다.

`add_filter( 'ncloud_mailer_enable_logging', '__return_false' );`

= 액션 =

**ncloud_mailer_init**
플러그인이 완전히 초기화된 후 실행됩니다.

**ncloud_mailer_after_send**
이메일 발송 성공 후 실행됩니다.

**ncloud_mailer_error**
이메일 발송 중 오류 발생 시 실행됩니다.

= 디버깅 =

**이메일 로그**

플러그인은 최근 100건의 이메일 로그를 워드프레스 transient(`ncloud_mailer_logs`)에 저장합니다. 프로그래밍 방식으로 로그를 조회할 수 있습니다:

`$logs = get_transient( 'ncloud_mailer_logs' );
foreach ( $logs as $log ) {
    echo $log['time'] . ' - ' . $log['status'] . ' - ' . $log['subject'];
}`

각 로그 항목에 포함되는 정보:
* `time` - 이메일 발송 시각
* `status` - 'success' 또는 'error'
* `to` - 수신자 이메일 주소
* `subject` - 이메일 제목
* `request_id` - Ncloud 요청 ID (성공 시)
* `code` - 오류 코드 (실패 시)
* `message` - 오류 메시지 (실패 시)

**워드프레스 디버그 로그**

`WP_DEBUG`가 활성화되어 있으면 오류가 `wp-content/debug.log`에도 기록됩니다:

`// wp-config.php에 추가
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );`

로그 형식: `[Ncloud Mailer Error] {code}: {message} (To: {recipients}, Subject: {subject})`

**로그 비활성화**

로그를 완전히 비활성화하려면:

`add_filter( 'ncloud_mailer_enable_logging', '__return_false' );`
