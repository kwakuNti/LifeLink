# LifeLink

**LifeLink** is an innovative web application designed to address the organ donation shortage in sub-Saharan Africa by implementing a sophisticated matching system that uses machine learning and blockchain technology. The platform aims to improve the efficiency of donor-recipient matching while ensuring the security and transparency of records.

## 🚀 Features

- **User Registration & Authentication**: Secure registration system for donors and recipients with email verification  
- **Advanced Matching Algorithm**: Utilizes machine learning (KMeans clustering, nearest neighbors) to efficiently match organ donors with recipients  
- **Dual Pipeline System**: Separate processing pipelines for kidney and liver transplant matching  
- **Blockchain Integration**: Uses Hyperledger Fabric for secure storage and retrieval of matching records  
- **Outcome Prediction**: Supervised learning models to predict transplant success rates  
- **User-Friendly Interface**: Simple and intuitive interface for users to navigate  
- **Comprehensive Testing**: Full CI/CD pipeline with automated testing  
- **Data Security**: Implements secure authentication and data protection measures  

## 🛠️ Technologies Used

### Frontend
- HTML, CSS, JavaScript  
- Responsive design for mobile and desktop  

### Backend
- **PHP 8.3**: Main backend logic and API endpoints  
- **MySQL 8**: Primary database for user data and matching records  
- **Python 3.11 (Flask)**: Machine learning API service  

### Machine Learning & Data Science
- **Python**: Model training and inference  
- **Flask**: ML API service  
- **Scikit-learn**: Clustering and supervised learning  
- **XGBoost**: Advanced gradient boosting models  
- **Neural Networks**: Multi-layer perceptron for complex pattern recognition  

### Blockchain
- **Hyperledger Fabric**: Secure ledger for matching records  
- **Chaincode**: Smart contracts for data integrity  

### DevOps & Testing
- **GitHub Actions**: Continuous Integration/Continuous Deployment  
- **PHPUnit**: PHP testing framework  
- **Composer**: PHP dependency management  
- **Docker**: Containerization for consistent environments  

## 📋 Prerequisites

Before setting up LifeLink, ensure you have the following installed:

- PHP 8.3 or higher  
- MySQL 8.0 or higher  
- Python 3.11  
- Composer  
- Node.js (for any frontend dependencies)  
- Docker (for blockchain setup)  
- Git  

## 🔧 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/kwakuNti/lifelink.git
cd lifelink
```

### 2. Backend Setup (PHP)
```bash
composer install
cp config/connection.php.example config/connection.php
# Edit config/connection.php with your database credentials
```

### 3. Database Setup
```bash
mysql -u root -p -e "CREATE DATABASE life;"
mysql -u root -p life < db/life.sql
mysql -u root -p -e "CREATE DATABASE life_test;"
mysql -u root -p life_test < db/life_test.sql
```

### 4. Python/Flask ML Service Setup
```bash
cd flask
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
pip install -r requirements.txt

export DB_HOST=localhost
export DB_USER=your_db_user
export DB_PASS=your_db_password
export DB_NAME=life
python app.py
```

### 5. Blockchain Setup (Optional)
```bash
cd blockchain
# Follow blockchain-setup.txt for detailed Hyperledger Fabric setup
```

### 6. Web Server Setup
```bash
php -S localhost:8000 -t public/
# Or place in your web server's document root and access via http://localhost/lifelink
```

## 🚀 Usage

### For Donors
1. **Register** and verify email  
2. **Complete Profile**: Enter medical info  
3. **Matching**: System includes you in the matching pool  
4. **Dashboard**: Monitor matches  

### For Recipients
1. **Register** via hospital  
2. **Medical Profile**: Fill detailed info  
3. **Matching Process**: ML-based compatibility check  
4. **View Matches**: Dashboard access  
5. **Follow-up**  

### For Medical Professionals
1. **Verification** of recipients  
2. **Match Review**  
3. **Outcome Tracking**  

## 🧪 Testing

### PHP Tests
```bash
./vendor/bin/phpunit
./vendor/bin/phpunit --coverage-html coverage-report
./vendor/bin/phpunit tests/Unit/
```

### Python Tests
```bash
cd flask
python -m pytest tests/
```

### CI/CD Pipeline
- PHP 8.3 and Python 3.11 setup  
- MySQL 8 container  
- Flask ML service startup  
- PHPUnit test execution  
- Coverage + artifact uploads  

## 🤖 Machine Learning Pipeline

### Kidney Matching Pipeline
- **Processing**: Clean and preprocess  
- **Feature Engineering**: Interaction terms, skew transforms  
- **Clustering**: KMeans  
- **Prediction**: Supervised model  
- **Matching**: Nearest neighbors  

### Liver Matching Pipeline
- **Features**: Liver-specific criteria  
- **Modeling**: XGBoost + Neural Nets  
- **Success Prediction**  
- **Risk Assessment**  

## 🔐 Security Features

- **Encryption**: Data in transit and at rest  
- **Blockchain Integrity**  
- **Access Control**  
- **Audit Trail**  
- **HIPAA Compliance**  

## 🤝 Contributing

### Steps
1. Fork repo  
2. Create feature branch:
   ```bash
   git checkout -b feature/YourFeatureName
   ```
3. Make changes + tests  
4. Commit:
   ```bash
   git commit -m "Add: Your descriptive commit message"
   ```
5. Push:
   ```bash
   git push origin feature/YourFeatureName
   ```
6. Open PR  

### Guidelines
- Clear commit messages  
- Unit tests required  
- Update documentation  
- Follow PSR-12 (PHP) and PEP 8 (Python)  

## 📊 Project Structure

```
LifeLink/
├── actions/              # GitHub Actions workflows
├── assets/              # Frontend assets (CSS, JS, images)
├── blockchain/          # Hyperledger Fabric setup and chaincode
├── config/              # Configuration files
├── db/                  # Database schemas and migrations
├── docs/                # Project documentation
├── flask/               # Python ML service
│   ├── app.py
│   ├── requirements.txt
│   └── venv/
├── includes/            # PHP include files
├── logs/                # Application logs
├── models/              # PHP model classes
├── public/              # Web server root
├── templates/           # PHP templates
├── tests/               # Test files
├── vendor/              # Composer dependencies
├── composer.json
├── phpunit.xml
└── README.md
```

## 📈 Performance & Scalability

- Indexed DB queries  
- Redis caching  
- Horizontally scalable architecture  
- Optimized ML algorithms  
- Efficient chaincode  



## 📞 Contact & Support

**Project Maintainer**: Clifford Nti Nkansah  
- **Email**: nkansahclifford@gmail.com  
- **GitHub**: [@kwakuNti](https://github.com/kwakuNti)  
- **LinkedIn**: [Connect with me](https://www.linkedin.com/in/cliffordnkansah/)  

## 🙏 Acknowledgments

- Medical professionals who provided domain expertise  
- Open-source communities  
- Research institutions  
- Beta testers and early adopters  

---

**LifeLink - Connecting Lives Through Technology** 💙
