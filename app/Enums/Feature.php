<?php

namespace App\Enums;

enum Feature: string
{
    // ── Tier 1 – Growth ───────────────────────────────────────────────────────
    case HOUSEKEEPING         = 'housekeeping';
    case ADVANCED_ANALYTICS   = 'advanced_analytics';
    case EMAIL_MARKETING      = 'email_marketing';
    case UPSELLING            = 'upselling';
    case GUEST_SURVEYS        = 'guest_surveys';

    // ── Tier 2 – Operations ───────────────────────────────────────────────────
    case INVENTORY_MANAGEMENT = 'inventory_management';
    case MAINTENANCE_REQUESTS = 'maintenance_requests';
    case GUEST_MESSAGING      = 'guest_messaging';
    case DIGITAL_CHECKIN      = 'digital_checkin';
    case STAFF_SCHEDULING     = 'staff_scheduling';
    case GROUP_BOOKING        = 'group_booking';

    // ── Tier 3 – Revenue ──────────────────────────────────────────────────────
    case DYNAMIC_PRICING      = 'dynamic_pricing';
    case CORPORATE_PORTAL     = 'corporate_portal';
    case MULTI_CURRENCY       = 'multi_currency';
    case CUSTOM_REPORTS       = 'custom_reports';

    // ── Tier 4 – Premium Branding ─────────────────────────────────────────────
    case WHITE_LABEL          = 'white_label';
    case CUSTOM_DOMAIN        = 'custom_domain';
    case PRIORITY_SUPPORT     = 'priority_support';
    case API_ACCESS           = 'api_access';
    case AI_CONCIERGE         = 'ai_concierge';

    public function label(): string
    {
        return match ($this) {
            self::HOUSEKEEPING         => 'Housekeeping Module',
            self::ADVANCED_ANALYTICS   => 'Advanced Analytics',
            self::EMAIL_MARKETING      => 'Email Marketing Tool',
            self::UPSELLING            => 'Upselling Engine',
            self::GUEST_SURVEYS        => 'Guest Satisfaction Surveys',
            self::INVENTORY_MANAGEMENT => 'Inventory & Asset Management',
            self::MAINTENANCE_REQUESTS => 'Maintenance Requests',
            self::GUEST_MESSAGING      => 'In-stay Guest Messaging',
            self::DIGITAL_CHECKIN      => 'Digital Check-in',
            self::STAFF_SCHEDULING     => 'Staff Scheduling',
            self::GROUP_BOOKING        => 'Group Booking Manager',
            self::DYNAMIC_PRICING      => 'Dynamic Pricing Engine',
            self::CORPORATE_PORTAL     => 'Corporate / B2B Portal',
            self::MULTI_CURRENCY       => 'Multi-currency Support',
            self::CUSTOM_REPORTS       => 'Custom Report Builder',
            self::WHITE_LABEL          => 'White-label Branding',
            self::CUSTOM_DOMAIN        => 'Custom Domain',
            self::PRIORITY_SUPPORT     => 'Priority Support',
            self::API_ACCESS           => 'API Access',
            self::AI_CONCIERGE         => 'AI Concierge (Chat)',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::HOUSEKEEPING         => 'Track room cleaning tasks, assign to staff, and manage turnaround after check-out.',
            self::ADVANCED_ANALYTICS   => 'RevPAR, ADR, occupancy trends, guest demographics, and forecasting dashboards.',
            self::EMAIL_MARKETING      => 'Send promotional emails and newsletters to past and upcoming guests.',
            self::UPSELLING            => 'Offer room upgrades, early check-in, and add-ons during the booking flow.',
            self::GUEST_SURVEYS        => 'Auto-send satisfaction surveys after check-out and view aggregated results.',
            self::INVENTORY_MANAGEMENT => 'Track furniture, electronics, appliances, and all hotel assets with condition, location, purchase value, and warranty.',
            self::MAINTENANCE_REQUESTS => 'Guests and staff submit maintenance issues; tracked to resolution.',
            self::GUEST_MESSAGING      => 'In-platform chat between front desk and in-house guests.',
            self::DIGITAL_CHECKIN      => 'Pre-arrival ID upload, arrival time, and preferences form for guests.',
            self::STAFF_SCHEDULING     => 'Shift planning and assignment for hotel staff.',
            self::GROUP_BOOKING        => 'Handle block reservations for conferences, weddings, and events.',
            self::DYNAMIC_PRICING      => 'Auto-adjust room rates based on occupancy, season, and demand.',
            self::CORPORATE_PORTAL     => 'Dedicated booking portal with negotiated corporate rates.',
            self::MULTI_CURRENCY       => 'Display and accept payment in the guest\'s local currency.',
            self::CUSTOM_REPORTS       => 'Build custom reports by date range, booking status, and revenue.',
            self::WHITE_LABEL          => 'Remove platform branding from pages, invoices, and emails.',
            self::CUSTOM_DOMAIN        => 'Serve the hotel booking page from the hotel\'s own domain.',
            self::PRIORITY_SUPPORT     => 'Dedicated account manager with SLA-backed support response times.',
            self::API_ACCESS           => 'REST API key to integrate the platform with external systems.',
            self::AI_CONCIERGE         => 'Embed an AI-powered chat assistant on the hotel\'s public page. Guests can ask about rooms, rates, policies, and local attractions — answered instantly by a Claude-powered concierge trained on the hotel\'s own data.',
        };
    }

    public function tier(): string
    {
        return match ($this) {
            self::HOUSEKEEPING,
            self::ADVANCED_ANALYTICS,
            self::EMAIL_MARKETING,
            self::UPSELLING,
            self::GUEST_SURVEYS         => 'Growth',
            self::INVENTORY_MANAGEMENT,
            self::MAINTENANCE_REQUESTS,
            self::GUEST_MESSAGING,
            self::DIGITAL_CHECKIN,
            self::STAFF_SCHEDULING,
            self::GROUP_BOOKING         => 'Operations',
            self::DYNAMIC_PRICING,
            self::CORPORATE_PORTAL,
            self::MULTI_CURRENCY,
            self::CUSTOM_REPORTS        => 'Revenue',
            self::WHITE_LABEL,
            self::CUSTOM_DOMAIN,
            self::PRIORITY_SUPPORT,
            self::API_ACCESS,
            self::AI_CONCIERGE          => 'Premium',
        };
    }

    public function tierColor(): string
    {
        return match ($this->tier()) {
            'Growth'     => 'emerald',
            'Operations' => 'blue',
            'Revenue'    => 'purple',
            'Premium'    => 'amber',
            default      => 'slate',
        };
    }

    /** Whether this feature has a real working implementation. */
    public function isLive(): bool
    {
        return match ($this) {
            self::HOUSEKEEPING,
            self::ADVANCED_ANALYTICS,
            self::INVENTORY_MANAGEMENT,
            self::CORPORATE_PORTAL,
            self::AI_CONCIERGE,
            self::EMAIL_MARKETING,
            self::UPSELLING,
            self::GUEST_SURVEYS,
            self::MAINTENANCE_REQUESTS,
            self::GUEST_MESSAGING,
            self::DIGITAL_CHECKIN,
            self::STAFF_SCHEDULING,
            self::GROUP_BOOKING => true,
            default             => false,
        };
    }

    /** All features grouped by tier for display. */
    public static function grouped(): array
    {
        $grouped = [];
        foreach (self::cases() as $feature) {
            $grouped[$feature->tier()][] = $feature;
        }
        return $grouped;
    }
}
