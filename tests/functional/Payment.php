<?php

namespace Stancer\Tests\functional;

use Stancer;
use Stancer\Payment as testedClass;

/**
 * @namespace \Tests\functional
 *
 * @internal
 */
class Payment extends TestCase
{
    use Stancer\Tests\Provider\Currencies;
    use Stancer\Tests\Provider\Network;

    protected ?string $order = null;
    protected array $paymentList = [];

    public function beforeTestMethod($testMethod)
    {
        if ($testMethod === 'testList' && !$this->order) {
            $this->order = uniqid();
        }

        return parent::beforeTestMethod($testMethod);
    }

    public function testBadCredential()
    {
        $this
            ->given(Stancer\Config::init(['stest_' . bin2hex(random_bytes(12))])->setHost(getenv('API_HOST')))
            ->and($this->newTestedInstance(uniqid()))
            ->then
                ->exception(function () {
                    $this->testedInstance->getCard();
                })
                    ->isInstanceOf(Stancer\Exceptions\NotAuthorizedException::class)
        ;
    }

    public function testGetData()
    {
        $this
            ->assert('Unknown payment result a 404 exception')
                ->if($this->newTestedInstance($id = 'paym_' . bin2hex(random_bytes(12))))
                ->then
                    ->exception(function () {
                        $this->testedInstance->getAmount();
                    })
                        ->isInstanceOf(Stancer\Exceptions\NotFoundException::class)
                        ->message
                            ->isIdenticalTo('No such payment ' . $id)

            ->assert('Get test payment')
                ->if($this->newTestedInstance('paym_FQgpGVJpyGPVJVIuQtO3zy6i'))
                ->then
                    ->integer($this->testedInstance->getAmount())
                        ->isIdenticalTo(7810)

                    ->string($this->testedInstance->getCurrency())
                        ->isIdenticalTo('usd')

                    ->string($this->testedInstance->getDescription())
                        ->isIdenticalTo('Automatic test, 78.10 USD')

                    ->string($this->testedInstance->getMethod())
                        ->isIdenticalTo('card')

                    ->object($card = $this->testedInstance->getCard())
                        ->isInstanceOf(Stancer\Card::class)

                    ->string($card->getId())
                        ->isIdenticalTo('card_nsA0eap90E6HRod6j54pnVWg')

                    ->object($customer = $this->testedInstance->getCustomer())
                        ->isInstanceOf(Stancer\Customer::class)

                    ->string($customer->getId())
                        ->isIdenticalTo('cust_6FbQaYtxjADzerqdO5gs79as')
        ;
    }

    /**
     * @dataProvider cardCurrencyDataProvider
     *
     * @param mixed $currency
     */
    public function testList($currency)
    {
        $this
            ->assert('Regular listing')
                ->given($this->newTestedInstance)
                ->and($this->testedInstance->setAmount($amount = rand(50, 10000)))
                ->and($this->testedInstance->setDescription(sprintf('Automatic test for list, %.02f %s', $amount / 100, $currency)))
                ->and($this->testedInstance->setCurrency($currency))
                ->and($this->testedInstance->setCard($card = new Stancer\Card()))
                ->and($this->testedInstance->setOrderId($this->order))
                ->and($card->setNumber($this->getValidCardNumber()))
                ->and($card->setExpirationMonth(rand(1, 12)))
                ->and($card->setExpirationYear(date('Y') + rand(1, 5)))
                ->and($card->setCvc((string) rand(100, 999)))
                ->and($this->testedInstance->setCustomer($customer = new Stancer\Customer()))
                ->and($customer->setName('John Doe'))
                ->and($customer->setEmail('john.doe' . $this->getRandomString(10) . '@example.com'))
                ->and($this->testedInstance->send())
                ->and(array_push($this->paymentList, $this->testedInstance))
                ->then
                    ->generator($gen = testedClass::list(['order_id' => $this->order]))
        ;

        $methods = [
            'getId',
            'getAmount',
            'getDescription',
            'getCurrency',
            'getOrderId',
        ];
        $cust = [
            'getId',
            'getEmail',
            'getMobile',
            'getName',
        ];

        foreach ($gen as $idx => $object) {
            $this
                ->object($object)
                    ->isInstanceOfTestedClass
                ->string($object->getCard()->getId())
                    ->isEqualTo($this->paymentList[$idx]->getCard()->getId())
            ;

            foreach ($methods as $method) {
                $this
                    ->variable($object->{$method}())
                        ->isIdenticalTo($this->paymentList[$idx]->{$method}())
                ;
            }

            foreach ($cust as $method) {
                $this
                    ->variable($object->getCustomer()->{$method}())
                        ->isIdenticalTo($this->paymentList[$idx]->getCustomer()->{$method}())
                ;
            }
        }

        $this
            ->assert('Empty list')
                ->generator(testedClass::list(['order_id' => bin2hex(random_bytes(12))]))
                    ->yields
                        ->variable
                            ->isNull
        ;
    }

