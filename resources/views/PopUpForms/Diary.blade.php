<div class="popup modal" id="row_delete" style="background-color: rgba(176, 190, 227, 0.4)">
    <div class="modal-dialog modal-lg" role="document">
        <form class="modal-content" action="{{ route('diary.delete') }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('GET')
            <div class="modal-header" style="background-color: rgba(76, 112, 205, 0.8);" >
                <h5 class="modal-title" style="color: white">Delete Record</h5>
                <button type="button" class="close popup-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white">&times;</span>
                </button>
            </div>
            <div class="modal-body bg-light-subtle container">
                <input id="row_id" name="id" value="" hidden>
                <p>Are you sure that you want to delete the record below?</p>
                <table>
                    <tr style="background-color: rgba(176, 190, 227, 0.8);">
                        <th></th>
                        <th style="text-align: center;width: 155px;">Glucose Before</th>
                        <th style="text-align: center;width: 155px;background-color: rgba(176, 190, 227, 0.3);">Food Carbo<br>(Grams)</th>
                        <th style="text-align: center;width: 155px;">Insulin Dose<br>(Units)</th>
                        <th style="text-align: center;width: 155px;background-color: rgba(176, 190, 227, 0.3);">Glucose After</th>
                    </tr>
                    <tr class="info"></tr>
                </table>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary form-control" style="background-color: rgba(90, 140, 228, 0.8);">Delete</button>
            </div>
        </form>
    </div>

</div>


<div class="popup modal" id="create_row" style="background-color: rgba(176, 190, 227, 0.4)">
    <div class="modal-dialog modal-lg" role="document">
        <form class="modal-content" action="{{ route('diary.add') }}" method="post" enctype="multipart/form-data" autocomplete="off">
            @csrf
            @method('GET')
            <div class="modal-header" style="background-color: rgba(76, 112, 205, 0.8);" >
                <h5 class="modal-title" style="color: white"><i class="fi fi-sr-document"></i> Add New Record</h5>
                <button type="button" class="close popup-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white">&times;</span>
                </button>
            </div>
            <div class="modal-body container">
                <table class="mb-5">
                    <tr style="background-color: rgba(176, 190, 227, 0.8);">
                        <th style="text-align: center;">Glucose Before</th>
                        <th style="text-align: center;">Food Carbo in Grams</th>
                        <th style="text-align: center;">Insulin Dose in Units</th>
                        <th style="text-align: center;">Glucose After</th>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <input id="glucose_old" placeholder="Glucose Before" type="number" step="0.01" class="form-control" name="glucose_old" required  autofocus>
                        </td>
                        <td style="text-align: center;background-color: rgba(176, 190, 227, 0.3);">
                            <div style="display: flex;">
                                <input id="food_carbo" placeholder="Food Carbo" class="form-control" name="food_carbo"  readonly>
                                <button type="button" class="close remove_carbo_create mt-2" data-dismiss="modal" aria-label="Close" >
                                    <i class="fi fi-bs-cross-small"></i>
                                </button>
                            </div>

                        </td>
                        <td style="text-align: center;display: flex;">
                            <input id="insulin" placeholder="Insulin Dose" class="form-control" name="insulin" required readonly>

                            <button type="button" class="close remove_create mt-2" data-dismiss="modal" aria-label="Close" >
                                <i class="fi fi-bs-cross-small"></i>
                            </button>
                        </td>
                        <td style="text-align: center;background-color: rgba(176, 190, 227, 0.3);">
                            <input id="glucose_new" placeholder="Glucose After" type="number" step="0.01" class="form-control" name="glucose_new" required>
                        </td>
                    </tr>
                </table>

                <div class="show_cal_create mb-5">
                    <hr  class="mt-4 mb-4">
                    <h3>Calculate Insulin Dose</h3>
                    <div style="display: flex; justify-content: space-between;">

                        <select class="form-select col-11 shadow-lg" id="algorithm_create" >
                            <option value="" selected hidden>Select Algorithm</option>
                            <option value="linear_regression">Linear Regression</option>
                            <option value="random_forest">Random Forest</option>
                            <option value="gradient_boosting">Gradient Boosting</option>
                            <option value="sliding_scale_carbo">Sliding Scale + Carbo Calculation</option>
                        </select>

                        <div>
                            <button type="button" id="predict_create" class="calculator" ><i class="fi fi-rr-calculator"></i></button>
                        </div>
                    </div>
                    <p class="small error_create" style="color: red"></p>

                </div>

                <hr  class="mt-4 mb-4">
                <h3>Search Food</h3>
                <p class="small">For the calculation of the carbohydrates you have consumed, please search for the foods you ate in the list below. </p>
                <div style="display: flex; justify-content: space-between;position: relative;">
                    <input type="text" value="" list="autocomplete_create" class="form-control col-11 shadow-lg" id="search-food" placeholder="Search Food ..." autocomplete="off">
                    <ul id="autocomplete_create" class="autocomplete-items col-11"> </ul>
                   <div>
                       <button type="button" id="search-button" class="calculator"><i class="fi fi-bs-search"></i></button>
                   </div>

                </div>
                <hr  class="mt-4 mb-4">

                <div class="hidden-foods mt-4"></div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary form-control" style="background-color: rgba(90, 140, 228, 0.8);">Save</button>
            </div>
        </form>
    </div>

