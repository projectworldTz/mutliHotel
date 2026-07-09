<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class DpoPayGateway implements PaymentGatewayInterface
{
    public function getName(): string { return 'Card Payment (DPO Pay)'; }
    public function getKey(): string  { return 'dpo_card'; }

    /**
     * DPO is redirect-based, not a phone/PIN prompt: the customer is sent to
     * DPO's hosted payment page to enter card details, then returns to us.
     */
    public function initiate(Booking $booking, array $data): array
    {
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'method'     => $this->getKey(),
            'status'     => 'pending',
            'amount'     => $booking->grand_total,
            'currency'   => $booking->currency ?? config('app.currency'),
            'metadata'   => [
                'reference'    => $booking->booking_number,
                'provider'     => 'dpo_card',
                'initiated_at' => now()->toISOString(),
            ],
        ]);

        // TODO: Call the real DPO createToken API once company_token/service_type are configured.
        // DPO's API is XML-based (Content-Type: application/xml), e.g.:
        //
        // $xml = new \SimpleXMLElement('<API3G/>');
        // $xml->addChild('CompanyToken', config('payments.dpo_card.company_token'));
        // $xml->addChild('Request', 'createToken');
        // $trans = $xml->addChild('Transaction');
        // $trans->addChild('PaymentAmount', number_format($booking->grand_total, 2, '.', ''));
        // $trans->addChild('PaymentCurrency', $booking->currency ?? config('app.currency'));
        // $trans->addChild('CompanyRef', $booking->booking_number);
        // $trans->addChild('RedirectURL', route('dpo.return', ['payment' => $payment->id]));
        // $trans->addChild('BackURL', route('booking.checkout'));
        // $services = $trans->addChild('Services');
        // $service  = $services->addChild('Service');
        // $service->addChild('ServiceType', config('payments.dpo_card.service_type'));
        // $service->addChild('ServiceDescription', 'Hotel Booking Payment');
        // $service->addChild('ServiceDate', now()->format('Y/m/d H:i'));
        //
        // $response = Http::withBody($xml->asXML(), 'application/xml')
        //     ->post(config('payments.dpo_card.base_url') . '/API/v6/');
        // $token = (string) simplexml_load_string($response->body())->TransactionToken;
        // $redirectUrl = config('payments.dpo_card.base_url') . '/payv2.php?ID=' . $token;

        Log::info('DPO Pay payment initiated', [
            'booking' => $booking->booking_number,
            'amount'  => $booking->grand_total,
        ]);

        // No live DPO credentials configured yet — send the customer to a local
        // stand-in for DPO's hosted checkout so the redirect round-trip can be
        // exercised end-to-end. Swap for the real $redirectUrl above once
        // DPO_COMPANY_TOKEN / DPO_SERVICE_TYPE are set.
        $redirectUrl = config('payments.dpo_card.company_token')
            ? config('payments.dpo_card.base_url') . '/payv2.php?ID=' . urlencode((string) $payment->id)
            : route('dpo.simulate', $payment);

        return [
            'success'      => true,
            'payment_id'   => $payment->id,
            'redirect_url' => $redirectUrl,
            'message'      => 'Redirecting to secure card payment page…',
        ];
    }

    /**
     * DPO does not push a webhook — the merchant calls verifyToken
     * server-side after the customer's browser returns from the hosted page.
     */
    public function verify(Payment $payment, array $gatewayData): array
    {
        // TODO: Call the real DPO verifyToken API and check <Result>000</Result>
        // before trusting the return trip, e.g.:
        //
        // $xml = new \SimpleXMLElement('<API3G/>');
        // $xml->addChild('CompanyToken', config('payments.dpo_card.company_token'));
        // $xml->addChild('Request', 'verifyToken');
        // $xml->addChild('TransactionToken', $gatewayData['transaction_token']);
        // $response = Http::withBody($xml->asXML(), 'application/xml')
        //     ->post(config('payments.dpo_card.base_url') . '/API/v6/');
        // $result = simplexml_load_string($response->body());
        // if ((string) $result->Result !== '000') { return ['success' => false, 'message' => (string) $result->ResultExplanation]; }

        $payment->update([
            'status'         => 'paid',
            'transaction_id' => $gatewayData['transaction_id'] ?? ('DPO-' . now()->format('YmdHis')),
        ]);

        Transaction::create([
            'booking_id'             => $payment->booking_id,
            'payment_id'             => $payment->id,
            'type'                   => 'charge',
            'amount'                 => $payment->amount,
            'currency'               => $payment->currency,
            'status'                 => 'success',
            'gateway'                => $this->getKey(),
            'gateway_transaction_id' => $gatewayData['transaction_id'] ?? null,
            'gateway_response'       => json_encode($gatewayData),
        ]);

        return ['success' => true, 'message' => 'Card payment confirmed.'];
    }

    public function refund(Payment $payment, float $amount): array
    {
        // TODO: Call DPO's refundToken API when credentials are available
        Transaction::create([
            'booking_id' => $payment->booking_id,
            'payment_id' => $payment->id,
            'type'       => 'refund',
            'amount'     => $amount,
            'currency'   => $payment->currency,
            'status'     => 'success',
            'gateway'    => $this->getKey(),
        ]);

        if ($amount >= (float) $payment->amount) {
            $payment->update(['status' => 'refunded']);
        }

        return ['success' => true, 'refund_id' => null, 'message' => "Card refund of TZS " . number_format($amount, 0) . " initiated."];
    }
}
