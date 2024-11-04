from flask import Flask, request, jsonify
from flask_cors import CORS
import numpy as np
import pandas as pd
from datetime import datetime
from sklearn.linear_model import LinearRegression
from sklearn.ensemble import GradientBoostingRegressor, RandomForestRegressor
from sklearn.preprocessing import StandardScaler
from sklearn.metrics.pairwise import cosine_similarity
import pymysql
import logging
import threading

# Initialize the Flask application
app = Flask(__name__)

# Enable Cross-Origin Resource Sharing (CORS) to allow requests from different origins
CORS(app)

# Set up logging with INFO level to capture important events
logging.basicConfig(level=logging.INFO)

# Global variables to cache models and data timestamps
cached_models = {}        # Dictionary to store the trained models and related data
last_data_timestamp = None  # Variable to store the timestamp of the last data update
model_lock = threading.Lock()  # Lock to ensure thread safety during model training

# We will not use global cached_models, as models are now per patient request

def get_max_timestamp():
    """
    Fetch the maximum updated_at or created_at timestamp from both patients_statistics and patients tables.
    This function checks the most recent update in the database to determine if retraining is necessary.
    """
    # Establish a connection to the MySQL database
    db_connection = pymysql.connect(
        host='127.0.0.1',     # Database host address
        user='root',          # Database username
        password='',          # Database password
        database='insulin_db' # Name of the database
    )
    cursor = db_connection.cursor()
    
    # Get the maximum of created_at and updated_at timestamps from patients_statistics table
    cursor.execute("SELECT GREATEST(MAX(created_at), MAX(updated_at)) FROM patients_statistics")
    stats_timestamp = cursor.fetchone()[0] or datetime.min
    
    # Get the maximum of created_at and updated_at timestamps from patients table
    cursor.execute("SELECT GREATEST(MAX(created_at), MAX(updated_at)) FROM patients")
    patients_timestamp = cursor.fetchone()[0] or datetime.min  # Use datetime.min if no timestamp is found
    
    # Close the database connection
    db_connection.close()
    
    # Return the most recent timestamp between the two tables
    return max(stats_timestamp, patients_timestamp)