    /**
     * @dataProvider cardCurrencyDataProvider
     *
     * @param mixed $currency
     */
    public function testPay($currency)
    {
        $this
            ->given($this->newTestedInstance)
            ->then
                ->object($this->testedInstance->setAmount($amount = rand(50, 10000)))
                    ->isTestedInstance

                ->object($this->testedInstance->setDescription(sprintf('Automatic test, %.02f %s', $amount / 100, $currency)))
                    ->isTestedInstance

                ->object($this->testedInstance->setCurrency($currency))
                    ->isTestedInstance

                ->object($this->testedInstance->setCard($card = new Stancer\Card()))
                    ->isTestedInstance

                ->object($card->setNumber($this->getValidCardNumber()))
                    ->isInstanceOf(Stancer\Card::class)

                ->object($card->setExpirationMonth(rand(1, 12)))
                    ->isInstanceOf(Stancer\Card::class)

                ->object($card->setExpirationYear(date('Y') + rand(1, 5)))
                    ->isInstanceOf(Stancer\Card::class)

                ->object($card->setCvc((string) rand(100, 999)))
                    ->isInstanceOf(Stancer\Card::class)

                ->object($this->testedInstance->setCustomer($customer = new Stancer\Customer()))
                    ->isTestedInstance

                ->object($customer->setName('John Doe'))
                    ->isInstanceOf(Stancer\Customer::class)

                ->object($customer->setEmail('john.doe' . $this->getRandomString(10) . '@example.com'))
                    ->isInstanceOf(Stancer\Customer::class)

                ->object($this->testedInstance->send())
                    ->isTestedInstance

                ->string($this->testedInstance->getId())
        ;
    }