</div>

<div class="popup modal" id="update_row" style="background-color: rgba(176, 190, 227, 0.4)">
    <div class="modal-dialog modal-lg" role="document">
        <form class="modal-content" action="{{ route('diary.update') }}" method="post" enctype="multipart/form-data" autocomplete="off">
            <input type="text" value="" id="id_up" name="id" hidden>
            @csrf
            @method('GET')
            <div class="modal-header" style="background-color: rgba(76, 112, 205, 0.8);" >
                <h5 class="modal-title" style="color: white"><i class="fi fi-sr-document"></i> Update the Record</h5>
                <button type="button" class="close popup-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white">&times;</span>
                </button>
            </div>
            <div class="modal-body container table-responsive">
                <table class="mb-5 ">
                    <tr style="background-color: rgba(176, 190, 227, 0.8);">
                        <th></th>
                        <th style="text-align: center;">Glucose Before</th>
                        <th style="text-align: center;">Food Carbo in Grams</th>
                        <th style="text-align: center;">Insulin Dose in Units</th>
                        <th style="text-align: center;">Glucose After</th>
                    </tr>
                    <tr>
                        <td id="timestamp_up" style="text-align: center;background-color: rgba(176, 190, 227, 0.8); color: white;font-weight: bold;"></td>
                        <td style="text-align: center;">
                            <input id="glucose_old_up" placeholder="Glucose Before" type="number" step="0.01" class="form-control" name="glucose_old" required  autofocus>
                        </td>
                        <td style="text-align: center;background-color: rgba(176, 190, 227, 0.3);">
                            <input type="text" name="first_food_carbo" id="first_food_carbo" readonly hidden>
                            <div style="display: flex;">
                                <input id="food_carbo_up" placeholder="Food Carbo" class="form-control" name="food_carbo" readonly>
                                <input type="text" name="food_carbo_temp" id="food_carbo_temp" readonly hidden>

                                <button type="button" class="close remove_carbo_update mt-2" data-dismiss="modal" aria-label="Close" >
                                    <i class="fi fi-bs-cross-small"></i>
                                </button>
                            </div>

                        </td>
                        <td style="text-align: center; display:flex;">
                            <input id="insulin_up" placeholder="Insulin Dose"  class="form-control" name="insulin" style="margin-top:6px;" required readonly>
                            <button type="button" class="close remove_update mt-2" data-dismiss="modal" aria-label="Close" >
                                <i class="fi fi-bs-cross-small"></i>
                            </button>
                        </td>
                        <td style="text-align: center;background-color: rgba(176, 190, 227, 0.3);">
                            <input id="glucose_new_up" placeholder="Glucose After" type="number" step="0.01" class="form-control" name="glucose_new" required>
                        </td>
                    </tr>
                </table>

                <div class="show_cal_update mb-5">
                    <hr  class="mt-4 mb-4">
                    <h3>Calculate Insulin Dose</h3>
                    <div  style="display: flex; justify-content: space-between;">

                        <select class="form-select col-11 shadow-lg" id="algorithm_update" >
                            <option value="" selected hidden>Select Algorithm</option>
                            <option value="linear_regression">Linear Regression</option>
                            <option value="random_forest">Random Forest</option>
                            <option value="gradient_boosting">Gradient Boosting</option>
                            <option value="sliding_scale_carbo">Sliding Scale + Carbo Calculation</option>
                        </select>

                        <div>
                            <button type="button" id="predict_upadate" class="calculator" ><i class="fi fi-rr-calculator"></i></button>
                        </div>
                    </div>
                    <p class="small error_update" style="color: red"></p>
                </div>

                <hr  class="mt-4 mb-4">
                <h3>Search Food</h3>
                <p class="small">For the calculation of the carbohydrates you have consumed, please search for the foods you ate in the list below. </p>
                <div style="display: flex; justify-content: space-between;position: relative;">
                    <input type="text" value="" list="autocomplete_update" class="form-control col-11 shadow-lg" id="search-food_up" placeholder="Search Food..." >
                    <ul id="autocomplete_update" class="autocomplete-items col-11"> </ul>
                    <div>
                        <button type="button" id="search-button_up" class="calculator" data-dismiss="modal" aria-label="Close"><i class="fi fi-bs-search"></i></button>
                    </div>

                </div>
                <hr  class="mt-4 mb-4">

                <div class="hidden-foods_up mt-4"></div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary form-control" style="background-color: rgba(90, 140, 228, 0.8);">Save</button>
            </div>
        </form>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    // Debounce function to limit the rate at which a function can fire.
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }

    $(document).ready(function (){

        $('.create').click(function(){
            $('#glucose_old').val('');
            $('#food_carbo').val('');
            $('#insulin').val('');
            $('#glucose_new').val('');
            $('#search-food').val('');
            $('.hidden-foods').empty();
            $('.show_cal_create').hide(); // Hide the div if the input is empty
            $('.remove_create').hide();
            $('.remove_carbo_create').hide();
            $('#create_row').fadeIn();
        });

        let counter = 1;

        $('#search-button').click(function () {
            // Get the value from the input field
            const key = $('#search-food').val();
            const Data = { food: key };

            if (key !== null && key !== '') {
                // Build the HTML for the new table row with a dynamic select element and a button to calculate carbohydrates
                const newRowHtml = `
            <table class="mb-3">
                <tr style="background-color: rgba(176, 190, 227, 0.8);" id="food-row-${counter}">
                    <div>
                        <select class="form-select" name="food" id="select-food${counter}">

                        </select>
                    </div>
                    <td class="col-3">
                        <select class="form-select" id="serving${counter}" >

                        </select>
                    </td>
                    <td class="col-3">
                        <input name="quantity" placeholder="Quantity" type="number" step="1" class="form-control" id="quantity${counter}">
                    </td>
                    <td class="col-3">
                        <input name="carbohydred" placeholder="CarboHydred"  type="number" step="1" class="form-control" id="carbohydred${counter}" readonly>
                    </td>
                    <td class="col-3">
                        <button class="btn btn-primary" id="calculate-btn${counter}" type="button">Calculate Carbohydrates</button>
                    </td>
                </tr>
            </table>`;

                // Append the HTML to the hidden foods container
                $('.hidden-foods').append(newRowHtml);

                // Fetch the food list using Axios
                fetchFoodList(Data, counter);

                // // Attach event listener to the "Calculate Carbohydrates" button
                attachCalculationButtonEvent(counter);

                // Increment the counter after adding the new row
                counter++;
            }
        });

// Function to fetch food list and populate the select options
        function fetchFoodList(data, currentCounter) {
            axios.post('/food-list', {
                food: data.food // This passes the food query correctly in the body
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Ensure CSRF token is included
                }
            })
                .then(response => {
                    const foodList = response.data.foods|| [];

                    const uniqueServingUnits = new Set(); // Using a Set to store unique serving units

                    // Get the dynamically created select element for food and serving
                    const selectFood = document.getElementById(`select-food${currentCounter}`);
                    const selectServing = document.getElementById(`serving${currentCounter}`);

                    if (selectFood && selectServing) {
                        if (foodList.length === 0) {
                            console.log(`No food items found for query: ${data.food}`);
                            return;
                        }

                        // Loop through the food items and create option elements
                        foodList.forEach(food => {
                            // Append to food select
                            const foodOption = new Option(food.food_name, food.food_name);
                            selectFood.add(foodOption);

                            // Add unique serving units to the serving select
                            if (!uniqueServingUnits.has(food.serving_unit)) {
                                uniqueServingUnits.add(food.serving_unit);
                                const servingOption = new Option(food.serving_unit, food.nf_total_carbohydrate); // Store carbs in value
                                selectServing.add(servingOption);
                            }
                        });
                    } else {
                        console.error(`Select elements not found: #select-food${currentCounter} or #serving${currentCounter}`);
                    }
                })
                .catch(error => {
                    console.error('Error fetching food list:', error);
                });
        }

        function attachCalculationButtonEvent(counter) {
            const calculateBtn = document.getElementById(`calculate-btn${counter}`);
            const selectServing = document.getElementById(`serving${counter}`);
            const quantityInput = document.getElementById(`quantity${counter}`);
            const carbInput = document.getElementById(`carbohydred${counter}`);

            // Event listener for the calculate button
            calculateBtn.addEventListener('click', function() {
                calculateCarbohydrates(selectServing, quantityInput, carbInput);
                updateTotalCarbohydrates(); // Update total carbohydrates after calculation
            });

            // Attach event listener to the `carbohydred` input field to track changes in real time
            carbInput.addEventListener('input', updateTotalCarbohydrates);
        }

// Function to calculate carbohydrates and update the input field
        function calculateCarbohydrates(servingSelect, quantityInput, carbInput) {
            const selectedServing = servingSelect.options[servingSelect.selectedIndex];
            const carbsPerServing = parseFloat(selectedServing.value); // Get carbs from value
            const quantity = parseFloat(quantityInput.value) || 0; // Default to 0 if not filled

            if (!isNaN(carbsPerServing) && !isNaN(quantity)) {
                const totalCarbs = carbsPerServing * quantity;
                carbInput.value = totalCarbs.toFixed(2); // Update the CarboHydred input field
            } else {
                carbInput.value = ''; // Clear the field if the input is invalid
            }

            updateTotalCarbohydrates(); // Update the total carbohydrates whenever a single field is calculated
        }

// Function to sum all carbohydrates and update the total field
        function updateTotalCarbohydrates() {
            let totalCarbs = 0;

            // Loop through all input fields with IDs starting with `carbohydred`
            document.querySelectorAll('input[id^="carbohydred"]').forEach(input => {
                const value = parseFloat(input.value) || 0; // Default to 0 if empty
                totalCarbs += value;

            });

            $('#food_carbo').val(totalCarbs);
            $('.remove_carbo_create').show();
        }






        // Use event delegation
        $(document).on('click', '.row_tr', function(event) {
            // Extract data attributes from the row
            const {id, timestamp, glucose_old, glucose_new, food, insulin} = $(this).data();

            const $button = $(event.target).closest('.td_delete').find('button');
            if ($button.length) {
                // Populate the delete form
                $('#row_id').val(id);
                $('.info').html(
                    '<td style="text-align: center;background-color: rgba(176, 190, 227, 0.8); color: white;font-weight: bold "><i>'+timestamp+'</i></td>'+
                    '<td style="text-align: center;"><i>'+glucose_old+'</i></td>'+
                    '<td style="text-align: center;"><i>'+food+'</i></td>'+
                    '<td style="text-align: center;"><i>'+insulin+'</i></td>'+
                    '<td style="text-align: center;"><i>'+glucose_new+'</i></td>'
                );
                $('#row_delete').fadeIn();
                return
            }

            // Populate the update form (edit operation)
            $('#id_up').val(id);
            $('#timestamp_up').html('<i>' + new Date(timestamp).toLocaleDateString('en-GB') + '</i><br><i>' + new Date(timestamp).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }) + '</i>');
            $('#glucose_old_up').val(glucose_old);
            $('#food_carbo_up').val(food);
            $('#food_carbo_temp').val(food);
            $('#first_food_carbo').val(food);
            $('.remove_carbo_update').hide();

            $('#insulin_up').val(insulin);
            $('#glucose_new_up').val(glucose_new);
            $('#search-food_up').val('');
            $('.remove_update').hide();

            $('.hidden-foods_up').empty();
            $('#update_row').fadeIn();
        });


        let counter_up = 1;

        $('#search-button_up').click(function () {
            // Get the value from the input field
            const key = $('#search-food_up').val();
            const Data = { food: key };

            if (key !== null && key !== '') {
                // Build the HTML for the new table row with a dynamic select element and a button to calculate carbohydrates
                const newRowHtml = `
            <table class="mb-3">
                <tr style="background-color: rgba(176, 190, 227, 0.8);" id="food-row-_up${counter_up}">
                    <div>
                        <select class="form-select" name="food" id="select-food_up${counter_up}">

                        </select>
                    </div>
                    <td class="col-3">
                        <select class="form-select" id="serving_up${counter_up}" >

                        </select>
                    </td>
                    <td class="col-3">
                        <input name="quantity" placeholder="Quantity" type="number" step="1" class="form-control" id="quantity_up${counter_up}">
                    </td>
                    <td class="col-3">
                        <input name="carbohydred" placeholder="CarboHydred"  type="number" step="1" class="form-control" id="carbohydred_up${counter_up}" readonly>
                    </td>
                    <td class="col-3">
                        <button class="btn btn-primary" id="calculate-btn_up${counter_up}"  type="button">Calculate Carbohydrates</button>
                    </td>
                </tr>
            </table>`;

                // Append the HTML to the hidden foods container
                $('.hidden-foods_up').append(newRowHtml);

                // Fetch the food list using Axios
                fetchFoodList_up(Data, counter_up);

                // // Attach event listener to the "Calculate Carbohydrates" button
                attachCalculationButtonEvent_up(counter_up);

                // Increment the counter after adding the new row
                counter_up++;
            }
        });


        // Function to fetch food list and populate the select options
        function fetchFoodList_up(data, currentCounter) {
            axios.post('/food-list', {
                food: data.food // This passes the food query correctly in the body
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Ensure CSRF token is included
                }
            })
                .then(response => {
                    const foodList = response.data.foods|| [];

                    const uniqueServingUnits_up = new Set(); // Using a Set to store unique serving units

                    // Get the dynamically created select element for food and serving
                    const selectFood = document.getElementById(`select-food_up${currentCounter}`);
                    const selectServing = document.getElementById(`serving_up${currentCounter}`);

                    if (selectFood && selectServing) {
                        if (foodList.length === 0) {
                            console.log(`No food items found for query: ${data.food}`);
                            return;
                        }

                        // Loop through the food items and create option elements
                        foodList.forEach(food => {
                            // Append to food select
                            const foodOption = new Option(food.food_name, food.food_name);
                            selectFood.add(foodOption);

                            // Add unique serving units to the serving select
                            if (!uniqueServingUnits_up.has(food.serving_unit)) {
                                uniqueServingUnits_up.add(food.serving_unit);
                                const servingOption = new Option(food.serving_unit, food.nf_total_carbohydrate); // Store carbs in value
                                selectServing.add(servingOption);
                            }
                        });
                    } else {
                        console.error(`Select elements not found: #select-food_up${currentCounter} or #serving_up${currentCounter}`);
                    }
                })
                .catch(error => {
                    console.error('Error fetching food list:', error);
                });
        }

        function attachCalculationButtonEvent_up(counter) {
            const calculateBtn = document.getElementById(`calculate-btn_up${counter}`);
            const selectServing = document.getElementById(`serving_up${counter}`);
            const quantityInput = document.getElementById(`quantity_up${counter}`);
            const carbInput = document.getElementById(`carbohydred_up${counter}`);

            // Event listener for the calculate button
            calculateBtn.addEventListener('click', function() {
                calculateCarbohydrates_up(selectServing, quantityInput, carbInput);
                updateTotalCarbohydrates_up(); // Update total carbohydrates after calculation
            });

            // Attach event listener to the `carbohydred` input field to track changes in real time
            carbInput.addEventListener('input', updateTotalCarbohydrates_up);
        }

