<?php

namespace App\Services\AppStore\Helpers;


enum AppStoreProductType: string
{
    case APP_IOS = '1';
    case APP_BUNDLE_IOS = '1-B';
    case APP_BUNDLE_MAC = 'F1-B';

    case PAID_CUSTOM_IOS = '1E';
    case PAID_CUSTOM_IPADOS = '1EP';
    case PAID_CUSTOM_UNIVERSAL = '1EU';

    case APP_UNIVERSAL = '1F';
    case APP_IPAD = '1T';

    case REDOWNLOAD = '3';
    case REDOWNLOAD_UNIVERSAL = '3F';

    case UPDATE = '7';
    case UPDATE_UNIVERSAL = '7F';
    case UPDATE_IPAD = '7T';

    case APP_MAC = 'F1';
    case UPDATE_MAC = 'F7';

    case IAP_MAC = 'FI1';
    case IAP = 'IA1';
    case IAP_MAC_LEGACY = 'IA1-M';

    case IAP_RESTORED = 'IA3';

    case SUB_NON_RENEWING = 'IA9';
    case SUB_NON_RENEWING_MAC = 'IA9-M';

    case SUB_AUTO_RENEWING = 'IAY';
    case SUB_AUTO_RENEWING_MAC = 'IAY-M';

    public function description(): string
    {
        return match ($this) {
            self::APP_IOS =>
            'Free or paid app (iOS, iPadOS, visionOS, watchOS)',

            self::APP_BUNDLE_IOS =>
            'iOS, iPadOS, visionOS app bundle',

            self::APP_BUNDLE_MAC =>
            'Mac app bundle',

            self::PAID_CUSTOM_IOS =>
            'Paid custom iOS app',

            self::PAID_CUSTOM_IPADOS =>
            'Paid custom iPadOS app',

            self::PAID_CUSTOM_UNIVERSAL =>
            'Paid custom universal app',

            self::APP_UNIVERSAL =>
            'Universal app, excluding tvOS',

            self::APP_IPAD =>
            'iPad apps',

            self::REDOWNLOAD =>
            'Re-download: app update (iOS, tvOS, visionOS, watchOS)',

            self::REDOWNLOAD_UNIVERSAL =>
            'Re-download: universal app, excluding tvOS',

            self::UPDATE =>
            'Update: app update (iOS, tvOS, visionOS, watchOS)',

            self::UPDATE_UNIVERSAL =>
            'Update: universal app, excluding tvOS',

            self::UPDATE_IPAD =>
            'Update: app update (iPadOS, visionOS)',

            self::APP_MAC =>
            'Free or paid Mac app',

            self::UPDATE_MAC =>
            'Update: app update (Mac)',

            self::IAP_MAC =>
            'In-App Purchase (Mac)',

            self::IAP =>
            'In-App Purchase (iOS, iPadOS, visionOS)',

            self::IAP_MAC_LEGACY =>
            'In-App Purchase (Mac)',

            self::IAP_RESTORED =>
            'Restored non-consumable In-App Purchase',

            self::SUB_NON_RENEWING =>
            'Non-renewing subscription (iOS, iPadOS, visionOS)',

            self::SUB_NON_RENEWING_MAC =>
            'Non-renewing subscription (Mac)',

            self::SUB_AUTO_RENEWING =>
            'Auto-renewable subscription (iOS, iPadOS, visionOS)',

            self::SUB_AUTO_RENEWING_MAC =>
            'Auto-renewable subscription (Mac)',
        };
    }

    // Optional but very handy for reporting & grouping
    public function category(): string
    {
        return match ($this) {
            self::APP_IOS,
            self::APP_BUNDLE_IOS,
            self::APP_BUNDLE_MAC,
            self::PAID_CUSTOM_IOS,
            self::PAID_CUSTOM_IPADOS,
            self::PAID_CUSTOM_UNIVERSAL,
            self::APP_UNIVERSAL,
            self::APP_IPAD,
            self::APP_MAC
            => 'App',

            self::REDOWNLOAD,
            self::REDOWNLOAD_UNIVERSAL,
            self::UPDATE,
            self::UPDATE_UNIVERSAL,
            self::UPDATE_IPAD,
            self::UPDATE_MAC
            => 'Update',

            self::IAP,
            self::IAP_MAC,
            self::IAP_MAC_LEGACY,
            self::IAP_RESTORED
            => 'In-App Purchase',

            self::SUB_NON_RENEWING,
            self::SUB_NON_RENEWING_MAC,
            self::SUB_AUTO_RENEWING,
            self::SUB_AUTO_RENEWING_MAC
            => 'Subscription',
        };
    }
}
