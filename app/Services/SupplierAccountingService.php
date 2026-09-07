<?php

namespace App\Services;

use App\Models\Account;
use App\Models\FinancialAccount;
use App\Models\GoodsReceipt;
use App\Models\Journal;
use App\Models\SupplierBill;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SupplierAccountingService
{
    public function billReceipt(GoodsReceipt $receipt): SupplierBill
    {
        if ($receipt->bill) return $receipt->bill;
        $order = $receipt->purchaseOrder;
        $amount = (float) $receipt->items->sum(fn ($row) => (float) $row->received_packages * (float) $row->orderItem->cost_per_package);
        if ($amount <= 0) throw ValidationException::withMessages(['costs' => 'Gharama ya bidhaa zilizopokelewa lazima iwe zaidi ya sifuri.']);

        $journal = $this->journal($order->business_id, $receipt->receipt_date, 'supplier_bill', $receipt->id, "Goods receipt {$receipt->receipt_number}");
        $journal->entries()->createMany([
            ['account_id'=>$this->account('1200')->id,'debit'=>$amount,'credit'=>0],
            ['account_id'=>$this->account('2000')->id,'debit'=>0,'credit'=>$amount],
        ]);
        $bill = SupplierBill::create([
            'bill_number'=>$this->number('BILL'),'supplier_id'=>$order->supplier_id,'business_id'=>$order->business_id,
            'goods_receipt_id'=>$receipt->id,'original_amount'=>$amount,'balance'=>$amount,
            'bill_date'=>$receipt->receipt_date,'status'=>'unpaid','journal_id'=>$journal->id,'created_by'=>auth()->id(),
        ]);
        if ($order->payment_type === 'cash') {
            if (!$order->payment_financial_account_id) throw ValidationException::withMessages(['financial_account_id'=>'Chagua financial account ya kulipia cash purchase.']);
            $this->pay($bill, ['financial_account_id'=>$order->payment_financial_account_id,'amount'=>$amount,'payment_date'=>$receipt->receipt_date,'payment_method'=>'cash','notes'=>'Automatic payment for '.$receipt->receipt_number]);
        }
        return $bill;
    }

    public function pay(SupplierBill $bill, array $data): SupplierPayment
    {
        return DB::transaction(function () use ($bill, $data) {
            $locked = SupplierBill::lockForUpdate()->findOrFail($bill->id);
            $amount = (float) $data['amount'];
            if ($amount <= 0 || $amount > (float) $locked->balance) throw ValidationException::withMessages(['amount'=>'Malipo hayawezi kuzidi salio la supplier bill.']);
            $financial = FinancialAccount::lockForUpdate()->findOrFail($data['financial_account_id']);
            if (!$financial->is_active || $financial->current_balance < $amount) throw ValidationException::withMessages(['financial_account_id'=>'Financial account haina salio la kutosha au haiko active.']);
            $payment = SupplierPayment::create($data + ['supplier_bill_id'=>$locked->id,'payment_number'=>$this->number('SPAY'),'paid_by'=>auth()->id()]);
            $journal = $this->journal($locked->business_id, $data['payment_date'], 'supplier_payment', $payment->id, "Supplier payment {$payment->payment_number}");
            $journal->entries()->createMany([
                ['account_id'=>$this->account('2000')->id,'debit'=>$amount,'credit'=>0],
                ['account_id'=>$this->account('1000')->id,'financial_account_id'=>$financial->id,'debit'=>0,'credit'=>$amount],
            ]);
            $payment->update(['journal_id'=>$journal->id]);
            $balance = (float) $locked->balance - $amount;
            $locked->update(['balance'=>$balance,'status'=>$balance <= 0 ? 'paid' : 'partial']);
            return $payment;
        });
    }

    private function journal(int $businessId, $date, string $type, int $sourceId, string $description): Journal
    {
        return Journal::create(['business_id'=>$businessId,'journal_number'=>$this->number('JRN'),'journal_date'=>$date,'source_type'=>$type,'source_id'=>$sourceId,'description'=>$description,'status'=>'posted','created_by'=>auth()->id(),'posted_by'=>auth()->id(),'posted_at'=>now()]);
    }
    private function account(string $code): Account { return Account::where('code',$code)->where('is_active',true)->first() ?? throw new RuntimeException("Accounting account {$code} haijapatikana."); }
    private function number(string $prefix): string { return $prefix.'-'.now()->format('Ymd').'-'.strtoupper(Str::random(8)); }
}
