<?php

namespace Omnipay\GoCardlessV2Tests\Message;

use GoCardlessPro\Client;
use GoCardlessPro\Resources\Payment;
use GoCardlessPro\Services\PaymentsService;
use Omnipay\GoCardlessV2\Message\FindPaymentRequest;
use Omnipay\GoCardlessV2\Message\PaymentResponse;
use Omnipay\Tests\TestCase;

class FindPaymentRequestTest extends TestCase
{
    /**
     * @var FindPaymentRequest
     */
    private $request;

    /**
     * @var array fully populated sample payment data to drive test
     */
    private $sampleData = [
        'paymentId' => 'CU123123123',
    ];

    public function setUp()
    {
        $gateway = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'payments',
                ]
            )
            ->getMock();
        $paymentService = $this->getMockBuilder(PaymentsService::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'get',
                ]
            )
            ->getMock();

        $gateway->expects($this->any())
            ->method('payments')
            ->will($this->returnValue($paymentService));
        $paymentService->expects($this->any())
            ->method('get')
            ->will($this->returnCallback([$this, 'paymentGet']));

        $this->request = new FindPaymentRequest($this->getHttpClient(), $this->getHttpRequest(), $gateway);
        $this->request->initialize($this->sampleData);
    }

    public function testGetDataReturnsCorrectArray()
    {
        // this should be blank
        $this->assertSame([], $this->request->getData());
    }

    public function testRequestDataIsStoredCorrectly()
    {
        $this->assertSame($this->sampleData['paymentId'], $this->request->getPaymentId());
    }

    public function testSendDataReturnsCorrectType()
    {
        // this will trigger additional validation as the sendData method calls payment create that validates the parameters handed to it match
        // the original data handed in to the initialise (in $this->samplePayment).
        $result = $this->request->send();
        $this->assertInstanceOf(PaymentResponse::class, $result);
    }

    // Assert the payment get method is being handed the paymentId
    public function paymentGet($data)
    {
        $this->assertEquals($this->sampleData['paymentId'], $data);

        return $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