    /**
     * @dataProvider cardCurrencyDataProvider
     *
     * @param mixed $currency
     */
    public function testSend($currency)
    {
        $this
            ->assert('With authentication')
                ->given($amount = rand(50, 99999))
                ->and($description = vsprintf('Automatic auth test, %.02f %s', [
                    $amount / 100,
                    $currency,
                ]))

                ->if($card = new Stancer\Card())
                ->and($card->setNumber($this->getValidCardNumber()))
                ->and($card->setExpirationMonth(rand(1, 12)))
                ->and($card->setExpirationYear(date('Y') + rand(1, 5)))
                ->and($card->setCvc((string) rand(100, 999)))

                ->if($customer = new Stancer\Customer())
                ->and($customer->setName('John Doe'))
                ->and($customer->setEmail('john.doe' . $this->getRandomString(10) . '@example.com'))

                ->if($url = 'https://www.example.org/?' . uniqid())

                ->if($this->newTestedInstance)
                ->and($this->testedInstance->setAmount($amount))
                ->and($this->testedInstance->setAuth($url))
                ->and($this->testedInstance->setCurrency($currency))
                ->and($this->testedInstance->setCard($card))
                ->and($this->testedInstance->setCustomer($customer))
                ->and($this->testedInstance->setDescription($description))

                // You may not need to do that, we will use REMOTE_ADDR and REMOTE_PORT environment variable
                //  as IP and port (they are populated by Apache or nginx)
                ->if($ip = $this->ipDataProvider(true))
                ->and($port = rand(1, 65535))
                ->and($this->testedInstance->setDevice(new Stancer\Device(['ip' => $ip, 'port' => $port])))

                ->then
                    ->object($this->testedInstance->send())
                        ->isTestedInstance

                    ->string($this->testedInstance->getId())
                        ->startWith('paym_')
                        ->hasLength(29)

                    ->dateTime($this->testedInstance->getCreationDate())
                        ->hasDay(date('d'))

                    ->string($this->testedInstance->getMethod())
                        ->isIdenticalTo('card')

                    ->variable($this->testedInstance->getStatus())
                        ->isNull

                    ->string($card->getId())
                        ->startWith('card_')
                        ->hasLength(29)

                    ->dateTime($card->getCreationDate())
                        ->hasDay(date('d'))

                    ->string($customer->getId())
                        ->startWith('cust_')
                        ->hasLength(29)

                    ->dateTime($customer->getCreationDate())
                        ->hasDay(date('d'))

                    ->object($auth = $this->testedInstance->getAuth())
                        ->isInstanceOf(Stancer\Auth::class)

                    ->string($auth->getReturnUrl())
                        ->isIdenticalTo($url)

                    ->string($auth->getRedirectUrl())
                        ->startWith('https://3ds.')

                    ->string($auth->getStatus())
                        ->isIdenticalTo(Stancer\Auth\Status::AVAILABLE)

            ->assert('For payment page')
                ->given($amount = rand(50, 99999))
                ->and($description = vsprintf('Non authenticated payment page test, %.02f %s', [
                    $amount / 100,
                    $currency,
                ]))

                ->if($customer = new Stancer\Customer())
                ->and($customer->setName('John Doe'))
                ->and($customer->setEmail('john.doe' . $this->getRandomString(10) . '@example.com'))

                ->if($this->newTestedInstance)
                ->and($this->testedInstance->setAmount($amount))
                ->and($this->testedInstance->setCurrency($currency))
                ->and($this->testedInstance->setCustomer($customer))
                ->and($this->testedInstance->setDescription($description))

                ->then
                    ->object($this->testedInstance->send())
                        ->isTestedInstance

                    ->string($this->testedInstance->getId())
                        ->startWith('paym_')
                        ->hasLength(29)

                    ->dateTime($this->testedInstance->getCreationDate())
                        ->hasDay(date('d'))

                    ->variable($this->testedInstance->getMethod())
                        ->isNull

                    ->string($customer->getId())
                        ->startWith('cust_')
                        ->hasLength(29)

                    ->dateTime($customer->getCreationDate())
                        ->hasDay(date('d'))

            ->assert('For payment page with authentication')
                ->given($amount = rand(50, 99999))
                ->and($description = vsprintf('Authenticated payment page test, %.02f %s', [
                    $amount / 100,
                    $currency,
                ]))

                ->if($customer = new Stancer\Customer())
                ->and($customer->setName('John Doe'))
                ->and($customer->setEmail('john.doe' . $this->getRandomString(10) . '@example.com'))

                ->if($this->newTestedInstance)
                ->and($this->testedInstance->setAmount($amount))
                ->and($this->testedInstance->setAuth(true))
                ->and($this->testedInstance->setCurrency($currency))
                ->and($this->testedInstance->setCustomer($customer))
                ->and($this->testedInstance->setDescription($description))

                ->then
                    ->object($this->testedInstance->send())
                        ->isTestedInstance

                    ->string($this->testedInstance->getId())
                        ->startWith('paym_')
                        ->hasLength(29)

                    ->dateTime($this->testedInstance->getCreationDate())
                        ->hasDay(date('d'))

                    ->variable($this->testedInstance->getMethod())
                        ->isNull

                    ->variable($this->testedInstance->getStatus())
                        ->isNull

                    ->string($customer->getId())
                        ->startWith('cust_')
                        ->hasLength(29)

                    ->dateTime($customer->getCreationDate())
                        ->hasDay(date('d'))

                    ->object($auth = $this->testedInstance->getAuth())
                        ->isInstanceOf(Stancer\Auth::class)

                    ->variable($auth->getReturnUrl())
                        ->isNull

                    ->variable($auth->getRedirectUrl())
                        ->isNull

                    ->string($auth->getStatus())
                        ->isIdenticalTo(Stancer\Auth\Status::REQUESTED)

            ->assert('Patch card and status')
                ->given($this->newTestedInstance)
                ->and($amount = rand(50, 99999))
                ->and($description = sprintf('Automatic test, PATCH card, %.02f %s', $amount / 100, $currency))

                ->if($customer = new Stancer\Customer())
                ->and($customer->setName('John Doe'))
                ->and($customer->setEmail('john.doe' . $this->getRandomString(10) . '@example.com'))

                ->if($this->testedInstance->setAmount($amount))
                ->and($this->testedInstance->setCurrency($currency))
                ->and($this->testedInstance->setDescription($description))
                ->and($this->testedInstance->setCustomer($customer))

                ->if($card = new Stancer\Card())
                ->and($card->setNumber($this->getValidCardNumber()))
                ->and($card->setExpirationMonth(rand(1, 12)))
                ->and($card->setExpirationYear(rand(1, 15) + date('Y')))
                ->and($card->setCvc((string) rand(100, 999)))

                ->then
                    ->object($this->testedInstance->send())
                        ->isTestedInstance

                    ->string($this->testedInstance->getId())
                        ->startWith('paym_')
                        ->hasLength(29)

                    ->variable($this->testedInstance->getMethod())
                        ->isNull

                    ->variable($this->testedInstance->getCard())
                        ->isNull

                    ->variable($this->testedInstance->getSepa())
                        ->isNull

                    ->variable($this->testedInstance->getStatus())
                        ->isNull

                    ->object($this->testedInstance->setCard($card))
                        ->isTestedInstance

                    ->object($this->testedInstance->send())
                        ->isTestedInstance

                    ->string($this->testedInstance->getMethod())
                        ->isEqualTo('card')

                    ->object($this->testedInstance->getCard())
                        ->isIdenticalTo($card)

                    ->variable($this->testedInstance->getSepa())
                        ->isNull

                    ->variable($this->testedInstance->getStatus())
                        ->isNull

                    ->string($card->getId())
                        ->startWith('card_')
                        ->hasLength(29)

                    ->object($this->testedInstance->setStatus(Stancer\Payment\Status::AUTHORIZE)->send())
                        ->isTestedInstance

                    ->string($this->testedInstance->getStatus())
                        ->isIdenticalTo(Stancer\Payment\Status::AUTHORIZED)

                    ->object($this->testedInstance->setStatus(Stancer\Payment\Status::CAPTURE)->send())
                        ->isTestedInstance

                    ->string($this->testedInstance->getStatus())
                        ->isIdenticalTo(Stancer\Payment\Status::TO_CAPTURE)

            ->assert('With unique ID')
                ->given($this->newTestedInstance)
                ->and($amount = rand(50, 99999))
                ->and($description = sprintf('Automatic test, with unique ID, %.02f %s', $amount / 100, $currency))
                ->and($uniqueID = $this->getRandomString(10, 20))

                ->if($name = 'Pickle Rick')
                ->and($email = 'pickle.rick' . $this->getRandomString(10) . '@example.com')
                ->and($mobile = $this->getRandomNumber())

                ->if($card = new Stancer\Card())
                ->and($card->setNumber($this->getValidCardNumber()))
                ->and($card->setExpirationMonth(rand(1, 12)))
                ->and($card->setExpirationYear(rand(1, 15) + date('Y')))
                ->and($card->setCvc((string) rand(100, 999)))

                ->if($customer = new Stancer\Customer())
                ->and($customer->setName($name))
                ->and($customer->setEmail($email))
                ->and($customer->setMobile($mobile))

                ->if($this->testedInstance->setAmount($amount))
                ->and($this->testedInstance->setCard($card))
                ->and($this->testedInstance->setCurrency($currency))
                ->and($this->testedInstance->setDescription($description))
                ->and($this->testedInstance->setCustomer($customer))
                ->and($this->testedInstance->setUniqueId($uniqueID))

                ->then
                    ->object($this->testedInstance->send())
                        ->isTestedInstance

                    ->string($id = $this->testedInstance->getId())
                        ->startWith('paym_')
                        ->hasLength(29)

                    ->string($this->testedInstance->getMethod())
                        ->isIdenticalTo('card')

                    ->string($this->testedInstance->getUniqueId())
                        ->isIdenticalTo($uniqueID)

                    ->object($this->testedInstance->getCard())
                        ->isIdenticalTo($card)

                    ->string($card->getId())
                        ->startWith('card_')
                        ->hasLength(29)

                    ->object($this->testedInstance->getCustomer())
                        ->isIdenticalTo($customer)

                    ->string($customerID = $customer->getId())
                        ->startWith('cust_')
                        ->hasLength(29)

                ->if($this->newTestedInstance)
                ->and($this->testedInstance->setAmount(rand(50, 99999)))
                ->and($this->testedInstance->setCard($card))
                ->and($this->testedInstance->setCurrency($currency))
                ->and($this->testedInstance->setDescription('Will fail'))
                ->and($this->testedInstance->setUniqueId($uniqueID))
                ->then
                    ->exception(function () {
                        $this->testedInstance->send();
                    })
                        ->isInstanceOf(Stancer\Exceptions\ConflictException::class)
                        ->message
                            ->isIdenticalTo('Payment already exists, duplicate unique_id (' . $id . ')')

            ->assert('Allow duplicate customer')
                ->given($this->newTestedInstance)
                ->and($amount = rand(50, 99999))
                ->and($description = sprintf('Automatic test, duplicate customer, %.02f %s', $amount / 100, $currency))

                ->if($card = new Stancer\Card())
                ->and($card->setNumber($this->getValidCardNumber()))
                ->and($card->setExpirationMonth(rand(1, 12)))
                ->and($card->setExpirationYear(rand(1, 15) + date('Y')))
                ->and($card->setCvc((string) rand(100, 999)))

                ->if($customer = new Stancer\Customer())
                ->and($customer->setName($name)) // From previous test
                ->and($customer->setEmail($email)) // From previous test
                ->and($customer->setMobile($mobile)) // From previous test

                ->if($this->testedInstance->setAmount($amount))
                ->and($this->testedInstance->setCard($card))
                ->and($this->testedInstance->setCurrency($currency))
                ->and($this->testedInstance->setDescription($description))
                ->and($this->testedInstance->setCustomer($customer))

                ->then
                    ->object($this->testedInstance->send())
                        ->isTestedInstance

                    ->string($id = $this->testedInstance->getId())
                        ->startWith('paym_')
                        ->hasLength(29)

                    ->string($this->testedInstance->getMethod())
                        ->isIdenticalTo('card')

                    ->object($this->testedInstance->getCard())
                        ->isIdenticalTo($card)

                    ->string($card->getId())
                        ->startWith('card_')
                        ->hasLength(29)

                    ->object($this->testedInstance->getCustomer())
                        ->isIdenticalTo($customer)

                    ->string($customer->getId())
                        ->startWith('cust_')
                        ->hasLength(29)
                        ->isIdenticalTo($customerID) // From previous test
        ;
    }

