<?php

namespace Tests\Unit;

use App\Http\Controllers\Customer\PaymentController;
use App\Models\PaymentRequest;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ServiceInvoicePaymentValidationTest extends TestCase
{
    public function test_it_rejects_unpaid_payment_request(): void
    {
        $controller = new PaymentController();
        $paymentRequest = $this->makePaymentRequest(
            isPaid: 0,
            attribute: 'service_invoice',
            attributeId: 15
        );

        $result = $this->invokePrivate(
            $controller,
            'isValidServicePaymentSuccess',
            [$paymentRequest, 15]
        );

        $this->assertFalse($result);
    }

    public function test_it_rejects_non_service_invoice_payment_request(): void
    {
        $controller = new PaymentController();
        $paymentRequest = $this->makePaymentRequest(
            isPaid: 1,
            attribute: 'order',
            attributeId: 15
        );

        $result = $this->invokePrivate(
            $controller,
            'isValidServicePaymentSuccess',
            [$paymentRequest, 15]
        );

        $this->assertFalse($result);
    }

    public function test_it_accepts_matching_paid_service_invoice_request_by_attribute_id(): void
    {
        $controller = new PaymentController();
        $paymentRequest = $this->makePaymentRequest(
            isPaid: 1,
            attribute: 'service_invoice',
            attributeId: 22
        );

        $result = $this->invokePrivate(
            $controller,
            'isValidServicePaymentSuccess',
            [$paymentRequest, 22]
        );

        $this->assertTrue($result);
    }

    public function test_it_rejects_mismatched_service_invoice_id(): void
    {
        $controller = new PaymentController();
        $paymentRequest = $this->makePaymentRequest(
            isPaid: 1,
            attribute: 'service_invoice',
            attributeId: 22
        );

        $result = $this->invokePrivate(
            $controller,
            'isValidServicePaymentSuccess',
            [$paymentRequest, 23]
        );

        $this->assertFalse($result);
    }

    public function test_it_resolves_invoice_id_from_additional_data_when_attribute_id_is_missing(): void
    {
        $controller = new PaymentController();
        $paymentRequest = $this->makePaymentRequest(
            isPaid: 1,
            attribute: 'service_invoice',
            attributeId: null,
            additionalData: ['service_invoice_id' => 41]
        );

        $resolved = $this->invokePrivate(
            $controller,
            'resolveServiceInvoiceIdFromPaymentRequest',
            [$paymentRequest]
        );
        $isValid = $this->invokePrivate(
            $controller,
            'isValidServicePaymentSuccess',
            [$paymentRequest, 41]
        );

        $this->assertSame(41, $resolved);
        $this->assertTrue($isValid);
    }

    private function makePaymentRequest(
        int $isPaid,
        string $attribute,
        ?int $attributeId,
        ?array $additionalData = null
    ): PaymentRequest {
        $request = new PaymentRequest();
        $request->is_paid = $isPaid;
        $request->attribute = $attribute;
        $request->attribute_id = $attributeId;
        $request->additional_data = $additionalData ? json_encode($additionalData) : null;

        return $request;
    }

    private function invokePrivate(object $instance, string $method, array $arguments): mixed
    {
        $reflection = new ReflectionMethod($instance, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($instance, $arguments);
    }
}

