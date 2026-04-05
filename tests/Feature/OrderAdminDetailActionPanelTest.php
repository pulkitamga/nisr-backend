<?php

namespace Tests\Feature;

use Tests\TestCase;

class OrderAdminDetailActionPanelTest extends TestCase
{
    public function test_order_detail_uses_explicit_action_buttons_and_disables_direct_change_autosaves(): void
    {
        $detailView = file_get_contents(resource_path('views/admin-views/order/order-details.blade.php'));
        $orderJs = file_get_contents(public_path('assets/back-end/js/admin/order.js'));
        $orderController = file_get_contents(app_path('Http/Controllers/Admin/Order/OrderController.php'));

        $this->assertStringContainsString('order-summary-hero', $detailView);
        $this->assertStringContainsString('order-summary-section', $detailView);
        $this->assertStringContainsString("translate('order_summary')", $detailView);
        $this->assertStringContainsString("translate('Check_Branch_inventory')", $detailView);
        $this->assertStringContainsString('data-order-action="primary-next-status"', $detailView);
        $this->assertStringContainsString('data-order-action="apply-status"', $detailView);
        $this->assertStringContainsString('data-order-action="apply-branch"', $detailView);
        $this->assertStringContainsString('data-order-action="assign-delivery-man"', $detailView);
        $this->assertStringContainsString('data-order-action="apply-expected-date"', $detailView);
        $this->assertStringContainsString('payment-status-action', $detailView);
        $this->assertStringContainsString("translate('branch_is_required!')", $detailView);
        $this->assertStringContainsString("translate('bosta')", $detailView);

        $this->assertStringNotContainsString('$("#order_status").on(\'change\'', $orderJs);
        $this->assertStringNotContainsString('$("#order_delivered_from_branch").on(\'change\'', $orderJs);
        $this->assertStringNotContainsString('$("#addDeliveryMan").on(\'change\'', $orderJs);
        $this->assertStringNotContainsString('$("#expected_delivery_date").on(\'change\'', $orderJs);
        $this->assertStringNotContainsString('Branch is required!', $orderJs);
        $this->assertStringNotContainsString('Something went wrong', $orderJs);
        $this->assertStringContainsString("translate('branch_is_required!')", $orderController);
        $this->assertStringContainsString("translate('order_already_delivered_cannot_change_branch')", $orderController);
        $this->assertStringContainsString("translate('product_out_of_stock_for_selected_branch')", $orderController);
        $this->assertStringContainsString("translate('bosta_shipment_already_created')", $orderController);
        $this->assertStringContainsString("translate('bosta_tracking_number_missing')", $orderController);
        $this->assertStringContainsString("translate('bosta_error')", $orderController);
        $this->assertStringNotContainsString('Order already delivered. Cannot change branch.', $orderController);
        $this->assertStringNotContainsString('Product out of stock for selected branch', $orderController);
        $this->assertStringNotContainsString('Bosta shipment already created', $orderController);
        $this->assertStringNotContainsString('tracking number missing', $orderController);
    }
}
