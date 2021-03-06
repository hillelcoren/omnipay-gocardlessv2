<?php

namespace Omnipay\GoCardlessV2Tests\Message;

use GoCardlessPro\Client;
use GoCardlessPro\Resources\Payment;
use GoCardlessPro\Services\PaymentsService;
use Omnipay\GoCardlessV2\Message\PaymentResponse;
use Omnipay\GoCardlessV2\Message\UpdatePaymentRequest;
use Omnipay\Tests\TestCase;

class UpdatePaymentRequestTest extends TestCase
{
    /**
     * @var UpdatePaymentRequest
     */
    private $request;

    /**
     * @var array fully populated sample payment data to drive test
     */
    private $sampleData = [
        'paymentId' => 'CU123123123',
        'paymentMetaData' => [
            'meta1' => 'Lorem Ipsom Dolor Est',
            'meta2' => 'Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts.',
            'meta567890123456789012345678901234567890123456789' => 'Separated they live in Bookmarksgrove right at the coast of the Semantics, a large language ocean. A small river named Duden flows by their place and supplies it with the necessary regelialia.',
        ],
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
                    'update',
                ]
            )
            ->getMock();

        $gateway->expects($this->any())
            ->method('payments')
            ->will($this->returnValue($paymentService));
        $paymentService->expects($this->any())
            ->method('update')
            ->will($this->returnCallback([$this, 'paymentGet']));

        $this->request = new UpdatePaymentRequest($this->getHttpClient(), $this->getHttpRequest(), $gateway);
        $this->request->initialize($this->sampleData);
    }

    public function testGetDataReturnsCorrectArray()
    {
        $data = [
            'paymentData' => ['params' => ['metadata' => $this->sampleData['paymentMetaData']]],
            'paymentId' => $this->sampleData['paymentId'],
        ];
        $this->assertSame($data, $this->request->getData());
    }

    public function testRequestDataIsStoredCorrectly()
    {
        $this->assertSame($this->sampleData['paymentId'], $this->request->getPaymentId());
        $this->assertSame($this->sampleData['paymentMetaData'], $this->request->getPaymentMetaData());
    }

    public function testSendDataReturnsCorrectType()
    {
        // this will trigger additional validation as the sendData method calls payment create that validates the parameters handed to it match
        // the original data handed in to the initialise (in $this->samplePayment).
        $result = $this->request->send();
        $this->assertInstanceOf(PaymentResponse::class, $result);
    }

    // Assert the payment get method is being handed the paymentId
    public function paymentGet($id, $data)
    {
        $this->assertEquals($this->sampleData['paymentId'], $id);
        $this->assertEquals($this->request->getData()['paymentData'], $data);

        return $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