// Function to calculate carbohydrates and update the input field
        function calculateCarbohydrates_up(servingSelect, quantityInput, carbInput) {
            const selectedServing = servingSelect.options[servingSelect.selectedIndex];
            const carbsPerServing = parseFloat(selectedServing.value); // Get carbs from value
            const quantity = parseFloat(quantityInput.value) || 0; // Default to 0 if not filled

            if (!isNaN(carbsPerServing) && !isNaN(quantity)) {
                const totalCarbs = carbsPerServing * quantity;
                carbInput.value = totalCarbs.toFixed(2); // Update the CarboHydred input field
            } else {
                carbInput.value = ''; // Clear the field if the input is invalid
            }

            updateTotalCarbohydrates_up(); // Update the total carbohydrates whenever a single field is calculated
        }

// Function to sum all carbohydrates and update the total field
        function updateTotalCarbohydrates_up() {
            let totalCarbs = 0;
            const val_before = parseFloat((parseFloat($('#food_carbo_temp').val()) || 0.0).toFixed(2)); // Convert to number after toFixed

            // Loop through all input fields with IDs starting with `carbohydred`
            document.querySelectorAll('input[id^="carbohydred_up"]').forEach(input => {
                const value = parseFloat(input.value) || 0; // Default to 0 if empty
                totalCarbs += value;
            });

            totalCarbs += val_before; // Proper numeric addition
            $('#food_carbo_up').val(totalCarbs.toFixed(2)); // Update total with 2 decimal points
            $('.remove_carbo_update').show();
        }


        //function that close the popups forms
        $(".popup-close").click(function () {
            $(".popup").fadeOut();
        });

        $(document).ready(function() {
            // Function to show or hide the '.show_cal_create' div based on the value of '#glucose_old'
            function toggleShowCalCreate() {
                const value = $('#glucose_old').val();
                if (value !== '') {
                    $('.show_cal_create').show(); // Show the div if there's a value
                } else {
                    $('.show_cal_create').hide(); // Hide the div if empty
                }
            }

            // Call the function when the input value changes
            $('#glucose_old').on('input', function() {
                toggleShowCalCreate();
            });

            // Initial call to set the correct visibility on page load
            toggleShowCalCreate();
        });










        // Event listener for the 'Calculate Insulin Dose' button
        $('#predict_create').click(function() {
            // Collect input data
            const glucose_old = parseFloat($('#glucose_old').val()) || 0;
            const glucose_new = parseFloat($('#glucose_new').val()) || 0;
            const food_carbo = parseFloat($('#food_carbo').val()) || 0;
            const algorithm = $('#algorithm_create').val() ;

            // Validate the inputs
            if (isNaN(glucose_old) || algorithm==='') {
                $('.error_create').html("In order to calculate the insulin dose prediction, the fields 'insulin before' and 'select algorithm' must be filled out.");
                return;
            }

            // Prepare data object
            const data = {
                glucose_old: glucose_old,
                glucose_new: glucose_new,
                food_carbo: food_carbo,
                algorithm: algorithm,
            };


            axios.get('/predict', {
                params: data, // Pass the data as query parameters
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => {
                    const message = response.data.message;
                    $('#insulin').val(message);
                    $('.remove_create').show();
                })
                .catch(error => {
                    console.error('Error fetching food list:', error);
                });
        });


        $('#predict_upadate').click(function() {
            // Collect input data
            const glucose_old = parseFloat($('#glucose_old_up').val()) || 0;
            const glucose_new = parseFloat($('#glucose_new_up').val()) || 0;
            const food_carbo = parseFloat($('#food_carbo_up').val()) || 0;
            const algorithm = $('#algorithm_update').val() ;

            // Validate the inputs
            if (isNaN(glucose_old) || algorithm==='') {
                $('.error_update').html("In order to calculate the insulin dose prediction, the fields 'insulin before' and 'select algorithm' must be filled out.");
                return;
            }

            // Prepare data object
            const data = {
                glucose_old: glucose_old,
                glucose_new: glucose_new,
                food_carbo: food_carbo,
                algorithm: algorithm,
            };

            console.log(data);

            axios.get('/predict', {
                params: data, // Pass the data as query parameters
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => {
                    const message = response.data.message.toFixed(2);
                    $('#insulin_up').val(message);
                    $('.remove_update').show()
                })
                .catch(error => {
                    console.error('Error fetching food list:', error);
                });
        });


        $('.remove_create').click(function(){
            $('#insulin').val('');
            $(this).hide();
        });
        $('.remove_update').click(function(){
            $('#insulin_up').val('');
            $(this).hide();
        });

        //remove the value from carbo hydrate input
        $('.remove_carbo_create').click(function(){
            $('#food_carbo').val('');
            $(this).hide();
        });
        $('.remove_carbo_update').click(function(){
            var food_first= parseFloat($('#first_food_carbo').val())||"";
            $('#food_carbo_up').val(food_first);
            $(this).hide();
        });




// Implement debounce on the search-food input
        $('#search-food').on('input', debounce(function() {
            var food = $(this).val(); // Get the value from the input field

            $('#autocomplete_create').empty();

            if (food.length > 0) {
                axios.get('/autocomplete', {
                    params: {
                        food: food
                    }
                })
                    .then(function (response) {
                        var foodNames = response.data.food_names;

                        // Populate the autocomplete list with the returned data
                        foodNames.forEach(function(name) {
                            const listItem = $('<li></li>').text(name);
                            // Append the list item to the autocomplete list
                            $('#autocomplete_create').append(listItem);
                        });

                        // Show the autocomplete list
                        $('#autocomplete_create').show();
                    })
                    .catch(function (error) {
                        console.log('Error fetching autocomplete results.', error);
                    });
            } else {
                // Clear the list if the input is empty
                $('#autocomplete_create').hide();
            }
        }, 200)); // 200 milliseconds debounce time

        // Implement debounce on the search-food_up input
        $('#search-food_up').on('input', debounce(function() {
            var food = $(this).val(); // Get the value from the input field

            $('#autocomplete_update').empty();

            if (food.length > 0) {
                axios.get('/autocomplete', {
                    params: {
                        food: food
                    }
                })
                    .then(function (response) {
                        var foodNames = response.data.food_names;

                        // Populate the autocomplete list with the returned data
                        foodNames.forEach(function(name) {
                            const listItem = $('<li></li>').text(name);
                            // Append the list item to the autocomplete list
                            $('#autocomplete_update').append(listItem);
                        });

                        // Show the autocomplete list
                        $('#autocomplete_update').show();
                    })
                    .catch(function (error) {
                        console.log('Error fetching autocomplete results.', error);
                    });
            } else {
                // Clear the list if the input is empty
                $('#autocomplete_update').hide();
            }
        }, 200)); // 200 milliseconds debounce time

        // Hide the suggestions if the user clicks outside
        $(document).on('click', function(e) {
            if (!$("#search-food").is(e.target) && !$('#autocomplete_create').is(e.target) && $('#autocomplete_create').has(e.target).length === 0) {
                $('#autocomplete_create').hide();
            }
            if (!$("#search-food_up").is(e.target) && !$('#autocomplete_update').is(e.target) && $('#autocomplete_update').has(e.target).length === 0) {
                $('#autocomplete_update').hide();
            }
        });

        // Event delegation for dynamically created <li> elements in create modal
        $('#autocomplete_create').on('click', 'li', function() {
            const selectedText = $(this).text();  // Get the text of the clicked <li>
            $("#search-food").val(selectedText);  // Set input value to the selected food name
            $('#autocomplete_create').empty();  // Clear the suggestion list
            $('#autocomplete_create').hide();   // Hide the suggestion list
        });

        // Event delegation for dynamically created <li> elements in update modal
        $('#autocomplete_update').on('click', 'li', function() {
            const selectedText = $(this).text();  // Get the text of the clicked <li>
            $("#search-food_up").val(selectedText);  // Set input value to the selected food name
            $('#autocomplete_update').empty();  // Clear the suggestion list
            $('#autocomplete_update').hide();   // Hide the suggestion list
        });










    });


</script>
