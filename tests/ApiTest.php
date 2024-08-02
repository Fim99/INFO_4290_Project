<?php

use PHPUnit\Framework\TestCase;
class ApiTest extends TestCase
{
    public function testFetchDataFromAPI()
    {
        // Mock URL
        $url = 'https://api.nal.usda.gov/fdc/v1/food/123456?';

        // Mock response
        $expectedResponse = '{"fdcId": 123456, "description": "Mock Food"}';

        // Mock fetch function
        $fetchFunction = function($url) use ($expectedResponse) {
            return $expectedResponse;
        };

        // Use the mocked fetch function
        $actualResponse = fetchDataFromAPI($url, $fetchFunction);

        $this->assertNotNull($actualResponse);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $actualResponse);
    }

    public function testFetchDataFromAPIFailure()
    {
        // Mock URL
        $url = 'https://api.nal.usda.gov/fdc/v1/food/invalid?';

        // Mock fetch function to return false
        $fetchFunction = function($url) {
            return false;
        };

        // Use the mocked fetch function
        $actualResponse = fetchDataFromAPI($url, $fetchFunction);

        $this->assertNull($actualResponse);
    }
}
?>
