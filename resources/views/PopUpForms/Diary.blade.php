{{-- Modal για διαγραφή εγγραφής (Delete Record) --}}
<div class="popup modal" id="row_delete" style="background-color: rgba(176, 190, 227, 0.4)">
    <div class="modal-dialog modal-lg" role="document">
        {{-- Φόρμα για διαγραφή. Χρησιμοποιεί τη διαδρομή (route) 'diary.delete' μέσω GET.
     Το @csrf προστατεύει από επιθέσεις CSRF και το @method('GET') δηλώνει ότι θέλουμε να κάνουμε GET αίτημα. --}}
        <form class="modal-content" action="{{ route('diary.delete') }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('GET')

            {{-- Κεφαλίδα του modal --}}
            <div class="modal-header" style="background-color: rgba(76, 112, 205, 0.8);" >
                <h5 class="modal-title" style="color: white">Delete Record</h5>
                {{-- Κουμπί κλεισίματος του modal (x) --}}
                <button type="button" class="close popup-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white">&times;</span>
                </button>
            </div>

            {{-- Σώμα του modal με το μήνυμα επιβεβαίωσης διαγραφής --}}
            <div class="modal-body bg-light-subtle container">
                {{-- Κρυφό input που αποθηκεύει το ID της εγγραφής προς διαγραφή --}}
                <input id="row_id" name="id" value="" hidden>
                <p>Are you sure that you want to delete the record below?</p>
                {{-- Πίνακας απλής εμφάνισης για την τιμή της εγγραφής που θα διαγραφεί. --}}
                <table>
                    <tr style="background-color: rgba(176, 190, 227, 0.8);">
                        <th></th>
                        <th style="text-align: center;width: 155px;">Glucose Before Meal</th>
                        <th style="text-align: center;width: 155px;background-color: rgba(176, 190, 227, 0.3);">Food Carbo<br>(Grams)</th>
                        <th style="text-align: center;width: 155px;">Insulin Dose<br>(Units)</th>
                        <th style="text-align: center;width: 155px;background-color: rgba(176, 190, 227, 0.3);">Glucose After Meal</th>
                    </tr>
                    <tr class="info"></tr>
                </table>

            </div>
            {{-- Περιλαμβάνει το κουμπί υποβολής (Delete) --}}
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary form-control" style="background-color: rgba(90, 140, 228, 0.8);">Delete</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal για δημιουργία νέας εγγραφής (Create Row) με input για Timestamp --}}
<div class="popup modal" id="create_row" style="background-color: rgba(176, 190, 227, 0.4)">
    <div class="modal-dialog modal-lg" role="document">
        {{-- Χρησιμοποιεί τη διαδρομή (route) 'diary.add' μέσω GET. --}}
        <form class="modal-content" action="{{ route('diary.add') }}" method="post" enctype="multipart/form-data" autocomplete="off">
            @csrf
            @method('GET')

            {{-- Κεφαλίδα του modal με τίτλο "Add New Record" --}}
            <div class="modal-header" style="background-color: rgba(76, 112, 205, 0.8);" >
                <h5 class="modal-title" style="color: white"><i class="fi fi-sr-document"></i> Add New Record</h5>
                {{-- Κουμπί κλεισίματος (x) --}}
                <button type="button" class="close popup-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white">&times;</span>
                </button>
            </div>

            {{-- Σώμα του modal --}}
            <div class="modal-body container">
                <!-- Timestamp Input -->
                <div class="form-group">
                    <label for="timestamp">Date & Time</label>
                    <input id="timestamp" type="datetime-local" class="form-control" name="timestamp" required>
                </div>

                {{-- Πίνακας που περιλαμβάνει τα πεδία εισαγωγής νέων δεδομένων (Glucose Before/After, Food Carbo, Insulin Dose) --}}
                <table class="mb-5">
                    <tr style="background-color: rgba(176, 190, 227, 0.8);">
                        <th style="text-align: center;">Glucose Before Meal</th>
                        <th style="text-align: center;">Food Carbo in Grams</th>
                        <th style="text-align: center;">Insulin Dose in Units</th>
                        <th style="text-align: center;">Glucose After Meal</th>
                    </tr>
                    <tr>

                        {{-- Glucose Before Meal --}}
                        <td style="text-align: center;">
                            <input id="glucose_old" placeholder="Glucose Before" type="number" step="0.01" class="form-control glucose-input" name="glucose_old" required autofocus>
                        </td>

                        {{-- Food Carbo in Grams, με κουμπί (x) για διαγραφή της τιμής. --}}
                        <td style="text-align: center;background-color: rgba(176, 190, 227, 0.3);">
                            <div style="display: flex;">
                                <input id="food_carbo" placeholder="Food Carbo" class="form-control" name="food_carbo" type="number" min="0" step="0.01">
                                {{-- Το κουμπί remove_carbo_create κλείνει το modal αλλά μπορεί να αξιοποιείται από JS για να διαγράψει την τιμή. --}}
                                <button type="button" class="close remove_carbo_create mt-2" data-dismiss="modal" aria-label="Close" >
                                    <i class="fi fi-bs-cross-small"></i>
                                </button>
                            </div>
                        </td>

                        {{-- Insulin Dose in Units, με κουμπί (x) για διαγραφή της τιμής. --}}
                        <td style="text-align: center; display: flex;">
                            <input id="insulin" placeholder="Insulin Dose" class="form-control" name="insulin_dose" type="number" min="0" step="1">
                            <button type="button" class="close remove_create mt-2" data-dismiss="modal" aria-label="Close">
                                <i class="fi fi-bs-cross-small"></i>
                            </button>
                        </td>

                        {{-- Glucose After Meal --}}
                        <td style="text-align: center; background-color: rgba(176, 190, 227, 0.3);">
                            <input id="glucose_new" placeholder="Glucose After" type="number" step="0.01" class="form-control glucose-input" name="glucose_new">
                        </td>
                    </tr>
                </table>

                {{-- Κουμπί "Save" που υποβάλλει τη φόρμα. To preserveScroll είναι custom συνάρτηση JS που επαναφέρει το scroll στο σημείο που ήταν πριν. --}}
                <div style="text-align: right; margin-top: -30px; margin-right: 10px;">
                    <button type="submit" class="btn btn-sm btn-primary" onclick="preserveScroll()" style="background-color: rgba(90, 140, 228, 0.8); width:80px;">Save</button>
                </div>

                {{-- Τμήμα για υπολογισμό δόσης ινσουλίνης (Calculate Insulin Dose) --}}
                <div class="show_cal_create mb-5">
                    <hr class="mt-4 mb-4">
                    <h3>Calculate Insulin Dose</h3>
                    <div style="display: flex; justify-content: space-between;">
                        {{-- Επιλογή μεθόδου υπολογισμού ή αλγορίθμου (π.χ. linear_regression, random_forest, κ.λπ.) --}}
                        <select class="form-select col-11 shadow-lg" id="algorithm_create">
                            <option value="" selected hidden>Select Algorithm</option>
                            <option value="linear_regression">Linear Regression</option>
                            <option value="random_forest">Random Forest</option>
                            <option value="gradient_boosting">Gradient Boosting</option>
                            <option value="sliding_scale_carbo">Sliding Scale + Carbo Calculation</option>
                        </select>
                        <div>
                            {{-- Κουμπί που ενεργοποιεί τον υπολογισμό (με AJAX). --}}
                            <button type="button" id="predict_create" class="calculator"><i class="fi fi-rr-calculator"></i></button>
                        </div>
                    </div>
                    <p class="small error_create" style="color: red"></p>
                </div>

                <hr class="mt-4 mb-4">
                <h3>Search Food</h3>
                <p class="small">For the calculation of the carbohydrates you have consumed, please search for the foods you ate in the list below.</p>
                <div style="display: flex; justify-content: space-between; position: relative;">
                    {{-- Πλαίσιο αναζήτησης τροφίμων (search-food) με autocomplete. --}}
                    <input type="text" value="" list="autocomplete_create" class="form-control col-11 shadow-lg" id="search-food" placeholder="Search Food ..." autocomplete="off">
                    <ul id="autocomplete_create" class="autocomplete-items col-11"></ul>
                    <div>
                        {{-- Κουμπί αναζήτησης τροφίμων. --}}
                        <button type="button" id="search-button" class="calculator"><i class="fi fi-bs-search"></i></button>
                    </div>
                </div>
                {{-- Περιοχή που προβάλλει λεπτομέρειες ή λίστα των τροφίμων που επιλέχθηκαν (ενημερώνεται δυναμικά). --}}
                <hr class="mt-4 mb-4">
                <div class="hidden-foods mt-4"></div>
            </div>
        </form>
    </div>