    /**
     * @dataProvider cardCurrencyDataProvider
     *
     * @param mixed $currency
     */
    public function testSendWithCard($currency)
    {
        $this
            ->given($amount = rand(50, 99999))
            ->and($description = vsprintf('Automatic test, with card, %.02f %s', [
                $amount / 100,
                $currency,
            ]))

            ->if($card = new Stancer\Card())
            ->and($card->setNumber($this->getValidCardNumber()))
            ->and($card->setExpirationMonth(rand(1, 12)))
            ->and($card->setExpirationYear(date('Y') + rand(1, 5)))
            ->and($card->setCvc((string) rand(100, 999)))

            ->if($customer = new Stancer\Customer())
            ->and($customer->setName('John Doe'))
            ->and($customer->setEmail('john.doe' . $this->getRandomString(10) . '@example.com'))

            ->if($this->newTestedInstance)
            ->and($this->testedInstance->setAmount($amount))
            ->and($this->testedInstance->setCurrency($currency))
            ->and($this->testedInstance->setCard($card))
            ->and($this->testedInstance->setCustomer($customer))
            ->and($this->testedInstance->setDescription($description))

            ->then
                ->object($this->testedInstance->send())
                    ->isTestedInstance

                ->string($this->testedInstance->getId())
                    ->startWith('paym_')
                    ->hasLength(29)

                ->dateTime($this->testedInstance->getCreationDate())
                    ->hasDay(date('d'))

                ->string($this->testedInstance->getMethod())
                    ->isIdenticalTo('card')

                ->string($card->getId())
                    ->startWith('card_')
                    ->hasLength(29)

                ->dateTime($card->getCreationDate())
                    ->hasDay(date('d'))

                ->string($customer->getId())
                    ->startWith('cust_')
                    ->hasLength(29)

                ->dateTime($customer->getCreationDate())
                    ->hasDay(date('d'))

            ->then
                ->object($card->delete())
                ->object($customer->delete())
        ;
    }

