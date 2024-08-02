<?php

use PHPUnit\Framework\TestCase;
class FoodTest extends TestCase
{
    public function testBuildApiUrl()
    {
        $fdcId = 123456;
        $expectedUrl = "https://api.nal.usda.gov/fdc/v1/food/$fdcId?";

        $this->assertEquals($expectedUrl, buildApiUrlFood($fdcId));
    }

    public function testHighlightIngredients()
    {
        $ingredients = 'sugar, salt, butter';
        $highlightedIngredients = ['sugar', 'butter'];

        $expectedResult = '<span class="highlight">SUGAR</span>, SALT, <span class="highlight">BUTTER</span>';

        $this->assertEquals($expectedResult, highlightIngredients($ingredients, $highlightedIngredients));
    }

    public function testHighlightIngredientsNoMatch()
    {
        $ingredients = 'sugar, salt, butter';
        $highlightedIngredients = ['pepper', 'olive oil'];

        $expectedResult = 'SUGAR, SALT, BUTTER';

        $this->assertEquals($expectedResult, highlightIngredients($ingredients, $highlightedIngredients));
    }
}
?>
