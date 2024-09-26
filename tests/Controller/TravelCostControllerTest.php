<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TravelCostControllerTest extends WebTestCase
{
    public function testCalculateCost()
    {
        $client = static::createClient();

        $client->request('POST', '/calculate-cost', [], [], [], json_encode([
            'base_cost' => 10000,
            'participant' => "01.01.2020",
            'payment_date' => '20.12.2024',
            'travel_date' => '21.09.2025'
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertJson(1900, "бинго");  // Замените ожидаемое значение на актуальное
    }
}