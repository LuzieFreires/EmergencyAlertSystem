CREATE TABLE users (
    userID INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    contact_num VARCHAR(20) NOT NULL,
    location VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    userType ENUM('responder', 'resident') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE responders (
    responderID INT PRIMARY KEY,
    specialization VARCHAR(100) NOT NULL,
    availabilityStatus BOOLEAN DEFAULT true,
    FOREIGN KEY (responderID) REFERENCES users(userID)
        ON DELETE CASCADE
);

CREATE TABLE residents (
    residentID INT PRIMARY KEY,
    medicalCondition TEXT,
    FOREIGN KEY (residentID) REFERENCES users(userID)
        ON DELETE CASCADE
);

CREATE TABLE emergencies (
    emergencyID INT PRIMARY KEY AUTO_INCREMENT,
    residentID INT NOT NULL,
    responderID INT,
    status ENUM('pending', 'assigned', 'in_progress', 'resolved', 'cancelled') DEFAULT 'pending',
    responseTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    location VARCHAR(255) NOT NULL,
    type VARCHAR(100) NOT NULL,
    severityLevel ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (residentID) REFERENCES residents(residentID),
    FOREIGN KEY (responderID) REFERENCES responders(responderID)
);

CREATE TABLE alerts (
    alertID INT PRIMARY KEY AUTO_INCREMENT,
    message TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    priorityLevel ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    emergencyID INT,
    FOREIGN KEY (emergencyID) REFERENCES emergencies(emergencyID)
        ON DELETE SET NULL
);

CREATE TABLE sms_logs (
    smsID INT PRIMARY KEY AUTO_INCREMENT,
    recipientNumber VARCHAR(20) NOT NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'sent', 'delivered', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    alertID INT,
    FOREIGN KEY (alertID) REFERENCES alerts(alertID)
        ON DELETE SET NULL
);

CREATE TABLE alert_history (
    historyID INT PRIMARY KEY AUTO_INCREMENT,
    alertID INT,
    userID INT,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alertID) REFERENCES alerts(alertID)
        ON DELETE SET NULL,
    FOREIGN KEY (userID) REFERENCES users(userID)
        ON DELETE SET NULL
);

CREATE TABLE emergency_reports (
    reportID INT PRIMARY KEY AUTO_INCREMENT,
    emergencyID INT,
    report_content TEXT NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (emergencyID) REFERENCES emergencies(emergencyID)
        ON DELETE SET NULL
);

ALTER TABLE sms_logs
ADD COLUMN twilio_sid VARCHAR(34) UNIQUE,
ADD COLUMN twilio_status VARCHAR(20),
ADD COLUMN error_message TEXT;