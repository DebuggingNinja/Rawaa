<?php

namespace App\Services\Financials;

use App\Enums\FinancialType;
use App\Models\Expense;
use App\Models\Ledger;
use App\Models\ShipperLedger;
use App\Models\SupplierLedger;
use Illuminate\Http\Request;

class FinancialsService
{
  protected $request;
  public function setRequest($request)
  {
    $this->request = $request;
    return $this;
  }
  public function CreateFinacial(): bool
  {

    switch ($this->request['type'] ?? null) {
      case FinancialType::PAID_FOR_CLIENT:
        return $this->paymentForClient();
        break;
      case FinancialType::PAYMENT_FROM_CLIENT:
        return $this->paymentFromClient();
        break;
      case FinancialType::PAYMENT_TO_SUPPLIER:
        return $this->paymentToSupplier();
        break;
      case FinancialType::PAYMENT_FOR_SHIPPING:
        return $this->paymentToShipping();
      default:
        return (bool) $this->createExpense();
        break;
    }
  }

  public function paymentToSupplier()
  {
    if ($this->request) {
      $this->createExpense();
      $this->addPaymentToSupplier();
      BalanceCalculator::Supplier($this->request['supplier_id']);
      return true;
    }
    return false;
  }
  public function paymentToShipping()
  {
    if ($this->request) {
      $this->createExpense();
      $this->addPaymentToShipping();
      BalanceCalculator::company($this->request['shipping_company_id']);
      return true;
    }
    return false;
  }
  public function paymentFromClient()
  {
    if ($this->request) {
      $this->createExpense();
      $this->addClientPayment();
      BalanceCalculator::Client($this->request['client_id']);
      return true;
    }
    return false;
  }
  public function paymentForClient()
  {
    if ($this->request) {
      $this->createExpense();
      $this->addToCilentLedger();
      BalanceCalculator::Client($this->request['client_id']);
      return true;
    }
    return false;
  }


  public function addPaymentToSupplier()
  {
    SupplierLedger::create([
      'date'            => now(),
      'description'     => $this->request['description'],
      'debit'           => $this->request['amount'], // الموضوع هنا عكس المديون هو انا فلما السبلاير يدفعلي كريديت انا المفروض بسددله فالديبيت
      'balance'         => 0,
      'action'          => 'debit',
      'supplier_id'     => $this->request['supplier_id']
    ]);
  }
  public function addPaymentToShipping()
  {
    ShipperLedger::create([
      'date'            => now(),
      'description'     => $this->request['description'],
      'debit'           => $this->request['amount'], // الموضوع هنا عكس المديون هو انا فلما السبلاير يدفعلي كريديت انا المفروض بسددله فالديبيت
      'balance'         => 0,
      'action'          => 'debit',
      'shipping_company_id'     => $this->request['shipping_company_id']
    ]);
  }
  public function addClientPayment()
  {
    Ledger::create([
      'date'            => now(),
      'description'     => $this->request['description'],
      'credit'          => $this->request['amount'],
      'balance'         => 0,
      'action'          => 'credit',
      'currency'        => $this->request['currency'],
      'currency_rate'   => $this->request['rate'] ?? null,
      'client_id'       => $this->request['client_id']
    ]);
  }
  public function addToCilentLedger()
  {
    Ledger::create([
      'date'            => now(),
      'description'     => $this->request['description'],
      'debit'           => $this->request['amount'],
      'balance'         => 0,
      'action'          => 'debit',
      'currency'        => $this->request['currency'],
      'currency_rate'   => $this->request['rate'] ?? null,
      'client_id'       => $this->request['client_id']
    ]);
  }

  public function createExpense()
  {
    return Expense::create(
      [
        'description'   => $this->request['description'] ?? null,
        'amount'        => $this->request['amount'] ?? null,
        'rate'          => $this->request['rate'] ?? null,
        'currency'      => $this->request['currency'] ?? null,
        'date'          => $this->request['date'] ?? null,
        'client_id'     => $this->request['client_id'] ?? null,
        'type'          => $this->request['type'] ?? null,
        'supplier_id'   => $this->request['supplier_id'] ?? null,
        'shipping_company_id'   => $this->request['shipping_company_id'] ?? null,
      ]
    );
  }
}