    /**
     * @dataProvider sepaCurrencyDataProvider
     *
     * @param mixed $currency
     */
    public function testSendWithSepa($currency)
    {
        $this
            ->assert('With a sepa account')
                ->given($amount = rand(50, 99999))
                ->and($description = vsprintf('Automatic test, with SEPA, %.02f %s', [
                    $amount / 100,
                    $currency,
                ]))

                ->if($sepa = new Stancer\Sepa())
                ->and($sepa->setDateMandate(rand(946681200, 1893452400)))
                ->and($sepa->setIban($this->getValidIban()))
                ->and($sepa->setMandate($this->getRandomString(34)))
                ->and($sepa->setName($this->fake()->name()))

                ->if($customer = new Stancer\Customer())
                ->and($customer->setName('John Doe'))
                ->and($customer->setEmail('john.doe+' . $this->getRandomString(10) . '@example.com'))

                ->if($this->newTestedInstance)
                ->and($this->testedInstance->setAmount($amount))
                ->and($this->testedInstance->setCurrency($currency))
                ->and($this->testedInstance->setCustomer($customer))
                ->and($this->testedInstance->setDescription($description))
                ->and($this->testedInstance->setSepa($sepa))

                ->then
                    ->object($this->testedInstance->send())
                        ->isTestedInstance

                    ->string($this->testedInstance->getId())
                        ->startWith('paym_')
                        ->hasLength(29)

                    ->dateTime($this->testedInstance->getCreationDate())
                        ->hasDay(date('d'))

                    ->string($this->testedInstance->getMethod())
                        ->isIdenticalTo('sepa')

                    ->string($sepa->getId())
                        ->startWith('sepa_')
                        ->hasLength(29)

                    ->dateTime($sepa->getCreationDate())
                        ->hasDay(date('d'))

                    ->string($sepa->getBic())
                        ->startWith('TEST')

                    ->string($customer->getId())
                        ->startWith('cust_')
                        ->hasLength(29)

                    ->dateTime($customer->getCreationDate())
                        ->hasDay(date('d'))

                ->then
                    ->object($customer->delete())
                    ->object($sepa->delete())
        ;
    }
}
