<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('Sync Payments')
                ->label('Sync Payments from Stripe')
                ->color('primary')
                ->action(function () {
                    $user = auth()->user();
                    $stripeCustomerId = $user->stripe_id ?? null;
                    if (!$stripeCustomerId) {
                        return;
                    }

                    $stripe = new \Stripe\StripeClient(config('cashier.secret'));

                    $invoices = $stripe->invoices->all(['customer' => $stripeCustomerId, 'limit' => 100]);

                    foreach ($invoices->data as $invoice) {
                        $paidAt = $invoice->status === 'paid' ? \Carbon\Carbon::createFromTimestamp($invoice->created) : null;

                        \App\Models\Payment::updateOrCreate(
                            ['stripe_invoice_id' => $invoice->id],
                            [
                                'user_id' => $user->id,
                                'amount' => $invoice->amount_paid,
                                'currency' => $invoice->currency,
                                'status' => $invoice->status,
                                'invoice_url' => $invoice->hosted_invoice_url,
                                'paid_at' => $paidAt,
                            ]
                        );
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Sync Payments')
                ->modalSubheading('Fetch latest payment history from Stripe and update your records.')
                ->successNotificationTitle('Payments synced successfully'),
        ];
    }
}
