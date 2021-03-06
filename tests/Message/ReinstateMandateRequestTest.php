<?php

namespace Omnipay\GoCardlessV2Tests\Message;

use GoCardlessPro\Client;
use GoCardlessPro\Resources\Mandate;
use GoCardlessPro\Services\MandatesService;
use Omnipay\GoCardlessV2\Message\MandateResponse;
use Omnipay\GoCardlessV2\Message\ReinstateMandateRequest;
use Omnipay\Tests\TestCase;

class ReinstateMandateRequestTest extends TestCase
{
    /**
     * @var ReinstateMandateRequest
     */
    private $request;

    /**
     * @var array fully populated sample mandate data to drive test
     */
    private $sampleData = [
        'mandateReference' => 'CU123123123',
    ];

    public function setUp()
    {
        $gateway = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'mandates',
                ]
            )
            ->getMock();
        $mandateService = $this->getMockBuilder(MandatesService::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'reinstate',
                ]
            )
            ->getMock();

        $gateway->expects($this->any())
            ->method('mandates')
            ->will($this->returnValue($mandateService));
        $mandateService->expects($this->any())
            ->method('reinstate')
            ->will($this->returnCallback([$this, 'mandateGet']));

        $this->request = new ReinstateMandateRequest($this->getHttpClient(), $this->getHttpRequest(), $gateway);
        $this->request->initialize($this->sampleData);
    }

    public function testGetDataReturnsCorrectArray()
    {
        // this should be blank
        $this->assertSame([], $this->request->getData());
    }

    public function testRequestDataIsStoredCorrectly()
    {
        $this->assertSame($this->sampleData['mandateReference'], $this->request->getMandateReference());
    }

    public function testSendDataReturnsCorrectType()
    {
        // this will trigger additional validation as the sendData method calls mandate create that validates the parameters handed to it match
        // the original data handed in to the initialise (in $this->sampleMandate).
        $result = $this->request->send();
        $this->assertInstanceOf(MandateResponse::class, $result);
    }

    // Assert the mandate get method is being handed the mandateReference
    public function mandateGet($data)
    {
        $this->assertEquals($this->sampleData['mandateReference'], $data);

        return $this->getMockBuilder(Mandate::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