def load_and_train_models(patient_id, age, weight, k=5):
    """
    Load data from the database, preprocess it, select k-nearest patients based on age and weight,
    train machine learning models using data from these patients, and return the models.
    """
    # Load data from the database
    db_connection = pymysql.connect(
        host='127.0.0.1',     # Database host address
        user='root',          # Database username
        password='',          # Database password
        database='insulin_db' # Name of the database
    )

    # SQL query to fetch relevant data from patients_statistics and patients tables
    query = """
        SELECT ps.patient_id, ps.glucose_old, ps.insulin_dose, ps.food_carbo, ps.glucose_new, ps.created_at, ps.weight,
               p.birth_at, p.diagnosis, p.gender
        FROM patients_statistics ps
        JOIN patients p ON ps.patient_id = p.id
    """

    # Read the SQL query into a Pandas DataFrame
    df = pd.read_sql(query, con=db_connection)
    db_connection.close()  # Close the database connection

    # Preprocessing steps
    df['created_at'] = pd.to_datetime(df['created_at'])  # Convert 'created_at' to datetime
    df['insulin_dose'] = df['insulin_dose'].astype(float)  # Ensure 'insulin_dose' is float
    df['glucose_old'] = df['glucose_old'].astype(float)    # Ensure 'glucose_old' is float
    df['glucose_new'] = df['glucose_new'].astype(float)    # Ensure 'glucose_new' is float
    df['food_carbo'] = df['food_carbo'].fillna(0).astype(float)  # Fill missing 'food_carbo' with 0 and convert to float
    df['birth_at'] = pd.to_datetime(df['birth_at'])  # Convert 'birth_at' to datetime
    # Calculate 'age' as the difference between today and 'birth_at' in years
    df['age'] = ((pd.to_datetime('today') - df['birth_at']).dt.days / 365.25).astype(int)
    # Handle missing 'weight' values by filling with the mean weight
    df['weight'] = df['weight'].fillna(df['weight'].mean()).astype(float)
    # Map 'gender' from categorical to numerical values (male: 0, female: 1)
    df['gender'] = df['gender'].map({'male': 0, 'female': 1}).fillna(0).astype(int)
    # Map 'diagnosis' from categorical to numerical values (Type I: 0, Type II: 1)
    df['diagnosis'] = df['diagnosis'].map({'Type I': 0, 'Type II': 1}).fillna(0).astype(int)

    # Apply filtering conditions as needed
    df = df[df['insulin_dose'] <= 15]  # Keep only records where 'insulin_dose' is less than or equal to 15

    # Sort the DataFrame by 'created_at' timestamp
    df = df.sort_values('created_at')

    # Now, compute cosine similarity between the patient and all other patients based on age and weight

    # Create a DataFrame with unique patients and their age and weight
    patient_info = df[['patient_id', 'age', 'weight']].drop_duplicates('patient_id').set_index('patient_id')

    # Extract the age and weight of the current patient
    current_patient_vector = np.array([[age, weight]])

    # Extract the age and weight of all other patients
    other_patients_vectors = patient_info[['age', 'weight']].values

    # Compute cosine similarity between current patient and all patients
    # Use sklearn's cosine_similarity function
    similarities = cosine_similarity(current_patient_vector, other_patients_vectors)[0]

    # Create a DataFrame for similarities
    similarity_df = pd.DataFrame({
        'patient_id': patient_info.index,
        'similarity': similarities
    })

    # Exclude the current patient from the list (if present)
    similarity_df = similarity_df[similarity_df['patient_id'] != patient_id]

    # Sort by similarity in descending order (higher similarity first)
    similarity_df = similarity_df.sort_values('similarity', ascending=False)

    # Select the k-nearest patients
    k_nearest_patient_ids = similarity_df['patient_id'].head(k).tolist()

        # Εκτύπωση των 5 k-nearest patients για έλεγχο
    print(f"k-nearest patients for patient_id {patient_id} (based on age and weight): {k_nearest_patient_ids}")

    # If less than k patients are available, adjust k
    actual_k = len(k_nearest_patient_ids)
    logging.info(f"Found {actual_k} nearest patients based on age and weight.")

    # Filter the main dataframe to include only data from these patients
    df_filtered = df[df['patient_id'].isin(k_nearest_patient_ids)]

    # If no data is available after filtering, fall back to using all data
    if df_filtered.empty:
        logging.warning("No data available from k-nearest patients. Using all available data.")
        df_filtered = df

    # Define features and target variable for training
    train_columns = ['glucose_old', 'food_carbo', 'age', 'weight', 'diagnosis', 'gender']

    # Split data into training and testing sets
    total_instances = len(df_filtered)  # Total number of instances in the filtered dataset
    if total_instances < 5:
        logging.warning("Not enough data from k-nearest patients. Using all available data.")
        df_filtered = df
        total_instances = len(df_filtered)
    train_number = int(total_instances * 0.8)  # 80% for training
    train_instances = df_filtered.iloc[:train_number]  # Training data
    test_instances = df_filtered.iloc[train_number:]   # Testing data

    # Exclude entries where 'glucose_new' is not within [80, 180] from the test set
    test_instances_filtered = test_instances[
        (test_instances['glucose_new'] >= 80) & (test_instances['glucose_new'] <= 180)
    ]

    if not test_instances_filtered.empty:
        test_instances = test_instances_filtered  # Use the filtered test set
    else:
        # If no test instances remain after filtering, log a warning
        logging.warning("Warning: No test instances after filtering. Proceeding with unfiltered test set.")

    # Prepare feature matrices (X) and target vectors (Y) for training
    train_X = train_instances[train_columns].values  # Training features
    train_Y = train_instances['insulin_dose'].values  # Training target variable

    # Prepare feature matrix for testing
    test_X = test_instances[train_columns].copy()

    # Handle the last instance in the test set by setting 'food_carbo' to zero
    if not test_X.empty:
        last_index = test_X.index[-1]
        test_X.at[last_index, 'food_carbo'] = 0  # Set 'food_carbo' to zero for the last instance

    # Convert test_X back to numpy array after modification
    test_X = test_X.values
    test_Y = test_instances['insulin_dose'].values  # Testing target variable

    # Standardize features using StandardScaler
    scaler_insulin = StandardScaler()
    train_X_scaled = scaler_insulin.fit_transform(train_X)  # Fit scaler on training data and transform
    test_X_scaled = scaler_insulin.transform(test_X)        # Transform testing data using the same scaler

    # Initialize machine learning models
    regr_insulin = LinearRegression()  # Linear Regression model
    grad_boost_regr_insulin = GradientBoostingRegressor()  # Gradient Boosting Regressor
    rand_forest_insulin = RandomForestRegressor(
        n_estimators=100, max_depth=2, criterion='squared_error', random_state=0
    )  # Random Forest Regressor

    # Train models on scaled training data
    regr_insulin.fit(train_X_scaled, train_Y)            # Train Linear Regression model
    grad_boost_regr_insulin.fit(train_X_scaled, train_Y) # Train Gradient Boosting Regressor
    rand_forest_insulin.fit(train_X_scaled, train_Y)     # Train Random Forest Regressor

    # Predict insulin doses using test data for each model
    y_pred_regr = regr_insulin.predict(test_X_scaled)            # Predictions from Linear Regression
    y_pred_grad_boost_regr = grad_boost_regr_insulin.predict(test_X_scaled)  # Predictions from Gradient Boosting
    y_pred_rand_forest = rand_forest_insulin.predict(test_X_scaled)          # Predictions from Random Forest

    # Create a DataFrame with test results
    test_results = test_instances.copy()
    test_results['pred_regr'] = y_pred_regr                         # Add Linear Regression predictions
    test_results['pred_grad_boost'] = y_pred_grad_boost_regr        # Add Gradient Boosting predictions
    test_results['pred_rand_forest'] = y_pred_rand_forest           # Add Random Forest predictions
    test_results['error_regr'] = test_results['insulin_dose'] - test_results['pred_regr']  # Error for Linear Regression
    test_results['error_grad_boost'] = test_results['insulin_dose'] - test_results['pred_grad_boost']  # Error for Gradient Boosting
    test_results['error_rand_forest'] = test_results['insulin_dose'] - test_results['pred_rand_forest']  # Error for Random Forest

    # Compute per-patient mean errors for each model
    patient_errors = test_results.groupby('patient_id').mean()[
        ['error_regr', 'error_grad_boost', 'error_rand_forest']
    ]

    # Since models are per request, we don't cache them
    models = {
        'linear_regression': regr_insulin,
        'gradient_boosting': grad_boost_regr_insulin,
        'random_forest': rand_forest_insulin,
        'scaler': scaler_insulin,
        'patient_errors': patient_errors,
        'train_columns': train_columns
    }

    logging.info("Models trained using data from k-nearest patients.")
    return models  # Return the models