</div>

{{-- Modal για ενημέρωση (Update) μιας ήδη υπάρχουσας εγγραφής --}}
<div class="popup modal" id="update_row" style="background-color: rgba(176, 190, 227, 0.4)">
    <div class="modal-dialog modal-lg" role="document">
        {{-- Φόρμα που στέλνει ενημέρωση στη διαδρομή (route) 'diary.update' μέσω GET --}}
        <form class="modal-content" action="{{ route('diary.update') }}" method="post" enctype="multipart/form-data" autocomplete="off">
            {{-- Αποθηκεύουμε το ID της εγγραφής σε κρυφό input. --}}
            <input type="text" value="" id="id_up" name="id" hidden>
            @csrf
            @method('GET')

            {{-- Κεφαλίδα του modal --}}
            <div class="modal-header" style="background-color: rgba(76, 112, 205, 0.8);">
                <h5 class="modal-title" style="color: white"><i class="fi fi-sr-document"></i> Update the Record</h5>
                {{-- Κουμπί κλεισίματος (x) --}}
                <button type="button" class="close popup-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white">&times;</span>
                </button>
            </div>

            {{-- Σώμα του modal --}}
            <div class="modal-body container table-responsive">
                {{-- Timestamp για την ενημέρωση της ημερομηνίας και ώρας --}}
                <div class="form-group">
                    <label for="timestamp_up">Date & Time</label>
                    <input id="timestamp_up" type="datetime-local" class="form-control" name="timestamp" required>
                </div>

                {{-- Πίνακας που παρουσιάζει τα ήδη υπάρχοντα δεδομένα και επιτρέπει την επεξεργασία τους --}}
                <table class="mb-5">
                    <tr style="background-color: rgba(176, 190, 227, 0.8);">
                        <th></th>
                        <th style="text-align: center;">Glucose Before Meal</th>
                        <th style="text-align: center;">Food Carbo in Grams</th>
                        <th style="text-align: center;">Insulin Dose in Units</th>
                        <th style="text-align: center;">Glucose After Meal</th>
                    </tr>
                    <tr>
                        <td></td>
                        {{-- Πεδίο Glucose Before, με step="0.01" για δεκαδικούς --}}
                        <td style="text-align: center;">
                            <input id="glucose_old_up" placeholder="Glucose Before" type="number" step="0.01" class="form-control glucose-input" name="glucose_old" required autofocus>
                        </td>

                        {{-- Food Carbo in Grams. --}}
                        <td style="text-align: center; background-color: rgba(176, 190, 227, 0.3);">
                            <input type="text" name="first_food_carbo" id="first_food_carbo" readonly hidden>
                            <div style="display: flex;">
                                <input id="food_carbo_up" placeholder="Food Carbo" class="form-control" name="food_carbo" type="number" min="0" step="0.01">
                                <input type="text" name="food_carbo_temp" id="food_carbo_temp" readonly hidden>
                                {{-- Κουμπί (x) για διαγραφή της τιμής --}}
                                <button type="button" class="close remove_carbo_update mt-2" data-dismiss="modal" aria-label="Close">
                                    <i class="fi fi-bs-cross-small"></i>
                                </button>
                            </div>
                        </td>

                        {{-- Insulin Dose, επίσης με κουμπί (x) για διαγραφή της τιμής --}}
                        <td style="text-align: center; display: flex;">
                            <input id="insulin_up" placeholder="Insulin Dose" class="form-control" name="insulin_dose" type="number" min="0" step="1">
                            <button type="button" class="close remove_update mt-2" data-dismiss="modal" aria-label="Close">
                                <i class="fi fi-bs-cross-small"></i>
                            </button>
                        </td>

                        {{-- Glucose After Meal --}}
                        <td style="text-align: center; background-color: rgba(176, 190, 227, 0.3);">
                            <input id="glucose_new_up" placeholder="Glucose After" type="number" step="0.01" class="form-control glucose-input" name="glucose_new">
                        </td>
                    </tr>
                </table>

                {{-- Κουμπί "Save" για την ενημέρωση της εγγραφής --}}
                <div style="text-align: right; margin-top: -30px; margin-right: 10px;">
                    <button type="submit" class="btn btn-sm btn-primary" onclick="preserveScroll()" style="background-color: rgba(90, 140, 228, 0.8); width:80px;">Save</button>
                </div>

                {{-- Ενότητα για υπολογισμό δόσης ινσουλίνης (Calculate Insulin Dose) --}}
                <div class="show_cal_update mb-5">
                    <hr class="mt-4 mb-4">
                    <h3>Calculate Insulin Dose</h3>
                    <div style="display: flex; justify-content: space-between;">
                        <select class="form-select col-11 shadow-lg" id="algorithm_update">
                            <option value="" selected hidden>Select Algorithm</option>
                            <option value="linear_regression">Linear Regression</option>
                            <option value="random_forest">Random Forest</option>
                            <option value="gradient_boosting">Gradient Boosting</option>
                            <option value="sliding_scale_carbo">Sliding Scale + Carbo Calculation</option>
                        </select>
                        <div>
                            {{-- Κουμπί πρόβλεψης (υπολογισμού) με AJAX. --}}
                            <button type="button" id="predict_upadate" class="calculator"><i class="fi fi-rr-calculator"></i></button>
                        </div>
                    </div>
                    <p class="small error_update" style="color: red"></p>
                </div>

                <hr class="mt-4 mb-4">
                <h3>Search Food</h3>
                <p class="small">For the calculation of the carbohydrates you have consumed, please search for the foods you ate in the list below.</p>
                {{-- Αναζήτηση τροφίμων με autocomplete, όπως και στο create_row --}}
                <div style="display: flex; justify-content: space-between; position: relative;">
                    <input type="text" value="" list="autocomplete_update" class="form-control col-11 shadow-lg" id="search-food_up" placeholder="Search Food...">
                    <ul id="autocomplete_update" class="autocomplete-items col-11"></ul>
                    <div>
                        <button type="button" id="search-button_up" class="calculator" data-dismiss="modal" aria-label="Close"><i class="fi fi-bs-search"></i></button>
                    </div>
                </div>
                <hr class="mt-4 mb-4">
                {{-- Περιοχή που προβάλλει επιλεγμένα τρόφιμα ή αποτελέσματα αναζήτησης τροφίμων. --}}
                <div class="hidden-foods_up mt-4"></div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    /*
   Συνάρτηση debounce για να περιορίσουμε τη συχνότητα εκτέλεσης μιας συνάρτησης.
   Αποθηκεύει ένα timeout και αν η συνάρτηση κληθεί ξανά πριν λήξει το timeout,
   ακυρώνει την προηγούμενη κλήση και ξεκινάει πάλι.
    */
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

    // Function to get current date and time in 'YYYY-MM-DDTHH:MM:SS' format
    function getCurrentDateTime() {
        const now = new Date();
        const year = now.getFullYear();
        const month = ('0' + (now.getMonth() + 1)).slice(-2);
        const day = ('0' + now.getDate()).slice(-2);
        const hours = ('0' + now.getHours()).slice(-2);
        const minutes = ('0' + now.getMinutes()).slice(-2);
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    /*
   Συνάρτηση που μετατρέπει ένα timestamp (σε μορφή milliseconds, π.χ. από new Date().getTime())
   σε datetime-local string "YYYY-MM-DDTHH:MM".
    */
    function formatDateTimeForInput(timestamp) {
        const date = new Date(timestamp);
        const year = date.getFullYear();
        const month = ('0' + (date.getMonth() +1)).slice(-2);
        const day = ('0' + date.getDate()).slice(-2);
        const hours = ('0' + date.getHours()).slice(-2);
        const minutes = ('0' + date.getMinutes()).slice(-2);
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }


    $(document).ready(function() {
        // Συνάρτηση που ενημερώνει το χρώμα φόντου με βάση την τιμή της γλυκόζης (χαμηλή, φυσιολογική, υψηλή).
        function updateGlucoseFieldColor(inputField) {
            // Προσθέτουμε transition (στην αλλαγή background-color) στο input field
            inputField.style.transition = "background-color 0.3s ease";

            // Μετατρέπουμε την τιμή σε αριθμό
            var value = parseFloat(inputField.value);

            // Έλεγχος αν είναι κενό ή μη έγκυρο (NaN)
            if (isNaN(value) || value === '') {
                // Default background color
                inputField.style.backgroundColor = "white"; // Or your default color
            } else if (value < 80) {
                // Low glucose level background color
                inputField.style.backgroundColor = "#ffffe0"; // Light yellow
            } else if (value >= 80 && value <= 180) {
                // Normal glucose level background color
                inputField.style.backgroundColor = "#90ee90"; // Light green
            } else if (value > 180) {
                // High glucose level background color
                inputField.style.backgroundColor = "#ffcccb"; // Light red
            }
        }


        /*
        Συνδέουμε event listeners στα πεδία γλυκόζης της φόρμας "create_row"
        ώστε να ενημερώνεται το χρώμα φόντου όταν αλλάζει η τιμή.
        */
        $('#glucose_old, #glucose_new').on('input', function() {
            updateGlucoseFieldColor(this);
        });

        /*
        Συνδέουμε event listeners στα πεδία γλυκόζης της φόρμας "update_row"
        ώστε να ενημερώνεται το χρώμα φόντου όταν αλλάζει η τιμή.
        */
        $('#glucose_old_up, #glucose_new_up').on('input', function() {
            updateGlucoseFieldColor(this);
        });

        /*
       Αρχικοποιούμε τα χρώματα φόντου με βάση τις ήδη υπάρχουσες τιμές,
       σε περίπτωση που έχουμε προφορτωμένα δεδομένα στη σελίδα.
        */
        updateGlucoseFieldColor($('#glucose_old')[0]);
        updateGlucoseFieldColor($('#glucose_new')[0]);
        updateGlucoseFieldColor($('#glucose_old_up')[0]);
        updateGlucoseFieldColor($('#glucose_new_up')[0]);

        /*
       Όταν ο χρήστης κάνει κλικ στο κουμπί .create,
       "καθαρίζουμε" τα πεδία της φόρμας δημιουργίας νέας εγγραφής
       και εμφανίζουμε το modal #create_row.
       */
        $('.create').click(function(){
            $('#glucose_old').val('');
            $('#food_carbo').val('');
            $('#insulin').val('');
            $('#glucose_new').val('');
            $('#search-food').val('');
            $('.hidden-foods').empty();
            $('.show_cal_create').hide();
            $('.remove_create').hide();
            $('.remove_carbo_create').hide();
            // Ορισμός του timestamp input στην τρέχουσα ημερομηνία και ώρα
            $('#timestamp').val(getCurrentDateTime());
            // Εμφάνιση του modal
            $('#create_row').fadeIn();

            // Reset χρωμάτων στα πεδία γλυκόζης
            $('#glucose_old').trigger('input');
            $('#glucose_new').trigger('input');
        });

        /*
        Μεταβλητή counter για να δημιουργούμε μοναδικά IDs κατά την αναζήτηση τροφίμων (search).
        Κάθε φορά που προσθέτουμε μια νέα γραμμή φαγητού, αυξάνουμε την τιμή της.
        */
        let counter = 1;

        /*
        Όταν ο χρήστης πατάει το κουμπί #search-button (αναζήτηση τροφίμων),
        παίρνουμε το κείμενο από το #search-food, δημιουργούμε νέο HTML τμήμα πίνακα (table row)
        και το προσθέτουμε στο .hidden-foods.
        */
        $('#search-button').click(function () {
            // Λαμβάνουμε την τιμή από το πεδίο αναζήτησης τροφίμων
            const key = $('#search-food').val();
            // Δημιουργούμε ένα αντικείμενο Data που περιέχει το "food" = key
            const Data = { food: key };

            // Αν η τιμή δεν είναι κενή
            if (key !== null && key !== '') {
                // Δημιουργούμε HTML για τη νέα γραμμή με select και κουμπί "Calculate Carbohydrates"
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

                // Προσθέτουμε τη νέα γραμμή στο .hidden-foods
                $('.hidden-foods').append(newRowHtml);

                // Καλούμε τη συνάρτηση fetchFoodList για να φέρουμε λίστα τροφίμων μέσω Axios
                fetchFoodList(Data, counter);

                // Συνδέουμε το event "Calculate Carbohydrates" στο κουμπί για αυτή τη γραμμή
                attachCalculationButtonEvent(counter);

                // Αυξάνουμε τον counter για την επόμενη γραμμή
                counter++;
            }
        });

        /*
       Συνάρτηση που καλεί το backend (/food-list) για να ανακτήσει τη λίστα τροφίμων
       με βάση το όνομα ή την αναζήτηση και γεμίζει τα <select> με επιλογές.
        */
        function fetchFoodList(data, currentCounter) {
            axios.post('/food-list', {
                food: data.food // Στέλνουμε το κλειδί "food" στο σώμα του αιτήματος
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    // Συμπεριλαμβάνουμε το CSRF token στις κεφαλίδες (headers) για ασφάλεια
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Ensure CSRF token is included
                }
            })
                .then(response => {
                    // Από την απάντηση παίρνουμε τη λίστα τροφίμων ή κενή λίστα
                    const foodList = response.data.foods|| [];

                    // Χρησιμοποιούμε ένα Set για να αποθηκεύουμε μοναδικές μονάδες μερίδας (serving_unit)
                    const uniqueServingUnits = new Set(); // Using a Set to store unique serving units

                    // Παίρνουμε τα αντίστοιχα select για food και serving
                    const selectFood = document.getElementById(`select-food${currentCounter}`);
                    const selectServing = document.getElementById(`serving${currentCounter}`);

                    // Ελέγχουμε αν υπάρχουν τα select elements
                    if (selectFood && selectServing) {
                        // Αν δε βρέθηκαν αποτελέσματα
                        if (foodList.length === 0) {
                            console.log(`No food items found for query: ${data.food}`);
                            return;
                        }

                        /*
                       Για κάθε στοιχείο της λίστας τροφίμων προσθέτουμε επιλογές (option)
                       στο selectFood (food name) και προσθέτουμε τη μοναδική μονάδα στο selectServing.
                        */
                        foodList.forEach(food => {
                            // Προσθέτουμε την επιλογή στο select-food
                            const foodOption = new Option(food.food_name, food.food_name);
                            selectFood.add(foodOption);

                            // Προσθέτουμε τη μονάδα μερίδας (serving_unit) αν δεν υπάρχει ήδη
                            if (!uniqueServingUnits.has(food.serving_unit)) {
                                uniqueServingUnits.add(food.serving_unit);
                                /*
                               Δημιουργούμε ένα option που έχει ως value τον "nf_total_carbohydrate" (υδατάνθρακες),
                               ενώ ως κείμενο δείχνουμε το serving_unit (π.χ. "grams" ή "cup" κ.λπ.).
                                */
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

        /*
       Συνάρτηση που αναθέτει στο κουμπί "Calculate Carbohydrates" της εκάστοτε γραμμής
       να υπολογίζει τους υδατάνθρακες (carbohydred) όταν πατηθεί,
       και να ενημερώνει το συνολικό άθροισμα υδατανθράκων.
        */
        function attachCalculationButtonEvent(counter) {
            const calculateBtn = document.getElementById(`calculate-btn${counter}`);
            const selectServing = document.getElementById(`serving${counter}`);
            const quantityInput = document.getElementById(`quantity${counter}`);
            const carbInput = document.getElementById(`carbohydred${counter}`);

            // Όταν πατηθεί το κουμπί υπολογισμού...
            calculateBtn.addEventListener('click', function() {
                calculateCarbohydrates(selectServing, quantityInput, carbInput);
                updateTotalCarbohydrates(); // Ανανεώνουμε το συνολικό άθροισμα υδατανθράκων
            });

            // Παρακολουθούμε το πεδίο carbInput σε πραγματικό χρόνο (input event)
            carbInput.addEventListener('input', updateTotalCarbohydrates);
        }

        // Function to calculate carbohydrates and update the input field
        function calculateCarbohydrates(servingSelect, quantityInput, carbInput) {
            // Παίρνουμε την επιλεγμένη επιλογή από το servingSelect
            const selectedServing = servingSelect.options[servingSelect.selectedIndex];
            // Το value περιέχει τους υδατάνθρακες ανά 1 μερίδα
            const carbsPerServing = parseFloat(selectedServing.value);
            // Η ποσότητα που πληκτρολογεί ο χρήστης
            const quantity = parseFloat(quantityInput.value) || 0;

            // Αν και οι δύο τιμές είναι έγκυρες
            if (!isNaN(carbsPerServing) && !isNaN(quantity)) {
                const totalCarbs = carbsPerServing * quantity;
                // Στρογγυλοποιούμε στα 2 δεκαδικά
                carbInput.value = totalCarbs.toFixed(2);
            } else {
                // Διαγράφουμε την τιμή αν τα δεδομένα δεν είναι έγκυρα
                carbInput.value = '';
            }

            // Κάθε φορά που αλλάζει κάτι στους υδατάνθρακες, ενημερώνουμε το σύνολο
            updateTotalCarbohydrates();
        }


        /*
         * Συνάρτηση που αθροίζει όλους τους υδατάνθρακες (CarboHydred)
         * από όλα τα input πεδία που ξεκινούν με id="carbohydred".
         * Ενημερώνει το πεδίο #food_carbo με το συνολικό αποτέλεσμα
         * και εμφανίζει το κουμπί .remove_carbo_create.
         */
        // Function to sum all carbohydrates and update the total field
        function updateTotalCarbohydrates() {
            let totalCarbs = 0;

            // Εντοπίζουμε όλα τα input που ξεκινούν με id="carbohydred"
            document.querySelectorAll('input[id^="carbohydred"]').forEach(input => {
                // Μετατρέπουμε την τιμή σε αριθμό, αν είναι κενή το θεωρούμε 0
                const value = parseFloat(input.value) || 0;
                totalCarbs += value;
            });

            // Ενημερώνουμε το πεδίο #food_carbo με το άθροισμα
            $('#food_carbo').val(totalCarbs);
            // Εμφάνιση του κουμπιού (x) για διαγραφή των carbs
            $('.remove_carbo_create').show();
        }

        // Χρησιμοποιούμε event delegation
        $(document).on('click', '.row_tr', function(event) {
            // Παίρνουμε τα data attributes από την επιλεγμένη γραμμή (.row_tr)
            const {id, timestamp, glucose_old, glucose_new, food, insulin} = $(this).data();

            // Εντοπίζουμε το κοντινότερο button διαγραφής, εάν υπάρχει
            const $button = $(event.target).closest('.td_delete').find('button');
            if ($button.length) {
                /*
                 * Ανοίγουμε τη φόρμα διαγραφής (delete):
                 *  - Θέτουμε το ID της εγγραφής που θέλουμε να διαγράψουμε,
                 *  - Εμφανίζουμε στην .info τα στοιχεία της εγγραφής,
                 *  - Εμφανίζουμε το modal #row_delete.
                 */
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

            /*
             * Σε αντίθετη περίπτωση, φορτώνουμε την φόρμα update (edit operation):
             *  - Συμπληρώνουμε τα κατάλληλα πεδία με τα data,
             *  - Ανοίγουμε το modal #update_row.
             */
            $('#id_up').val(id);
            $('#timestamp_up').val(formatDateTimeForInput(timestamp));

            // Θέτουμε τις τιμές γλυκόζης και ενεργοποιούμε το .trigger('input')
            // για να ενημερωθεί το χρώμα φόντου με βάση τη συνάρτηση updateGlucoseFieldColor.
            $('#glucose_old_up').val(glucose_old).trigger('input');
            $('#glucose_new_up').val(glucose_new).trigger('input');

            // Θέτουμε το food_carbo (φύλαξη παλιάς τιμής σε temp fields)
            $('#food_carbo_up').val(food);
            $('#food_carbo_temp').val(food);
            $('#first_food_carbo').val(food);

            // Αρχικά κρύβουμε το κουμπί (x) αφαίρεσης για την τροποποίηση υδατανθράκων
            $('.remove_carbo_update').hide();

            // Θέτουμε τη δόση ινσουλίνης
            $('#insulin_up').val(insulin);

            // Καθαρίζουμε τυχόν προηγούμενη αναζήτηση φαγητού
            $('#search-food_up').val('');
            $('.remove_update').hide();

            // Αδειάζουμε το container που θα γεμίσουμε με τροφές
            $('.hidden-foods_up').empty();

            // Τέλος, προβάλλουμε το modal για update
            $('#update_row').fadeIn();
        });


        // counter_up: Μετρητής για τις νέες γραμμές αναζήτησης τροφίμων στη φόρμα ενημέρωσης (update).
        let counter_up = 1;

        /*
         * Όταν ο χρήστης κάνει κλικ στο κουμπί #search-button_up
         * (αναζήτηση τροφίμων στη φόρμα update),
         * δημιουργούμε μια νέα γραμμή με select elements κ.λπ.
         */
        $('#search-button_up').click(function () {
            // Παίρνουμε την τιμή που εισήγαγε ο χρήστης
            const key = $('#search-food_up').val();
            const Data = { food: key };

            // Ελέγχουμε αν είναι κενή
            if (key !== null && key !== '') {
                // Δημιουργούμε το HTML για τη νέα γραμμή
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

                // Προσθέτουμε το νέο κομμάτι HTML στη λίστα .hidden-foods_up
                $('.hidden-foods_up').append(newRowHtml);

                // Φέρνουμε τη λίστα τροφίμων μέσω Axios (post στο /food-list)
                fetchFoodList_up(Data, counter_up);

                // Συνδέουμε την event συνάρτηση στο κουμπί "Calculate Carbohydrates"
                attachCalculationButtonEvent_up(counter_up);

                // Αυξάνουμε τον μετρητή για την επόμενη γραμμή
                counter_up++;
            }
        });

        /*
         * Συνάρτηση που φέρνει τη λίστα τροφίμων (food-list) από το backend,
         * γεμίζει τα select στοιχεία (#select-food_upN και #serving_upN) με τα αποτελέσματα.
         */
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
                    // Λαμβάνουμε τη λίστα τροφίμων ή κενή λίστα αν δεν βρεθούν αποτελέσματα
                    const foodList = response.data.foods|| [];

                    // Χρησιμοποιούμε ένα Set για να κρατάμε μοναδικές μονάδες μερίδας
                    const uniqueServingUnits_up = new Set();

                    // Εντοπίζουμε τα select στοιχεία
                    const selectFood = document.getElementById(`select-food_up${currentCounter}`);
                    const selectServing = document.getElementById(`serving_up${currentCounter}`);

                    if (selectFood && selectServing) {
                        if (foodList.length === 0) {
                            console.log(`No food items found for query: ${data.food}`);
                            return;
                        }

                        /*
                         * Γεμίζουμε το selectFood με τα food_name
                         * και το selectServing με τις μοναδικές serving_unit
                         * (όπου η τιμή value = nf_total_carbohydrate).
                         */
                        foodList.forEach(food => {
                            // Προσθέτουμε επιλογή στο select-food
                            const foodOption = new Option(food.food_name, food.food_name);
                            selectFood.add(foodOption);

                            // Ελέγχουμε αν η μονάδα μερίδας υπάρχει ήδη
                            if (!uniqueServingUnits_up.has(food.serving_unit)) {
                                uniqueServingUnits_up.add(food.serving_unit);
                                // Στο value αποθηκεύουμε τους υδατάνθρακες ανά μερίδα (nf_total_carbohydrate)
                                const servingOption = new Option(food.serving_unit, food.nf_total_carbohydrate);
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

        /*
         * Συνάρτηση που συνδέει το κουμπί "Calculate Carbohydrates"
         * με τον υπολογισμό των υδατανθράκων σε κάθε νέα γραμμή της φόρμας ενημέρωσης (update).
         */
        function attachCalculationButtonEvent_up(counter) {
            // Εντοπίζουμε τα στοιχεία της γραμμής
            const calculateBtn = document.getElementById(`calculate-btn_up${counter}`);
            const selectServing = document.getElementById(`serving_up${counter}`);
            const quantityInput = document.getElementById(`quantity_up${counter}`);
            const carbInput = document.getElementById(`carbohydred_up${counter}`);

            // Event listener for the calculate button
            // Όταν ο χρήστης πατάει "Calculate Carbohydrates"...
            calculateBtn.addEventListener('click', function() {
                calculateCarbohydrates_up(selectServing, quantityInput, carbInput);
                updateTotalCarbohydrates_up(); // Update total carbohydrates after calculation
            });

            // Παρακολουθούμε τυχόν αλλαγές στο πεδίο των υδατανθράκων (carbohydred)
            carbInput.addEventListener('input', updateTotalCarbohydrates_up);
        }

        /*
         * Συνάρτηση που υπολογίζει τους υδατάνθρακες (carbohydred) για τη φόρμα update
         * με βάση το πόσους υδατάνθρακες ανά μερίδα (value του select)
         * και την ποσότητα (quantity).
         */
        function calculateCarbohydrates_up(servingSelect, quantityInput, carbInput) {
            // Παίρνουμε την επιλεγμένη μερίδα
            const selectedServing = servingSelect.options[servingSelect.selectedIndex];
            // Η τιμή του selectedServing είναι οι υδατάνθρακες ανά 1 μερίδα
            const carbsPerServing = parseFloat(selectedServing.value);
            // Μετατρέπουμε την ποσότητα σε αριθμό, αν είναι κενή επιστρέφει 0
            const quantity = parseFloat(quantityInput.value) || 0;

            if (!isNaN(carbsPerServing) && !isNaN(quantity)) {
                // totalCarbs = carbs ανά μερίδα * ποσότητα
                const totalCarbs = carbsPerServing * quantity;
                // Στρογγυλοποιούμε στα 2 δεκαδικά
                carbInput.value = totalCarbs.toFixed(2);
            } else {
                // Αν δεν είναι έγκυρο, καθαρίζουμε το πεδίο
                carbInput.value = '';
            }

            // Μετά από κάθε υπολογισμό, ενημερώνουμε το σύνολο υδατανθράκων
            updateTotalCarbohydrates_up();
        }






        /*
         * Συνάρτηση που αθροίζει όλους τους υδατάνθρακες (carbohydred_up)
         * από τα input πεδία του Update Modal και ενημερώνει το #food_carbo_up.
         */
        function updateTotalCarbohydrates_up() {
            let totalCarbs = 0;

            /*
             * Παίρνουμε την ήδη υπάρχουσα τιμή υδατανθράκων (π.χ. αν υπήρχαν
             * από την προηγούμενη αποθήκευση), αποθηκευμένη στο #food_carbo_temp,
             * τη μετατρέπουμε σε float και με toFixed(2) κρατάμε 2 δεκαδικά.
             */
            const val_before = parseFloat((parseFloat($('#food_carbo_temp').val()) || 0.0).toFixed(2));

            // Κάνουμε loop σε όλα τα input πεδία που το id τους αρχίζει με "carbohydred_up"
            document.querySelectorAll('input[id^="carbohydred_up"]').forEach(input => {
                // Μετατρέπουμε την τιμή σε αριθμό, προεπιλογή 0 αν είναι κενή
                const value = parseFloat(input.value) || 0;
                totalCarbs += value;
            });

            // Προσθέτουμε τη "παλιά" τιμή, αν υπάρχει
            totalCarbs += val_before;
            // Ενημερώνουμε το πεδίο #food_carbo_up (2 δεκαδικά)
            $('#food_carbo_up').val(totalCarbs.toFixed(2));
            // Εμφανίζουμε το κουμπί (.remove_carbo_update) για διαγραφή carbs
            $('.remove_carbo_update').show();
        }

        /*
         * Κλείνουμε όλα τα popups (modals) όταν πατηθεί το .popup-close κουμπί.
         * Επίσης μηδενίζουμε τα πεδία γλυκόζης και ενημερώνουμε το χρώμα φόντου (glucose input).
         */
        $(".popup-close").click(function () {
            $(".popup").fadeOut();

            // Επαναφορά πεδίων γλυκόζης και αντίστοιχων background colors
            $('.glucose-input').each(function() {
                $(this).val('');
                updateGlucoseFieldColor(this);
            });
        });

        $(document).ready(function() {
            /*
             * Συνάρτηση που εμφανίζει ή κρύβει το div .show_cal_create
             * ανάλογα με το αν υπάρχει τιμή στο πεδίο #glucose_old.
             */
            function toggleShowCalCreate() {
                const value = $('#glucose_old').val();
                if (value !== '') {
                    // Αν υπάρχει τιμή, δείχνουμε το .show_cal_create
                    $('.show_cal_create').show(); // Show the div if there's a value
                } else {
                    // Αν είναι κενό, κρύβουμε το .show_cal_create
                    $('.show_cal_create').hide(); // Hide the div if empty
                }
            }

            // Καλούμε τη συνάρτηση κάθε φορά που αλλάζει το περιεχόμενο του #glucose_old
            $('#glucose_old').on('input', function() {
                toggleShowCalCreate();
            });

            // Αρχική κλήση για ρύθμιση του div σωστά όταν φορτώνει η σελίδα
            toggleShowCalCreate();
        });

        /*
         * Πατώντας το κουμπί #predict_create, κάνουμε αίτημα για υπολογισμό δόσης ινσουλίνης
         * (calculate insulin dose) με τα τρέχοντα δεδομένα: glucose_old, glucose_new, food_carbo κ.λπ.
         */
        $('#predict_create').click(function() {
            // Συλλέγουμε τα δεδομένα από τα input πεδία
            const glucose_old = parseFloat($('#glucose_old').val()) || 0;
            const glucose_new = parseFloat($('#glucose_new').val()) || 0;
            const food_carbo = parseFloat($('#food_carbo').val()) || 0;
            const algorithm = $('#algorithm_create').val() ;

            // Έλεγχος εγκυρότητας: αν δεν έχει επιλεγεί αλγόριθμος ή λείπει το glucose_old
            if (isNaN(glucose_old) || algorithm==='') {
                $('.error_create').html("In order to calculate the insulin dose prediction, the fields 'insulin before' and 'select algorithm' must be filled out.");
                return;
            }

            // Δημιουργούμε ένα αντικείμενο data με τα σχετικά πεδία
            const data = {
                glucose_old: glucose_old,
                glucose_new: glucose_new,
                food_carbo: food_carbo,
                algorithm: algorithm,
            };

            /*
             * Κάνουμε GET αίτημα στο /predict περνώντας τα data ως query params.
             * Απαντάει το backend με το (response.data.message) που περιέχει τη συνιστώμενη δόση.
             */
            axios.get('/predict', {
                params: data, // Pass the data as query parameters
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => {
                    // Παίρνουμε τη δόση (message), τη μετατρέπουμε σε αριθμό, και στρογγυλοποιούμε
                    let message = parseFloat(response.data.message); // Convert message to a number if it's not already
                    message = Math.round(message); // Στρογγυλοποίηση στον κοντινότερο ακέραιο

                    // Καταχωρούμε τη στρογγυλοποιημένη δόση στο πεδίο #insulin
                    $('#insulin').val(message);

                    // Εμφανίζουμε το κουμπί (x) για να μπορούμε να αφαιρέσουμε τη δόση
                    $('.remove_create').show();
                })
                .catch(error => {
                    console.error('Error fetching prediction:', error);
                });
        });

        /*
         * Αντίστοιχη λειτουργία υπολογισμού δόσης ινσουλίνης στο update modal,
         * πατώντας το κουμπί #predict_upadate.
         */
        $('#predict_upadate').click(function() {
            // Συλλέγουμε τα δεδομένα από τα input πεδία
            const glucose_old = parseFloat($('#glucose_old_up').val()) || 0;
            const glucose_new = parseFloat($('#glucose_new_up').val()) || 0;
            const food_carbo = parseFloat($('#food_carbo_up').val()) || 0;
            const algorithm = $('#algorithm_update').val() ;

            // Έλεγχος εγκυρότητας
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

            // GET αίτημα στο /predict με τα στοιχεία
            axios.get('/predict', {
                params: data,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => {
                    let message = parseFloat(response.data.message);
                    message = Math.round(message);

                    // Ορίζουμε τη στρογγυλοποιημένη τιμή στο πεδίο #insulin_up
                    $('#insulin_up').val(message);
                    // Εμφανίζουμε το κουμπί (x) που "καθαρίζει" την τιμή
                    $('.remove_update').show()
                })
                .catch(error => {
                    console.error('Error fetching prediction:', error);
                });
        });

        /*
         * remove_create: Διαγράφει την τιμή του #insulin και κρύβει το κουμπί (x).
         * remove_update: Διαγράφει την τιμή του #insulin_up και κρύβει το κουμπί (x).
         */
        $('.remove_create').click(function(){
            $('#insulin').val('');
            $(this).hide();
        });
        $('.remove_update').click(function(){
            $('#insulin_up').val('');
            $(this).hide();
        });

        /*
         * remove_carbo_create: Καθαρίζει το #food_carbo και κρύβει το κουμπί (x).
         * remove_carbo_update: Επαναφέρει το #food_carbo_up στην προηγούμενη τιμή
         * (που είναι αποθηκευμένη στο #first_food_carbo) και κρύβει το κουμπί (x).
         */
        $('.remove_carbo_create').click(function(){
            $('#food_carbo').val('');
            $(this).hide();
        });
        $('.remove_carbo_update').click(function(){
            var food_first= parseFloat($('#first_food_carbo').val())||"";
            $('#food_carbo_up').val(food_first);
            $(this).hide();
        });

        /*
         * Προσθέτουμε συνάρτηση debounce (από το προηγούμενο τμήμα κώδικα)
         * στα πεδία αναζήτησης #search-food και #search-food_up.
         * Κάθε φορά που ο χρήστης πληκτρολογεί, περιμένουμε 200ms πριν καλέσουμε το server
         * για autocomplete, ώστε να μην στέλνουμε αιτήματα σε κάθε πλήκτρο.
         */
        $('#search-food').on('input', debounce(function() {
            var food = $(this).val(); // Η τιμή από το input

            $('#autocomplete_create').empty();

            if (food.length > 0) {
                // Κάνουμε GET στο /autocomplete περνώντας το food ως query param
                axios.get('/autocomplete', {
                    params: {
                        food: food
                    }
                })
                    .then(function (response) {
                        var foodNames = response.data.food_names;

                        // Για κάθε όνομα τροφίμου, δημιουργούμε ένα <li> στο autocomplete
                        foodNames.forEach(function(name) {
                            const listItem = $('<li></li>').text(name);
                            $('#autocomplete_create').append(listItem);
                        });

                        // Εμφανίζουμε τη λίστα των προτάσεων
                        $('#autocomplete_create').show();
                    })
                    .catch(function (error) {
                        console.log('Error fetching autocomplete results.', error);
                    });
            } else {
                // Αν το πεδίο είναι κενό, κρύβουμε το autocomplete
                $('#autocomplete_create').hide();
            }
        }, 200)); // 200 milliseconds debounce time

        $('#search-food_up').on('input', debounce(function() {
            var food = $(this).val();

            $('#autocomplete_update').empty();

            if (food.length > 0) {
                axios.get('/autocomplete', {
                    params: {
                        food: food
                    }
                })
                    .then(function (response) {
                        var foodNames = response.data.food_names;

                        foodNames.forEach(function(name) {
                            const listItem = $('<li></li>').text(name);
                            $('#autocomplete_update').append(listItem);
                        });

                        // Εμφανίζουμε τη λίστα προτάσεων
                        $('#autocomplete_update').show();
                    })
                    .catch(function (error) {
                        console.log('Error fetching autocomplete results.', error);
                    });
            } else {
                $('#autocomplete_update').hide();
            }
        }, 200)); // 200 milliseconds debounce time

        /*
         * Απόκρυψη των προτάσεων (autocomplete lists) αν ο χρήστης κάνει κλικ εκτός.
         * Ελέγχουμε αν το κλικ είναι έξω από τα #search-food / #autocomplete_create
         * ή #search-food_up / #autocomplete_update.
         */
        $(document).on('click', function(e) {
            if (!$("#search-food").is(e.target) && !$('#autocomplete_create').is(e.target) && $('#autocomplete_create').has(e.target).length === 0) {
                $('#autocomplete_create').hide();
            }
            if (!$("#search-food_up").is(e.target) && !$('#autocomplete_update').is(e.target) && $('#autocomplete_update').has(e.target).length === 0) {
                $('#autocomplete_update').hide();
            }
        });

        /*
         * Event delegation για δημιουργούμενα δυναμικά <li> στο #autocomplete_create:
         * Όταν ο χρήστης κάνει κλικ σε ένα <li>, παίρνουμε το κείμενό του
         * και το βάζουμε στο input #search-food. Μετά κρύβουμε τη λίστα.
         */
        $('#autocomplete_create').on('click', 'li', function() {
            const selectedText = $(this).text();
            $("#search-food").val(selectedText);
            $('#autocomplete_create').empty();
            $('#autocomplete_create').hide();
        });

        /*
         * Αντίστοιχα για το update modal:
         * Όταν ο χρήστης κάνει κλικ σε ένα <li> στο #autocomplete_update,
         * βάζουμε το κείμενο στο #search-food_up και κρύβουμε τη λίστα.
         */
        $('#autocomplete_update').on('click', 'li', function() {
            const selectedText = $(this).text();
            $("#search-food_up").val(selectedText);
            $('#autocomplete_update').empty();
            $('#autocomplete_update').hide();
        });

    });

    /*
     * Μετά το φόρτωμα του DOM (DOMContentLoaded), ρυθμίζουμε κάποια inputs τύπου datetime
     * για να μη συμπεριλαμβάνουν δευτερόλεπτα.
     */
    document.addEventListener('DOMContentLoaded', function () {
        const timestampInput = document.getElementById('timestamp');
        if (timestampInput) {
            // Σε κάθε αλλαγή τιμής (change), χωρίζουμε "YYYY-MM-DD" και "HH:MM"
            timestampInput.addEventListener('change', function () {
                const timestamp = this.value; // Get the value
                if(timestamp){
                    const[date,time] = timestamp.split("T");
                    const[hours,minutes] = time.split(":");
                    timestampInput.value = `${date}T${hours}:${minutes}`;
                }
            });
        }
        const timestampUpInput = document.getElementById('timestamp_up');
        if (timestampUpInput) {
            timestampUpInput.addEventListener('change', function () {
                const timestamp = this.value;
                if(timestamp){
                    const[date,time] = timestamp.split("T");
                    const[hours,minutes] = time.split(":");
                    timestampUpInput.value = `${date}T${hours}:${minutes}`;
                }
            });
        }

    });

    /*
     * Συνάρτηση preserveScroll: αποθηκεύουμε τη τρέχουσα κάθετη θέση κύλισης (scrollY)
     * στο localStorage, ώστε να μπορούμε να την επαναφέρουμε μετά από submit ή reload.
     */
    function preserveScroll() {
        localStorage.setItem('scrollPosition', window.scrollY);
    }

    /*
     * Μετά το DOMContentLoaded, ελέγχουμε αν υπάρχει scrollPosition στο localStorage
     * και μετακινούμε τη σελίδα σε εκείνη τη θέση. Κατόπιν το διαγράφουμε από το localStorage.
     */
    document.addEventListener('DOMContentLoaded', function() {
        const scrollPosition = localStorage.getItem('scrollPosition');
        if (scrollPosition) {
            window.scrollTo(0, parseInt(scrollPosition, 10));
            localStorage.removeItem('scrollPosition');
        }
    });

</script>
