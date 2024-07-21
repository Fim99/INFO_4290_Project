<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Meal Tracker</title>
    <?php 
    include 'bootstrap.html'; 
    include 'nav.php'; 
    ?>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Frequently Asked Questions</h1>
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header faq-header" id="headingOne">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        What is Meal Tracker?
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Meal Tracker is a web application designed to help you track and manage your daily meals, including their nutritional content. 
                        You can view foods nutritional information, ingredients and more as the data is up to date using FoodData Centeral's database! 
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header faq-header" id="headingTwo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        How do I add a new meal?
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        To add a new meal, navigate to the 'Meal Records' section. Here you can create a new meal that you can add food to.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header faq-header" id="headingThree">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        Can I edit or delete a meal after adding it?
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes, you can edit or delete a meal by navigating to the 'Meal Records' section. You can either delete a meal, reuse a meal, or set a meal as current.
                        you can also rename your currently selected meal if you wish.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header faq-header" id="headingFour">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                        How do I track the nutrients in my meals?
                    </button>
                </h2>
                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        The Meal Tracker automatically calculates the nutritional content of your meals based on the food items you add. 
                        You can view detailed nutritional information for each meal in the 'Meal Details' section by clicking on a meal in your records.
                    </div>
                </div>
            </div>
            <!-- Add more FAQ items as needed -->
        </div>
    </div>
</body>
</html>