@app.route('/predict_insulin', methods=['GET'])
def predict_insulin():
    """
    API endpoint to predict insulin dose based on input parameters.
    This function handles incoming GET requests, processes input, and returns predictions.
    """
    try:
        # Retrieve data from the query parameters
        glucose_old_input = request.args.get('glucose_old', '')       # Previous glucose level
        food_carbo_input = request.args.get('food_carbo', '0')        # Amount of carbohydrates in food
        algorithm = request.args.get('algorithm', '')                 # Algorithm to use for prediction
        patient_id_input = request.args.get('patient_id', '')         # Patient ID
        age_input = request.args.get('age', '')                       # Patient's age
        weight_input = request.args.get('weight', '')                 # Patient's weight
        diagnosis_input = request.args.get('diagnosis', '')           # Diagnosis type ('Type I' or 'Type II')
        gender_input = request.args.get('gender', '')                 # Gender ('male' or 'female')
        k_input = request.args.get('k', '5')                          # Number of nearest patients to consider

        # Input validation and preprocessing
        # Convert inputs to appropriate data types and handle errors
        try:
            glucose_old = float(glucose_old_input)  # Convert 'glucose_old' to float
            food_carbo = float(food_carbo_input)    # Convert 'food_carbo' to float
            patient_id = int(patient_id_input)      # Convert 'patient_id' to integer
            age = int(age_input)                    # Convert 'age' to integer
            weight = float(weight_input)            # Convert 'weight' to float
            k = int(k_input)                        # Convert 'k' to integer
            if k <= 0:
                k = 5  # Set default k if invalid
            # Map 'diagnosis' to numerical values
            if diagnosis_input == 'Type I':
                diagnosis = 0
            elif diagnosis_input == 'Type II':
                diagnosis = 1
            else:
                # Return error if 'diagnosis' is invalid
                return jsonify({'error': 'Invalid diagnosis provided. Choose from "Type I" or "Type II".'}), 400
            # Map 'gender' to numerical values
            if gender_input.lower() == 'male':
                gender = 0
            elif gender_input.lower() == 'female':
                gender = 1
            else:
                # Return error if 'gender' is invalid
                return jsonify({'error': 'Invalid gender provided. Choose from "male" or "female".'}), 400
        except ValueError as e:
            # Return error if input conversion fails
            return jsonify({'error': f'Invalid input: {e}'}), 400

        # Log the received input parameters for debugging
        logging.info(f"Received input - glucose_old: {glucose_old}, food_carbo: {food_carbo}, "
                     f"algorithm: {algorithm}, patient_id: {patient_id}, age: {age}, "
                     f"weight: {weight}, diagnosis: {diagnosis_input}, gender: {gender_input}, k: {k}")

        # Load and train models using data from k-nearest patients
        models = load_and_train_models(patient_id, age, weight, k)
        scaler_insulin = models['scaler']              # Scaler used for feature normalization
        patient_errors = models['patient_errors']      # Per-patient mean errors
        train_columns = models['train_columns']        # List of feature columns used for training

        # Prepare the input features for prediction
        input_features = np.array([[glucose_old, food_carbo, age, weight, diagnosis, gender]])
        input_features_scaled = scaler_insulin.transform(input_features)  # Scale the input features

        # Select the model based on the algorithm specified
        if algorithm == 'linear_regression':
            model = models['linear_regression']
            error_column = 'error_regr'
        elif algorithm == 'gradient_boosting':
            model = models['gradient_boosting']
            error_column = 'error_grad_boost'
        elif algorithm == 'random_forest':
            model = models['random_forest']
            error_column = 'error_rand_forest'
        else:
            # Return error if algorithm is invalid
            return jsonify({'error': 'Invalid algorithm selected. Choose from linear_regression, '
                                     'random_forest, or gradient_boosting.'}), 400

        # Make the prediction using the selected model
        predicted_insulin_dose = model.predict(input_features_scaled)[0]  # Predict insulin dose

        # Adjust the prediction using per-patient mean error if available
        mean_error = 0
        if patient_id in patient_errors.index:
            mean_error = patient_errors.loc[patient_id, error_column]
            predicted_insulin_dose += mean_error  # Adjust prediction
            logging.info(f"Adjusted prediction with mean error for patient {patient_id}: {mean_error}")
        else:
            logging.info(f"No mean error adjustment for patient {patient_id}")

        # Ensure the predicted insulin dose is within realistic bounds [0, 15]
        predicted_insulin_dose = max(0, min(predicted_insulin_dose, 15))

        # Round the predicted dose to two decimal places for readability
        predicted_insulin_dose = round(predicted_insulin_dose, 2)

        # Log the final predicted insulin dose
        logging.info(f"Predicted insulin dose for patient {patient_id}: {predicted_insulin_dose}")

        # Return the prediction as a JSON response
        return jsonify({'predicted_insulin_dose': predicted_insulin_dose})

    except Exception as e:
        # Log the error and return a 500 Internal Server Error response
        logging.error(f"Error in prediction: {e}")
        return jsonify({'error': 'An error occurred during prediction. Please try again later.'}), 500

if __name__ == '__main__':
    # Run the Flask app on localhost at port 5000 with debug mode enabled
    app.run(host='127.0.0.1', port=5000, debug=True)
