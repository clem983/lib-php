<?php

namespace Stancer\Tests\functional;

use Stancer;

/**
 * @namespace \Tests\functional
 *
 * @internal
 */
class Refund extends TestCase
{
    use Stancer\Tests\Provider\Currencies;

    /**
     * @dataProvider cardCurrencyDataProvider
     *
     * @param mixed $currency
     */
    public function testRefund($currency)
    {
        $this
            ->given($payment = new Stancer\Payment())
            ->and($payment->setAmount($amount = rand(50, 10000)))
            ->and($payment->setDescription(sprintf('Refund test, %.02f %s', $amount / 100, $currency)))
            ->and($payment->setCurrency($currency))
            ->and($payment->setCard($card = new Stancer\Card()))
            ->and($card->setNumber($this->getValidCardNumber()))
            ->and($card->setExpirationMonth(rand(1, 12)))
            ->and($card->setExpirationYear(date('Y') + rand(1, 5)))
            ->and($card->setCvc((string) rand(100, 999)))
            ->and($payment->setCustomer($customer = new Stancer\Customer()))
            ->and($customer->setName('John Doe'))
            ->and($customer->setEmail('john.doe' . $this->getRandomString(10) . '@example.com'))
            ->and($payment->send())
            ->if($this->newTestedInstance) // Needed to use "isInstanceOfTestedClass" asserter
            ->then
                ->string($payment->getStatus())
                    ->isIdenticalTo(Stancer\Payment\Status::TO_CAPTURE)

                ->object($payment->refund())
                    ->isIdenticalTo($payment)

                ->array($refunds = $payment->getRefunds())
                    ->hasSize(1)

                ->object($refund = $refunds[0])
                    ->isInstanceOfTestedClass

                ->integer($refund->getAmount())
                    ->isEqualTo($amount)

                ->string($refund->getCurrency())
                    ->isEqualTo(strtolower($currency))

                ->object($refund->getPayment())
                    ->isIdenticalTo($payment)

                ->string($refund->getStatus())
                    ->isIdenticalTo(Stancer\Refund\Status::PAYMENT_CANCELED)

                ->string($payment->getStatus())
                    ->isIdenticalTo(Stancer\Payment\Status::CANCELED)
        ;
    }

    /**
     * @dataProvider cardCurrencyDataProvider
     *
     * @param mixed $currency
     */
    public function testRefundMultiple($currency)
    {
        $this
            ->given($total = rand(500, 10000))
            ->and($amount1 = floor($total / 3))
            ->and($amount2 = max(50, floor(($total - $amount1) / rand(2, 10))))
            ->and($amount3 = $total - $amount1 - $amount2)

            ->if($payment = new Stancer\Payment())
            ->and($payment->setAmount($total))
            ->and($payment->setDescription(sprintf('Refund test, %.02f %s', $total / 100, $currency)))
            ->and($payment->setCurrency($currency))
            ->and($payment->setCard($card = new Stancer\Card()))
            ->and($card->setNumber('4000000000000077'))
            ->and($card->setExpirationMonth(rand(1, 12)))
            ->and($card->setExpirationYear(date('Y') + rand(1, 5)))
            ->and($card->setCvc((string) rand(100, 999)))
            ->and($payment->setCustomer($customer = new Stancer\Customer()))
            ->and($customer->setName('John Doe'))
            ->and($customer->setEmail('john.doe' . $this->getRandomString(10) . '@example.com'))
            ->and($payment->send())

            ->if($this->newTestedInstance) // Needed to use "isInstanceOfTestedClass" asserter
            ->then
                ->assert('Partial refund')
                    ->object($payment->refund($amount1))
                        ->isIdenticalTo($payment)

                    ->array($refunds = $payment->getRefunds())
                        ->hasSize(1)

                    ->object($refund1 = $refunds[0])
                        ->isInstanceOfTestedClass

                    ->integer($refund1->getAmount())
                        ->isEqualTo($amount1)

                    ->string($refund1->getCurrency())
                        ->isEqualTo(strtolower($currency))

                    ->object($refund1->getPayment())
                        ->isIdenticalTo($payment)

                    ->string($refund1->getStatus())
                        ->isIdenticalTo(Stancer\Refund\Status::TO_REFUND)

                ->assert('Second refund')
                    ->object($payment->refund($amount2))
                        ->isIdenticalTo($payment)

                    ->array($refunds = $payment->getRefunds())
                        ->hasSize(2)

                    ->object($refunds[0])
                        ->isIdenticalTo($refund1)

                    ->object($refund2 = $refunds[1])
                        ->isInstanceOfTestedClass
                        ->isNotEqualTo($refund1)

                    ->integer($refund2->getAmount())
                        ->isEqualTo($amount2)

                    ->string($refund2->getCurrency())
                        ->isEqualTo(strtolower($currency))

                    ->object($refund2->getPayment())
                        ->isIdenticalTo($payment)

                    ->string($refund2->getStatus())
                        ->isIdenticalTo(Stancer\Refund\Status::TO_REFUND)

                ->assert('Without amount, we are going to full refund')
                    ->object($payment->refund())
                        ->isIdenticalTo($payment)

                    ->array($refunds = $payment->getRefunds())
                        ->hasSize(3)

                    ->object($refunds[0])
                        ->isIdenticalTo($refund1)

                    ->object($refunds[1])
                        ->isIdenticalTo($refund2)

                    ->object($refund3 = $refunds[2])
                        ->isInstanceOfTestedClass
                        ->isNotEqualTo($refund1)
                        ->isNotEqualTo($refund2)

                    ->integer($refund3->getAmount())
                        ->isEqualTo($amount3)

                    ->string($refund3->getCurrency())
                        ->isEqualTo(strtolower($currency))

                    ->object($refund3->getPayment())
                        ->isIdenticalTo($payment)

                    ->string($refund3->getStatus())
                        ->isIdenticalTo(Stancer\Refund\Status::TO_REFUND)
        ;
    }
}
