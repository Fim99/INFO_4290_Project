<?php
use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase
{
    // Test getAdditionalParams
    public function testGetAdditionalParams()
    {
        $_GET['dataType'] = 'Branded';
        $_GET['pageSize'] = '10';

        $params = ['dataType', 'pageSize', 'pageNumber'];
        $result = getAdditionalParams($params);

        $expected = [
            'dataType' => 'Branded',
            'pageSize' => '10'
        ];

        $this->assertEquals($expected, $result);
    }

    // Test buildApiUrlSearch
    public function testBuildApiUrlSearch()
    {
        $query = 'apple';
        $additionalParams = [
            'dataType' => 'Branded',
            'pageSize' => '10'
        ];

        $url = buildApiUrlSearch($query, $additionalParams);
        $expected = "https://api.nal.usda.gov/fdc/v1/foods/search?query=apple&dataType=Branded&pageSize=10";

        $this->assertEquals($expected, $url);
    }

    // Test fetchApiDataSearch
    public function testFetchApiDataSearch()
    {
        // Mock the fetchDataFromAPI function
        $url = 'https://api.nal.usda.gov/fdc/v1/foods/search?query=apple';
        $mockResponse = json_encode([
            'foods' => [
                [
                    'fdcId' => 454004,
                    'description' => 'APPLE',
                    'dataType' => 'Branded',
                    'brandOwner' => 'TREECRISP 2 GO',
                    'foodCategory' => 'Pre-Packaged Fruit & Vegetables'
                ]
            ],
            'totalHits' => 26750,
            'currentPage' => 1,
            'totalPages' => 535
        ]);

        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['fetchDataFromAPI'])
            ->getMock();
        $mock->method('fetchDataFromAPI')
            ->willReturn($mockResponse);

        $data = fetchApiDataSearch($url);
        $this->assertIsArray($data); // Check if the result is an array
        $this->assertArrayHasKey('foods', $data); // Check for 'foods' key
        $this->assertNotEmpty($data['foods']); // Check if 'foods' is not empty

        // Check specific fields
        $this->assertEquals(454004, $data['foods'][0]['fdcId']);
        $this->assertEquals('APPLE', $data['foods'][0]['description']);
    }

    // Test getTableHeaders
// Test getTableHeaders
    public function testGetTableHeaders()
    {
        $dataType = 'Branded';
        $result = getTableHeaders($dataType);
        $expected = [
            '<div class="text-center">Add To Meal</div>',
            'Description',
            'FDC ID',
            'Food Category',
            'Brand Owner',
            'Brand',
            'Market Country'
        ];

        $this->assertEquals($expected, $result);
    }


    // Test displayTableRow// Test displayTableRow
    public function testDisplayTableRow()
    {
        $food = [
            'fdcId' => '12345',
            'description' => 'Apple',
            'foodCategory' => 'Fruit',
            'brandOwner' => 'BrandOwnerName',
            'brandName' => 'BrandName',
            'marketCountry' => 'USA'
        ];
        $dataType = 'Branded';

        $html = displayTableRow($food, $dataType);

        // Normalize quotes in the actual HTML for comparison
        $html = str_replace("'", '"', $html);

        // Check for presence of key elements and attributes
        $this->assertStringContainsString('<a href="meal_functions/food.php?fdcId=12345">Apple</a>', $html);
        $this->assertStringContainsString('<td>12345</td>', $html);
        $this->assertStringContainsString('<td>Fruit</td>', $html);
        $this->assertStringContainsString('<td>BrandOwnerName</td>', $html);
        $this->assertStringContainsString('<td>BrandName</td>', $html);
        $this->assertStringContainsString('<td>USA</td>', $html);
        $this->assertStringContainsString('<button type="submit" name="addToMeal" class="btn btn-primary btn-sm">+</button>', $html);
    }

}
