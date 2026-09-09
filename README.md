# ChatGLUCO: Web-based Application for Diabetes Patient Management

Welcome to the official repository for **ChatGLUCO**! This project was developed as my undergraduate thesis at the **University of the Aegean, Department of Information and Communication Systems Engineering**. 

Managing diabetes is a daily challenge that requires constant monitoring and personalized care. The goal of this project is to provide a smart, web-based platform that not only acts as a digital diary for patients but also actively predicts future glucose levels and suggests insulin doses using Machine Learning algorithms.

---

## Project Objective
The main objective of ChatGLUCO is to reduce complications (hyperglycemia/hypoglycemia), improve the quality of life, and empower patient autonomy. It achieves this by bridging traditional clinical protocols—such as the Sliding Scale Protocol and Carbohydrate Calculation—with advanced Machine Learning models.

## Key Features
Compared to existing applications (like Diabetes:M, Sugarmate, etc.), ChatGLUCO stands out by offering a unique blend of AI predictions and deep data visualization. 

* **Smart Diary Management:** Track glucose before/after meals, carbohydrates, and insulin doses in an easy-to-use interface.
* **Machine Learning Predictions:** Predict future glucose levels and required insulin doses dynamically.
* **Clinical Protocol Integration:** Fallback to traditional methods (Sliding Scale + Carbo Calculation) when ML data is insufficient.
* **Food Database API:** Built-in search for food items to automatically calculate carbohydrate intake.
* **Interactive Charts:** Visualize data through Time in Range (TIR) and Glucose-Insulin Correlation charts.
* **Data Export:** Export diary records to XLS for physician review.

---

## System Architecture

To ensure scalability and clean code separation, the system is built on a **4-Tier Architecture**.

![System Architecture](image_c58384.jpg)

1. **Presentation Tier (Frontend):** Built with Laravel's Blade Templating Engine, Bootstrap, JavaScript/jQuery, and Chart.js for responsive, real-time data visualization.
2. **Business Logic Tier (Backend):** Powered by **PHP Laravel** (Controllers, Models, Web Routes). It handles user authentication, CRUD operations, database queries, and CSRF/XSS protection.
3. **Machine Learning Tier (API):** A standalone **Flask (Python) RESTful API**. It receives HTTP requests from Laravel, loads the training data, and returns JSON predictions.
4. **Data Tier:** A unified **MySQL** database serving both Laravel and the Flask API.

---

## Machine Learning Models

The core "brain" of the application lives in the Flask API. We trained and evaluated three different algorithms to find the best fit for our data:
* **Linear Regression:** Used as a baseline model for its simplicity and interpretability.
* **Random Forest:** An ensemble method utilizing multiple decision trees, optimized via Grid Search. It proved highly resistant to noise and outliers.
* **XGBoost (Extreme Gradient Boosting):** Built on the Gradient Boosting philosophy, this model learns from the errors of previous iterations, also optimized via Grid Search.

**Performance Highlights:** For glucose prediction, both Random Forest and XGBoost showed excellent accuracy, achieving a Mean Absolute Error (MAE) of ~31 mg/dL and keeping patients in the target range (TIR) 83-85% of the time.

---

## Data Visualization

A huge part of patient autonomy is understanding the data. ChatGLUCO provides several interactive charts generated via Chart.js:

### 1. Time in Range (TIR)
Highlights periods of hypoglycemia (under 80 mg/dL), normal levels (80-180 mg/dL), and hyperglycemia (above 180 mg/dL).
![Time in Range](image_c583a8.jpg)

### 2. Glucose-Insulin Correlation
Helps users and doctors identify patterns and understand how changes in insulin doses affect glucose levels over time.
![Glucose-Insulin Correlation](image_c583c1.png)

### 3. Future Glucose Trend Prediction
Uses XGBoost to forecast glucose levels for the next two days (morning, noon, evening) based on a sliding window of the patient's historical data.
![Future Glucose Trend](image_c583c6.png)

---

## Future Work
While the system successfully meets its primary goals, there is always room for improvement. Future updates may include:
* **Patient Clustering:** Training tailored models based on diabetes type, age, and weight groups.
* **Deep Learning:** Implementing LSTMs for more advanced time-series predictions.
* **Wearables Integration:** Syncing directly with Continuous Glucose Monitors (CGMs) and smartwatches for seamless data flow.
* **Automated Alerts:** Warning systems for sudden, dangerous glucose fluctuations.

---

## Author
**Athanasios Gkamplias**  
Thesis Supervisor: Symeonidis Panagiotis (Associate Professor) 
*University of the Aegean, February 2025*
